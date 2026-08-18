<?php

namespace Database\Seeders;

use App\Models\FoulWord;
use App\Services\FoulWordFilter;
use Illuminate\Database\Seeder;

class FoulWordSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->words() as $word) {
            FoulWord::query()->updateOrCreate(
                ['word' => mb_strtolower(trim($word))],
                ['is_active' => true]
            );
        }

        FoulWordFilter::forgetCache();
    }

    /**
     * @return list<string>
     */
    private function words(): array
    {
        return [
            'asshole',
            'bastard',
            'bitch',
            'bullshit',
            'cock',
            'cunt',
            'dick',
            'dickhead',
            'faggot',
            'fuck',
            'fucked',
            'fucker',
            'fucking',
            'motherfucker',
            'nigger',
            'piss',
            'pussy',
            'shit',
            'slut',
            'twat',
            'wanker',
            'whore',
            'bc',
            'bhenchod',
            'bhosdi',
            'bhosdike',
            'bsdk',
            'chutiya',
            'chutiye',
            'gandu',
            'haramzada',
            'harami',
            'kamine',
            'kameena',
            'lauda',
            'lawda',
            'lund',
            'madarchod',
            'mc',
            'randi',
        ];
    }
}
