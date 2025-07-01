# Documentation Complète du Projet Task Manager
![alt text](image-3.png)
![alt text](image.png)

![alt text](image-1.png)

![alt text](image-2.png)

## Table des Matières
1. [Introduction](#introduction)
2. [Architecture du Projet](#architecture-du-projet)
3. [Installation et Configuration](#installation-et-configuration)
4. [Fonctionnalités](#fonctionnalités)
5. [API Documentation](#api-documentation)
6. [Tests](#tests)
7. [Déploiement](#déploiement)
8. [Bonnes Pratiques](#bonnes-pratiques)
9. [Améliorations Possibles](#améliorations-possibles)

## Introduction
Task Manager est une application web développée avec Symfony 7 ce projet réalisée dans le cadre d'un test technique chez NDA-Media Co. permettant la gestion collaborative de tâches entre utilisateurs. Elle répond aux exigences du test technique avec trois niveaux de fonctionnalités progressives.

**Objectifs principaux**:
- Permettre aux utilisateurs de créer et assigner des tâches
- Fournir un système d'authentification sécurisé
- Offrir une API RESTful pour l'intégration avec d'autres systèmes
- Garantir une expérience utilisateur intuitive

## Architecture du Projet

### Structure des Répertoires
```
src/
├── Controller/       # Contrôleurs MVC
├── Entity/           # Entités Doctrine
├── Form/             # Formulaires Symfony
├── Repository/       # Repository personnalisés
├── Service/          # Services métier
└── Kernel.php
```

### Stack Technologique
- **Backend**: Symfony 7
- **Base de données**: MySQL
- **Templating**: Twig
- **API**: API Platform
- **Authentification**: Symfony Security
- **Tests**: PHPUnit

### Diagramme d'Architecture
```
[Client] → [Symfony Application] → [MySQL Database]
                   ↓
            [Mailer Service]
                   ↓
             [SMTP Server]
```

## Installation et Configuration

### Prérequis
- PHP 8.2+
- Composer 2.5+
- MySQL 8.0+
- Node.js (pour les assets optionnels)

### Étapes d'Installation
1. Cloner le dépôt
   ```bash
   git clone https://github.com/votre-repo/task-manager.git
   cd task-manager
   ```

2. Installer les dépendances
   ```bash
   composer install
   ```

3. Configurer l'environnement
   ```bash
   cp .env .env.local
   ```
   Modifier les variables dans `.env.local`:
   ```ini
   
   DATABASE_URL="mysql://user:password@127.0.0.1:3306/task_manager?serverVersion=8.0"
   MAILER_DSN=smtp://user:pass@smtp.example.com:port
   ```
   ```yaml
   docker run --name nda-media-db -e MYSQL_ROOT_PASSWORD=ZXgXW8IVJBGIpq3H1Sg2 -e MYSQL_USER=user_01  -e MYSQL_PASSWORD=XNz0UdXDgCp71TCGuKUb -e MYSQL_DATABASE=taskmanager_db -p 3306:3306 -d mysql:8.0
   ```

4. Initialiser la base de données
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   php bin/console doctrine:fixtures:load
   ```

5. Lancer le serveur
   ```bash
   symfony serve:start
   ```

### Configuration Optionnelle
Pour activer les notifications :
```yaml
# config/packages/notifier.yaml
framework:
    notifier:
        channel_policy:
            urgent: ['email']
            high: ['email']
            medium: ['email']
            low: ['email']
```

## Fonctionnalités

### Niveau 1 : Les Bases
1. **Gestion des Utilisateurs**
   - Entité User avec :
     - Email (unique)
     - Mot de passe chiffré
     - Prénom/Nom
     - Rôles (ROLE_USER, ROLE_ADMIN)

2. **Gestion des Tâches**
   - Entité Task avec :
     - Titre et description
     - Statut (pending, in_progress, completed, cancelled)
     - Priorité (low, medium, high, urgent)
     - Dates (création, échéance, complétion)
     - Relations ManyToOne avec User

3. **Interface Basique**
   - Liste des tâches
   - Détail d'une tâche
   - API JSON

### Niveau 2 : Intégration Métier
1. **Authentification**
   - Inscription avec validation
   - Connexion/Déconnexion
   - Remember me
   - Restrictions d'accès

2. **Workflow des Tâches**
   ```mermaid
   graph TD
     A[Création] --> B[En Attente]
     B --> C[En Cours]
     C --> D[Terminée]
     B --> E[Annulée]
     C --> E
   ```

3. **Filtres et Tris**
   - Par statut
   - Par priorité
   - Par date d'échéance
   - Par utilisateur assigné

### Niveau 3 : Fonctionnalités Avancées
1. **Notifications**
   - Email lors de l'assignation
   - Template HTML responsive

2. **API REST**
   - CRUD complet
   - Sécurité JWT
   - Documentation Swagger

3. **Tests Automatisés**
   - Unitaires (entités, services)
   - Fonctionnels (contrôleurs)

## API Documentation

### Points d'Accès Principaux

#### Tâches
- `GET /api/tasks` - Liste des tâches
- `POST /api/tasks` - Créer une tâche
- `GET /api/tasks/{id}` - Détail d'une tâche
- `PUT /api/tasks/{id}` - Mettre à jour une tâche
- `DELETE /api/tasks/{id}` - Supprimer une tâche

#### Utilisateurs
- `POST /api/users` - Créer un compte
- `GET /api/users/{id}` - Profil utilisateur
- `PATCH /api/users/{id}` - Mettre à jour son profil

### Exemple de Requête
```bash
curl -X GET "http://localhost:8000/api/tasks" \
  -H "Authorization: Bearer votre_token_jwt"
```

### Schémas

#### Tâche
```json
{
  "id": 1,
  "title": "Révision du code",
  "description": "Revue complète du code avant livraison",
  "status": "in_progress",
  "priority": "high",
  "dueDate": "2023-12-15T14:30:00+00:00",
  "createdBy": {
    "id": 1,
    "email": "admin@example.com",
    "fullName": "Admin User"
  },
  "assignedTo": {
    "id": 2,
    "email": "dev@example.com",
    "fullName": "Developer Name"
  }
}
```

## Tests

### Stratégie de Test
- **Couverture**:
  - Entités: 100%
  - Services: 85%
  - Contrôleurs: 70%

### Exécution des Tests
```bash
php bin/phpunit --coverage-html var/coverage
```

### Exemple de Test
```php
public function testTaskCreation(): void
{
    $task = new Task();
    $task->setTitle('Test Task');
    $this->assertSame('Test Task', $task->getTitle());
}
```

## Déploiement

### Environnement de Production
1. Configurer le fichier `.env.prod`
2. Optimiser l'autoloader
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
3. Créer la base de données
   ```bash
   php bin/console doctrine:database:create --env=prod
   php bin/console doctrine:migrations:migrate --env=prod
   ```

### Serveur Web
Configuration Nginx recommandée :
```nginx
server {
    server_name taskmanager.example.com;
    root /var/www/task-manager/public;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        internal;
    }

    location ~ \.php$ {
        return 404;
    }

    error_log /var/log/nginx/task_manager_error.log;
    access_log /var/log/nginx/task_manager_access.log;
}
```

## Bonnes Pratiques

### Développement
1. **Commit Atomique** : Un commit par fonctionnalité/bugfix
2. **Conventions de Code** :
   - PSR-12 pour PHP
   - BEM pour le CSS
3. **Revues de Code** : Validation par un pair obligatoire

### Sécurité
- Validation des entrées utilisateur
- Protection CSRF sur les formulaires
- Hashage bcrypt pour les mots de passe
- Limitation des tentatives de connexion

### Performance
- Cache HTTP
- Optimisation des requêtes Doctrine
- Lazy loading des relations

## Améliorations Possibles

### Court Terme
- [ ] Tableau de bord avec indicateurs
- [ ] Export PDF des tâches
- [ ] Recherche full-text

### Moyen Terme
- [ ] Intégration calendrier
- [ ] Chat interne
- [ ] Synchronisation mobile

### Long Terme
- [ ] Workflow personnalisable
- [ ] Intégration OAuth2
- [ ] Analyse de charge

---

Cette documentation couvre l'ensemble des aspects techniques et fonctionnels du projet Task Manager. Pour toute question complémentaire, veuillez consulter le dépôt GitHub ou contacter l'équipe de développement.
