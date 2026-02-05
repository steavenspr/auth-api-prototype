# Schéma de la base de données

## Vue d'ensemble

La base de données contient actuellement une seule table: `users`.

Dans une future version, on pourrait ajouter:
- Table `password_resets` (pour la réinitialisation de mot de passe)
- Table `personal_access_tokens` (si on veut gérer plusieurs tokens par utilisateur)

Mais pour ce prototype, on reste simple avec juste la table users.

---

## Table: users

### Description

Stocke les informations des utilisateurs de l'application.

### Structure

| Colonne | Type | Longueur | Nullable | Default | Index | Description |
|---------|------|----------|----------|---------|-------|-------------|
| id | BIGINT UNSIGNED | - | NON | AUTO_INCREMENT | PRIMARY | Identifiant unique |
| name | VARCHAR | 255 | NON | - | - | Nom complet de l'utilisateur |
| email | VARCHAR | 255 | NON | - | UNIQUE | Adresse email (login) |
| password | VARCHAR | 255 | NON | - | - | Mot de passe hashé (bcrypt ou argon2) |
| email_verified_at | TIMESTAMP | - | OUI | NULL | - | Date de vérification de l'email |
| remember_token | VARCHAR | 100 | OUI | NULL | - | Token pour "Se souvenir de moi" |
| created_at | TIMESTAMP | - | OUI | NULL | - | Date de création du compte |
| updated_at | TIMESTAMP | - | OUI | NULL | - | Date de dernière modification |

### Contraintes

1. **PRIMARY KEY**
   - Colonne: `id`
   - Auto-incrémenté

2. **UNIQUE**
   - Colonne: `email`
   - Raison: un email ne peut être utilisé que par un seul compte

3. **NOT NULL**
   - Colonnes: `id`, `name`, `email`, `password`
   - Raison: ces champs sont obligatoires pour créer un compte

### Index

1. **Index primaire (PRIMARY)**
   - Colonne: `id`
   - Créé automatiquement avec la clé primaire

2. **Index unique**
   - Colonne: `email`
   - Permet de vérifier rapidement si un email existe déjà
   - Accélère les requêtes de type WHERE email = '...'

### Migrations Laravel

Voici le code de migration qui crée cette table:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter la migration.
     * Cette méthode est appelée quand on fait: php artisan migrate
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // Colonne id: clé primaire auto-incrémentée
            $table->id();
            
            // Colonne name: varchar(255), obligatoire
            $table->string('name');
            
            // Colonne email: varchar(255), obligatoire, unique
            $table->string('email')->unique();
            
            // Colonne email_verified_at: timestamp, nullable
            // Utilisée pour vérifier si l'utilisateur a confirmé son email
            $table->timestamp('email_verified_at')->nullable();
            
            // Colonne password: varchar(255), obligatoire
            // Stocke le hash du mot de passe (jamais le mot de passe en clair)
            $table->string('password');
            
            // Colonne remember_token: varchar(100), nullable
            // Utilisée pour la fonctionnalité "Se souvenir de moi"
            $table->rememberToken();
            
            // Colonnes created_at et updated_at: timestamps
            // Laravel les remplit automatiquement
            $table->timestamps();
        });
    }

    /**
     * Annuler la migration.
     * Cette méthode est appelée quand on fait: php artisan migrate:rollback
     */
    public function down(): void
    {
        // Supprimer complètement la table users
        Schema::dropIfExists('users');
    }
};
```

### Explication des types de données

1. **BIGINT UNSIGNED (id)**
   - Entier positif de 0 à 18 446 744 073 709 551 615
   - Permet d'avoir des millions d'utilisateurs sans problème
   - AUTO_INCREMENT: la base génère automatiquement la valeur

2. **VARCHAR(255)**
   - Chaîne de caractères de longueur variable
   - Maximum 255 caractères
   - Utilisé pour name, email, password

3. **VARCHAR(100) (remember_token)**
   - Plus court car c'est juste un token généré

4. **TIMESTAMP**
   - Date et heure au format: 2026-02-05 14:30:00
   - Utilise le fuseau horaire UTC
   - Laravel convertit automatiquement en Carbon (objet PHP manipulable)

### Exemple de données

```
+----+------------+---------------------+---------------------+----------+----------------+---------------------+---------------------+
| id | name       | email               | email_verified_at   | password | remember_token | created_at          | updated_at          |
+----+------------+---------------------+---------------------+----------+----------------+---------------------+---------------------+
| 1  | John Doe   | john@example.com    | NULL                | $2y$...  | abc123xyz      | 2026-02-05 10:30:00 | 2026-02-05 10:30:00 |
| 2  | Jane Smith | jane@example.com    | 2026-02-05 11:00:00 | $2y$...  | NULL           | 2026-02-05 11:00:00 | 2026-02-05 14:30:00 |
+----+------------+---------------------+---------------------+----------+----------------+---------------------+---------------------+
```

Remarques:
- La colonne `password` contient un hash (jamais le mot de passe en clair)
- `$2y$...` indique un hash bcrypt
- `email_verified_at` peut être NULL si l'utilisateur n'a pas encore vérifié son email
- `updated_at` change à chaque modification de l'utilisateur

---

## Sécurité

### 1. Mot de passe

Le mot de passe n'est JAMAIS stocké en clair dans la base de données.

On utilise le hachage:
- Bcrypt (par défaut dans Laravel)
- Ou Argon2 (plus sécurisé, configurable dans config/hashing.php)

Exemple de hash bcrypt:
```
$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
```

Composition:
- `$2y$`: algorithme bcrypt
- `10`: coût (nombre d'itérations = 2^10 = 1024)
- Le reste: sel + hash

Même si un attaquant vole la base de données, il ne peut pas retrouver les mots de passe.

### 2. Email unique

La contrainte UNIQUE sur l'email empêche:
- La création de doublons
- Les tentatives d'inscription multiple avec le même email

Laravel vérifie automatiquement lors de la validation avec la règle `unique:users,email`.

### 3. Index sur email

L'index UNIQUE sur email a deux avantages:
- Sécurité: vérifie l'unicité au niveau de la base
- Performance: accélère les requêtes de connexion (WHERE email = '...')

---

## Évolutions futures possibles

Pour une application en production, on pourrait ajouter:

1. **Colonne `role`**
   - Type: ENUM('admin', 'user') ou VARCHAR(50)
   - Pour différencier les administrateurs des utilisateurs simples

2. **Colonne `status`**
   - Type: ENUM('active', 'inactive', 'banned')
   - Pour désactiver un compte sans le supprimer

3. **Colonne `last_login_at`**
   - Type: TIMESTAMP
   - Pour suivre la dernière connexion

4. **Table `password_resets`**
   - Pour gérer la réinitialisation de mot de passe
   - Stocke un token temporaire

5. **Soft deletes**
   - Ajouter une colonne `deleted_at` (TIMESTAMP)
   - Permet de "supprimer" sans vraiment supprimer (archivage)

Mais pour ce prototype, on garde la structure simple pour se concentrer sur l'apprentissage des concepts de base.

---

## Commandes utiles

### Créer la table

```bash
php artisan migrate
```

### Supprimer la table (rollback)

```bash
php artisan migrate:rollback
```

### Remettre à zéro toute la base

```bash
php artisan migrate:fresh
```

### Voir le statut des migrations

```bash
php artisan migrate:status
```

### Remplir avec des données de test

```bash
php artisan db:seed
```

---

## Notes techniques

1. Laravel utilise Eloquent ORM pour communiquer avec la base
2. On ne manipule jamais directement le SQL
3. Les timestamps (created_at, updated_at) sont gérés automatiquement
4. Le Model User cache automatiquement le champ password dans les réponses JSON (attribut $hidden)
5. Laravel gère automatiquement les conversions de timezone (UTC en base, timezone locale dans l'application)
