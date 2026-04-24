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

        foreach (array_keys(AdSizes::all()) as $sizeType) {
            foreach ($this->templateDefinitions() as $template) {
                $layoutHtml = $this->layoutForSize(
                    (string) $template['layout_html'],
                    (string) $template['name'],
                    $sizeType
                );

                AdTemplate::query()->updateOrCreate(
                    [
                        'size_type' => $sizeType,
                        'name' => $template['name'],
                    ],
                    [
                        'description' => $template['description'],
                        'preview_image' => null,
                        'layout_html' => $layoutHtml,
                        'schema_json' => $schema,
                        'is_active' => true,
                        'created_by' => null,
                    ]
                );
            }
        }
    }

    /**
     * @return array<int, array{name:string, description:string, layout_html:string}>
     */
    private function templateDefinitions(): array
    {
        return [
            [
                'name' => 'School Admissions Open 2026',
                'description' => 'Modern ribbon layout for school admissions and K-12 enrollment campaigns.',
                'layout_html' => $this->layoutRibbonAdmissions(),
            ],
            [
                'name' => 'College Admission Notice',
                'description' => 'Split card style for college notices with timeline and application CTA.',
                'layout_html' => $this->layoutSplitCollege(),
            ],
            [
                'name' => 'University Admissions 2026',
                'description' => 'Editorial admissions creative for university applications and enrollment windows.',
                'layout_html' => $this->layoutEditorialUniversity(),
            ],
            [
                'name' => 'Coaching Class Enrollment',
                'description' => 'Classroom board-inspired design for coaching admissions.',
                'layout_html' => $this->layoutBoardCoaching(),
            ],
            [
                'name' => 'Hotel Grand Opening',
                'description' => 'Luxury hospitality launch banner for hotel and resort openings.',
                'layout_html' => $this->layoutRestaurantLaunch(),
            ],
            [
                'name' => 'New Shop Opening',
                'description' => 'Modern retail launch template for new shop openings and branch announcements.',
                'layout_html' => $this->layoutRetailLaunch(),
            ],
            [
                'name' => 'Cafe Opening Launch',
                'description' => 'Stylish cafe launch design with premium invitation-like aesthetics.',
                'layout_html' => $this->layoutCeremonyInvite(),
            ],
            [
                'name' => 'Salon Opening Special',
                'description' => 'Premium beauty salon launch design with service highlights and opening offer.',
                'layout_html' => $this->layoutMegaSaleBurst(),
            ],
            [
                'name' => 'Shop Mega Sales Offer',
                'description' => 'High-conversion sales design for seasonal shop discounts and festival deals.',
                'layout_html' => $this->layoutFurnitureMinimal(),
            ],
            [
                'name' => 'Products For Sale Campaign',
                'description' => 'Modern product selling ad template for e-commerce and local stores.',
                'layout_html' => $this->layoutElectronicsNeon(),
            ],
            [
                'name' => 'Properties For Sale Showcase',
                'description' => 'Professional real-estate layout for apartments, plots, and property promotions.',
                'layout_html' => $this->layoutHealthClinic(),
            ],
            [
                'name' => 'Products For Rent Promotion',
                'description' => 'Clean rental product ad for electronics, furniture, tools, and event inventory.',
                'layout_html' => $this->layoutRealEstate(),
            ],
        ];
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

    private function layoutForSize(string $defaultLayout, string $templateName, string $sizeType): string
    {
        $size = AdSizes::all()[$sizeType] ?? null;
        if (!$size) {
            return $defaultLayout;
        }

        $w = (int) ($size['w'] ?? 0);
        $h = (int) ($size['h'] ?? 0);
        $area = $w * $h;
        $ratio = $h > 0 ? ($w / $h) : 1.0;

        // For very small placements, use a strict compact layout:
        // fewer text blocks and a single image so content doesn't overflow.
        if ($w <= 320 || $h <= 180 || $area <= 110000) {
            if ($ratio >= 1.5) {
                return $this->compactHorizontalLayout($templateName);
            }
            if ($ratio <= 0.75) {
                return $this->compactVerticalLayout($templateName);
            }

            return $this->compactSquareLayout($templateName);
        }

        return $defaultLayout;
    }

    private function compactHorizontalLayout(string $templateName): string
    {
        $type = $this->compactType($templateName);

        if ($type === 'admissions') {
            return <<<'HTML'
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:Inter,sans-serif;overflow:hidden;border-radius:10px;background:linear-gradient(120deg,#e0f2fe,#dbeafe);">
  <div style="position:absolute;left:0;top:0;bottom:0;width:66%;padding:4% 5%;display:flex;flex-direction:column;justify-content:space-between;">
    <div>
      <span style="display:inline-flex;padding:4px 8px;border-radius:999px;background:#1d4ed8;color:#fff;font-size:10px;font-weight:900;line-height:1;">{{badge}}</span>
      <div style="margin-top:6px;font-size:22px;line-height:1.02;font-weight:900;color:#0f172a;">{{headline}}</div>
      <div style="margin-top:4px;font-size:11px;line-height:1.3;font-weight:600;color:#334155;">{{subheadline}}</div>
    </div>
    <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
      <span style="padding:4px 8px;border-radius:8px;background:#1e40af;color:#fff;font-size:10px;font-weight:900;">{{cta}}</span>
      <span style="font-size:10px;font-weight:800;color:#1e3a8a;">{{offer_text}}</span>
    </div>
  </div>
  <div style="position:absolute;right:0;top:0;bottom:0;width:34%;overflow:hidden;border-left:1px solid rgba(15,23,42,.14);">
    <img data-ad-key="image_hero" src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1000&q=80" alt="" style="width:100%;height:100%;object-fit:cover;">
  </div>
</div>
HTML;
        }

        if ($type === 'coaching') {
            return <<<'HTML'
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:Inter,sans-serif;overflow:hidden;border-radius:10px;background:linear-gradient(120deg,#ccfbf1,#d1fae5);">
  <div style="position:absolute;left:2%;right:2%;top:8%;bottom:8%;display:grid;grid-template-columns:62% 38%;gap:6px;">
    <div style="background:#042f2ecc;border:1px solid rgba(20,184,166,.35);border-radius:8px;padding:6px;color:#ecfeff;display:flex;flex-direction:column;justify-content:space-between;">
      <div>
        <div style="font-size:10px;font-weight:900;color:#5eead4;text-transform:uppercase;">{{badge}}</div>
        <div style="margin-top:4px;font-size:20px;line-height:1.02;font-weight:900;">{{headline}}</div>
      </div>
      <div style="display:flex;justify-content:space-between;gap:6px;align-items:center;">
        <span style="padding:4px 7px;border-radius:7px;background:#14b8a6;color:#042f2e;font-size:10px;font-weight:900;">{{cta}}</span>
        <span style="font-size:10px;color:#99f6e4;font-weight:800;">{{offer_text}}</span>
      </div>
    </div>
    <div style="border-radius:8px;overflow:hidden;border:1px solid rgba(15,23,42,.14);"><img data-ad-key="image_hero" src="https://images.unsplash.com/photo-1519452575417-564c1401ecc0?auto=format&fit=crop&w=1000&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
  </div>
</div>
HTML;
        }

        if ($type === 'opening') {
            return <<<'HTML'
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:Inter,sans-serif;overflow:hidden;border-radius:10px;background:linear-gradient(120deg,#fff7ed,#ffedd5);">
  <div style="position:absolute;left:0;top:0;bottom:0;width:60%;padding:5% 5%;display:flex;flex-direction:column;justify-content:space-between;">
    <div>
      <span style="display:inline-flex;padding:4px 8px;border-radius:999px;background:#c2410c;color:#fff;font-size:10px;font-weight:900;">{{badge}}</span>
      <div style="margin-top:6px;font-size:21px;line-height:1.02;font-weight:900;color:#7c2d12;">{{headline}}</div>
      <div style="margin-top:4px;font-size:10px;font-weight:700;color:#9a3412;">{{date_text}}</div>
    </div>
    <span style="display:inline-flex;align-self:flex-start;padding:4px 8px;border-radius:8px;background:#ea580c;color:#fff;font-size:10px;font-weight:900;">{{cta}}</span>
  </div>
  <div style="position:absolute;right:2%;top:8%;bottom:8%;width:36%;border-radius:10px;overflow:hidden;border:1px solid rgba(124,45,18,.2);"><img data-ad-key="image_hero" src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1000&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
</div>
HTML;
        }

        if ($type === 'sale') {
            return <<<'HTML'
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:Inter,sans-serif;overflow:hidden;border-radius:10px;background:linear-gradient(120deg,#fee2e2,#fecaca);">
  <div style="position:absolute;left:3%;top:10%;bottom:10%;width:62%;display:flex;flex-direction:column;justify-content:space-between;">
    <div>
      <span style="display:inline-flex;padding:4px 8px;border-radius:6px;background:#b91c1c;color:#fff;font-size:10px;font-weight:900;">{{badge}}</span>
      <div style="margin-top:5px;font-size:20px;line-height:1.02;font-weight:900;color:#7f1d1d;">{{headline}}</div>
      <div style="margin-top:4px;font-size:10px;color:#991b1b;font-weight:700;">{{offer_text}}</div>
    </div>
    <span style="display:inline-flex;align-self:flex-start;padding:4px 8px;border-radius:8px;background:#dc2626;color:#fff;font-size:10px;font-weight:900;">{{cta}}</span>
  </div>
  <div style="position:absolute;right:3%;top:12%;bottom:12%;width:32%;border-radius:999px;overflow:hidden;border:2px solid rgba(255,255,255,.8);"><img data-ad-key="image_hero" src="https://images.unsplash.com/photo-1556740758-90de374c12ad?auto=format&fit=crop&w=1000&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
</div>
HTML;
        }

        return <<<'HTML'
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:Inter,sans-serif;overflow:hidden;border-radius:10px;background:linear-gradient(120deg,#e0e7ff,#c7d2fe);">
  <div style="position:absolute;left:4%;top:10%;bottom:10%;width:60%;display:flex;flex-direction:column;justify-content:space-between;">
    <div>
      <span style="display:inline-flex;padding:4px 8px;border-radius:999px;background:#4338ca;color:#fff;font-size:10px;font-weight:900;">{{badge}}</span>
      <div style="margin-top:6px;font-size:20px;line-height:1.03;font-weight:900;color:#312e81;">{{headline}}</div>
      <div style="margin-top:4px;font-size:10px;color:#3730a3;font-weight:700;">{{location_text}}</div>
    </div>
    <span style="display:inline-flex;align-self:flex-start;padding:4px 8px;border-radius:8px;background:#312e81;color:#fff;font-size:10px;font-weight:900;">{{cta}}</span>
  </div>
  <div style="position:absolute;right:3%;top:10%;bottom:10%;width:34%;border-radius:10px;overflow:hidden;border:1px solid rgba(49,46,129,.25);"><img data-ad-key="image_hero" src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=1000&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
</div>
HTML;
    }

    private function compactVerticalLayout(string $templateName): string
    {
        $type = $this->compactType($templateName);
        $bg = $type === 'sale' ? 'linear-gradient(160deg,#fee2e2,#fecaca)' : ($type === 'opening' ? 'linear-gradient(160deg,#fff7ed,#ffedd5)' : 'linear-gradient(160deg,#f0f9ff,#e2e8f0)');
        $accent = $type === 'sale' ? '#b91c1c' : ($type === 'opening' ? '#c2410c' : '#1d4ed8');

        $html = <<<'HTML'
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:Inter,sans-serif;overflow:hidden;border-radius:10px;background:BG_REPLACE;">
  <div style="position:absolute;left:6%;right:6%;top:4%;bottom:4%;display:grid;grid-template-rows:44% 56%;gap:6px;">
    <div style="border-radius:10px;overflow:hidden;border:1px solid rgba(30,41,59,.14);">
      <img data-ad-key="image_hero" src="https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?auto=format&fit=crop&w=1000&q=80" alt="" style="width:100%;height:100%;object-fit:cover;">
    </div>
    <div style="background:#ffffffd6;border:1px solid rgba(30,41,59,.12);border-radius:10px;padding:8px;display:flex;flex-direction:column;justify-content:space-between;">
      <div>
        <div style="font-size:10px;font-weight:900;color:ACCENT_REPLACE;text-transform:uppercase;">{{badge}}</div>
        <div style="margin-top:4px;font-size:18px;line-height:1.02;font-weight:900;color:#0f172a;">{{headline}}</div>
        <div style="margin-top:3px;font-size:11px;line-height:1.3;font-weight:600;color:#334155;">{{subheadline}}</div>
      </div>
      <span style="display:inline-flex;align-self:flex-start;padding:5px 8px;border-radius:8px;background:ACCENT_REPLACE;color:#fff;font-size:10px;font-weight:900;">{{cta}}</span>
    </div>
  </div>
</div>
HTML;
        $html = str_replace('BG_REPLACE', $bg, $html);
        return str_replace('ACCENT_REPLACE', $accent, $html);
    }

    private function compactSquareLayout(string $templateName): string
    {
        $type = $this->compactType($templateName);
        $accent = $type === 'sale' ? '#b91c1c' : ($type === 'opening' ? '#c2410c' : '#1e3a8a');

        $html = <<<'HTML'
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:Inter,sans-serif;overflow:hidden;border-radius:12px;background:linear-gradient(145deg,#eff6ff,#f8fafc);">
  <div style="position:absolute;left:6%;right:6%;top:6%;bottom:6%;display:grid;grid-template-rows:1fr auto;gap:8px;">
    <div style="border-radius:12px;overflow:hidden;border:1px solid rgba(30,58,138,.2);">
      <img data-ad-key="image_hero" src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=1000&q=80" alt="" style="width:100%;height:100%;object-fit:cover;">
    </div>
    <div style="background:#fff;border:1px solid rgba(30,58,138,.2);border-radius:10px;padding:8px;">
      <div style="display:flex;justify-content:space-between;gap:6px;align-items:center;">
        <span style="font-size:10px;font-weight:900;color:ACCENT_REPLACE;">{{badge}}</span>
        <span style="font-size:10px;font-weight:800;color:#334155;">{{offer_text}}</span>
      </div>
      <div style="margin-top:4px;font-size:18px;line-height:1.03;font-weight:900;color:#0f172a;">{{headline}}</div>
      <div style="margin-top:4px;font-size:11px;font-weight:600;color:#334155;">{{subheadline}}</div>
    </div>
  </div>
</div>
HTML;
        return str_replace('ACCENT_REPLACE', $accent, $html);
    }

    private function compactType(string $templateName): string
    {
        $name = mb_strtolower($templateName);

        if (str_contains($name, 'admission') || str_contains($name, 'school') || str_contains($name, 'college') || str_contains($name, 'university')) {
            return 'admissions';
        }
        if (str_contains($name, 'coaching')) {
            return 'coaching';
        }
        if (str_contains($name, 'opening') || str_contains($name, 'cafe') || str_contains($name, 'hotel') || str_contains($name, 'salon')) {
            return 'opening';
        }
        if (str_contains($name, 'sale') || str_contains($name, 'offer')) {
            return 'sale';
        }

        return 'property';
    }

    private function layoutRibbonAdmissions(): string
    {
        return <<<'HTML'
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:Poppins,Inter,sans-serif;overflow:hidden;border-radius:14px;background:linear-gradient(120deg,#dbeafe,#e0f2fe);">
  <div style="position:absolute;left:-10%;top:-25%;width:60%;height:90%;background:rgba(2,132,199,.18);transform:rotate(18deg);"></div>
  <div style="position:absolute;right:-18%;bottom:-30%;width:58%;height:95%;background:rgba(30,64,175,.16);transform:rotate(-16deg);"></div>

  <div style="position:absolute;left:4%;top:10%;bottom:10%;width:56%;display:flex;flex-direction:column;justify-content:space-between;">
    <div>
      <span style="display:inline-block;padding:6px 11px;border-radius:999px;background:#0369a1;color:#fff;font-size:11px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;">{{badge}}</span>
      <div style="margin-top:10px;font-size:36px;line-height:1.03;font-weight:900;color:#0c4a6e;">{{headline}}</div>
      <div style="margin-top:8px;font-size:13px;font-weight:600;color:#0f172a;opacity:.85;">{{subheadline}}</div>
      <div style="margin-top:10px;display:grid;gap:5px;font-size:12px;font-weight:700;color:#082f49;">
        <div>• {{line1}}</div><div>• {{line2}}</div><div>• {{line3}}</div>
      </div>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
      <span style="padding:8px 12px;border-radius:10px;background:#075985;color:#fff;font-size:12px;font-weight:800;">{{cta}}</span>
      <span style="font-size:11px;font-weight:800;color:#0c4a6e;">{{date_text}}</span>
      <span style="font-size:11px;font-weight:700;color:#075985;">{{phone}}</span>
    </div>
  </div>

  <div style="position:absolute;right:4%;top:10%;bottom:10%;width:34%;display:grid;grid-template-rows:1fr 1fr;gap:8px;">
    <div style="border-radius:16px;overflow:hidden;border:3px solid rgba(255,255,255,.8);box-shadow:0 14px 24px rgba(2,132,199,.25);"><img data-ad-key="image_hero" src="https://images.unsplash.com/photo-1588072432836-e10032774350?auto=format&fit=crop&w=1200&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
      <div style="border-radius:12px;overflow:hidden;border:2px solid rgba(255,255,255,.8);"><img data-ad-key="image_1" src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=800&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
      <div style="border-radius:12px;overflow:hidden;border:2px solid rgba(255,255,255,.8);"><img data-ad-key="image_2" src="https://images.unsplash.com/photo-1513258496099-48168024aec0?auto=format&fit=crop&w=800&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
    </div>
  </div>
</div>
HTML;
    }

    private function layoutSplitCollege(): string
    {
        return <<<'HTML'
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:Nunito Sans,Inter,sans-serif;overflow:hidden;border-radius:14px;background:#f8fafc;">
  <div style="position:absolute;left:0;top:0;bottom:0;width:48%;background:linear-gradient(160deg,#1d4ed8,#2563eb 60%,#60a5fa);"></div>
  <div style="position:absolute;right:0;top:0;bottom:0;width:52%;background:linear-gradient(180deg,#eff6ff,#dbeafe);"></div>

  <div style="position:absolute;left:4%;top:9%;bottom:9%;width:41%;display:flex;flex-direction:column;justify-content:space-between;color:#fff;">
    <div>
      <div style="font-size:11px;font-weight:900;letter-spacing:.08em;text-transform:uppercase;opacity:.9;">{{badge}}</div>
      <div style="margin-top:10px;font-size:32px;line-height:1.04;font-weight:900;">{{headline}}</div>
      <div style="margin-top:8px;font-size:13px;font-weight:600;opacity:.9;">{{subheadline}}</div>
    </div>
    <div style="display:grid;gap:6px;font-size:12px;font-weight:700;">
      <div>{{line1}}</div><div>{{line2}}</div><div>{{line3}}</div>
      <div style="margin-top:4px;padding-top:6px;border-top:1px solid rgba(255,255,255,.35);font-size:11px;">{{website}}</div>
    </div>
  </div>

  <div style="position:absolute;right:4%;top:10%;bottom:10%;width:46%;display:grid;grid-template-rows:1fr auto;gap:8px;">
    <div style="border-radius:10px;overflow:hidden;border:1px solid rgba(29,78,216,.16);box-shadow:0 8px 22px rgba(37,99,235,.18);"><img data-ad-key="image_hero" src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1200&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
    <div style="background:#fff;border-radius:10px;border:1px solid rgba(29,78,216,.2);padding:8px;display:flex;justify-content:space-between;gap:8px;align-items:center;">
      <div style="font-size:11px;color:#1e3a8a;font-weight:800;">{{date_text}}<br><span style="color:#334155;font-weight:700;">{{location_text}}</span></div>
      <span style="padding:7px 10px;border-radius:8px;background:#1d4ed8;color:#fff;font-size:11px;font-weight:900;white-space:nowrap;">{{cta}}</span>
    </div>
  </div>
</div>
HTML;
    }

    private function layoutEditorialUniversity(): string
    {
        return <<<'HTML'
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:'DM Serif Display',Georgia,serif;overflow:hidden;border-radius:14px;background:linear-gradient(145deg,#ede9fe,#faf5ff);">
  <div style="position:absolute;inset:0;background-image:repeating-linear-gradient(90deg,rgba(109,40,217,.08) 0 1px,transparent 1px 24px);"></div>

  <div style="position:absolute;left:6%;top:11%;right:6%;bottom:11%;display:grid;grid-template-columns:1.05fr .95fr;gap:14px;">
    <div style="display:flex;flex-direction:column;justify-content:space-between;">
      <div>
        <div style="font-family:Inter,sans-serif;font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#6d28d9;">{{badge}}</div>
        <div style="margin-top:8px;font-size:38px;line-height:1.02;color:#3b0764;font-weight:400;">{{headline}}</div>
        <div style="margin-top:8px;font-family:Inter,sans-serif;font-size:13px;color:#4c1d95;font-weight:600;">{{subheadline}}</div>
      </div>
      <div style="font-family:Inter,sans-serif;font-size:12px;color:#581c87;font-weight:700;display:grid;gap:5px;">
        <div>— {{line1}}</div><div>— {{line2}}</div><div>— {{line3}}</div>
      </div>
    </div>

    <div style="background:#fff;border:1px solid rgba(109,40,217,.28);border-radius:16px;padding:8px;display:grid;grid-template-rows:1fr auto;gap:8px;">
      <div style="border-radius:12px;overflow:hidden;"><img data-ad-key="image_hero" src="https://images.unsplash.com/photo-1498243691581-b145c3f54a5a?auto=format&fit=crop&w=1200&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
      <div style="font-family:Inter,sans-serif;background:#faf5ff;border-radius:10px;padding:8px;display:grid;gap:5px;">
        <div style="font-size:11px;color:#6d28d9;font-weight:800;">{{offer_text}}</div>
        <div style="font-size:10px;color:#6b21a8;font-weight:700;">{{date_text}}</div>
        <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
          <span style="font-size:10px;color:#7e22ce;font-weight:700;">{{phone}}</span>
          <span style="padding:5px 10px;border-radius:999px;background:#6d28d9;color:#fff;font-size:10px;font-family:Inter,sans-serif;font-weight:900;">{{cta}}</span>
        </div>
      </div>
    </div>
  </div>
</div>
HTML;
    }

    private function layoutBoardCoaching(): string
    {
        return <<<'HTML'
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:Inter,sans-serif;overflow:hidden;border-radius:14px;background:#ccfbf1;">
  <div style="position:absolute;inset:0;background:linear-gradient(180deg,rgba(13,148,136,.12),rgba(20,184,166,.24));"></div>
  <div style="position:absolute;left:5%;right:5%;top:12%;bottom:12%;background:#042f2e;border-radius:12px;border:4px solid #115e59;box-shadow:inset 0 0 0 2px rgba(255,255,255,.08),0 14px 24px rgba(6,78,59,.26);padding:12px;display:grid;grid-template-columns:1.2fr .8fr;gap:10px;color:#ecfeff;">
    <div style="display:flex;flex-direction:column;justify-content:space-between;">
      <div>
        <div style="font-size:11px;font-weight:900;letter-spacing:.08em;text-transform:uppercase;color:#99f6e4;">{{badge}}</div>
        <div style="margin-top:8px;font-size:31px;line-height:1.02;font-weight:900;">{{headline}}</div>
        <div style="margin-top:8px;font-size:12px;color:#a7f3d0;font-weight:600;">{{subheadline}}</div>
      </div>
      <div style="display:grid;gap:4px;font-size:12px;font-weight:700;color:#ccfbf1;">
        <div>✓ {{line1}}</div><div>✓ {{line2}}</div><div>✓ {{line3}}</div>
      </div>
    </div>
    <div style="display:grid;grid-template-rows:1fr auto;gap:8px;">
      <div style="border-radius:10px;overflow:hidden;border:2px dashed rgba(153,246,228,.7);"><img data-ad-key="image_hero" src="https://images.unsplash.com/photo-1519452575417-564c1401ecc0?auto=format&fit=crop&w=1200&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
      <div style="background:#134e4a;border-radius:10px;padding:7px;display:grid;gap:4px;">
        <div style="font-size:10px;color:#5eead4;font-weight:900;">{{offer_text}}</div>
        <div style="font-size:10px;color:#99f6e4;font-weight:700;">{{date_text}}</div>
        <span style="display:inline-flex;justify-content:center;padding:6px 9px;border-radius:8px;background:#14b8a6;color:#042f2e;font-size:10px;font-weight:900;">{{cta}}</span>
      </div>
    </div>
  </div>
</div>
HTML;
    }

    private function layoutRestaurantLaunch(): string
    {
        return <<<'HTML'
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:Montserrat,Inter,sans-serif;overflow:hidden;border-radius:14px;background:#1c1917;">
  <div style="position:absolute;inset:0;background:linear-gradient(120deg,rgba(194,65,12,.92),rgba(28,25,23,.88) 62%);"></div>
  <div style="position:absolute;right:-10%;top:-20%;width:45%;height:60%;border-radius:50%;background:rgba(251,146,60,.18);"></div>

  <div style="position:absolute;left:4%;right:4%;top:10%;bottom:10%;display:grid;grid-template-columns:1fr 1fr;gap:10px;align-items:stretch;">
    <div style="display:flex;flex-direction:column;justify-content:space-between;color:#ffedd5;">
      <div>
        <div style="font-size:11px;font-weight:900;letter-spacing:.12em;text-transform:uppercase;color:#fdba74;">{{badge}}</div>
        <div style="margin-top:8px;font-size:34px;line-height:1;font-weight:900;color:#fff7ed;">{{headline}}</div>
        <div style="margin-top:8px;font-size:13px;line-height:1.4;font-weight:500;color:#fed7aa;">{{subheadline}}</div>
      </div>
      <div style="display:grid;gap:4px;font-size:12px;font-weight:700;">
        <div>{{line1}}</div><div>{{line2}}</div><div>{{line3}}</div>
      </div>
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <span style="padding:7px 12px;border-radius:999px;background:#fb923c;color:#431407;font-size:11px;font-weight:900;">{{cta}}</span>
        <span style="font-size:10px;font-weight:800;">{{phone}}</span>
      </div>
    </div>
    <div style="display:grid;grid-template-rows:1fr auto;gap:8px;">
      <div style="border-radius:14px;overflow:hidden;border:2px solid rgba(255,237,213,.4);box-shadow:0 14px 25px rgba(0,0,0,.38);"><img data-ad-key="image_hero" src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
        <div style="border-radius:10px;overflow:hidden;border:1px solid rgba(255,237,213,.35);"><img data-ad-key="image_1" src="https://images.unsplash.com/photo-1445019980597-93fa8acb246c?auto=format&fit=crop&w=1200&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
        <div style="background:#292524;border-radius:10px;border:1px solid rgba(251,146,60,.4);padding:7px;display:grid;gap:3px;align-content:center;">
          <div style="font-size:10px;color:#fb923c;font-weight:900;">{{offer_text}}</div>
          <div style="font-size:10px;color:#fdba74;font-weight:700;">{{date_text}}</div>
          <div style="font-size:10px;color:#fed7aa;">{{location_text}}</div>
        </div>
      </div>
    </div>
  </div>
</div>
HTML;
    }

    private function layoutRetailLaunch(): string
    {
        return <<<'HTML'
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:Inter,sans-serif;overflow:hidden;border-radius:14px;background:linear-gradient(135deg,#fefce8,#fff7ed 45%,#fef3c7);">
  <div style="position:absolute;inset:0;opacity:.4;background-image:linear-gradient(90deg,rgba(161,98,7,.16) 1px,transparent 1px),linear-gradient(180deg,rgba(161,98,7,.16) 1px,transparent 1px);background-size:18px 18px;"></div>
  <div style="position:absolute;left:5%;right:5%;top:10%;bottom:10%;display:grid;grid-template-columns:.95fr 1.05fr;gap:10px;">
    <div style="background:#ffffff;border-radius:14px;border:1px solid rgba(161,98,7,.25);padding:10px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 10px 20px rgba(161,98,7,.12);">
      <div>
        <span style="display:inline-block;padding:5px 10px;border-radius:999px;background:#a16207;color:#fff;font-size:10px;font-weight:900;">{{badge}}</span>
        <div style="margin-top:8px;font-size:30px;line-height:1.04;font-weight:900;color:#713f12;">{{headline}}</div>
        <div style="margin-top:7px;font-size:12px;font-weight:600;color:#854d0e;">{{subheadline}}</div>
      </div>
      <div style="display:grid;gap:4px;font-size:11px;font-weight:800;color:#78350f;">
        <div>{{line1}}</div><div>{{line2}}</div><div>{{line3}}</div>
      </div>
      <div style="display:flex;justify-content:space-between;gap:8px;align-items:center;">
        <span style="padding:6px 10px;border-radius:8px;background:#fbbf24;color:#422006;font-size:10px;font-weight:900;">{{cta}}</span>
        <span style="font-size:10px;font-weight:800;color:#92400e;">{{website}}</span>
      </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;grid-template-rows:1fr 1fr;gap:8px;">
      <div style="grid-column:1 / span 2;border-radius:12px;overflow:hidden;border:2px solid rgba(180,83,9,.22);"><img data-ad-key="image_hero" src="https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?auto=format&fit=crop&w=1200&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
      <div style="border-radius:10px;overflow:hidden;border:1px solid rgba(180,83,9,.2);"><img data-ad-key="image_1" src="https://images.unsplash.com/photo-1559339352-11d035aa65de?auto=format&fit=crop&w=900&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
      <div style="border-radius:10px;background:#fffbeb;border:1px dashed rgba(180,83,9,.35);padding:7px;display:grid;align-content:center;gap:3px;">
        <div style="font-size:10px;color:#a16207;font-weight:900;">{{offer_text}}</div>
        <div style="font-size:10px;color:#92400e;font-weight:700;">{{date_text}}</div>
        <div style="font-size:10px;color:#b45309;">{{location_text}}</div>
      </div>
    </div>
  </div>
</div>
HTML;
    }

    private function layoutCeremonyInvite(): string
    {
        return <<<'HTML'
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:'Playfair Display',Georgia,serif;overflow:hidden;border-radius:14px;background:radial-gradient(circle at top,#fdf4ff,#fae8ff 55%,#f3e8ff);">
  <div style="position:absolute;left:3%;right:3%;top:8%;bottom:8%;border:2px solid rgba(162,28,175,.32);border-radius:16px;padding:10px;background:rgba(255,255,255,.55);">
    <div style="position:absolute;left:10px;right:10px;top:10px;bottom:10px;border:1px dashed rgba(162,28,175,.4);border-radius:12px;"></div>

    <div style="position:relative;z-index:2;height:100%;display:grid;grid-template-columns:1.1fr .9fr;gap:10px;">
      <div style="display:flex;flex-direction:column;justify-content:space-between;padding:4px 6px;color:#581c87;">
        <div>
          <div style="font-family:Inter,sans-serif;font-size:10px;font-weight:900;letter-spacing:.12em;text-transform:uppercase;color:#a21caf;">{{badge}}</div>
          <div style="margin-top:8px;font-size:34px;line-height:1.05;font-weight:700;">{{headline}}</div>
          <div style="margin-top:8px;font-family:Inter,sans-serif;font-size:12px;font-weight:600;color:#6b21a8;">{{subheadline}}</div>
        </div>
        <div style="font-family:Inter,sans-serif;display:grid;gap:4px;font-size:11px;font-weight:700;">
          <div>{{date_text}}</div><div>{{location_text}}</div><div>{{phone}}</div>
        </div>
      </div>
      <div style="display:grid;grid-template-rows:1fr auto;gap:7px;">
        <div style="border-radius:12px;overflow:hidden;border:2px solid rgba(162,28,175,.28);"><img data-ad-key="image_hero" src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1200&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
        <div style="background:#faf5ff;border-radius:10px;border:1px solid rgba(162,28,175,.25);padding:7px;display:flex;justify-content:space-between;gap:8px;align-items:center;font-family:Inter,sans-serif;">
          <span style="font-size:10px;font-weight:800;color:#86198f;">{{offer_text}}</span>
          <span style="padding:5px 9px;border-radius:999px;background:#a21caf;color:#fff;font-size:10px;font-weight:900;">{{cta}}</span>
        </div>
      </div>
    </div>
  </div>
</div>
HTML;
    }

    private function layoutMegaSaleBurst(): string
    {
        return <<<'HTML'
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:Inter,sans-serif;overflow:hidden;border-radius:14px;background:#fef2f2;">
  <div style="position:absolute;inset:0;background:radial-gradient(circle at 20% 30%,rgba(239,68,68,.32),transparent 36%),radial-gradient(circle at 80% 70%,rgba(220,38,38,.28),transparent 40%),linear-gradient(135deg,#fff1f2,#fee2e2);"></div>
  <div style="position:absolute;left:4%;right:4%;top:10%;bottom:10%;display:grid;grid-template-columns:1fr 1fr;gap:10px;">
    <div style="display:flex;flex-direction:column;justify-content:space-between;">
      <div>
        <span style="display:inline-flex;padding:6px 10px;border-radius:6px;background:#b91c1c;color:#fff;font-size:11px;font-weight:900;text-transform:uppercase;">{{badge}}</span>
        <div style="margin-top:9px;font-size:36px;line-height:1;font-weight:950;color:#7f1d1d;">{{headline}}</div>
        <div style="margin-top:7px;font-size:13px;font-weight:600;color:#991b1b;">{{subheadline}}</div>
      </div>
      <div style="display:grid;gap:5px;font-size:12px;font-weight:800;color:#7f1d1d;">
        <div>★ {{line1}}</div><div>★ {{line2}}</div><div>★ {{line3}}</div>
      </div>
      <div style="display:flex;gap:8px;align-items:center;">
        <span style="padding:7px 11px;border-radius:9px;background:#dc2626;color:#fff;font-size:11px;font-weight:900;">{{cta}}</span>
        <span style="font-size:11px;color:#991b1b;font-weight:900;">{{offer_text}}</span>
      </div>
    </div>
    <div style="display:grid;grid-template-rows:1fr auto;gap:8px;">
      <div style="clip-path:polygon(0 0,95% 0,100% 20%,100% 100%,5% 100%,0 80%);border:2px solid rgba(185,28,28,.28);overflow:hidden;"><img data-ad-key="image_hero" src="https://images.unsplash.com/photo-1521017432531-fbd92d768814?auto=format&fit=crop&w=1200&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
        <div style="border-radius:10px;overflow:hidden;border:1px solid rgba(185,28,28,.25);"><img data-ad-key="image_1" src="https://images.unsplash.com/photo-1521590832167-7bcbfaa6381f?auto=format&fit=crop&w=1200&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
        <div style="border-radius:10px;background:#fff;border:1px solid rgba(185,28,28,.25);padding:7px;display:grid;gap:3px;align-content:center;">
          <div style="font-size:10px;color:#b91c1c;font-weight:900;">{{date_text}}</div>
          <div style="font-size:10px;color:#7f1d1d;font-weight:700;">{{location_text}}</div>
        </div>
      </div>
    </div>
  </div>
</div>
HTML;
    }

    private function layoutFurnitureMinimal(): string
    {
        return <<<'HTML'
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:Inter,sans-serif;overflow:hidden;border-radius:14px;background:#f8fafc;">
  <div style="position:absolute;inset:0;background:linear-gradient(180deg,#f8fafc,#e2e8f0 55%,#f8fafc);"></div>
  <div style="position:absolute;left:0;bottom:0;width:100%;height:34%;background:linear-gradient(90deg,#334155,#475569);"></div>

  <div style="position:absolute;left:5%;right:5%;top:10%;bottom:8%;display:grid;grid-template-columns:1.1fr .9fr;gap:10px;">
    <div style="display:flex;flex-direction:column;justify-content:space-between;">
      <div>
        <div style="font-size:10px;font-weight:900;letter-spacing:.08em;text-transform:uppercase;color:#334155;">{{badge}}</div>
        <div style="margin-top:9px;font-size:33px;line-height:1.02;font-weight:900;color:#0f172a;">{{headline}}</div>
        <div style="margin-top:7px;font-size:12px;color:#334155;font-weight:600;">{{subheadline}}</div>
      </div>
      <div style="display:grid;gap:5px;font-size:11px;font-weight:800;color:#1e293b;">
        <div>{{line1}}</div><div>{{line2}}</div><div>{{line3}}</div>
      </div>
      <div style="display:flex;justify-content:space-between;gap:8px;align-items:center;padding:8px 10px;border-radius:10px;background:#ffffff;border:1px solid rgba(51,65,85,.2);">
        <span style="font-size:10px;font-weight:900;color:#334155;">{{offer_text}}</span>
        <span style="padding:6px 9px;border-radius:8px;background:#0f172a;color:#fff;font-size:10px;font-weight:900;">{{cta}}</span>
      </div>
    </div>
    <div style="display:grid;grid-template-rows:1fr auto;gap:8px;">
      <div style="border-radius:12px;overflow:hidden;border:1px solid rgba(51,65,85,.3);box-shadow:0 12px 20px rgba(15,23,42,.18);"><img data-ad-key="image_hero" src="https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=1200&q=80" alt="" style="width:100%;height:100%;object-fit:cover;filter:saturate(.9);"></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
        <div style="border-radius:10px;overflow:hidden;border:1px solid rgba(51,65,85,.2);"><img data-ad-key="image_1" src="https://images.unsplash.com/photo-1470309864661-68328b2cd0a5?auto=format&fit=crop&w=1200&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
        <div style="font-size:10px;color:#e2e8f0;font-weight:700;background:rgba(15,23,42,.78);border-radius:10px;padding:7px;display:grid;gap:3px;align-content:center;">
          <div>{{date_text}}</div><div>{{location_text}}</div><div>{{website}}</div>
        </div>
      </div>
    </div>
  </div>
</div>
HTML;
    }

    private function layoutElectronicsNeon(): string
    {
        return <<<'HTML'
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:Inter,sans-serif;overflow:hidden;border-radius:14px;background:#020617;">
  <div style="position:absolute;inset:0;background:radial-gradient(circle at 15% 20%,rgba(6,182,212,.36),transparent 32%),radial-gradient(circle at 78% 25%,rgba(168,85,247,.3),transparent 32%),radial-gradient(circle at 70% 85%,rgba(34,211,238,.22),transparent 34%);"></div>
  <div style="position:absolute;inset:0;opacity:.16;background-image:linear-gradient(90deg,rgba(148,163,184,.4) 1px,transparent 1px),linear-gradient(180deg,rgba(148,163,184,.4) 1px,transparent 1px);background-size:24px 24px;"></div>

  <div style="position:absolute;left:4%;right:4%;top:10%;bottom:10%;display:grid;grid-template-columns:1fr 1fr;gap:10px;">
    <div style="display:flex;flex-direction:column;justify-content:space-between;">
      <div>
        <span style="display:inline-block;padding:5px 10px;border-radius:999px;background:#06b6d4;color:#082f49;font-size:10px;font-weight:900;">{{badge}}</span>
        <div style="margin-top:8px;font-size:34px;line-height:1;color:#e0f2fe;font-weight:950;">{{headline}}</div>
        <div style="margin-top:7px;font-size:12px;color:#a5f3fc;font-weight:600;">{{subheadline}}</div>
      </div>
      <div style="display:grid;gap:4px;font-size:11px;color:#67e8f9;font-weight:700;">
        <div>▸ {{line1}}</div><div>▸ {{line2}}</div><div>▸ {{line3}}</div>
      </div>
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <span style="padding:6px 10px;border-radius:8px;background:#a855f7;color:#faf5ff;font-size:10px;font-weight:900;">{{cta}}</span>
        <span style="font-size:10px;color:#22d3ee;font-weight:900;">{{offer_text}}</span>
      </div>
    </div>
    <div style="display:grid;grid-template-rows:1fr auto;gap:8px;">
      <div style="border-radius:14px;overflow:hidden;border:1px solid rgba(34,211,238,.45);box-shadow:0 0 0 2px rgba(56,189,248,.15),0 0 24px rgba(6,182,212,.26);"><img data-ad-key="image_hero" src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=1200&q=80" alt="" style="width:100%;height:100%;object-fit:cover;mix-blend-mode:screen;opacity:.92;"></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
        <div style="border-radius:10px;overflow:hidden;border:1px solid rgba(168,85,247,.45);"><img data-ad-key="image_1" src="https://images.unsplash.com/photo-1556740758-90de374c12ad?auto=format&fit=crop&w=1200&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
        <div style="border-radius:10px;background:rgba(15,23,42,.8);border:1px solid rgba(56,189,248,.4);padding:7px;display:grid;gap:3px;align-content:center;">
          <div style="font-size:10px;color:#67e8f9;font-weight:900;">{{date_text}}</div>
          <div style="font-size:10px;color:#a5f3fc;font-weight:700;">{{website}}</div>
          <div style="font-size:10px;color:#22d3ee;">{{phone}}</div>
        </div>
      </div>
    </div>
  </div>
</div>
HTML;
    }

    private function layoutHealthClinic(): string
    {
        return <<<'HTML'
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:Inter,sans-serif;overflow:hidden;border-radius:14px;background:linear-gradient(140deg,#ecfdf5,#dcfce7);">
  <div style="position:absolute;left:0;top:0;width:46%;height:100%;background:linear-gradient(170deg,rgba(22,101,52,.16),rgba(34,197,94,.12));clip-path:polygon(0 0,100% 0,78% 100%,0 100%);"></div>

  <div style="position:absolute;left:5%;right:5%;top:10%;bottom:10%;display:grid;grid-template-columns:.95fr 1.05fr;gap:10px;">
    <div style="display:flex;flex-direction:column;justify-content:space-between;">
      <div>
        <span style="display:inline-flex;padding:5px 10px;border-radius:999px;background:#166534;color:#fff;font-size:10px;font-weight:900;">{{badge}}</span>
        <div style="margin-top:8px;font-size:32px;line-height:1.04;font-weight:900;color:#14532d;">{{headline}}</div>
        <div style="margin-top:7px;font-size:12px;color:#166534;font-weight:600;">{{subheadline}}</div>
      </div>
      <div style="display:grid;gap:4px;font-size:11px;color:#166534;font-weight:800;">
        <div>+ {{line1}}</div><div>+ {{line2}}</div><div>+ {{line3}}</div>
      </div>
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <span style="padding:6px 10px;border-radius:8px;background:#22c55e;color:#052e16;font-size:10px;font-weight:900;">{{cta}}</span>
        <span style="font-size:10px;font-weight:900;color:#15803d;">{{phone}}</span>
      </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;grid-template-rows:1fr auto;gap:8px;">
      <div style="grid-column:1 / span 2;border-radius:12px;overflow:hidden;border:1px solid rgba(21,128,61,.26);box-shadow:0 10px 18px rgba(22,101,52,.16);"><img data-ad-key="image_hero" src="https://images.unsplash.com/photo-1560472354-b33ff0c44a43?auto=format&fit=crop&w=1200&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
      <div style="border-radius:10px;overflow:hidden;border:1px solid rgba(21,128,61,.22);"><img data-ad-key="image_1" src="https://images.unsplash.com/photo-1501183638710-841dd1904471?auto=format&fit=crop&w=1200&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
      <div style="border-radius:10px;background:#f0fdf4;border:1px solid rgba(34,197,94,.3);padding:7px;display:grid;gap:3px;align-content:center;">
        <div style="font-size:10px;color:#15803d;font-weight:900;">{{date_text}}</div>
        <div style="font-size:10px;color:#166534;font-weight:700;">{{location_text}}</div>
        <div style="font-size:10px;color:#22c55e;">{{website}}</div>
      </div>
    </div>
  </div>
</div>
HTML;
    }

    private function layoutRealEstate(): string
    {
        return <<<'HTML'
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:Inter,sans-serif;overflow:hidden;border-radius:14px;background:linear-gradient(130deg,#eef2ff,#e0e7ff 40%,#f8fafc);">
  <div style="position:absolute;left:0;right:0;bottom:0;height:36%;background:linear-gradient(90deg,#1e1b4b,#3730a3);"></div>

  <div style="position:absolute;left:4%;right:4%;top:9%;bottom:9%;display:grid;grid-template-columns:1fr 1fr;gap:10px;">
    <div style="background:#fff;border-radius:14px;border:1px solid rgba(55,48,163,.22);padding:10px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 12px 22px rgba(30,27,75,.12);">
      <div>
        <span style="display:inline-block;padding:5px 10px;border-radius:999px;background:#3730a3;color:#fff;font-size:10px;font-weight:900;">{{badge}}</span>
        <div style="margin-top:8px;font-size:32px;line-height:1.04;font-weight:900;color:#1e1b4b;">{{headline}}</div>
        <div style="margin-top:7px;font-size:12px;color:#3730a3;font-weight:600;">{{subheadline}}</div>
      </div>
      <div style="display:grid;gap:5px;font-size:11px;font-weight:800;color:#312e81;">
        <div>{{line1}}</div><div>{{line2}}</div><div>{{line3}}</div>
      </div>
      <div style="display:flex;justify-content:space-between;gap:8px;align-items:center;">
        <span style="font-size:10px;font-weight:900;color:#4338ca;">{{offer_text}}</span>
        <span style="padding:6px 9px;border-radius:8px;background:#1e1b4b;color:#fff;font-size:10px;font-weight:900;">{{cta}}</span>
      </div>
    </div>
    <div style="display:grid;grid-template-rows:1fr auto;gap:8px;">
      <div style="border-radius:14px;overflow:hidden;border:2px solid rgba(79,70,229,.26);"><img data-ad-key="image_hero" src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=1200&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
        <div style="border-radius:10px;overflow:hidden;border:1px solid rgba(79,70,229,.2);"><img data-ad-key="image_2" src="https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=1200&q=80" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
        <div style="border-radius:10px;background:rgba(30,27,75,.9);color:#e0e7ff;padding:7px;display:grid;gap:3px;align-content:center;">
          <div style="font-size:10px;font-weight:800;">{{date_text}}</div>
          <div style="font-size:10px;font-weight:700;">{{location_text}}</div>
          <div style="font-size:10px;">{{phone}}</div>
        </div>
      </div>
    </div>
  </div>
</div>
HTML;
    }
}
