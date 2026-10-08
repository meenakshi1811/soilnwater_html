<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryHomepageImage;
use Illuminate\Database\Seeder;

class CategoryHomepageImageSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            [
                'keywords' => ['property', 'real estate', 'office space'],
                'contexts' => [
                    CategoryHomepageImage::CONTEXT_ADS => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&q=80',
                    CategoryHomepageImage::CONTEXT_OFFERS => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800&q=80',
                    CategoryHomepageImage::CONTEXT_VENDORS => 'https://images.unsplash.com/photo-1441986300917-64676bd600d8?w=800&q=80',
                ],
            ],
            [
                'keywords' => ['service', 'repair', 'maintenance', 'homecare'],
                'contexts' => [
                    CategoryHomepageImage::CONTEXT_ADS => 'https://images.unsplash.com/photo-1521737711867-e3b97375f020?w=800&q=80',
                    CategoryHomepageImage::CONTEXT_SERVICES => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=800&q=80',
                    CategoryHomepageImage::CONTEXT_VENDORS => 'https://images.unsplash.com/photo-1607472586893-edb57bdc0e39?w=800&q=80',
                ],
            ],
            [
                'keywords' => ['restaurant', 'food', 'cafe', 'dining'],
                'contexts' => [
                    CategoryHomepageImage::CONTEXT_ADS => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&q=80',
                    CategoryHomepageImage::CONTEXT_OFFERS => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=800&q=80',
                    CategoryHomepageImage::CONTEXT_VENDORS => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=800&q=80',
                ],
            ],
            [
                'keywords' => ['electronic', 'mobile', 'tech', 'laptop'],
                'contexts' => [
                    CategoryHomepageImage::CONTEXT_ADS => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
                    CategoryHomepageImage::CONTEXT_OFFERS => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
                    CategoryHomepageImage::CONTEXT_VENDORS => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
                ],
            ],
            [
                'keywords' => ['salon', 'spa', 'beauty'],
                'contexts' => [
                    CategoryHomepageImage::CONTEXT_OFFERS => 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=800&q=80',
                    CategoryHomepageImage::CONTEXT_VENDORS => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=800&q=80',
                    CategoryHomepageImage::CONTEXT_SERVICES => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=800&q=80',
                ],
            ],
            [
                'keywords' => ['grocery', 'mart', 'supermarket'],
                'contexts' => [
                    CategoryHomepageImage::CONTEXT_OFFERS => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=800&q=80',
                    CategoryHomepageImage::CONTEXT_VENDORS => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=800&q=80',
                ],
            ],
            [
                'keywords' => ['dental', 'clinic', 'health'],
                'contexts' => [
                    CategoryHomepageImage::CONTEXT_VENDORS => 'https://images.unsplash.com/photo-1629909613654-28e377c037b2?w=800&q=80',
                    CategoryHomepageImage::CONTEXT_SERVICES => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?w=800&q=80',
                ],
            ],
            [
                'keywords' => ['legal', 'law'],
                'contexts' => [
                    CategoryHomepageImage::CONTEXT_CONSULTANTS => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=800&q=80',
                ],
            ],
            [
                'keywords' => ['tax', 'account', 'finance'],
                'contexts' => [
                    CategoryHomepageImage::CONTEXT_CONSULTANTS => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=800&q=80',
                ],
            ],
            [
                'keywords' => ['business', 'startup', 'consult'],
                'contexts' => [
                    CategoryHomepageImage::CONTEXT_CONSULTANTS => 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=800&q=80',
                ],
            ],
            [
                'keywords' => ['education', 'training', 'school'],
                'contexts' => [
                    CategoryHomepageImage::CONTEXT_ADS => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=800&q=80',
                    CategoryHomepageImage::CONTEXT_VENDORS => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=800&q=80',
                ],
            ],
            [
                'keywords' => ['general'],
                'contexts' => [
                    CategoryHomepageImage::CONTEXT_ADS => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=800&q=80',
                    CategoryHomepageImage::CONTEXT_VENDORS => 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=800&q=80',
                    CategoryHomepageImage::CONTEXT_SERVICES => 'https://images.unsplash.com/photo-1581092918056-0e12f16c08ab?w=800&q=80',
                    CategoryHomepageImage::CONTEXT_CONSULTANTS => 'https://images.unsplash.com/photo-1521737711867-e3b97375f020?w=800&q=80',
                ],
            ],
        ];

        foreach ($definitions as $definition) {
            $category = $this->findCategoryByKeywords($definition['keywords']);
            if (! $category) {
                continue;
            }

            foreach ($definition['contexts'] as $context => $imagePath) {
                CategoryHomepageImage::query()->updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'context' => $context,
                    ],
                    [
                        'image_path' => $imagePath,
                    ]
                );
            }
        }
    }

    /**
     * @param  list<string>  $keywords
     */
    private function findCategoryByKeywords(array $keywords): ?Category
    {
        foreach ($keywords as $keyword) {
            $match = Category::query()
                ->where('name', 'like', '%'.$keyword.'%')
                ->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')
                ->first();

            if ($match) {
                return $match;
            }
        }

        return null;
    }
}
