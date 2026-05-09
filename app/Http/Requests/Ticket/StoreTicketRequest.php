<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Sanctum middleware handles auth
    }

    public function rules(): array
    {
        return [
            'title'              => 'required|string|max:255',
            'description'        => 'required|string',
            'company_id'         => 'nullable|exists:companies,id',
            'company_domain'     => 'nullable|string|max:255', // alternatywa dla company_id (API)
            'priority'           => 'nullable|in:critical,high,normal,low',
            'category'           => 'nullable|in:network,server,software,hardware,email,backup,security,other',
            'source_identifier'  => 'nullable|string|max:255',
            'contact_name'       => 'nullable|string|max:255',
            'contact_email'      => 'nullable|email',
            'tags'               => 'nullable|array',
            'tags.*'             => 'string|max:50',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if (empty($this->company_id) && empty($this->company_domain)) {
                $v->errors()->add('company', 'Wymagane jest company_id lub company_domain.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'Tytuł zgłoszenia jest wymagany.',
            'description.required' => 'Opis zgłoszenia jest wymagany.',
            'priority.in'          => 'Priorytet musi być: critical, high, normal lub low.',
            'category.in'          => 'Nieprawidłowa kategoria.',
        ];
    }
}
