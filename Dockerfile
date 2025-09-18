```dockerfile
# Utiliser une image de base alpine pour réduire la taille de l'image
FROM php:8.1-fpm-alpine AS builder

# Installer les extensions PHP nécessaires.  Remplacer par vos extensions réelles.
RUN apk add --no-cache \
    git \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copier uniquement le composer.json et composer.lock pour optimiser le cache
COPY composer.json composer.lock /app/

# Installer les dépendances
WORKDIR /app
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Copier le reste du code source
COPY . /app

# Exécuter des commandes de build spécifiques à l'application (si nécessaire)
# RUN php artisan optimize

# Créer un utilisateur non-root
RUN addgroup --system --gid 1001 www-data && adduser --system --uid 1001 --gid 1001 www-data

# Changer le propriétaire des fichiers de l'application
RUN chown -R www-data:www-data /app

# Image finale pour la production
FROM php:8.1-fpm-alpine

# Copier uniquement le nécessaire pour une petite image
COPY --from=builder /app/ /app
COPY --from=builder /usr/local/bin/composer /usr/local/bin/composer

# Définir l'utilisateur
USER www-data

# Exposer le port PHP-FPM
EXPOSE 9000

# Définir le répertoire de travail
WORKDIR /app

# Définir le répertoire web
ENV DOCUMENT_ROOT=/app/public

# Démarrer PHP-FPM
CMD ["php-fpm"]
```

**Explications et bonnes pratiques:**

* **Deux étapes:**  L'utilisation de deux étapes (builder et production) permet d'optimiser le cache.  La couche `builder` effectue les tâches gourmandes en temps (installation des dépendances) et la couche `production` copie uniquement le résultat.  Cela évite de reconstruire l'image à chaque modification du code source si le `composer.json` et `composer.lock` n'ont pas changé.

* **Alpine Linux:**  L'utilisation d'Alpine Linux réduit considérablement la taille de l'image.

* **`--no-cache`:**  Utilisé avec `apk add` pour éviter de stocker des fichiers inutiles dans le cache.

* **Extensions PHP:**  N'installez que les extensions PHP strictement nécessaires.

* **Composer:**  Installer Composer et gérer les dépendances.  `--no-interaction`, `--optimize-autoloader`, et `--no-dev` optimisent l'installation.

* **Utilisateur non-root:**  L'utilisation de l'utilisateur `www-data` améliore la sécurité.

* **`chown`:**  Change le propriétaire des fichiers pour que l'utilisateur `www-data` puisse les accéder.

* **`EXPOSE`:**  Expose le port 9000 (port par défaut de PHP-FPM).  Adaptez si nécessaire.

* **`DOCUMENT_ROOT`:** Spécifie le répertoire web de votre application.  Adaptez en fonction de votre structure de projet.

* **`CMD ["php-fpm"]`:** Lance PHP-FPM au démarrage du conteneur.

**Avant d'utiliser ce Dockerfile:**

* **Remplacez les extensions PHP par celles dont votre application a besoin.**
* **Assurez-vous que votre répertoire `public` est correctement configuré.**
* **Adaptez le `WORKDIR` et `DOCUMENT_ROOT` si nécessaire.**
* **Ajoutez des commandes de build spécifiques à votre application si nécessaire (ex: `php artisan migrate`).**
* **Considérez l'ajout d'un processus de supervision comme `supervisord` pour une meilleure gestion des processus.**
* **En production, il est recommandé d'utiliser un reverse proxy comme Nginx ou Apache devant PHP-FPM pour gérer les requêtes HTTP.**


Ce Dockerfile est un point de départ.  Vous devrez peut-être l'adapter en fonction des besoins spécifiques de votre application `task-manager-dna-media`.
