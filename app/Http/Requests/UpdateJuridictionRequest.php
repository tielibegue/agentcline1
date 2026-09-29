<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateJuridictionRequest extends StoreJuridictionRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $regles = parent::rules();

        $regles['code'] = [
            'required',
            'string',
            'max:30',
            'alpha_dash',
            Rule::unique('juridictions', 'code')->ignore($this->route('juridiction')),
        ];

        return $regles;
    }
}
