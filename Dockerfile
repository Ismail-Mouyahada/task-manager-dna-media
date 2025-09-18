```dockerfile
# Utiliser une image de base alpine pour r�duire la taille de l'image
FROM php:8.1-fpm-alpine AS builder

# Installer les extensions PHP n�cessaires.  Remplacer par vos extensions r�elles.
RUN apk add --no-cache \
    git \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copier uniquement le composer.json et composer.lock pour optimiser le cache
COPY composer.json composer.lock /app/

# Installer les d�pendances
WORKDIR /app
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Copier le reste du code source
COPY . /app

# Ex�cuter des commandes de build sp�cifiques � l'application (si n�cessaire)
# RUN php artisan optimize

# Cr�er un utilisateur non-root
RUN addgroup --system --gid 1001 www-data && adduser --system --uid 1001 --gid 1001 www-data

# Changer le propri�taire des fichiers de l'application
RUN chown -R www-data:www-data /app

# Image finale pour la production
FROM php:8.1-fpm-alpine

# Copier uniquement le n�cessaire pour une petite image
COPY --from=builder /app/ /app
COPY --from=builder /usr/local/bin/composer /usr/local/bin/composer

# D�finir l'utilisateur
USER www-data

# Exposer le port PHP-FPM
EXPOSE 9000

# D�finir le r�pertoire de travail
WORKDIR /app

# D�finir le r�pertoire web
ENV DOCUMENT_ROOT=/app/public

# D�marrer PHP-FPM
CMD ["php-fpm"]
```

**Explications et bonnes pratiques:**

* **Deux �tapes:**  L'utilisation de deux �tapes (builder et production) permet d'optimiser le cache.  La couche `builder` effectue les t�ches gourmandes en temps (installation des d�pendances) et la couche `production` copie uniquement le r�sultat.  Cela �vite de reconstruire l'image � chaque modification du code source si le `composer.json` et `composer.lock` n'ont pas chang�.

* **Alpine Linux:**  L'utilisation d'Alpine Linux r�duit consid�rablement la taille de l'image.

* **`--no-cache`:**  Utilis� avec `apk add` pour �viter de stocker des fichiers inutiles dans le cache.

* **Extensions PHP:**  N'installez que les extensions PHP strictement n�cessaires.

* **Composer:**  Installer Composer et g�rer les d�pendances.  `--no-interaction`, `--optimize-autoloader`, et `--no-dev` optimisent l'installation.

* **Utilisateur non-root:**  L'utilisation de l'utilisateur `www-data` am�liore la s�curit�.

* **`chown`:**  Change le propri�taire des fichiers pour que l'utilisateur `www-data` puisse les acc�der.

* **`EXPOSE`:**  Expose le port 9000 (port par d�faut de PHP-FPM).  Adaptez si n�cessaire.

* **`DOCUMENT_ROOT`:** Sp�cifie le r�pertoire web de votre application.  Adaptez en fonction de votre structure de projet.

* **`CMD ["php-fpm"]`:** Lance PHP-FPM au d�marrage du conteneur.

**Avant d'utiliser ce Dockerfile:**

* **Remplacez les extensions PHP par celles dont votre application a besoin.**
* **Assurez-vous que votre r�pertoire `public` est correctement configur�.**
* **Adaptez le `WORKDIR` et `DOCUMENT_ROOT` si n�cessaire.**
* **Ajoutez des commandes de build sp�cifiques � votre application si n�cessaire (ex: `php artisan migrate`).**
* **Consid�rez l'ajout d'un processus de supervision comme `supervisord` pour une meilleure gestion des processus.**
* **En production, il est recommand� d'utiliser un reverse proxy comme Nginx ou Apache devant PHP-FPM pour g�rer les requ�tes HTTP.**


Ce Dockerfile est un point de d�part.  Vous devrez peut-�tre l'adapter en fonction des besoins sp�cifiques de votre application `task-manager-dna-media`.
