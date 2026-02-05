# Documentation des endpoints API

Base URL: `http://localhost:8000/api`

## Table des matières

1. [Authentification](#authentification)
2. [Gestion des utilisateurs](#gestion-des-utilisateurs)
3. [Codes de réponse HTTP](#codes-de-réponse-http)

---

## Authentification

### 1. Inscription (Register)

**Endpoint:** `POST /auth/register`

**Description:** Créer un nouveau compte utilisateur.

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePass123!@#"
}
```

**Validation:**
- `name`: requis, string, min 2 caractères, max 255 caractères
- `email`: requis, email valide, unique dans la base
- `password`: requis, min 12 caractères, doit contenir:
  - Au moins 1 majuscule
  - Au moins 1 minuscule
  - Au moins 1 chiffre
  - Au moins 1 caractère spécial

**Réponse succès (201):**
```json
{
  "message": "Utilisateur créé avec succès",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2026-02-05T10:30:00.000000Z",
    "updated_at": "2026-02-05T10:30:00.000000Z"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

**Réponse erreur validation (422):**
```json
{
  "message": "Les données fournies sont invalides",
  "errors": {
    "email": [
      "Ce email est déjà utilisé"
    ],
    "password": [
      "Le mot de passe doit contenir au moins 12 caractères"
    ]
  }
}
```

**Réponse erreur serveur (500):**
```json
{
  "error": "Une erreur est survenue lors de la création du compte"
}
```

---

### 2. Connexion (Login)

**Endpoint:** `POST /auth/login`

**Description:** Se connecter avec un compte existant.

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "email": "john@example.com",
  "password": "SecurePass123!@#"
}
```

**Validation:**
- `email`: requis, email valide
- `password`: requis

**Réponse succès (200):**
```json
{
  "message": "Connexion réussie",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2026-02-05T10:30:00.000000Z",
    "updated_at": "2026-02-05T10:30:00.000000Z"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

**Réponse erreur identifiants invalides (401):**
```json
{
  "error": "Email ou mot de passe incorrect"
}
```

**Réponse erreur validation (422):**
```json
{
  "message": "Les données fournies sont invalides",
  "errors": {
    "email": [
      "Le champ email doit être une adresse email valide"
    ]
  }
}
```

---

### 3. Profil utilisateur (Me)

**Endpoint:** `GET /auth/me`

**Description:** Récupérer les informations de l'utilisateur connecté.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Réponse succès (200):**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2026-02-05T10:30:00.000000Z",
    "updated_at": "2026-02-05T10:30:00.000000Z"
  }
}
```

**Réponse token manquant ou invalide (401):**
```json
{
  "error": "Token invalide ou manquant"
}
```

---

### 4. Déconnexion (Logout)

**Endpoint:** `POST /auth/logout`

**Description:** Invalider le token JWT de l'utilisateur connecté.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Réponse succès (200):**
```json
{
  "message": "Déconnexion réussie"
}
```

**Réponse token manquant ou invalide (401):**
```json
{
  "error": "Token invalide ou manquant"
}
```

---

## Gestion des utilisateurs

Toutes ces routes nécessitent un token JWT valide.

### 5. Liste des utilisateurs

**Endpoint:** `GET /users`

**Description:** Récupérer la liste de tous les utilisateurs.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Paramètres query (optionnels):**
- `page`: numéro de la page (pagination)
- `per_page`: nombre d'éléments par page (max 100, défaut 15)

**Exemple:** `GET /users?page=1&per_page=20`

**Réponse succès (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "created_at": "2026-02-05T10:30:00.000000Z",
      "updated_at": "2026-02-05T10:30:00.000000Z"
    },
    {
      "id": 2,
      "name": "Jane Smith",
      "email": "jane@example.com",
      "created_at": "2026-02-05T11:00:00.000000Z",
      "updated_at": "2026-02-05T11:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 2,
    "last_page": 1
  }
}
```

---

### 6. Afficher un utilisateur

**Endpoint:** `GET /users/{id}`

**Description:** Récupérer les informations d'un utilisateur spécifique.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Réponse succès (200):**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2026-02-05T10:30:00.000000Z",
    "updated_at": "2026-02-05T10:30:00.000000Z"
  }
}
```

**Réponse utilisateur non trouvé (404):**
```json
{
  "error": "Utilisateur non trouvé"
}
```

---

### 7. Créer un utilisateur

**Endpoint:** `POST /users`

**Description:** Créer un nouvel utilisateur.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "name": "Alice Martin",
  "email": "alice@example.com",
  "password": "SecurePass456!@#"
}
```

**Validation:**
- Identique à l'endpoint /auth/register

**Réponse succès (201):**
```json
{
  "message": "Utilisateur créé avec succès",
  "user": {
    "id": 3,
    "name": "Alice Martin",
    "email": "alice@example.com",
    "created_at": "2026-02-05T12:00:00.000000Z",
    "updated_at": "2026-02-05T12:00:00.000000Z"
  }
}
```

---

### 8. Modifier un utilisateur

**Endpoint:** `PUT /users/{id}`

**Description:** Modifier les informations d'un utilisateur existant.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "name": "John Updated",
  "email": "john.updated@example.com"
}
```

**Validation:**
- `name`: optionnel, string, min 2 caractères, max 255 caractères
- `email`: optionnel, email valide, unique (sauf pour l'utilisateur actuel)
- Le mot de passe ne peut PAS être modifié via cette route

**Réponse succès (200):**
```json
{
  "message": "Utilisateur modifié avec succès",
  "user": {
    "id": 1,
    "name": "John Updated",
    "email": "john.updated@example.com",
    "created_at": "2026-02-05T10:30:00.000000Z",
    "updated_at": "2026-02-05T14:00:00.000000Z"
  }
}
```

**Réponse utilisateur non trouvé (404):**
```json
{
  "error": "Utilisateur non trouvé"
}
```

---

### 9. Supprimer un utilisateur

**Endpoint:** `DELETE /users/{id}`

**Description:** Supprimer un utilisateur de manière définitive.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Réponse succès (200):**
```json
{
  "message": "Utilisateur supprimé avec succès"
}
```

**Réponse utilisateur non trouvé (404):**
```json
{
  "error": "Utilisateur non trouvé"
}
```

---

## Codes de réponse HTTP

### Codes de succès

| Code | Signification | Utilisation |
|------|---------------|-------------|
| 200 | OK | Requête réussie (GET, PUT, DELETE) |
| 201 | Created | Ressource créée avec succès (POST) |

### Codes d'erreur client

| Code | Signification | Utilisation |
|------|---------------|-------------|
| 400 | Bad Request | Requête malformée |
| 401 | Unauthorized | Token manquant, invalide ou expiré |
| 404 | Not Found | Ressource non trouvée |
| 422 | Unprocessable Entity | Validation des données échouée |

### Codes d'erreur serveur

| Code | Signification | Utilisation |
|------|---------------|-------------|
| 500 | Internal Server Error | Erreur serveur non gérée |

---

## Format des erreurs de validation (422)

Toutes les erreurs de validation suivent ce format:

```json
{
  "message": "Les données fournies sont invalides",
  "errors": {
    "champ1": [
      "Message d'erreur 1 pour champ1",
      "Message d'erreur 2 pour champ1"
    ],
    "champ2": [
      "Message d'erreur pour champ2"
    ]
  }
}
```

---

## Notes importantes

1. Tous les endpoints retournent du JSON
2. Toujours envoyer le header `Accept: application/json`
3. Le token JWT expire après 60 minutes par défaut
4. Les mots de passe ne sont JAMAIS retournés dans les réponses
5. Les timestamps sont au format ISO 8601 UTC
6. La pagination utilise 15 éléments par page par défaut
