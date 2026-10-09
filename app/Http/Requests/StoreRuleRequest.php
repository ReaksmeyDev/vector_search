<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRuleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'document_title' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'string', 'max:100'],
            'article_no' => ['nullable', 'string', 'max:100'],
            'content_chunk' => ['required', 'string', 'min:10'],
        ];
    }

    /**
     * Custom Khmer validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'document_title.required' => 'សូមបញ្ចូលចំណងជើងឯកសារច្បាប់ ឬលិខិតបទដ្ឋាន។',
            'document_title.max' => 'ចំណងជើងឯកសារមិនអាចលើសពី ២៥៥ តួអក្សរឡើយ។',
            'document_type.required' => 'សូមជ្រើសរើស ឬបញ្ចូលប្រភេទឯកសារ (ឧ. ព្រះរាជក្រឹត្យ អនុក្រឹត្យ ប្រកាស)។',
            'content_chunk.required' => 'សូមបញ្ចូលខ្លឹមសារនៃវិធានច្បាប់ ឬមាត្រា។',
            'content_chunk.min' => 'ខ្លឹមសារវិធានត្រូវមានយ៉ាងតិច ១០ តួអក្សរ។',
        ];
    }
}
