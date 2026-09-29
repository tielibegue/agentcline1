<?php

namespace App\Http\Requests;

use App\Enums\PrioriteTicket;
use App\Enums\TypeTicket;
use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(TypeTicket::valeurs())],
            'priorite' => ['required', Rule::in(PrioriteTicket::valeurs())],
            'titre' => ['required', 'string', 'min:5', 'max:180'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'application' => ['nullable', 'string', Rule::in(array_keys(Ticket::APPLICATIONS))],
            'module_fonctionnel' => ['nullable', 'string', 'max:255'],
            'version_application' => ['nullable', 'string', 'max:50'],
            'environnement' => ['nullable', 'string', 'max:255'],
            'date_incident' => ['nullable', 'date', 'before_or_equal:today'],
            'reproductible' => ['nullable', 'boolean'],
            'juridiction_id' => ['nullable', 'integer', 'exists:juridictions,id'],
            'pieces_jointes' => ['nullable', 'array', 'max:5'],
            'pieces_jointes.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,csv,txt,log,zip'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'type' => 'type de demande',
            'priorite' => 'priorité',
            'titre' => 'objet de la demande',
            'description' => 'description détaillée',
            'application' => 'application concernée',
            'module_fonctionnel' => 'module / écran concerné',
            'version_application' => 'version de l’application',
            'environnement' => 'environnement / poste',
            'date_incident' => 'date de l’incident',
            'reproductible' => 'caractère reproductible',
            'juridiction_id' => 'juridiction',
            'pieces_jointes' => 'pièces jointes',
            'pieces_jointes.*' => 'pièce jointe',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'titre.min' => 'L’objet de la demande doit contenir au moins 5 caractères.',
            'description.min' => 'Merci de décrire précisément le problème (20 caractères minimum).',
            'pieces_jointes.max' => 'Vous ne pouvez joindre que 5 fichiers par envoi.',
            'pieces_jointes.*.max' => 'Chaque pièce jointe ne doit pas dépasser 10 Mo.',
        ];
    }
}
