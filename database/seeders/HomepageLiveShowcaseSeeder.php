<?php

namespace Database\Seeders;

use App\Models\HomepageShowcaseItem;
use Illuminate\Database\Seeder;

/**
 * The Featured Businesses and Offers showcase cards as shown on the live homepage (October 2026).
 * Images are copies of the live uploads in public/assets/images/homepage-seed (converted to WebP).
 * Safe to re-run: cards are matched by type + title and updated, other cards are left alone.
 */
class HomepageLiveShowcaseSeeder extends Seeder
{
    private const IMAGE_DIR = 'assets/images/homepage-seed/';

    public function run(): void
    {
        // [title, border colour, link, image]
        $featured = [
            ["Discover What's Around You", 'orange', '/vendors', 'featured-discover-around-you.webp'],
            ['Learn & Explore', 'green', '/vendors', 'featured-learn-explore.webp'],
            ['City Electronics', 'red', '/vendors', 'featured-city-electronics.webp'],
            ['Blossom Boutique', 'green', '/vendors', 'featured-blossom-boutique.webp'],
            ['Discover Local', 'orange', '/vendors', 'featured-discover-local.webp'],
            ['FreshMart Supermarket', 'green', '/vendors', 'featured-freshmart.webp'],
            ['Offers', 'orange', '/', 'featured-offers.webp'],
        ];

        foreach ($featured as $index => [$title, $tone, $link, $image]) {
            HomepageShowcaseItem::query()->updateOrCreate(
                ['type' => HomepageShowcaseItem::TYPE_FEATURED_BUSINESS, 'title' => $title],
                [
                    'link_url' => $link,
                    'image_path' => self::IMAGE_DIR.$image,
                    'category_tone' => $tone,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }

        // [title, description, location, category label, colour, icon, image]
        $offers = [
            ['Spice Garden', 'North Indian, Chinese & Continental', 'Rajpur Road, Dehradun', 'Restaurant', 'brown', 'fa-utensils', 'offer-spice-garden.webp'],
            ['Style & Glow Salon', 'Hair, skin & bridal packages', 'Dalanwala, Dehradun', 'Salon & Spa', 'purple', 'fa-spa', 'offer-style-glow-salon.webp'],
            ['FreshMart Supermarket', 'Daily essentials & fresh produce', 'ISBT Road, Dehradun', 'Grocery Store', 'green', 'fa-cart-shopping', 'offer-freshmart.webp'],
            ['TechWorld Electronics', 'TVs, laptops, mobiles & accessories', 'Rajpur Road, Dehradun', 'Electronics', 'blue', 'fa-laptop', 'offer-techworld-electronics.webp'],
            ['Books & Stationary', 'Books & Stationary', 'Dehradun , Uttaranchal', 'Books & Stationary', 'orange', 'fa-store', 'offer-books-stationary.webp'],
            ['Home Improvement', 'Home Improvement', 'Dehradun , Uttaranchal', 'Home Improvement', 'orange', 'fa-store', 'offer-home-improvement.webp'],
        ];

        foreach ($offers as $index => [$title, $description, $location, $category, $tone, $icon, $image]) {
            HomepageShowcaseItem::query()->updateOrCreate(
                ['type' => HomepageShowcaseItem::TYPE_OFFER_PROMO, 'title' => $title],
                [
                    'description' => $description,
                    'link_url' => '/offers-market',
                    'location' => $location,
                    'category_label' => $category,
                    'category_tone' => $tone,
                    'category_icon' => $icon,
                    // Live shows "SPECIAL OFFER" and "Limited time": no badge or end date set
                    'discount_badge' => null,
                    'valid_until' => null,
                    'image_path' => self::IMAGE_DIR.$image,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
