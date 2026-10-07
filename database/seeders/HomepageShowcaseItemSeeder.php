<?php

namespace Database\Seeders;

use App\Models\HomepageShowcaseItem;
use Illuminate\Database\Seeder;

class HomepageShowcaseItemSeeder extends Seeder
{
    public function run(): void
    {
        if (HomepageShowcaseItem::query()->exists()) {
            return;
        }

        $vendorsUrl = route('frontend.vendors.index');
        $offersUrl = route('frontend.offers.index');

        $featured = [
            [
                'title' => 'Himalaya Bakers',
                'category_label' => 'Bakery & Snacks',
                'location' => 'Dehradun',
                'rating' => 4.6,
                'review_count' => 128,
                'image_path' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=900&q=80',
                'link_url' => $vendorsUrl,
                'category_tone' => 'orange',
                'category_icon' => 'fa-store',
                'headline' => 'Up to 30% off',
                'subheadline' => 'Fresh bakery picks from trusted local favourites',
                'promo_badge' => 'Featured Deal',
                'promo_sub' => 'Bakery & Snacks · Dehradun',
                'strip_primary' => 'Exclusive offers when you enquire via SoilnWater',
                'strip_secondary' => '4.6★ rating · 128 reviews · Same-day pickup',
                'sort_order' => 1,
            ],
            [
                'title' => 'GreenCare Nursery',
                'category_label' => 'Plants & Home',
                'location' => 'Dehradun',
                'rating' => 4.8,
                'review_count' => 86,
                'image_path' => 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=900&q=80',
                'link_url' => $vendorsUrl,
                'category_tone' => 'green',
                'category_icon' => 'fa-leaf',
                'headline' => 'Up to 50% off',
                'subheadline' => 'Seasonal plants, pots and home garden essentials',
                'promo_badge' => 'Local Spotlight',
                'promo_sub' => 'Plants & Home · Dehradun',
                'strip_primary' => 'Free guidance on indoor & outdoor plants',
                'strip_secondary' => '4.8★ rating · 86 reviews · Home delivery',
                'sort_order' => 2,
            ],
            [
                'title' => 'City Electronics',
                'category_label' => 'Electronics',
                'location' => 'Dehradun',
                'rating' => 4.5,
                'review_count' => 214,
                'image_path' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=900&q=80',
                'link_url' => $vendorsUrl,
                'category_tone' => 'red',
                'category_icon' => 'fa-laptop',
                'headline' => 'Festive deals',
                'subheadline' => 'Appliances, gadgets and accessories on offer',
                'promo_badge' => 'Hot Picks',
                'promo_sub' => 'Electronics · Dehradun',
                'strip_primary' => 'Extended warranty on select brands this month',
                'strip_secondary' => '4.5★ rating · 214 reviews · EMI available',
                'sort_order' => 3,
            ],
            [
                'title' => 'Blossom Boutique',
                'category_label' => 'Clothing',
                'location' => 'Dehradun',
                'rating' => 4.7,
                'review_count' => 67,
                'image_path' => 'https://images.unsplash.com/photo-1441986300917-64676bd600d8?w=900&q=80',
                'link_url' => $vendorsUrl,
                'category_tone' => 'green',
                'category_icon' => 'fa-shirt',
                'headline' => 'New arrivals',
                'subheadline' => 'Ethnic wear, casuals and festive collections',
                'promo_badge' => 'Featured Deal',
                'promo_sub' => 'Clothing · Dehradun',
                'strip_primary' => 'Styling help and alteration support in-store',
                'strip_secondary' => '4.7★ rating · 67 reviews · Try at shop',
                'sort_order' => 4,
            ],
            [
                'title' => 'Spice Garden Restaurant',
                'category_label' => 'Restaurant',
                'location' => 'Rajpur Road, Dehradun',
                'rating' => 4.5,
                'review_count' => 182,
                'image_path' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=900&q=80',
                'link_url' => $vendorsUrl,
                'category_tone' => 'orange',
                'category_icon' => 'fa-utensils',
                'headline' => 'Dining deals',
                'subheadline' => 'North Indian, Chinese & Continental',
                'promo_badge' => 'Featured Deal',
                'promo_sub' => 'Restaurant · Rajpur Road',
                'strip_primary' => 'Table booking and takeaway available',
                'strip_secondary' => '4.5★ rating · 182 reviews',
                'sort_order' => 5,
            ],
            [
                'title' => 'FreshMart Supermarket',
                'category_label' => 'Grocery Store',
                'location' => 'ISBT Road, Dehradun',
                'rating' => 4.3,
                'review_count' => 156,
                'image_path' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=900&q=80',
                'link_url' => $vendorsUrl,
                'category_tone' => 'green',
                'category_icon' => 'fa-cart-shopping',
                'headline' => 'Daily essentials',
                'subheadline' => 'Fresh fruits, vegetables and household needs',
                'promo_badge' => 'Local Spotlight',
                'promo_sub' => 'Grocery · ISBT Road',
                'strip_primary' => 'Home delivery in select areas',
                'strip_secondary' => '4.3★ rating · 156 reviews',
                'sort_order' => 6,
            ],
        ];

        foreach ($featured as $row) {
            HomepageShowcaseItem::create(array_merge($row, [
                'type' => HomepageShowcaseItem::TYPE_FEATURED_BUSINESS,
                'is_active' => true,
            ]));
        }

        $offers = [
            [
                'title' => 'Spice Garden',
                'description' => 'North Indian, Chinese & Continental',
                'category_label' => 'Restaurant',
                'category_tone' => 'brown',
                'category_icon' => 'fa-utensils',
                'discount_badge' => '20% OFF',
                'location' => 'Rajpur Road, Dehradun',
                'valid_until' => '2026-09-30',
                'image_path' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=800&q=80',
                'link_url' => $offersUrl,
                'sort_order' => 1,
            ],
            [
                'title' => 'Style & Glow Salon',
                'description' => 'Hair, skin & bridal packages',
                'category_label' => 'Salon & Spa',
                'category_tone' => 'purple',
                'category_icon' => 'fa-spa',
                'discount_badge' => '30% OFF',
                'location' => 'Dalanwala, Dehradun',
                'valid_until' => '2026-10-15',
                'image_path' => 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=800&q=80',
                'link_url' => $offersUrl,
                'sort_order' => 2,
            ],
            [
                'title' => 'FreshMart Supermarket',
                'description' => 'Daily essentials & fresh produce',
                'category_label' => 'Grocery Store',
                'category_tone' => 'green',
                'category_icon' => 'fa-cart-shopping',
                'discount_badge' => 'BUY 1 GET 1 FREE',
                'location' => 'ISBT Road, Dehradun',
                'valid_until' => '2026-10-20',
                'image_path' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=800&q=80',
                'link_url' => $offersUrl,
                'sort_order' => 3,
            ],
            [
                'title' => 'TechWorld Electronics',
                'description' => 'TVs, laptops, mobiles & accessories',
                'category_label' => 'Electronics',
                'category_tone' => 'blue',
                'category_icon' => 'fa-laptop',
                'discount_badge' => 'UP TO 40% OFF',
                'location' => 'Rajpur Road, Dehradun',
                'valid_until' => '2026-12-31',
                'image_path' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
                'link_url' => $offersUrl,
                'sort_order' => 4,
            ],
        ];

        foreach ($offers as $row) {
            HomepageShowcaseItem::create(array_merge($row, [
                'type' => HomepageShowcaseItem::TYPE_OFFER_PROMO,
                'is_active' => true,
            ]));
        }
    }
}
