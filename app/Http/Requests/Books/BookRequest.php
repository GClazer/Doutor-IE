<?php

namespace App\Http\Requests\Books;

use Illuminate\Foundation\Http\FormRequest;

class BookRequest extends FormRequest
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
        return array_merge(
            $this->baseRules(),
            $this->buildIndexRules($this->get('indices', []))
        );
    }

    private function baseRules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
        ];
    }

    private function buildIndexRules(array $indices, string $path = 'indices'): array
    {
        $rules = [];

        foreach ($indices as $key => $index) {
            $currentPath = "{$path}.{$key}";
            $rules["{$currentPath}.titulo"] = ['required', 'string', 'max:255'];
            $rules["{$currentPath}.pagina"] = ['required', 'integer'];
            $rules["{$currentPath}.indice_pai_id"] = ['nullable', 'exists:book_indexes,id'];

            $subindices = $index['subindices'] ?? [];

            if (count($subindices)) {
                $rules = array_merge(
                    $rules,
                    $this->buildIndexRules($subindices, "{$currentPath}.subindices")
                );
            }
        }

        return $rules;
    }
}
