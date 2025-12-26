<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole('creator');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'title' => 'required|string|max:100',
            'caption' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:100',
            'peopleTagged' => 'nullable|array',
            'images' => 'required|array',
            'images.*' => 'required|image|mimes:jpeg,png,webp,gif|max:10240',
        ];
    }
}
