<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
        'modules',
        'offer_price',
    ];

    protected $casts = [
        'modules' => 'array',
        'offer_price' => 'decimal:2',
    ];

    /**
     * @return list<string>
     */
    public static function moduleAliases(string $module): array
    {
        $module = strtolower(trim($module));
        $normalizedModule = str_replace(['-', ' '], '_', $module);

        return match ($normalizedModule) {
            'service_provider', 'service_providers' => ['service_providers', 'services'],
            default => [$module],
        };
    }

    public function scopeForModule(Builder $query, string $module): Builder
    {
        return $query->where(function (Builder $moduleQuery) use ($module): void {
            foreach (self::moduleAliases($module) as $alias) {
                $moduleQuery->orWhereJsonContains('modules', $alias);
            }
        });
    }

    public function hasModule(string $module): bool
    {
        $modules = $this->modules ?? [];

        return collect(self::moduleAliases($module))
            ->contains(fn (string $alias): bool => in_array($alias, $modules, true));
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
