<?php

namespace App\Support;

use App\Models\StudyMaterial;
use Illuminate\Database\Eloquent\Builder;

final class HomepageStudyMaterialLibraryCards
{
    /**
     * @return list<array{
     *     url: string,
     *     image: string,
     *     title: string,
     *     subtitle: string,
     *     badge_label: string,
     *     badge_tone: string,
     *     badge_icon: string
     * }>
     */
    public static function build(): array
    {
        $definitions = [
            [
                'material_type' => 'question_papers',
                'url' => route('study-materials.notes', ['material_type' => 'question_papers']),
                'title' => 'Previous Year Question Papers',
                'subtitle' => 'Board, University & Competitive Exams',
                'badge_label' => 'Previous Year Question Papers',
                'badge_tone' => 'orange',
                'badge_icon' => 'fa-file-lines',
                'fallback_image' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=800&q=80',
            ],
            [
                'material_type' => 'sample_papers',
                'url' => route('study-materials.notes', ['material_type' => 'sample_papers']),
                'title' => 'Solved Papers',
                'subtitle' => 'Step-by-step solutions for better understanding',
                'badge_label' => 'Solved Papers',
                'badge_tone' => 'green',
                'badge_icon' => 'fa-clipboard-check',
                'fallback_image' => 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=800&q=80',
            ],
            [
                'material_type' => 'notes',
                'url' => route('study-materials.notes', ['material_type' => 'notes']),
                'title' => 'Notes & Study Guides',
                'subtitle' => 'Subject-wise notes, revision material and quick reference guides',
                'badge_label' => 'Notes & Study Guides',
                'badge_tone' => 'blue',
                'badge_icon' => 'fa-book-bookmark',
                'fallback_image' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=800&q=80',
            ],
            [
                'material_types' => ['reference_books', 'study_guides', 'assignments'],
                'url' => route('study-materials.notes', [
                    'material_types' => ['reference_books', 'study_guides', 'assignments'],
                ]),
                'title' => 'Reference Books & Resources',
                'subtitle' => 'Recommended books, e-books and useful learning resources',
                'badge_label' => 'Reference Books & Resources',
                'badge_tone' => 'purple',
                'badge_icon' => 'fa-book-open',
                'fallback_image' => 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=800&q=80',
                'footer_note' => 'Curated by Subject Experts',
            ],
            [
                'material_type' => 'question_papers',
                'url' => route('study-materials.notes', ['material_type' => 'question_papers']),
                'title' => 'Competitive Exam Material',
                'subtitle' => 'UPSC, SSC, Banking, Railway & More',
                'badge_label' => 'Competitive Exam Material',
                'badge_tone' => 'red',
                'badge_icon' => 'fa-award',
                'fallback_image' => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=800&q=80',
                'footer_note' => 'UPSC, SSC, Banking, Railway & More',
            ],
            [
                'material_types' => ['videos', 'study_guides'],
                'url' => route('institutes.index'),
                'title' => 'Online Courses & Learning',
                'subtitle' => 'Learn Anytime, Anywhere',
                'badge_label' => 'Online Courses & Learning',
                'badge_tone' => 'indigo',
                'badge_icon' => 'fa-laptop',
                'fallback_image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&q=80',
                'footer_note' => 'Skill Development & Digital Learning',
            ],
        ];

        $cards = [];

        foreach ($definitions as $definition) {
            $image = self::resolveImage($definition);

            $cards[] = [
                'url' => $definition['url'],
                'image' => $image,
                'title' => $definition['title'],
                'subtitle' => $definition['subtitle'],
                'description' => $definition['subtitle'],
                'badge_label' => $definition['badge_label'],
                'category_label' => $definition['badge_label'],
                'badge_tone' => $definition['badge_tone'],
                'category_tone' => $definition['badge_tone'],
                'badge_icon' => $definition['badge_icon'],
                'category_icon' => $definition['badge_icon'],
                'footer_note' => $definition['footer_note'] ?? $definition['subtitle'],
                'location' => 'Dehradun',
            ];
        }

        return $cards;
    }

    /**
     * @param array<string, mixed> $definition
     */
    private static function resolveImage(array $definition): string
    {
        $query = StudyMaterial::query()
            ->approved()
            ->publiclyListed()
            ->whereNotNull('thumbnail')
            ->where('thumbnail', '!=', '');

        if (! empty($definition['material_types'])) {
            $query->whereIn('material_type', $definition['material_types']);
        } elseif (! empty($definition['material_type'])) {
            $query->where('material_type', $definition['material_type']);
        }

        $thumbnail = $query
            ->latest('id')
            ->value('thumbnail');

        if (filled($thumbnail)) {
            return asset($thumbnail);
        }

        return $definition['fallback_image'];
    }

    /**
     * @return list<array{label: string, url: string}>
     */
    public static function navLinks(): array
    {
        return [
            [
                'label' => 'Previous Papers',
                'url' => route('study-materials.notes', ['material_type' => 'question_papers']),
            ],
            [
                'label' => 'Solved Papers',
                'url' => route('study-materials.notes', ['material_type' => 'sample_papers']),
            ],
            [
                'label' => 'Notes',
                'url' => route('study-materials.notes', ['material_type' => 'notes']),
            ],
            [
                'label' => 'Reference Books',
                'url' => route('study-materials.notes', [
                    'material_types' => ['reference_books', 'study_guides'],
                ]),
            ],
        ];
    }
}
