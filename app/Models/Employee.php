<?php

namespace App\Models;

use App\Support\ModulePermissions;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Route;
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

    public function isTeacher(): bool
    {
        return false;
    }

    public function isEducator(): bool
    {
        return false;
    }

    public function isSchool(): bool
    {
        return false;
    }

    public function isInstitute(): bool
    {
        return false;
    }

    public function isSchoolOrInstitute(): bool
    {
        return false;
    }

    public function isStudent(): bool
    {
        return false;
    }

    public function isParent(): bool
    {
        return false;
    }

    public function hasParentProfileEnabled(): bool
    {
        return false;
    }

    public function portalRoutePrefix(): string
    {
        return 'school';
    }

    public function portalRoute(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        return \App\Support\SchoolInstituteHelper::portalRoute($this, $name, $parameters, $absolute);
    }

    public function parentProfile(): HasOne
    {
        return $this->hasOne(ParentProfile::class, 'user_id')->whereRaw('1 = 0');
    }

    public function dashboardUrl(): string
    {
        $slug = $this->firstReadableModuleSlug();

        if ($slug) {
            $entryRoute = ModulePermissions::entryRouteName($slug);
            if ($entryRoute && Route::has($entryRoute)) {
                return route($entryRoute);
            }

            return route('modules.show', ['module' => $slug]);
        }

        return route('employee.dashboard');
    }

    public function panelTitle(): string
    {
        return 'Employee Portal';
    }

    public function isDashboardRouteActive(): bool
    {
        return request()->routeIs('employee.dashboard') || request()->routeIs('modules.show');
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
