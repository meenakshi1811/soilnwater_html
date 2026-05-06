<?php

namespace Database\Seeders;

use App\Models\AdSize;
use Illuminate\Database\Seeder;

class AdminOnlyAdSizeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->adminOnlySizes() as $sizeKey => $size) {
            AdSize::query()->updateOrCreate(
                ['size_key' => $sizeKey],
                [
                    'name' => $size['name'],
                    'width' => $size['w'],
                    'height' => $size['h'],
                    'admin_only' => true,
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * @return array<string, array{name:string,w:int,h:int}>
     */
    private function adminOnlySizes(): array
    {
        return [
            'admin_520x360' => ['name' => 'Admin 520×360', 'w' => 520, 'h' => 360],
            'admin_520x300' => ['name' => 'Admin 520×300', 'w' => 520, 'h' => 300],
            'admin_458x458' => ['name' => 'Admin 458×458', 'w' => 458, 'h' => 458],
            'admin_458x300' => ['name' => 'Admin 458×300', 'w' => 458, 'h' => 300],
            'admin_458x229' => ['name' => 'Admin 458×229', 'w' => 458, 'h' => 229],
            'admin_360x360' => ['name' => 'Admin 360×360', 'w' => 360, 'h' => 360],
            'admin_320x300' => ['name' => 'Admin 320×300', 'w' => 320, 'h' => 300],
            'admin_229x229' => ['name' => 'Admin 229×229', 'w' => 229, 'h' => 229],
        ];
    }
}
