<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('community_posts')) {
            return;
        }

        DB::table('community_posts')
            ->where(function ($query): void {
                foreach ($this->dummySlugPrefixes() as $prefix) {
                    $query->orWhere('slug', 'like', $prefix.'%');
                }

                $query->orWhereIn('slug', $this->dummyExactSlugs());
            })
            ->delete();
    }

    public function down(): void
    {
        // Dummy seeded posts cannot be restored. Re-run community post seeders if needed.
    }

    /**
     * @return list<string>
     */
    private function dummySlugPrefixes(): array
    {
        return [
            'sample-',
            'showcase-',
            'book-story-',
            'book-biography-',
            'book-autobiography-',
            'awareness-',
            'childrens-corner-',
            'business-',
            'womens-world-',
            'senior-citizens-forum-',
            'youth-corner-',
            'student-corner-',
            'agriculture-',
            'my-area-',
            'community-issue-',
            'astro-',
            'environment-',
            'religion-',
            'creative-corner-',
            'competition-',
        ];
    }

    /**
     * @return list<string>
     */
    private function dummyExactSlugs(): array
    {
        return [
            'test',
            'what-is-lorem-ipsum',
        ];
    }
};
