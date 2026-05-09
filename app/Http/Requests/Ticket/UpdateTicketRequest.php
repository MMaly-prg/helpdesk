<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status'             => 'nullable|in:open,in_progress,waiting_for_client,resolved,closed',
            'priority'           => 'nullable|in:critical,high,normal,low',
            'category'           => 'nullable|in:network,server,software,hardware,email,backup,security,other',
            'assigned_to'        => 'nullable|exists:users,id',
            'resolution_summary' => 'nullable|string',
            'tags'               => 'nullable|array',
            'tags.*'             => 'string|max:50',
        ];
    }
}
