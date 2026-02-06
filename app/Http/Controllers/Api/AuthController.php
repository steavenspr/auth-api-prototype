<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Exception;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * Instance du service d'authentification.
     *
     * Cette propriété stocke l'instance du AuthService.
     * On l'injecte via le constructeur (injection de dépendance).
     *
     * @var AuthService
     */
    protected AuthService $authService;

    /**
     * Constructeur du controller.
     *
     * Laravel injecte automatiquement le AuthService quand il crée
     * une instance de ce controller. C'est ce qu'on appelle
     * l'injection de dépendance.
     *
     * Avantages de l'injection de dépendance:
     * 1. On ne crée pas nous-mêmes l'instance (new AuthService())
     * 2. Laravel gère la création et le cycle de vie
     * 3. Facile à tester (on peut injecter un mock)
     * 4. Code plus propre et découplé
     *
     * @param AuthService $authService Instance du service injectée par Laravel
     */
    public function __construct(AuthService $authService)
    {
        // On stocke l'instance du service dans la propriété
        // pour pouvoir l'utiliser dans toutes les méthodes du controller
        $this->authService = $authService;
    }

    /**
     * Inscription d'un nouvel utilisateur.
     *
     * Route: POST /api/auth/register
     *
     * Cette méthode:
     * 1. Reçoit les données validées par RegisterRequest
     * 2. Appelle le service pour créer l'utilisateur
     * 3. Retourne une réponse JSON avec l'utilisateur et le token
     *
     * @param RegisterRequest $request Les données validées automatiquement
     * @return JsonResponse La réponse JSON
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Étape 1: Récupérer les données validées
            // $request->validated() retourne uniquement les champs qui ont passé la validation
            // Ici: ['name' => '...', 'email' => '...', 'password' => '...']
            $validatedData = $request->validated();

            // Étape 2: Appeler le service pour créer l'utilisateur
            // Le service s'occupe de toute la logique:
            // - Hasher le password
            // - Créer l'utilisateur en base
            // - Générer le token JWT
            $result = $this->authService->register($validatedData);

            // Étape 3: Retourner une réponse JSON avec code 201 (Created)
            // response()->json() crée une réponse HTTP avec du JSON
            // Premier paramètre: les données à retourner
            // Deuxième paramètre: le code HTTP (201 = ressource créée)
            return response()->json([
                'message' => 'Utilisateur créé avec succès',
                'user' => $result['user'],
                'token' => $result['token']
            ], 201);

        } catch (Exception $e) {
            // Si une erreur survient dans le service (exception levée),
            // on l'attrape ici et on retourne une erreur 500 (Internal Server Error)
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Connexion d'un utilisateur existant.
     *
     * Route: POST /api/auth/login
     *
     * Cette méthode:
     * 1. Reçoit les données validées par LoginRequest
     * 2. Appelle le service pour vérifier les identifiants
     * 3. Retourne une réponse JSON avec l'utilisateur et le token
     *
     * @param LoginRequest $request Les données validées automatiquement
     * @return JsonResponse La réponse JSON
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // Étape 1: Récupérer les données validées
            // Ici: ['email' => '...', 'password' => '...']
            $validatedData = $request->validated();

            // Étape 2: Appeler le service pour vérifier les identifiants
            // Le service vérifie:
            // - Que l'email existe
            // - Que le password est correct
            // - Génère un token si tout est OK
            $result = $this->authService->login(
                $validatedData['email'],
                $validatedData['password']
            );

            // Étape 3: Retourner une réponse JSON avec code 200 (OK)
            return response()->json([
                'message' => 'Connexion réussie',
                'user' => $result['user'],
                'token' => $result['token']
            ], 200);

        } catch (Exception $e) {
            // Si les identifiants sont incorrects, le service lève une exception
            // On retourne une erreur 401 (Unauthorized)
            return response()->json([
                'error' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Récupérer les informations de l'utilisateur connecté.
     *
     * Route: GET /api/auth/me
     *
     * Cette méthode nécessite un token JWT valide dans le header Authorization.
     *
     * Cette méthode:
     * 1. Lit le token JWT depuis le header Authorization
     * 2. Appelle le service pour récupérer l'utilisateur
     * 3. Retourne les informations de l'utilisateur
     *
     * @return JsonResponse La réponse JSON
     */
    public function me(): JsonResponse
    {
        try {
            // Appeler le service pour récupérer l'utilisateur depuis le token
            // Le service:
            // - Lit le token depuis le header Authorization: Bearer <token>
            // - Vérifie la signature du token
            // - Vérifie que le token n'est pas expiré
            // - Récupère l'utilisateur depuis la base
            $user = $this->authService->me();

            // Retourner l'utilisateur avec code 200 (OK)
            return response()->json([
                'user' => $user
            ], 200);

        } catch (Exception $e) {
            // Si le token est invalide, expiré, ou manquant,
            // on retourne une erreur 401 (Unauthorized)
            return response()->json([
                'error' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Déconnexion de l'utilisateur.
     *
     * Route: POST /api/auth/logout
     *
     * Cette méthode nécessite un token JWT valide dans le header Authorization.
     *
     * Cette méthode:
     * 1. Lit le token JWT depuis le header Authorization
     * 2. Invalide le token (l'ajoute à la blacklist)
     * 3. Retourne une confirmation
     *
     * @return JsonResponse La réponse JSON
     */
    public function logout(): JsonResponse
    {
        try {
            // Appeler le service pour invalider le token
            // Le token sera ajouté à une blacklist et ne pourra plus être utilisé
            $this->authService->logout();

            // Retourner une confirmation avec code 200 (OK)
            return response()->json([
                'message' => 'Déconnexion réussie'
            ], 200);

        } catch (Exception $e) {
            // Si une erreur survient, retourner une erreur 500
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
