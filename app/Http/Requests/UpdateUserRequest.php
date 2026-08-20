<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('encryptedId');
        
        // Decrypt the ID if it's encrypted
        if ($userId) {
            try {
                $decryptedId = app(\App\Services\EncryptedIdService::class)->decrypt($userId);
                \Log::info('UpdateUserRequest ID decryption', [
                    'encrypted_id' => $userId,
                    'decrypted_id' => $decryptedId,
                    'is_numeric' => is_numeric($decryptedId)
                ]);
                if ($decryptedId && is_numeric($decryptedId)) {
                    $userId = (int) $decryptedId;
                } else {
                    \Log::warning('Decrypted ID is not numeric or null', ['decrypted_id' => $decryptedId]);
                    $userId = null;
                }
            } catch (\Exception $e) {
                \Log::error('Failed to decrypt user ID in UpdateUserRequest', [
                    'encrypted_id' => $userId,
                    'error' => $e->getMessage()
                ]);
                $userId = null;
            }
        } else {
            \Log::warning('No encrypted ID found in route');
        }
        
        \Log::info('UpdateUserRequest validation rules', ['user_id' => $userId]);
        
        return [
            'name' => ['required'],
            'email' => ['required', function ($attribute, $value, $fail) use ($userId) {
                $member = \App\Models\Member::where('email', $value)->where('user_id', '!=', $userId)->first();
                if ($member) {
                    $fail('The email has already been taken.');
                }
            }],
            'password' => ['nullable', 'confirmed', 'min:8'],
            'role' => ['required'],
            'membercode' => ['nullable', Rule::unique('users', 'membercode')->ignore($userId ?? 0)],
        ];
    }
}
