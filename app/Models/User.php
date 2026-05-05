<?php

namespace App\Models;

use App\Support\ModulePermissions;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'full_name',
        'email',
        'phone_number',
        'role',
        'is_active',
        'created_by',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function isGeneralUser(): bool
    {
        return $this->role === 'user';
    }

    public function hasVerifiedContact(): bool
    {
        return ! is_null($this->email_verified_at) && ! is_null($this->phone_verified_at);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function isStaff(): bool
    {
        return $this->isAdmin() || $this->isEmployee();
    }

    /**
     * Check Spatie permission for a module action (e.g. products.read). Admin has full access.
     */
    public function canModule(string $moduleSlug, string $action): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (! $this->isEmployee() || ! $this->is_active) {
            return false;
        }

        return $this->can($moduleSlug.'.'.$action);
    }

    public function firstReadableModuleSlug(): ?string
    {
        if ($this->isAdmin()) {
            return array_key_first(ModulePermissions::modules());
        }

        foreach (array_keys(ModulePermissions::modules()) as $slug) {
            if ($this->canModule($slug, 'read')) {
                return $slug;
            }
        }

        return null;
    }
}
