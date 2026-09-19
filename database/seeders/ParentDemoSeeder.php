<?php

namespace Database\Seeders;

use App\Models\ChildProfile;
use App\Models\ParentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ParentDemoSeeder extends Seeder
{
    public function run(): void
    {
        $password = 'Parent@123';

        $parent = User::query()->updateOrCreate(
            ['email' => 'parent.demo@soilnwater.test'],
            [
                'name' => 'Rahul Mehta',
                'full_name' => 'Rahul Mehta',
                'phone_number' => '9876501234',
                'whatsapp_number' => '9876501234',
                'address' => '14 Sunrise Apartments, Vesu',
                'city' => 'Surat',
                'pincode' => '395007',
                'role' => 'parent',
                'is_active' => true,
                'is_blocked' => false,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );

        $parentProfile = ParentProfile::query()->updateOrCreate(
            ['user_id' => $parent->id],
            [
                'is_enabled' => true,
                'status' => 'approved',
                'approved_at' => now()->subMonth(),
                'approved_by' => null,
                'rejection_reason' => null,
                'converted_from_user' => false,
                'bio' => 'Parent of a Class 10 student looking for quality tuition and study resources in Surat.',
                'languages' => ['English', 'Hindi', 'Gujarati'],
                'location' => 'Surat, Gujarat',
                'profile_completion' => 85,
            ]
        );

        $parentProfile->recalculateCompletion();

        $childUser = User::query()->updateOrCreate(
            ['email' => 'child.demo.parent@soilnwater.test'],
            [
                'name' => 'Aarav Mehta',
                'full_name' => 'Aarav Mehta',
                'phone_number' => '9876501235',
                'date_of_birth' => '2011-04-12',
                'city' => 'Surat',
                'role' => 'student',
                'is_active' => false,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );

        ChildProfile::query()->updateOrCreate(
            [
                'parent_user_id' => $parent->id,
                'child_user_id' => $childUser->id,
            ],
            [
                'full_name' => 'Aarav Mehta',
                'email' => 'child.demo.parent@soilnwater.test',
                'phone_number' => '9876501235',
                'date_of_birth' => '2011-04-12',
                'age' => 14,
                'gender' => 'male',
                'class_grade' => 'Class 10',
                'board' => 'CBSE',
                'school_name' => 'Delhi Public School, Surat',
                'subjects' => ['Physics', 'Mathematics', 'Science'],
                'is_primary' => true,
                'status' => 'approved',
                'approved_at' => now()->subMonth(),
                'approved_by' => null,
                'rejection_reason' => null,
            ]
        );

        $this->command?->info('Parent demo data seeded successfully.');
        $this->command?->newLine();
        $this->command?->table(
            ['Field', 'Value'],
            [
                ['Parent login', 'parent.demo@soilnwater.test / '.$password],
                ['Parent dashboard', url('/parent/dashboard')],
                ['Child name', 'Aarav Mehta (Class 10, CBSE)'],
            ]
        );
    }
}
