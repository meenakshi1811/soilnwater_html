<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_study_material_cards', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('link_url', 500);
            $table->string('image_path', 500);
            $table->string('tone', 20)->default('purple');
            $table->string('icon', 64)->default('fa-book-open');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Start with the six cards the homepage showed before this section became editable
        $now = now();
        $cards = [
            ['Previous Year Question Papers', 'Board, University & Competitive Exams', '/study-materials/notes?material_type=question_papers', 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=800&q=80', 'orange', 'fa-file-lines'],
            ['Solved Papers', 'Step-by-step solutions for better understanding', '/study-materials/notes?material_type=sample_papers', 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=800&q=80', 'green', 'fa-clipboard-check'],
            ['Notes & Study Guides', 'Subject-wise notes, revision material and quick reference guides', '/study-materials/notes?material_type=notes', 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=800&q=80', 'blue', 'fa-book-bookmark'],
            ['Reference Books & Resources', 'Recommended books, e-books and useful learning resources', '/study-materials/notes?material_types%5B0%5D=reference_books&material_types%5B1%5D=study_guides&material_types%5B2%5D=assignments', 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=800&q=80', 'purple', 'fa-book-open'],
            ['Competitive Exam Material', 'UPSC, SSC, Banking, Railway & More', '/study-materials/notes?material_type=question_papers', 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=800&q=80', 'red', 'fa-award'],
            ['Online Courses & Learning', 'Learn Anytime, Anywhere', '/institutes', 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&q=80', 'indigo', 'fa-laptop'],
        ];

        DB::table('homepage_study_material_cards')->insert(array_map(
            fn (array $card, int $index): array => [
                'title' => $card[0],
                'subtitle' => $card[1],
                'link_url' => $card[2],
                'image_path' => $card[3],
                'tone' => $card[4],
                'icon' => $card[5],
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $cards,
            array_keys($cards)
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_study_material_cards');
    }
};
