<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     *
     * Pour l'inscription, tout le monde peut s'inscrire, donc on retourne true.
     * Si on voulait restreindre l'inscription (par exemple, seulement les admins
     * peuvent créer des comptes), on mettrait de la logique ici.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Les règles de validation pour l'inscription.
     *
     * Laravel va automatiquement vérifier ces règles AVANT que la requête
     * n'atteigne le Controller. Si une règle échoue, Laravel retourne
     * automatiquement une erreur 422 avec les messages d'erreur.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Règles pour le champ 'name'
            'name' => [
                'required',        // Obligatoire
                'string',          // Doit être une chaîne de caractères
                'min:2',           // Minimum 2 caractères
                'max:255',         // Maximum 255 caractères
            ],

            // Règles pour le champ 'email'
            'email' => [
                'required',        // Obligatoire
                'string',          // Doit être une chaîne de caractères
                'email',           // Doit être un email valide (format: xxx@yyy.zzz)
                'max:255',         // Maximum 255 caractères
                'unique:users,email',  // Doit être unique dans la table users, colonne email
            ],

            // Règles pour le champ 'password'
            'password' => [
                'required',        // Obligatoire
                'string',          // Doit être une chaîne de caractères
                'min:12',          // Minimum 12 caractères
                'regex:/[a-z]/',   // Doit contenir au moins 1 minuscule
                'regex:/[A-Z]/',   // Doit contenir au moins 1 majuscule
                'regex:/[0-9]/',   // Doit contenir au moins 1 chiffre
                'regex:/[@$!%*#?&]/',  // Doit contenir au moins 1 caractère spécial
            ],
        ];
    }

    /**
     * Messages d'erreur personnalisés (optionnel).
     *
     * Si on ne définit pas cette méthode, Laravel utilise des messages par défaut.
     * Ici, on personnalise les messages pour qu'ils soient plus clairs pour l'utilisateur.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Messages pour le champ 'name'
            'name.required' => 'Le nom est obligatoire',
            'name.min' => 'Le nom doit contenir au moins 2 caractères',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères',

            // Messages pour le champ 'email'
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'L\'email doit être une adresse email valide',
            'email.unique' => 'Cet email est déjà utilisé',

            // Messages pour le champ 'password'
            'password.required' => 'Le mot de passe est obligatoire',
            'password.min' => 'Le mot de passe doit contenir au moins 12 caractères',
            'password.regex' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial (@$!%*#?&)',
        ];
    }
}
