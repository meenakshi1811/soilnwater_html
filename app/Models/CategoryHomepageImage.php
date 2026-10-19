<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryHomepageImage extends Model
{
    public const CONTEXT_ADS = 'ads';

    public const CONTEXT_OFFERS = 'offers';

    public const CONTEXT_VENDORS = 'vendors';

    public const CONTEXT_SERVICES = 'services';

    public const CONTEXT_CONSULTANTS = 'consultants';

    public const CONTEXT_EDUCATION = 'education';

    /**
     * Category modules whose categories the Education & knowledge section uses (Teachers & Tutors,
     * Schools, Institutes).
     */
    public const EDUCATION_MODULES = ['educators', 'schools', 'institutes'];

    protected $fillable = [
        'category_id',
        'context',
        'image_path',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function imageUrl(): string
    {
        $path = trim((string) $this->image_path);
        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset($path);
    }

    /**
     * @return array<string, string>
     */
    public static function contextLabels(): array
    {
        return [
            self::CONTEXT_ADS => 'Ads & listings',
            self::CONTEXT_OFFERS => 'Offers',
            self::CONTEXT_VENDORS => 'Vendors',
            self::CONTEXT_SERVICES => 'Services',
            self::CONTEXT_CONSULTANTS => 'Consultants',
            self::CONTEXT_EDUCATION => 'Education & knowledge',
        ];
    }
}
