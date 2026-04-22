<?php

namespace Database\Seeders;

use App\Models\AdTemplate;
use App\Support\AdSizes;
use Illuminate\Database\Seeder;

class AdTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $schema = [
            'fields' => [
                ['key' => 'headline', 'label' => 'Headline', 'type' => 'text', 'required' => true, 'max' => 60],
                ['key' => 'subheadline', 'label' => 'Subheadline', 'type' => 'text', 'required' => false, 'max' => 90],
                ['key' => 'badge', 'label' => 'Badge (e.g. 50% OFF)', 'type' => 'text', 'required' => false, 'max' => 16],
                ['key' => 'line1', 'label' => 'Service 1', 'type' => 'text', 'required' => false, 'max' => 28],
                ['key' => 'line2', 'label' => 'Service 2', 'type' => 'text', 'required' => false, 'max' => 28],
                ['key' => 'line3', 'label' => 'Service 3', 'type' => 'text', 'required' => false, 'max' => 28],
                ['key' => 'cta', 'label' => 'CTA Button', 'type' => 'text', 'required' => false, 'max' => 18],
                ['key' => 'phone', 'label' => 'Phone', 'type' => 'text', 'required' => false, 'max' => 20],
                ['key' => 'website', 'label' => 'Website', 'type' => 'text', 'required' => false, 'max' => 40],
                ['key' => 'image_hero', 'label' => 'Hero Image', 'type' => 'image', 'required' => false],
                ['key' => 'image_1', 'label' => 'Image 1', 'type' => 'image', 'required' => false],
                ['key' => 'image_2', 'label' => 'Image 2', 'type' => 'image', 'required' => false],
            ],
        ];

        foreach (array_keys(AdSizes::all()) as $sizeType) {
            $templates = [
                [
                    'name' => 'Beauty Salon (Classic)',
                    'description' => 'Warm classic layout with offer badge and three image circles.',
                    'layout_html' => $this->layoutClassic(),
                ],
                [
                    'name' => 'Modern Furniture (Minimal)',
                    'description' => 'Minimal product-focused layout for furniture and retail.',
                    'layout_html' => $this->layoutMinimal(),
                ],
                [
                    'name' => 'Grand Opening (Split)',
                    'description' => 'Announcement style layout with strong headline and visuals.',
                    'layout_html' => $this->layoutSplit(),
                ],
            ];

            foreach ($templates as $tpl) {
                AdTemplate::query()->firstOrCreate(
                    [
                        'size_type' => $sizeType,
                        'name' => $tpl['name'],
                    ],
                    [
                        'description' => $tpl['description'],
                        'preview_image' => null,
                        'layout_html' => $tpl['layout_html'],
                        'schema_json' => $schema,
                        'is_active' => true,
                        'created_by' => null,
                    ]
                );
            }
        }
    }

    private function layoutClassic(): string
    {
        return <<<HTML
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;overflow:hidden;border-radius:12px;background:linear-gradient(135deg,#f4e6d9,#f7f2ec);">
  <div style="position:absolute;inset:0;background:radial-gradient(1200px 600px at 100% 0%,rgba(125,61,22,.18),transparent 60%);"></div>
  <div style="position:absolute;left:-8%;top:-12%;width:60%;height:60%;background:rgba(125,61,22,.14);border-radius:50%;filter:blur(0px);"></div>
  <div style="position:absolute;right:-15%;bottom:-25%;width:75%;height:75%;background:rgba(125,61,22,.16);border-radius:50%;"></div>

  <div style="position:absolute;left:7%;top:10%;right:42%;">
    <div style="font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#7a4a2a;font-weight:800;">Special Today</div>
    <div style="margin-top:6px;font-size:34px;line-height:1.05;color:#5a3219;font-weight:900;text-transform:uppercase;">{{headline}}</div>
    <div style="margin-top:8px;font-size:14px;color:rgba(90,50,25,.85);font-weight:600;">{{subheadline}}</div>

    <div style="margin-top:16px;border-top:1px solid rgba(90,50,25,.14);padding-top:12px;">
      <div style="font-size:12px;font-weight:900;color:#5a3219;text-transform:uppercase;">Our services</div>
      <div style="margin-top:8px;display:grid;gap:6px;font-size:13px;color:rgba(90,50,25,.9);font-weight:700;">
        <div>• {{line1}}</div>
        <div>• {{line2}}</div>
        <div>• {{line3}}</div>
      </div>
    </div>

    <div style="margin-top:14px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
      <span style="display:inline-flex;align-items:center;justify-content:center;padding:9px 12px;border-radius:10px;background:#5a3219;color:#fff;font-weight:900;font-size:12px;">{{cta}}</span>
      <span style="font-size:12px;color:rgba(90,50,25,.9);font-weight:800;">{{phone}}</span>
      <span style="font-size:12px;color:rgba(90,50,25,.75);font-weight:700;">{{website}}</span>
    </div>
  </div>

  <div style="position:absolute;right:7%;top:12%;width:32%;height:76%;display:grid;grid-template-rows:1fr 1fr 1fr;gap:10px;">
    <div style="border-radius:999px;overflow:hidden;border:8px solid rgba(255,255,255,.9);box-shadow:0 10px 18px rgba(15,23,42,.12);">
      <img data-ad-key="image_hero" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">
    </div>
    <div style="border-radius:999px;overflow:hidden;border:8px solid rgba(255,255,255,.9);box-shadow:0 10px 18px rgba(15,23,42,.12);">
      <img data-ad-key="image_1" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">
    </div>
    <div style="border-radius:999px;overflow:hidden;border:8px solid rgba(255,255,255,.9);box-shadow:0 10px 18px rgba(15,23,42,.12);">
      <img data-ad-key="image_2" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">
    </div>
  </div>

  <div style="position:absolute;left:54%;top:18%;transform:translateX(-50%);width:120px;height:120px;border-radius:999px;background:#7a4a2a;color:#fff;display:flex;align-items:center;justify-content:center;text-align:center;font-weight:900;line-height:1.1;border:8px dotted rgba(255,255,255,.45);box-shadow:0 12px 24px rgba(15,23,42,.12);">
    <div style="font-size:20px;">{{badge}}</div>
  </div>
</div>
HTML;
    }

    private function layoutMinimal(): string
    {
        return <<<HTML
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;overflow:hidden;border-radius:12px;background:#0b1220;">
  <div style="position:absolute;inset:0;background:radial-gradient(900px 520px at 18% 18%,rgba(25,118,210,.35),transparent 55%),radial-gradient(900px 520px at 85% 20%,rgba(46,125,50,.28),transparent 55%);"></div>
  <div style="position:absolute;inset:0;opacity:.18;background-image:linear-gradient(90deg,rgba(255,255,255,.12) 1px,transparent 1px),linear-gradient(180deg,rgba(255,255,255,.08) 1px,transparent 1px);background-size:22px 22px;"></div>

  <div style="position:absolute;left:6%;top:10%;right:6%;display:grid;grid-template-columns:1.1fr .9fr;gap:14px;align-items:stretch;height:80%;">
    <div style="display:flex;flex-direction:column;justify-content:center;padding:16px 14px;">
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <span style="display:inline-flex;padding:6px 10px;border-radius:999px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);color:#fff;font-weight:800;font-size:12px;letter-spacing:.06em;text-transform:uppercase;">{{badge}}</span>
        <span style="color:rgba(255,255,255,.75);font-weight:700;font-size:12px;">{{website}}</span>
      </div>

      <div style="margin-top:10px;font-size:40px;line-height:1.02;font-weight:950;color:#fff;">{{headline}}</div>
      <div style="margin-top:10px;font-size:14px;line-height:1.45;color:rgba(255,255,255,.8);font-weight:600;max-width:92%;">{{subheadline}}</div>

      <div style="margin-top:14px;display:grid;gap:8px;">
        <div style="display:flex;gap:10px;align-items:center;color:rgba(255,255,255,.9);font-weight:800;font-size:13px;"><span style="width:8px;height:8px;border-radius:999px;background:#4aa3ff;"></span>{{line1}}</div>
        <div style="display:flex;gap:10px;align-items:center;color:rgba(255,255,255,.86);font-weight:800;font-size:13px;"><span style="width:8px;height:8px;border-radius:999px;background:#66bb6a;"></span>{{line2}}</div>
        <div style="display:flex;gap:10px;align-items:center;color:rgba(255,255,255,.82);font-weight:800;font-size:13px;"><span style="width:8px;height:8px;border-radius:999px;background:#f5a623;"></span>{{line3}}</div>
      </div>

      <div style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <span style="display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border-radius:12px;background:linear-gradient(90deg,#1976d2,#4aa3ff);color:#fff;font-weight:900;font-size:13px;box-shadow:0 10px 20px rgba(25,118,210,.35);">{{cta}}</span>
        <span style="color:rgba(255,255,255,.88);font-weight:800;font-size:13px;">{{phone}}</span>
      </div>
    </div>

    <div style="border-radius:16px;overflow:hidden;border:1px solid rgba(255,255,255,.16);background:rgba(2,6,23,.35);box-shadow:0 16px 30px rgba(0,0,0,.32);">
      <img data-ad-key="image_hero" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:block;opacity:.92;">
    </div>
  </div>
</div>
HTML;
    }

    private function layoutSplit(): string
    {
        return <<<HTML
<div class="ad-canvas" style="position:relative;width:100%;height:100%;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;overflow:hidden;border-radius:12px;background:linear-gradient(180deg,#ffffff,#f6fbff);">
  <div style="position:absolute;inset:0;background:radial-gradient(900px 520px at 95% 20%,rgba(245,166,35,.22),transparent 55%);"></div>
  <div style="position:absolute;left:0;top:0;bottom:0;width:56%;background:linear-gradient(135deg,rgba(25,118,210,.14),rgba(46,125,50,.10));"></div>

  <div style="position:absolute;left:6%;top:10%;width:44%;bottom:10%;display:flex;flex-direction:column;justify-content:space-between;">
    <div>
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <span style="display:inline-flex;padding:6px 10px;border-radius:999px;background:#0b1220;color:#fff;font-weight:900;font-size:12px;letter-spacing:.06em;text-transform:uppercase;">{{badge}}</span>
        <span style="color:rgba(11,18,32,.7);font-weight:800;font-size:12px;">{{website}}</span>
      </div>
      <div style="margin-top:10px;font-size:36px;line-height:1.06;font-weight:950;color:#0b1220;">{{headline}}</div>
      <div style="margin-top:10px;font-size:14px;line-height:1.5;color:rgba(11,18,32,.75);font-weight:650;">{{subheadline}}</div>

      <div style="margin-top:14px;display:grid;gap:7px;">
        <div style="display:flex;gap:8px;align-items:center;color:#0b1220;font-weight:800;font-size:13px;"><span style="width:7px;height:7px;border-radius:99px;background:#1976d2;"></span>{{line1}}</div>
        <div style="display:flex;gap:8px;align-items:center;color:#0b1220;font-weight:800;font-size:13px;"><span style="width:7px;height:7px;border-radius:99px;background:#2e7d32;"></span>{{line2}}</div>
        <div style="display:flex;gap:8px;align-items:center;color:#0b1220;font-weight:800;font-size:13px;"><span style="width:7px;height:7px;border-radius:99px;background:#f5a623;"></span>{{line3}}</div>
      </div>
    </div>

    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
      <span style="display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border-radius:12px;background:#1976d2;color:#fff;font-weight:900;font-size:13px;box-shadow:0 12px 22px rgba(25,118,210,.25);">{{cta}}</span>
      <span style="color:#0b1220;font-weight:900;font-size:13px;">{{phone}}</span>
    </div>
  </div>

  <div style="position:absolute;right:6%;top:10%;bottom:10%;width:34%;display:grid;grid-template-rows:1.25fr .75fr;gap:12px;">
    <div style="border-radius:16px;overflow:hidden;border:1px solid rgba(15,23,42,.10);box-shadow:0 16px 30px rgba(15,23,42,.14);background:#fff;">
      <img data-ad-key="image_hero" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
      <div style="border-radius:14px;overflow:hidden;border:1px solid rgba(15,23,42,.10);background:#fff;">
        <img data-ad-key="image_1" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">
      </div>
      <div style="border-radius:14px;overflow:hidden;border:1px solid rgba(15,23,42,.10);background:#fff;">
        <img data-ad-key="image_2" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">
      </div>
    </div>
  </div>
</div>
HTML;
    }
}

