```dockerfile
# Utilisez une image de base minimale et optimis�e pour PHP
FROM php:8.1-fpm-bullseye

# D�finir l'utilisateur et le groupe
ARG UID=1000
ARG GID=1000
RUN groupadd -g ${GID} -o www-data && useradd -m -u ${UID} -g ${GID} -s /bin/bash www-data

# Copier uniquement le fichier composer.json pour installer les d�pendances
COPY composer.json composer.lock ./
RUN --mount=type=cache,id=composer \
    composer install --no-interaction --optimize-autoloader --no-dev \
    && rm -rf /var/www/html/vendor/composer

# Copier le reste du code source
COPY . /var/www/html

# Installer les extensions PHP n�cessaires (ajuster selon vos besoins)
RUN docker-php-ext-install pdo_mysql mbstring

# D�finir le propri�taire des fichiers et des dossiers
RUN chown -R www-data:www-data /var/www/html

# Activer les extensions opcache
RUN echo "zend_extension=/usr/lib/php/20220902/opcache" >> /usr/local/etc/php/conf.d/docker-opcache.ini

# D�finir l'utilisateur et le groupe pour ex�cuter PHP-FPM
USER www-data

# Exposer le port n�cessaire pour PHP-FPM
EXPOSE 9000

# Copier un script d'entr�e pour ex�cuter php-fpm en arri�re-plan
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# D�finir le point d'entr�e
ENTRYPOINT ["/entrypoint.sh"]
```

**entrypoint.sh:**

```bash
#!/bin/bash

# Ex�cuter php-fpm en arri�re-plan
php-fpm --nodaemonize --fpm-config /usr/local/etc/php-fpm.conf &

# Attendre que php-fpm soit d�marr�
while ! curl -f http://localhost:9000; do
  sleep 1
done

# Garder le conteneur en marche
tail -f /dev/null
```


**Explications des optimisations:**

* **Image de base minimale:**  `php:8.1-fpm-bullseye` est une image l�g�re et officielle.  `bullseye` est une version Debian stable.
* **Optimisation des couches:** Le `Dockerfile` est con�u pour minimiser le nombre de couches et maximiser le r�utilisation du cache.  Les d�pendances Composer sont install�es s�par�ment avant le reste du code.
* **S�curit�:** L'utilisation d'un utilisateur non-root (`www-data`) am�liore la s�curit�.  Les permissions sont correctement d�finies.
* **Ports:** Le port 9000 (port par d�faut de PHP-FPM) est expos�.
* **Utilisateur non-root:**  L'utilisateur `www-data` est utilis� pour ex�cuter l'application.
* **Production:** L'utilisation d' `--optimize-autoloader` lors de l'installation de Composer am�liore les performances.  Opcache est activ� pour une meilleure performance.  Le script `entrypoint.sh` assure un d�marrage correct et robuste de php-fpm.

**Avant de construire l'image:**

* **Assurez-vous d'avoir un fichier `composer.json` et `composer.lock` dans votre r�pertoire.**
* **Ajustez les extensions PHP (`docker-php-ext-install`) selon les besoins de votre application.**
* **Si vous utilisez une base de donn�es, vous devrez configurer la connexion dans votre code.**  Ce Dockerfile ne g�re pas la configuration de la base de donn�es.  Vous devrez probablement utiliser un autre conteneur pour votre base de donn�es (par exemple, MySQL ou PostgreSQL) et lier les deux conteneurs.


Ce Dockerfile fournit une base solide pour votre application.  N'h�sitez pas � l'adapter � vos besoins sp�cifiques.  N'oubliez pas de construire l'image avec `docker build -t task-manager-dna-media .` et de la lancer avec `docker run -p 80:9000 task-manager-dna-media`.  (Vous devrez peut-�tre adapter le port 80 selon vos besoins).
