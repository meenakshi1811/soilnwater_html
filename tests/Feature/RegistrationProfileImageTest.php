<?php

namespace Tests\Feature;

use App\Models\Consultant;
use App\Models\ServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationProfileImageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider professionalRoles
     */
    public function test_professional_registration_stores_the_profile_image(string $role, string $modelClass, string $uploadDirectory): void
    {
        Mail::fake();
        $email = str_replace('_', '.', $role).'@gmail.com';
        $image = $this->profileImage($role.'.png');

        $response = $this->post(route('register'), [
            'fullname' => 'Profile Image Test',
            'email' => $email,
            'phone_number' => $role === 'consultant' ? '9876543210' : '9876543211',
            'whatsapp_number' => '9876543212',
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

        $response->assertRedirect(route('login'));

        $profile = $modelClass::query()->where('email', $email)->firstOrFail();

        $this->assertNotNull($profile->logo);
        $this->assertStringStartsWith($uploadDirectory, $profile->logo);
        $this->assertFileExists(public_path($profile->logo));

        @unlink(public_path($profile->logo));
    }

    public function test_professional_registration_requires_a_profile_image(): void
    {
        $response = $this->from(route('register'))->post(route('register'), [
            'fullname' => 'Missing Image',
            'email' => 'missing.image@gmail.com',
            'phone_number' => '9876543213',
            'whatsapp_number' => '9876543214',
            'address' => '123 Test Street',
            'city' => 'Test City',
            'pincode' => '123456',
            'role' => 'consultant',
            'pan_number' => 'ABCDE1234F',
            'has_gst' => '0',
            'date_of_birth' => now()->subYears(25)->toDateString(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'accept_terms' => '1',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('profile_image');
        $this->assertDatabaseMissing('users', ['email' => 'missing.image@gmail.com']);
    }

    public static function professionalRoles(): array
    {
        return [
            'consultant' => ['consultant', Consultant::class, 'uploads/consultants/logos/'],
            'service provider' => ['service_provider', ServiceProvider::class, 'uploads/service_providers/logos/'],
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
