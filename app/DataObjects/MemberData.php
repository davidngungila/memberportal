<?php

declare(strict_types=1);

namespace App\DataObjects;

class MemberData
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $gender = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?string $address = null,
        public readonly ?string $occupation = null,
        public readonly ?string $employer = null,
        public readonly ?string $branch = null,
        public readonly ?string $registration_date = null,
        public readonly ?string $status = null,
        public readonly ?string $membercode = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? $data['Name'] ?? null,
            gender: $data['gender'] ?? $data['Gender'] ?? null,
            phone: $data['phone'] ?? $data['Phone'] ?? null,
            email: $data['email'] ?? $data['Email'] ?? null,
            address: $data['address'] ?? $data['Address'] ?? null,
            occupation: $data['occupation'] ?? $data['Occupation'] ?? null,
            employer: $data['employer'] ?? $data['Employer'] ?? null,
            branch: $data['branch'] ?? $data['Branch'] ?? null,
            registration_date: $data['registration_date'] ?? $data['RegistrationDate'] ?? $data['registration_date'] ?? null,
            status: $data['status'] ?? $data['Status'] ?? null,
            membercode: $data['membercode'] ?? $data['member_number'] ?? $data['MemberNumber'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'gender' => $this->gender,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'occupation' => $this->occupation,
            'employer' => $this->employer,
            'branch' => $this->branch,
            'registration_date' => $this->registration_date,
            'status' => $this->status,
            'membercode' => $this->membercode,
        ];
    }
}
