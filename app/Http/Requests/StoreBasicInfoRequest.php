<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreBasicInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'membership_type_id' => 'required|exists:member_types,id',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:members,email',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'nullable|date|before:today',
            'national_id' => 'nullable|string|max:50|unique:members,national_id',
            'registration_date' => 'required|date',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422));
    }
}
