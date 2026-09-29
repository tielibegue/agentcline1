<?php

namespace App\Http\Requests;

use App\Enums\RoleUtilisateur;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUtilisateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->estAdministrateur() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'role' => ['required', Rule::in(RoleUtilisateur::valeurs())],
            'juridiction_id' => [
                'nullable',
                'integer',
                'exists:juridictions,id',
                Rule::requiredIf(fn () => $this->input('role') === RoleUtilisateur::JURIDICTION->value),
            ],
            'fonction' => ['nullable', 'string', 'max:100'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'actif' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nom complet',
            'email' => 'adresse e-mail',
            'password' => 'mot de passe',
            'role' => 'rôle',
            'juridiction_id' => 'juridiction de rattachement',
            'fonction' => 'fonction',
            'telephone' => 'téléphone',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'juridiction_id.required' => 'Une juridiction de rattachement est obligatoire pour un compte de juridiction.',
        ];
    }
}
