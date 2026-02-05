# Auth API Prototype

Projet personnel d'apprentissage pour maîtriser la création d'une API d'authentification avec Laravel et JWT.

## Description

API REST d'authentification avec gestion CRUD des utilisateurs. Ce projet est un prototype pour comprendre l'architecture Laravel avant de travailler sur le projet Moustass.

L'objectif est de comprendre:
- L'architecture MVC avec séparation des responsabilités
- L'authentification par token JWT
- La validation des données avec Form Requests
- Les Services pour la logique métier
- Les tests unitaires et d'intégration
- La gestion des erreurs avec try-catch
- La documentation API avec Swagger

## Technologies utilisées

- PHP 8.x
- Laravel 11.x
- MySQL 8.x
- JWT (tymon/jwt-auth)
- Swagger (darkaonline/l5-swagger)
- PHPUnit pour les tests

## Prérequis

- PHP >= 8.xx
- Composer
- MySQL >= 8.0
- Git

## Installation

### 1. Cloner le repository

```bash
git clone https://github.com/votre-username/auth-api-prototype.git
cd auth-api-prototype
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configuration de l'environnement

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configurer la base de données

Créer la base de données MySQL:

```sql
CREATE DATABASE auth_prototype;
```

Modifier le fichier .env:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=auth_prototype
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
```

### 5. Exécuter les migrations

```bash
php artisan migrate
```

### 6. Configurer JWT

```bash
php artisan jwt:secret
```

### 7. Lancer le serveur

```bash
php artisan serve
```

L'API sera accessible sur: http://localhost:8000

## Utilisation

### Tester l'API

Utiliser Postman ou Insomnia pour tester les endpoints.

Voir le fichier API_ENDPOINTS.md pour la liste complète des routes disponibles.

### Accéder à la documentation Swagger

Une fois le projet lancé:

```
http://localhost:8000/api/documentation
```

## Tests

### Exécuter tous les tests

```bash
php artisan test
```

### Exécuter les tests unitaires uniquement

```bash
php artisan test --testsuite=Unit
```

### Exécuter les tests d'intégration uniquement

```bash
php artisan test --testsuite=Feature
```

### Exécuter un test spécifique

```bash
php artisan test --filter=AuthServiceTest
```

## Structure du projet

Voir le fichier ARCHITECTURE.md pour comprendre l'organisation du code.

## Contribuer

Ce projet est un prototype d'apprentissage personnel. Les contributions ne sont pas acceptées pour le moment.

## Auteur

S.R Steavens

## Licence

Ce projet est un prototype éducatif sans licence particulière.
