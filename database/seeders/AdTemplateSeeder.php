<?php

namespace Database\Seeders;

use App\Models\AdTemplate;
use App\Support\AdSizes;
use Illuminate\Database\Seeder;

class AdTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $schema = $this->schema();

        $templateConfigs = [
            [
                'name' => 'School Admissions Open 2026',
                'description' => 'Clean admissions layout for K-12 school enrollment campaigns.',
                'theme' => ['bg' => '#e0f2fe', 'surface' => '#ffffff', 'accent' => '#0369a1', 'accent2' => '#0ea5e9', 'text' => '#082f49'],
                'badge' => 'Admissions Open',
            ],
            [
                'name' => 'College Admission Notice',
                'description' => 'Professional college admission promotion with eligibility highlights.',
                'theme' => ['bg' => '#eff6ff', 'surface' => '#ffffff', 'accent' => '#1d4ed8', 'accent2' => '#3b82f6', 'text' => '#172554'],
                'badge' => 'Apply Now',
            ],
            [
                'name' => 'University Application Drive',
                'description' => 'Premium university campaign template for online application windows.',
                'theme' => ['bg' => '#f5f3ff', 'surface' => '#ffffff', 'accent' => '#6d28d9', 'accent2' => '#8b5cf6', 'text' => '#2e1065'],
                'badge' => '2026 Intake',
            ],
            [
                'name' => 'Coaching Class Enrollment',
                'description' => 'High-conversion educational coaching ad with faculty and batch details.',
                'theme' => ['bg' => '#ecfeff', 'surface' => '#ffffff', 'accent' => '#0f766e', 'accent2' => '#14b8a6', 'text' => '#042f2e'],
                'badge' => 'Limited Seats',
            ],
            [
                'name' => 'Restaurant Grand Opening',
                'description' => 'Vibrant restaurant launch ad for opening week footfall.',
                'theme' => ['bg' => '#fff7ed', 'surface' => '#ffffff', 'accent' => '#c2410c', 'accent2' => '#f97316', 'text' => '#431407'],
                'badge' => 'Opening Week',
            ],
            [
                'name' => 'New Shop or Mall Launch',
                'description' => 'Retail launch template for stores, mini marts, and shopping malls.',
                'theme' => ['bg' => '#fefce8', 'surface' => '#ffffff', 'accent' => '#a16207', 'accent2' => '#eab308', 'text' => '#422006'],
                'badge' => 'New Arrival',
            ],
            [
                'name' => 'Opening Ceremony Invitation',
                'description' => 'Elegant invitation style for ribbon-cutting and inauguration events.',
                'theme' => ['bg' => '#fdf4ff', 'surface' => '#ffffff', 'accent' => '#a21caf', 'accent2' => '#d946ef', 'text' => '#4a044e'],
                'badge' => 'You Are Invited',
            ],
            [
                'name' => 'Mega Sales Offer Campaign',
                'description' => 'Bold commercial sale template for festive and seasonal discounts.',
                'theme' => ['bg' => '#fef2f2', 'surface' => '#ffffff', 'accent' => '#b91c1c', 'accent2' => '#ef4444', 'text' => '#450a0a'],
                'badge' => 'Up To 70% OFF',
            ],
            [
                'name' => 'Furniture Sale Festival',
                'description' => 'Furniture showroom sale ad for sofas, beds, and office interiors.',
                'theme' => ['bg' => '#f8fafc', 'surface' => '#ffffff', 'accent' => '#334155', 'accent2' => '#64748b', 'text' => '#0f172a'],
                'badge' => 'Furniture Sale',
            ],
            [
                'name' => 'Electronics Clearance Sale',
                'description' => 'Modern gadget retail template for clearance and exchange offers.',
                'theme' => ['bg' => '#ecfeff', 'surface' => '#ffffff', 'accent' => '#155e75', 'accent2' => '#06b6d4', 'text' => '#083344'],
                'badge' => 'Clearance',
            ],
            [
                'name' => 'Health Clinic Opening',
                'description' => 'Trust-focused healthcare launch ad for clinics and diagnostic centers.',
                'theme' => ['bg' => '#ecfdf5', 'surface' => '#ffffff', 'accent' => '#166534', 'accent2' => '#22c55e', 'text' => '#052e16'],
                'badge' => 'Now Open',
            ],
            [
                'name' => 'Real Estate Open House',
                'description' => 'Professional property promotion for projects and open house events.',
                'theme' => ['bg' => '#eef2ff', 'surface' => '#ffffff', 'accent' => '#3730a3', 'accent2' => '#6366f1', 'text' => '#1e1b4b'],
                'badge' => 'Book a Visit',
            ],
        ];

        foreach (array_keys(AdSizes::all()) as $sizeType) {
            foreach ($templateConfigs as $config) {
                AdTemplate::query()->firstOrCreate(
                    [
                        'size_type' => $sizeType,
                        'name' => $config['name'],
                    ],
                    [
                        'description' => $config['description'],
                        'preview_image' => null,
                        'layout_html' => $this->buildProfessionalLayout($config['theme'], $config['badge']),
                        'schema_json' => $schema,
                        'is_active' => true,
                        'created_by' => null,
                    ]
                );
            }
        }
    }

    private function schema(): array
    {
        return [
            'fields' => [
                ['key' => 'headline', 'label' => 'Main Headline', 'type' => 'text', 'required' => true, 'max' => 70],
                ['key' => 'subheadline', 'label' => 'Subheadline', 'type' => 'text', 'required' => false, 'max' => 140],
                ['key' => 'badge', 'label' => 'Badge Text', 'type' => 'text', 'required' => false, 'max' => 30, 'default' => 'Limited Offer'],
                ['key' => 'line1', 'label' => 'Highlight 1', 'type' => 'text', 'required' => false, 'max' => 40],
                ['key' => 'line2', 'label' => 'Highlight 2', 'type' => 'text', 'required' => false, 'max' => 40],
                ['key' => 'line3', 'label' => 'Highlight 3', 'type' => 'text', 'required' => false, 'max' => 40],
                ['key' => 'offer_text', 'label' => 'Offer / Fee / Discount', 'type' => 'text', 'required' => false, 'max' => 45],
                ['key' => 'date_text', 'label' => 'Important Date', 'type' => 'text', 'required' => false, 'max' => 45],
                ['key' => 'location_text', 'label' => 'Location / Campus / Branch', 'type' => 'text', 'required' => false, 'max' => 60],
                ['key' => 'cta', 'label' => 'CTA Label', 'type' => 'text', 'required' => false, 'max' => 24],
                ['key' => 'phone', 'label' => 'Phone', 'type' => 'text', 'required' => false, 'max' => 24],
                ['key' => 'website', 'label' => 'Website / URL', 'type' => 'text', 'required' => false, 'max' => 60],
                ['key' => 'image_hero', 'label' => 'Hero Image', 'type' => 'image', 'required' => false],
                ['key' => 'image_1', 'label' => 'Supporting Image 1', 'type' => 'image', 'required' => false],
                ['key' => 'image_2', 'label' => 'Supporting Image 2', 'type' => 'image', 'required' => false],
            ],
        ];
    }

    /**
     * @param array{bg:string,surface:string,accent:string,accent2:string,text:string} $theme
     */
    private function buildProfessionalLayout(array $theme, string $badge): string
    {
        $bg = $theme['bg'];
        $surface = $theme['surface'];
        $accent = $theme['accent'];
        $accent2 = $theme['accent2'];
        $text = $theme['text'];

        return <<<HTML
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;overflow:hidden;border-radius:14px;background:{$bg};color:{$text};">
  <div style="position:absolute;inset:0;background:radial-gradient(1200px 640px at 92% 8%,{$accent2}22,transparent 60%),radial-gradient(900px 520px at 10% 95%,{$accent}22,transparent 58%);"></div>

  <div style="position:absolute;left:4%;right:4%;top:6%;bottom:6%;display:grid;grid-template-columns:1.15fr .85fr;gap:14px;align-items:stretch;">
    <div style="background:{$surface}dd;border:1px solid {$accent}26;border-radius:14px;padding:14px 14px 12px 14px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 10px 26px rgba(15,23,42,.08);">
      <div>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
          <span style="display:inline-flex;padding:6px 11px;border-radius:999px;background:{$accent};color:#fff;font-weight:900;font-size:11px;letter-spacing:.04em;text-transform:uppercase;">{{badge}}</span>
          <span style="font-size:11px;font-weight:700;color:{$accent};">{{date_text}}</span>
        </div>

        <div style="margin-top:10px;font-size:34px;line-height:1.04;font-weight:950;letter-spacing:-.01em;">{{headline}}</div>
        <div style="margin-top:8px;font-size:13px;line-height:1.45;font-weight:600;opacity:.9;">{{subheadline}}</div>

        <div style="margin-top:10px;display:grid;gap:6px;">
          <div style="font-size:12px;font-weight:800;display:flex;gap:8px;align-items:center;"><span style="width:7px;height:7px;border-radius:999px;background:{$accent};"></span>{{line1}}</div>
          <div style="font-size:12px;font-weight:800;display:flex;gap:8px;align-items:center;"><span style="width:7px;height:7px;border-radius:999px;background:{$accent2};"></span>{{line2}}</div>
          <div style="font-size:12px;font-weight:800;display:flex;gap:8px;align-items:center;"><span style="width:7px;height:7px;border-radius:999px;background:{$accent};"></span>{{line3}}</div>
        </div>
      </div>

      <div style="margin-top:12px;display:grid;gap:8px;">
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
          <span style="display:inline-flex;padding:8px 12px;border-radius:10px;background:linear-gradient(90deg,{$accent},{$accent2});color:#fff;font-weight:900;font-size:12px;">{{cta}}</span>
          <span style="font-size:12px;font-weight:900;color:{$accent};">{{offer_text}}</span>
        </div>
        <div style="font-size:11px;font-weight:800;opacity:.9;">{{location_text}}</div>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
          <span style="font-size:11px;font-weight:800;">{{phone}}</span>
          <span style="font-size:11px;font-weight:700;opacity:.85;">{{website}}</span>
        </div>
      </div>
    </div>

    <div style="display:grid;grid-template-rows:1.15fr .85fr;gap:10px;">
      <div style="border-radius:14px;overflow:hidden;border:1px solid {$accent}2f;box-shadow:0 12px 24px rgba(15,23,42,.12);background:#fff;">
        <img data-ad-key="image_hero" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div style="border-radius:12px;overflow:hidden;border:1px solid {$accent}26;background:#fff;">
          <img data-ad-key="image_1" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">
        </div>
        <div style="border-radius:12px;overflow:hidden;border:1px solid {$accent}26;background:#fff;">
          <img data-ad-key="image_2" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">
        </div>
      </div>
    </div>
  </div>

  <div style="position:absolute;right:7%;top:8%;padding:5px 10px;border-radius:999px;background:{$surface};border:1px solid {$accent}3a;color:{$accent};font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.06em;">{$badge}</div>
</div>
HTML;
    }
}
