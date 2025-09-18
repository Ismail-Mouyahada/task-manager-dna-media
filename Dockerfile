```dockerfile
# Utilisez une image de base minimale et optimisée pour PHP
FROM php:8.1-fpm-bullseye

# Définir l'utilisateur et le groupe
ARG UID=1000
ARG GID=1000
RUN groupadd -g ${GID} -o www-data && useradd -m -u ${UID} -g ${GID} -s /bin/bash www-data

# Copier uniquement le fichier composer.json pour installer les dépendances
COPY composer.json composer.lock ./
RUN --mount=type=cache,id=composer \
    composer install --no-interaction --optimize-autoloader --no-dev \
    && rm -rf /var/www/html/vendor/composer

# Copier le reste du code source
COPY . /var/www/html

# Installer les extensions PHP nécessaires (ajuster selon vos besoins)
RUN docker-php-ext-install pdo_mysql mbstring

# Définir le propriétaire des fichiers et des dossiers
RUN chown -R www-data:www-data /var/www/html

# Activer les extensions opcache
RUN echo "zend_extension=/usr/lib/php/20220902/opcache" >> /usr/local/etc/php/conf.d/docker-opcache.ini

# Définir l'utilisateur et le groupe pour exécuter PHP-FPM
USER www-data

# Exposer le port nécessaire pour PHP-FPM
EXPOSE 9000

# Copier un script d'entrée pour exécuter php-fpm en arrière-plan
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Définir le point d'entrée
ENTRYPOINT ["/entrypoint.sh"]
```

**entrypoint.sh:**

```bash
#!/bin/bash

# Exécuter php-fpm en arrière-plan
php-fpm --nodaemonize --fpm-config /usr/local/etc/php-fpm.conf &

# Attendre que php-fpm soit démarré
while ! curl -f http://localhost:9000; do
  sleep 1
done

# Garder le conteneur en marche
tail -f /dev/null
```


**Explications des optimisations:**

* **Image de base minimale:**  `php:8.1-fpm-bullseye` est une image légère et officielle.  `bullseye` est une version Debian stable.
* **Optimisation des couches:** Le `Dockerfile` est conçu pour minimiser le nombre de couches et maximiser le réutilisation du cache.  Les dépendances Composer sont installées séparément avant le reste du code.
* **Sécurité:** L'utilisation d'un utilisateur non-root (`www-data`) améliore la sécurité.  Les permissions sont correctement définies.
* **Ports:** Le port 9000 (port par défaut de PHP-FPM) est exposé.
* **Utilisateur non-root:**  L'utilisateur `www-data` est utilisé pour exécuter l'application.
* **Production:** L'utilisation d' `--optimize-autoloader` lors de l'installation de Composer améliore les performances.  Opcache est activé pour une meilleure performance.  Le script `entrypoint.sh` assure un démarrage correct et robuste de php-fpm.

**Avant de construire l'image:**

* **Assurez-vous d'avoir un fichier `composer.json` et `composer.lock` dans votre répertoire.**
* **Ajustez les extensions PHP (`docker-php-ext-install`) selon les besoins de votre application.**
* **Si vous utilisez une base de données, vous devrez configurer la connexion dans votre code.**  Ce Dockerfile ne gère pas la configuration de la base de données.  Vous devrez probablement utiliser un autre conteneur pour votre base de données (par exemple, MySQL ou PostgreSQL) et lier les deux conteneurs.


Ce Dockerfile fournit une base solide pour votre application.  N'hésitez pas à l'adapter à vos besoins spécifiques.  N'oubliez pas de construire l'image avec `docker build -t task-manager-dna-media .` et de la lancer avec `docker run -p 80:9000 task-manager-dna-media`.  (Vous devrez peut-être adapter le port 80 selon vos besoins).
