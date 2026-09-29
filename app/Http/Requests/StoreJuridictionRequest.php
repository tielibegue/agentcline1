<?php

namespace App\Http\Requests;

use App\Models\Juridiction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJuridictionRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('juridictions', 'code')],
            'libelle' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Juridiction::TYPES))],
            'ville' => ['nullable', 'string', 'max:100'],
            'commune' => ['nullable', 'string', 'max:100'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'responsable' => ['nullable', 'string', 'max:255'],
            'actif' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'code' => 'code de la juridiction',
            'libelle' => 'libellé',
            'type' => 'type de juridiction',
            'ville' => 'ville',
            'commune' => 'commune',
            'telephone' => 'téléphone',
            'email' => 'adresse e-mail',
            'responsable' => 'responsable',
        ];
    }
}
