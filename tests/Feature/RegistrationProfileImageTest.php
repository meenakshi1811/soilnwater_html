<?php

namespace Tests\Feature;

use App\Models\Consultant;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationProfileImageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider imageRegistrationRoles
     */
    public function test_registration_stores_the_profile_image(string $role, string $modelClass, string $uploadDirectory): void
    {
        Mail::fake();
        $email = str_replace('_', '.', $role).'@gmail.com';
        $image = $this->profileImage($role.'.png');

        $response = $this->post(route('register'), [
            'fullname' => 'Profile Image Test',
            'email' => $email,
            'phone_number' => match ($role) {
                'user' => '9876543210',
                'vendor' => '9876543211',
                'consultant' => '9876543212',
                default => '9876543213',
            },
            'whatsapp_number' => '9876543299',
            'address' => '123 Test Street',
            'city' => 'Test City',
            'pincode' => '123456',
            'role' => $role,
            'pan_number' => 'ABCDE1234F',
            'has_gst' => '0',
            'government_certificate_number' => null,
            'profile_image' => $image,
            'date_of_birth' => now()->subYears(25)->toDateString(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'accept_terms' => '1',
        ]);

        $response->assertRedirect($role === 'user'
            ? route('register.contact.verify.form')
            : route('login'));

        $profile = $modelClass::query()->where('email', $email)->firstOrFail();
        $imagePath = $profile instanceof User ? $profile->profile_image : $profile->logo;
        $user = User::query()->where('email', $email)->firstOrFail();

        $this->assertNotNull($imagePath);
        $this->assertSame($imagePath, $user->profile_image);
        $this->assertStringStartsWith($uploadDirectory, $imagePath);
        $this->assertFileExists(public_path($imagePath));

        @unlink(public_path($imagePath));
    }

    /**
     * @dataProvider requiredImageRoles
     */
    public function test_registration_requires_a_profile_image(string $role, string $phoneNumber): void
    {
        $email = 'missing.'.str_replace('_', '.', $role).'@gmail.com';
        $response = $this->from(route('register'))->post(route('register'), [
            'fullname' => 'Missing Image',
            'email' => $email,
            'phone_number' => $phoneNumber,
            'whatsapp_number' => '9876543298',
            'address' => '123 Test Street',
            'city' => 'Test City',
            'pincode' => '123456',
            'role' => $role,
            'pan_number' => 'ABCDE1234F',
            'has_gst' => '0',
            'date_of_birth' => now()->subYears(25)->toDateString(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'accept_terms' => '1',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('profile_image');
        $this->assertDatabaseMissing('users', ['email' => $email]);
    }

    public static function imageRegistrationRoles(): array
    {
        return [
            'user' => ['user', User::class, 'uploads/users/profiles/'],
            'vendor' => ['vendor', Vendor::class, 'uploads/vendors/logos/'],
            'consultant' => ['consultant', Consultant::class, 'uploads/consultants/logos/'],
            'service provider' => ['service_provider', ServiceProvider::class, 'uploads/service_providers/logos/'],
        ];
    }

    public static function requiredImageRoles(): array
    {
        return [
            'user' => ['user', '9876543280'],
            'vendor' => ['vendor', '9876543281'],
            'consultant' => ['consultant', '9876543282'],
            'service provider' => ['service_provider', '9876543283'],
        ];
    }

    private function profileImage(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'profile-image-');
        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true
        ));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }
}
