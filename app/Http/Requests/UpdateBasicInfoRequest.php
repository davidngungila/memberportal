<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Services\EncryptedIdService;

class UpdateBasicInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $encryptedId = $this->route('encryptedId');
        $userId = null;
        
        if ($encryptedId) {
            try {
                $userId = (int) app(EncryptedIdService::class)->decrypt($encryptedId);
            } catch (\Exception $e) {
                // If decryption fails, we'll handle it in the controller
            }
        }
        
        $memberId = null;
        if ($userId) {
            $member = \App\Models\Member::where('user_id', $userId)->first();
            if ($member) {
                $memberId = $member->id;
            }
        }

        return [
            'membership_type_id' => 'nullable|exists:member_types,id',
            'full_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:members,email,' . $memberId,
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date|before:today',
            'national_id' => 'nullable|string|max:50',
            'registration_date' => 'nullable|date',
            'profile_photo' => 'nullable|image|max:5120',
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
