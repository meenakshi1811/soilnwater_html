<?php

namespace App\Models;

use App\Support\ModulePermissions;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Employee extends Authenticatable
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected static function newFactory(): EmployeeFactory
    {
        return EmployeeFactory::new();
    }

    /**
     * Keep Spatie roles/permissions on the existing web guard used by the admin role UI.
     *
     * @var string
     */
    protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'is_active',
        'created_by',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isAdmin(): bool
    {
        return false;
    }

    public function isEmployee(): bool
    {
        return true;
    }

    public function isGeneralUser(): bool
    {
        return false;
    }

    public function isVendor(): bool
    {
        return false;
    }

    public function isConsultant(): bool
    {
        return false;
    }

    public function isServiceProvider(): bool
    {
        return false;
    }

    public function isStaff(): bool
    {
        return $this->is_active;
    }

    public function isBlocked(): bool
    {
        return false;
    }

    public function canModule(string $moduleSlug, string $action): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return $this->can($moduleSlug.'.'.$action);
    }

    public function firstReadableModuleSlug(): ?string
    {
        foreach (array_keys(ModulePermissions::modules()) as $slug) {
            if ($this->canModule($slug, 'read')) {
                return $slug;
            }
        }

        return null;
    }

    public function assignedRoleName(): ?string
    {
        return $this->roles->first()?->name;
    }
}
