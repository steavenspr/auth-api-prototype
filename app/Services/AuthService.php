<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class AuthService
{
    /**
     * Inscription d'un nouvel utilisateur.
     *
     * Cette méthode:
     * 1. Hash le mot de passe
     * 2. Crée l'utilisateur en base de données
     * 3. Génère un token JWT pour cet utilisateur
     * 4. Retourne les données de l'utilisateur + le token
     *
     * @param array $data Les données validées (name, email, password)
     * @return array Tableau contenant 'user' et 'token'
     * @throws Exception Si une erreur survient lors de la création
     */
    public function register(array $data): array
    {
        try {
            // Étape 1: Hasher le mot de passe
            // Hash::make() utilise bcrypt par défaut (ou argon2 selon config/hashing.php)
            // Le mot de passe en clair n'est JAMAIS stocké en base
            $data['password'] = Hash::make($data['password']);

            // Étape 2: Créer l'utilisateur dans la base de données
            // User::create() utilise Eloquent pour insérer dans la table users
            // Seuls les champs dans $fillable du Model peuvent être insérés
            $user = User::create($data);

            // Étape 3: Générer un token JWT pour cet utilisateur
            // JWTAuth::fromUser() crée un token contenant:
            // - sub: l'id de l'utilisateur (via getJWTIdentifier())
            // - iat: timestamp de création
            // - exp: timestamp d'expiration (iat + TTL)
            $token = JWTAuth::fromUser($user);

            // Étape 4: Retourner l'utilisateur et le token
            return [
                'user' => $user,
                'token' => $token
            ];

        } catch (Exception $e) {
            // Si une erreur survient (par exemple, violation de contrainte unique),
            // on relance l'exception pour que le Controller puisse la gérer
            throw new Exception('Erreur lors de la création du compte: ' . $e->getMessage());
        }
    }

    /**
     * Connexion d'un utilisateur existant.
     *
     * Cette méthode:
     * 1. Cherche l'utilisateur par email
     * 2. Vérifie que le mot de passe est correct
     * 3. Génère un token JWT
     * 4. Retourne les données de l'utilisateur + le token
     *
     * @param string $email L'email de l'utilisateur
     * @param string $password Le mot de passe en clair
     * @return array Tableau contenant 'user' et 'token'
     * @throws Exception Si les identifiants sont incorrects
     */
    public function login(string $email, string $password): array
    {
        try {
            // Étape 1: Chercher l'utilisateur par email
            // User::where() crée une requête SQL: SELECT * FROM users WHERE email = ?
            // ->first() récupère le premier résultat (ou null si aucun résultat)
            $user = User::where('email', $email)->first();

            // Étape 2: Vérifier que l'utilisateur existe
            if (!$user) {
                // L'email n'existe pas dans la base
                throw new Exception('Email ou mot de passe incorrect');
            }

            // Étape 3: Vérifier que le mot de passe est correct
            // Hash::check() compare le mot de passe en clair avec le hash stocké
            // Elle fait:
            // 1. Récupère le sel du hash stocké
            // 2. Hash le mot de passe en clair avec ce sel
            // 3. Compare les deux hashs
            if (!Hash::check($password, $user->password)) {
                // Le mot de passe ne correspond pas
                throw new Exception('Email ou mot de passe incorrect');
            }

            // Étape 4: Générer un token JWT
            $token = JWTAuth::fromUser($user);

            // Étape 5: Retourner l'utilisateur et le token
            return [
                'user' => $user,
                'token' => $token
            ];

        } catch (Exception $e) {
            // Relancer l'exception pour que le Controller puisse la gérer
            throw $e;
        }
    }

    /**
     * Récupérer l'utilisateur actuellement authentifié.
     *
     * Cette méthode est appelée quand l'utilisateur envoie son token
     * et veut récupérer ses informations.
     *
     * @return User L'utilisateur authentifié
     * @throws Exception Si le token est invalide ou expiré
     */
    public function me(): User
    {
        try {
            // JWTAuth::parseToken() lit le token depuis le header Authorization
            // ->authenticate() vérifie la signature et l'expiration, puis retourne l'utilisateur
            // Si le token est invalide ou expiré, une exception est levée
            $user = JWTAuth::parseToken()->authenticate();

            // Si $user est null, le token est valide mais l'utilisateur n'existe plus
            if (!$user) {
                throw new Exception('Utilisateur non trouvé');
            }

            return $user;

        } catch (Exception $e) {
            // Relancer l'exception pour que le Controller puisse la gérer
            throw new Exception('Token invalide ou expiré');
        }
    }

    /**
     * Déconnexion de l'utilisateur.
     *
     * Cette méthode invalide le token JWT actuel.
     * L'utilisateur devra se reconnecter pour obtenir un nouveau token.
     *
     * @return bool True si la déconnexion a réussi
     * @throws Exception Si une erreur survient
     */
    public function logout(): bool
    {
        try {
            // JWTAuth::parseToken() lit le token depuis le header Authorization
            // ->invalidate() ajoute le token à une blacklist
            // Ce token ne pourra plus être utilisé même s'il n'est pas expiré
            JWTAuth::parseToken()->invalidate();

            return true;

        } catch (Exception $e) {
            throw new Exception('Erreur lors de la déconnexion');
        }
    }
}
