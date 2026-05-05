<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('ad_templates')
            ->where('size_type', 'square')
            ->orderBy('id')
            ->select(['id', 'layout_html'])
            ->chunkById(100, function ($templates): void {
                foreach ($templates as $template) {
                    $updatedHtml = $this->replaceSquareDimensions((string) $template->layout_html);

                    if ($updatedHtml === (string) $template->layout_html) {
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

        DB::table('user_ads')
            ->where('size_type', 'square')
            ->whereNotNull('rendered_html')
            ->orderBy('id')
            ->select(['id', 'rendered_html'])
            ->chunkById(100, function ($ads): void {
                foreach ($ads as $ad) {
                    $currentHtml = (string) $ad->rendered_html;
                    $updatedHtml = $this->replaceSquareDimensions($currentHtml);

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

    public function down(): void
    {
        DB::table('ad_templates')
            ->where('size_type', 'square')
            ->orderBy('id')
            ->select(['id', 'layout_html'])
            ->chunkById(100, function ($templates): void {
                foreach ($templates as $template) {
                    $updatedHtml = $this->replaceSquareDimensions((string) $template->layout_html, false);

                    if ($updatedHtml === (string) $template->layout_html) {
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

        DB::table('user_ads')
            ->where('size_type', 'square')
            ->whereNotNull('rendered_html')
            ->orderBy('id')
            ->select(['id', 'rendered_html'])
            ->chunkById(100, function ($ads): void {
                foreach ($ads as $ad) {
                    $currentHtml = (string) $ad->rendered_html;
                    $updatedHtml = $this->replaceSquareDimensions($currentHtml, false);

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

    private function replaceSquareDimensions(string $html, bool $to640 = true): string
    {
        $find = $to640
            ? ['600x600', '600×600', 'width:600px;height:600px', 'height:600px;width:600px', 'viewBox="0 0 600 600"']
            : ['640x640', '640×640', 'width:640px;height:640px', 'height:640px;width:640px', 'viewBox="0 0 640 640"'];

        $replace = $to640
            ? ['640x640', '640×640', 'width:640px;height:640px', 'height:640px;width:640px', 'viewBox="0 0 640 640"']
            : ['600x600', '600×600', 'width:600px;height:600px', 'height:600px;width:600px', 'viewBox="0 0 600 600"'];

        return str_replace($find, $replace, $html);
    }
};
