<?php

namespace Database\Seeders;

use App\Models\HomepageStudyMaterialCard;
use Illuminate\Database\Seeder;

/**
 * The six Study Material Library cards as on the live homepage (the same cards the
 * create-table migration inserts). Only fills an empty table, so admin edits are never undone.
 */
class HomepageStudyMaterialCardSeeder extends Seeder
{
    public function run(): void
    {
        if (HomepageStudyMaterialCard::query()->exists()) {
            return;
        }

        // [title, subtitle, link, image, colour, icon]
        $cards = [
            ['Previous Year Question Papers', 'Board, University & Competitive Exams', '/study-materials/notes?material_type=question_papers', 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=800&q=80', 'orange', 'fa-file-lines'],
            ['Solved Papers', 'Step-by-step solutions for better understanding', '/study-materials/notes?material_type=sample_papers', 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=800&q=80', 'green', 'fa-clipboard-check'],
            ['Notes & Study Guides', 'Subject-wise notes, revision material and quick reference guides', '/study-materials/notes?material_type=notes', 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=800&q=80', 'blue', 'fa-book-bookmark'],
            ['Reference Books & Resources', 'Recommended books, e-books and useful learning resources', '/study-materials/notes?material_types%5B0%5D=reference_books&material_types%5B1%5D=study_guides&material_types%5B2%5D=assignments', 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=800&q=80', 'purple', 'fa-book-open'],
            ['Competitive Exam Material', 'UPSC, SSC, Banking, Railway & More', '/study-materials/notes?material_type=question_papers', 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=800&q=80', 'red', 'fa-award'],
            ['Online Courses & Learning', 'Learn Anytime, Anywhere', '/institutes', 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&q=80', 'indigo', 'fa-laptop'],
        ];

        foreach ($cards as $index => [$title, $subtitle, $link, $image, $tone, $icon]) {
            HomepageStudyMaterialCard::query()->create([
                'title' => $title,
                'subtitle' => $subtitle,
                'link_url' => $link,
                'image_path' => $image,
                'tone' => $tone,
                'icon' => $icon,
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }
}
