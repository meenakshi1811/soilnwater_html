<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->updateTemplates('top_categories_ad_2', true);
        $this->updateRenderedAds('top_categories_ad_2', true);
    }

    public function down(): void
    {
        $this->updateTemplates('top_categories_ad_2', false);
        $this->updateRenderedAds('top_categories_ad_2', false);
    }

    private function updateTemplates(string $sizeType, bool $toNewDimensions): void
    {
        DB::table('ad_templates')
            ->where('size_type', $sizeType)
            ->orderBy('id')
            ->select(['id', 'layout_html'])
            ->chunkById(100, function ($templates) use ($toNewDimensions): void {
                foreach ($templates as $template) {
                    $currentHtml = (string) $template->layout_html;
                    $updatedHtml = $this->replaceTopCategoryDimensions($currentHtml, $toNewDimensions);

                    if ($updatedHtml === $currentHtml) {
                        continue;
                    }

                    DB::table('ad_templates')
                        ->where('id', $template->id)
                        ->update([
                            'layout_html' => $updatedHtml,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    private function updateRenderedAds(string $sizeType, bool $toNewDimensions): void
    {
        DB::table('user_ads')
            ->where('size_type', $sizeType)
            ->whereNotNull('rendered_html')
            ->orderBy('id')
            ->select(['id', 'rendered_html'])
            ->chunkById(100, function ($ads) use ($toNewDimensions): void {
                foreach ($ads as $ad) {
                    $currentHtml = (string) $ad->rendered_html;
                    $updatedHtml = $this->replaceTopCategoryDimensions($currentHtml, $toNewDimensions);

                    if ($updatedHtml === $currentHtml) {
                        continue;
                    }

                    DB::table('user_ads')
                        ->where('id', $ad->id)
                        ->update([
                            'rendered_html' => $updatedHtml,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    private function replaceTopCategoryDimensions(string $html, bool $toNewDimensions = true): string
    {
        $find = $toNewDimensions
            ? ['296 / 132', '296x132', '296×132', 'height:132px', '132px']
            : ['296 / 292', '296x292', '296×292', 'height:292px', '292px'];

        $replace = $toNewDimensions
            ? ['296 / 292', '296x292', '296×292', 'height:292px', '292px']
            : ['296 / 132', '296x132', '296×132', 'height:132px', '132px'];

        return str_replace($find, $replace, $html);
    }
};
