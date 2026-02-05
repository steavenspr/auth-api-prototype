# Architecture du projet

Ce document explique l'organisation du code et le rôle de chaque composant.

## Principe de base

On applique le principe de séparation des responsabilités (Separation of Concerns):

- Les Controllers ne contiennent AUCUNE logique métier
- Les Services contiennent TOUTE la logique métier
- Les Models communiquent avec la base de données
- Les Requests valident les données
- Les Middlewares filtrent les requêtes

## Structure des dossiers

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php       → Gère les routes d'authentification
│   │   └── UserController.php       → Gère le CRUD des utilisateurs
│   ├── Middleware/
│   │   └── JwtAuthMiddleware.php    → Vérifie que le token JWT est valide
│   └── Requests/
│       ├── LoginRequest.php         → Valide les données de connexion
│       ├── RegisterRequest.php      → Valide les données d'inscription
│       ├── CreateUserRequest.php    → Valide la création d'un user
│       └── UpdateUserRequest.php    → Valide la modification d'un user
├── Models/
│   └── User.php                     → Représente la table users
├── Services/
│   ├── AuthService.php              → Logique métier pour l'authentification
│   └── UserService.php              → Logique métier pour le CRUD users
└── Exceptions/
    └── Handler.php                  → Gestion centralisée des erreurs

database/
├── migrations/
│   └── create_users_table.php       → Structure de la table users
└── seeders/
    └── DatabaseSeeder.php           → Données de test

routes/
└── api.php                          → Définition de toutes les routes

tests/
├── Unit/
│   ├── AuthServiceTest.php          → Tests unitaires du service auth
│   └── UserServiceTest.php          → Tests unitaires du service user
└── Feature/
    ├── AuthTest.php                 → Tests d'intégration auth
    └── UserTest.php                 → Tests d'intégration CRUD user
```

## Rôle de chaque couche

### 1. Routes (routes/api.php)

Définit les URLs de l'API et les associe aux méthodes des controllers.

Exemple:
```php
Route::post('/auth/register', [AuthController::class, 'register']);
```

Cette ligne signifie: quand quelqu'un envoie un POST sur /auth/register, Laravel appelle la méthode register() du AuthController.

### 2. Requests (app/Http/Requests/)

Valident les données AVANT qu'elles n'atteignent le controller.

Si la validation échoue, Laravel retourne automatiquement une erreur 422 avec les messages d'erreur. Le controller n'est même pas exécuté.

Exemple:
```php
class RegisterRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:12',
        ];
    }
}
```

### 3. Controllers (app/Http/Controllers/Api/)

Reçoivent la requête validée, appellent le Service approprié, retournent la réponse JSON.

Ils ne font QUE de l'orchestration. Aucune logique métier.

Exemple:
```php
public function register(RegisterRequest $request)
{
    try {
        $result = $this->authService->register($request->validated());
        return response()->json($result, 201);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
```

### 4. Services (app/Services/)

Contiennent TOUTE la logique métier.

C'est ici qu'on hash les mots de passe, qu'on génère les tokens JWT, qu'on vérifie les données, etc.

Exemple:
```php
public function register(array $data)
{
    // Hash du mot de passe
    $data['password'] = Hash::make($data['password']);
    
    // Création de l'utilisateur
    $user = User::create($data);
    
    // Génération du token JWT
    $token = JWTAuth::fromUser($user);
    
    return [
        'user' => $user,
        'token' => $token
    ];
}
```

### 5. Models (app/Models/)

Représentent les tables de la base de données.

Eloquent (l'ORM de Laravel) permet de manipuler les données sans écrire de SQL.

Exemple:
```php
$user = User::create(['email' => 'test@example.com']);
// Eloquent traduit ça en: INSERT INTO users (email) VALUES ('test@example.com')
```

### 6. Middleware (app/Http/Middleware/)

Filtrent les requêtes AVANT qu'elles n'atteignent le controller.

Exemple: vérifier que le token JWT est valide avant de donner accès à une route protégée.

Si le middleware refuse, le controller n'est jamais exécuté.

### 7. Exception Handler (app/Exceptions/Handler.php)

Attrape toutes les erreurs de l'application et les formate de manière cohérente.

Permet d'avoir des réponses d'erreur uniformes dans toute l'API.

## Flux complet d'une requête

Prenons l'exemple: POST /auth/register

```
1. Requête HTTP arrive
   ↓
2. routes/api.php → oriente vers AuthController@register
   ↓
3. RegisterRequest → valide les données (email, password)
   ↓ (si validation OK)
4. AuthController@register → reçoit les données validées
   ↓
5. AuthController appelle → AuthService@register()
   ↓
6. AuthService → hash le password, crée le user, génère le token
   ↓
7. AuthService utilise → Model User pour insérer dans MySQL
   ↓
8. MySQL → enregistre les données
   ↓
9. AuthService → retourne les données au Controller
   ↓
10. AuthController → retourne la réponse JSON avec code 201
   ↓
11. Réponse envoyée au client
```

Si une erreur survient à n'importe quelle étape, le try-catch l'attrape et retourne une erreur JSON.

## Pourquoi cette architecture ?

### Avantages

1. **Code propre et lisible**
   - Chaque classe a UNE seule responsabilité
   - On sait où chercher quand on a un bug

2. **Facilité de test**
   - Les Services peuvent être testés indépendamment
   - Pas besoin de faire une vraie requête HTTP pour tester la logique

3. **Réutilisabilité**
   - Le même Service peut être appelé par plusieurs Controllers
   - Les Requests peuvent être réutilisées

4. **Maintenance**
   - Modifier la logique métier = modifier le Service uniquement
   - Modifier la validation = modifier la Request uniquement

### Règles à respecter

1. JAMAIS de logique métier dans les Controllers
2. JAMAIS de requête directe au Model depuis le Controller (toujours passer par un Service)
3. TOUJOURS valider les données avec une Request
4. TOUJOURS utiliser try-catch dans les Controllers
5. TOUJOURS typer les paramètres et les retours de fonction

## Tests

### Tests unitaires

Testent les Services de manière isolée, sans toucher à la base de données.

On utilise des mocks pour simuler les Models.

### Tests d'intégration

Testent les endpoints complets (routes + controllers + services + database).

On utilise une vraie base de données (souvent SQLite en mémoire pour la rapidité).

## Conclusion

Cette architecture peut sembler lourde pour un petit projet, mais c'est exactement ce qu'on utilise dans les vrais projets Laravel en entreprise.

Maîtriser cette structure maintenant te permettra de travailler sur n'importe quel projet Laravel professionnel.
