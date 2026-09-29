<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUtilisateurRequest extends StoreUtilisateurRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $regles = parent::rules();

        $regles['email'] = [
            'required',
            'string',
            'email',
            'max:255',
            Rule::unique('users', 'email')->ignore($this->route('utilisateur')),
        ];

        // En modification, le mot de passe ne change que s'il est renseigné.
        $regles['password'] = ['nullable', 'confirmed', Password::min(8)->letters()->numbers()];

        return $regles;
    }
}
