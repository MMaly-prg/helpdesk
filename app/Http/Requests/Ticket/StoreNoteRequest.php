<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'content'       => 'required|string',
            'time_minutes'  => 'nullable|integer|min:0|max:1440',
            'change_status' => 'nullable|in:open,in_progress,waiting_for_client,resolved,closed',
            'is_public'     => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'content.required'    => 'Treść notatki jest wymagana.',
            'time_minutes.max'    => 'Czas nie może przekroczyć 24h (1440 min) w jednej notatce.',
        ];
    }
}
