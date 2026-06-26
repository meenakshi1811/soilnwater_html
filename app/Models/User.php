<?php

namespace App\Models;

use App\Support\ModulePermissions;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
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
        'author_slug',
        'author_image',
        'full_name',
        'email',
        'phone_number',
        'whatsapp_number',
        'address',
        'city',
        'pincode',
        'latitude',
        'longitude',
        'role',
        'date_of_birth',
        'profile_image',
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
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }


    public function authorDisplayName(): string
    {
        return $this->name ?? $this->full_name ?? 'Community author';
    }

    public function authorUniqueName(): string
    {
        if (filled($this->author_slug)) {
            return $this->author_slug;
        }

        $base = Str::slug($this->authorDisplayName()) ?: 'author';

        return $base.'-'.$this->id;
    }

    public function authorImageUrl(): ?string
    {
        return filled($this->author_image) ? asset($this->author_image) : null;
    }

    public function authorInitials(): string
    {
        return collect(preg_split('/\s+/', trim($this->authorDisplayName())) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('') ?: 'CA';
    }

    public function communityPosts(): HasMany
    {
        return $this->hasMany(CommunityPost::class);
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

    public function isVendor(): bool
    {
        return $this->role === 'vendor';
    }

    public function isConsultant(): bool
    {
        return $this->role === 'consultant';
    }

    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class);
    }

    public function consultant(): HasOne
    {
        return $this->hasOne(Consultant::class);
    }

    public function isServiceProvider(): bool
    {
        return $this->role === 'service_provider';
    }

    public function serviceProvider(): HasOne
    {
        return $this->hasOne(ServiceProvider::class);
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
