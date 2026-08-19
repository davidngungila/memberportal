<?php

namespace App\Imports;

use App\Contracts\GoogleSheetRepositoryInterface;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MembersImport implements ToCollection, WithHeadingRow
{
    protected $googleSheetRepository;
    protected $importedCount = 0;
    protected $errors = [];
    protected $createdUsers = [];

    public function __construct(GoogleSheetRepositoryInterface $googleSheetRepository)
    {
        $this->googleSheetRepository = $googleSheetRepository;
    }

    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            try {
                $memberData = [
                    'membercode' => $row['member_number'] ?? $row['MemberNumber'] ?? null,
                    'full_name' => $row['full_name'] ?? $row['Full_Name'] ?? $row['name'] ?? $row['Name'] ?? null,
                    'gender' => $row['gender'] ?? $row['Gender'] ?? null,
                    'phone' => $row['phone'] ?? $row['Phone'] ?? null,
                    'email' => $row['email'] ?? $row['Email'] ?? null,
                    'status' => $row['status'] ?? $row['Status'] ?? 'Active',
                    'registration_date' => $row['registration_date'] ?? $row['Registration_Date'] ?? now()->format('Y-m-d'),
                    'date_of_birth' => $row['date_of_birth'] ?? $row['Date_Of_Birth'] ?? null,
                    'national_id' => $row['national_id'] ?? $row['National_ID'] ?? null,
                    'occupation' => $row['occupation'] ?? $row['Occupation'] ?? null,
                    'employer' => $row['employer'] ?? $row['Employer'] ?? null,
                    'residential_address' => $row['residential_address'] ?? $row['Residential_Address'] ?? null,
                    'member_type' => $row['member_type'] ?? $row['Member_Type'] ?? 'Regular',
                    'marital_status' => $row['marital_status'] ?? $row['Marital_Status'] ?? null,
                    'bank_name' => $row['bank_name'] ?? $row['Bank_Name'] ?? null,
                    'bank_branch' => $row['bank_branch'] ?? $row['Bank_Branch'] ?? null,
                    'account_name' => $row['account_name'] ?? $row['Account_Name'] ?? null,
                    'account_number' => $row['account_number'] ?? $row['Account_Number'] ?? null,
                    'bank_account_status' => $row['bank_account_status'] ?? $row['Bank_Account_Status'] ?? null,
                    'mobile_money_provider' => $row['mobile_money_provider'] ?? $row['Mobile_Money_Provider'] ?? null,
                    'mobile_money_number' => $row['mobile_money_number'] ?? $row['Mobile_Money_Number'] ?? null,
                    'emergency_contact_name' => $row['emergency_contact_name'] ?? $row['Emergency_Contact_Name'] ?? null,
                    'emergency_contact_phone' => $row['emergency_contact_phone'] ?? $row['Emergency_Contact_Phone'] ?? null,
                    'emergency_contact_relationship' => $row['emergency_contact_relationship'] ?? $row['Emergency_Contact_Relationship'] ?? null,
                    'registration_fee' => $row['registration_fee'] ?? $row['Registration_Fee'] ?? null,
                    'notes' => $row['notes'] ?? $row['Notes'] ?? null,
                    'photo' => $row['photo'] ?? $row['Photo'] ?? null,
                ];

                if (empty($memberData['membercode']) || empty($memberData['full_name'])) {
                    $this->errors[] = "Row skipped: Missing member number or full name";
                    continue;
                }

                // Save to database
                Member::updateOrCreate(
                    ['membercode' => $memberData['membercode']],
                    $memberData
                );

                // Also add to Google Sheets for compatibility
                $this->googleSheetRepository->addMember($memberData);
                $this->importedCount++;

                // Create user account if email is provided and doesn't exist
                if (!empty($memberData['email'])) {
                    $existingUser = User::where('email', $memberData['email'])->first();
                    if (!$existingUser) {
                        $user = User::create([
                            'name' => $memberData['full_name'],
                            'email' => $memberData['email'],
                            'password' => Hash::make('password123'), // Default password
                            'role' => 'member',
                            'member_number' => $memberData['membercode'],
                            'photo' => $memberData['photo'], // Include photo if available
                        ]);
                        $this->createdUsers[] = $memberData['email'];
                    } else {
                        // Update existing user with photo if available
                        if (!empty($memberData['photo'])) {
                            $existingUser->photo = $memberData['photo'];
                            $existingUser->save();
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->errors[] = "Row error: " . $e->getMessage();
            }
        }
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getCreatedUsers(): array
    {
        return $this->createdUsers;
    }
}
