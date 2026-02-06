<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     *
     * Pour la connexion, tout le monde peut essayer de se connecter.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Les règles de validation pour la connexion.
     *
     * Pour se connecter, on a besoin seulement de l'email et du mot de passe.
     * On ne vérifie pas la complexité du mot de passe ici car il a déjà été
     * validé lors de l'inscription.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Règles pour le champ 'email'
            'email' => [
                'required',    // Obligatoire
                'string',      // Doit être une chaîne de caractères
                'email',       // Doit être un email valide
            ],

            // Règles pour le champ 'password'
            'password' => [
                'required',    // Obligatoire
                'string',      // Doit être une chaîne de caractères
            ],
        ];
    }

    /**
     * Messages d'erreur personnalisés.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'L\'email doit être une adresse email valide',
            'password.required' => 'Le mot de passe est obligatoire',
        ];
    }
}
