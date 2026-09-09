<?php

namespace App\Support;

class StudyMaterialUploadConfig
{
    public const UPLOADER_ROLES = [
        'teacher' => ['label' => 'Teacher', 'icon' => 'fa-chalkboard-user'],
        'student' => ['label' => 'Student', 'icon' => 'fa-user-graduate'],
        'parent' => ['label' => 'Parent', 'icon' => 'fa-people-roof'],
        'education_professional' => ['label' => 'Education Professional', 'icon' => 'fa-briefcase'],
        'other' => ['label' => 'Other', 'icon' => 'fa-user'],
    ];

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function types(): array
    {
        return [
            'notes' => self::notes(),
            'question_papers' => self::questionPapers(),
            'sample_papers' => self::solvedPapers(),
            'worksheets' => self::worksheets(),
            'assignments' => self::assignments(),
            'reference_books' => self::referenceBooks(),
            'study_guides' => self::studyGuides(),
            'videos' => self::videos(),
        ];
    }

    public static function type(string $key): array
    {
        return self::types()[$key] ?? self::notes();
    }

    /**
     * @return list<string>
     */
    public static function typeKeys(): array
    {
        return array_keys(self::types());
    }

    private static function notes(): array
    {
        return [
            'key' => 'notes',
            'title' => 'Upload Study Material / Notes',
            'subtitle' => 'Share your knowledge with students across the world. Upload notes, question papers, worksheets, presentations and more.',
            'quote' => 'Knowledge shared is knowledge multiplied.',
            'icon' => 'fa-note-sticky',
            'tips' => [
                'Use a clear and descriptive title',
                'Select the correct class, subject and material type',
                'Upload clear and readable files',
                'Add relevant keywords for better search visibility',
                'Do not upload copyrighted content without permission',
            ],
            'guidelines' => [
                'Share educational and non-commercial content',
                'Ensure content is accurate and useful for learners',
                'Use proper formatting and readable text',
                'Add tags to help students find your material',
            ],
            'not_allowed' => [
                'Plagiarized or copyright-protected content',
                'Offensive, misleading or harmful content',
                'Low quality or unreadable files',
                'Personal contact details inside files',
            ],
            'preview_label' => 'Sample Preview (PDF)',
            'preview_caption' => 'This is how your material will appear after upload.',
            'footer' => 'Share Knowledge, Create Opportunities!',
            'file_hint' => 'Supported formats: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, PNG | Max file size: 50 MB',
            'accept' => '.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png',
            'options' => [
                'public_visible' => 'Make this material publicly visible (recommended)',
                'allow_download' => 'Allow download',
                'original_content' => 'This is my original content',
            ],
        ];
    }

    private static function questionPapers(): array
    {
        return [
            'key' => 'question_papers',
            'title' => 'Upload Question Paper',
            'subtitle' => 'Share previous year papers, sample papers, model papers and more. Help students prepare and succeed!',
            'quote' => 'Past questions today, brighter tomorrows tomorrow.',
            'icon' => 'fa-file-circle-question',
            'tips' => self::commonTips(),
            'guidelines' => [
                'Previous year question papers',
                'Sample / model question papers',
                'Board and university exam papers',
                'Entrance exam question papers',
            ],
            'not_allowed' => self::commonNotAllowed(),
            'preview_label' => 'Preview (Sample)',
            'preview_caption' => 'Example of an uploaded question paper card.',
            'footer' => 'Share Papers, Build Confidence!',
            'file_hint' => 'Supported formats: PDF, DOC, DOCX, JPG, PNG | Max file size: 50 MB',
            'accept' => '.pdf,.doc,.docx,.jpg,.jpeg,.png',
            'options' => [
                'include_solutions' => 'Include Solutions / Answer Key (if available)',
                'model_paper' => 'This is a Model / Sample Paper',
                'allow_download' => 'Allow download (make publicly available)',
                'marking_scheme' => 'Include Marking Scheme',
                'previous_year' => 'This is a Previous Year Paper',
                'original_content' => 'This is my original content',
            ],
        ];
    }

    private static function solvedPapers(): array
    {
        return [
            'key' => 'sample_papers',
            'title' => 'Upload Solved Paper',
            'subtitle' => 'Share solved papers with step-by-step answers. Help students understand solutions and improve scores.',
            'quote' => 'Solutions today, stronger learners tomorrow!',
            'icon' => 'fa-file-circle-check',
            'tips' => self::commonTips(),
            'guidelines' => [
                'Board exam solved papers',
                'Competitive exam solved papers',
                'Model answer papers',
                'Step-by-step solution papers',
            ],
            'not_allowed' => self::commonNotAllowed(),
            'preview_label' => 'Preview (Sample Solved Paper)',
            'preview_caption' => 'Example solved paper preview card.',
            'footer' => 'Share Solutions, Create Impact!',
            'file_hint' => 'Supported formats: PDF, DOC, DOCX, JPG, PNG | Max file size: 50 MB',
            'accept' => '.pdf,.doc,.docx,.jpg,.jpeg,.png',
            'options' => [
                'step_by_step' => 'Include step-by-step solutions',
                'marking_scheme' => 'Include marking scheme',
                'allow_download' => 'Allow download',
                'original_content' => 'This is my original content',
                'important_tips' => 'Include important tips',
                'model_answer' => 'Tag as model answer',
                'previous_year' => 'Tag as previous year solved paper',
                'featured' => 'Set as featured content (request)',
            ],
        ];
    }

    private static function worksheets(): array
    {
        return [
            'key' => 'worksheets',
            'title' => 'Upload Worksheet',
            'subtitle' => 'Share practice worksheets, activity sheets and homework tasks. Help students learn by doing.',
            'quote' => 'Small worksheets, big learning!',
            'icon' => 'fa-file-pen',
            'tips' => self::commonTips(),
            'guidelines' => [
                'Practice worksheets',
                'Revision worksheets',
                'Activity sheets',
                'Homework sheets',
            ],
            'not_allowed' => self::commonNotAllowed(),
            'preview_label' => 'Preview (Sample Worksheet)',
            'preview_caption' => 'Example worksheet preview card.',
            'footer' => 'Small Worksheets, Big Learning!',
            'file_hint' => 'Supported formats: PDF, DOC, DOCX, JPG, PNG | Max file size: 50 MB',
            'accept' => '.pdf,.doc,.docx,.jpg,.jpeg,.png',
            'options' => [
                'answer_key' => 'Include Answer Key / Solutions',
                'allow_download' => 'Allow Download',
                'original_content' => 'This is my original content',
                'revision_sheet' => 'This is a Revision Worksheet',
                'activity_sheet' => 'This is an Activity Sheet',
                'homework_sheet' => 'This is a Homework Sheet',
            ],
        ];
    }

    private static function assignments(): array
    {
        return [
            'key' => 'assignments',
            'title' => 'Upload Assignment',
            'subtitle' => 'Share assignments, homework tasks, project work and more. Help students learn, practice and excel!',
            'quote' => 'Good assignments today, brighter futures tomorrow!',
            'icon' => 'fa-clipboard-list',
            'tips' => self::commonTips(),
            'guidelines' => [
                'Homework assignments',
                'Project work',
                'Classroom tasks',
                'Practice assignments',
            ],
            'not_allowed' => self::commonNotAllowed(),
            'preview_label' => 'Preview (Sample Assignment)',
            'preview_caption' => 'Example assignment preview card.',
            'footer' => 'Together for a Better Tomorrow!',
            'file_hint' => 'Supported formats: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, PNG | Max file size: 50 MB (per file)',
            'accept' => '.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png',
            'options' => [
                'answer_key' => 'Include Answer Key / Solution (if available)',
                'allow_download' => 'Allow download (make publicly available)',
                'original_content' => 'This is my original content',
                'group_assignment' => 'Make this a group assignment',
                'curriculum_aligned' => 'Align with curriculum (NCERT / CBSE / State Board)',
                'featured' => 'Set as featured content (request)',
            ],
        ];
    }

    private static function referenceBooks(): array
    {
        return [
            'key' => 'reference_books',
            'title' => 'Upload Reference Book',
            'subtitle' => 'Share textbooks, guidebooks, reference books and more. Help students find the right resources for their learning journey!',
            'quote' => 'A good book today, a brighter tomorrow!',
            'icon' => 'fa-book',
            'tips' => self::commonTips(),
            'guidelines' => [
                'Textbooks and guidebooks',
                'Reference books by subject',
                'Competitive exam books',
                'Language and skill books',
            ],
            'not_allowed' => self::commonNotAllowed(),
            'preview_label' => 'Preview (Sample Book)',
            'preview_caption' => 'Example reference book card preview.',
            'footer' => 'Share Knowledge, Create Opportunities!',
            'file_hint' => 'Supported formats: PDF, DOC, DOCX | Max file size: 50 MB',
            'accept' => '.pdf,.doc,.docx',
            'options' => [
                'public_visible' => 'Make this book publicly visible (recommended)',
                'allow_download' => 'Allow download',
                'original_content' => 'This is my original content',
                'recommended_book' => 'Recommended Book',
                'practice_questions' => 'Includes Practice Questions',
                'includes_solutions' => 'Includes Solutions',
                'competitive_exams' => 'Useful for Competitive Exams',
            ],
        ];
    }

    private static function studyGuides(): array
    {
        return [
            'key' => 'study_guides',
            'title' => 'Upload Study Guide',
            'subtitle' => 'Share comprehensive study guides, revision notes, topic summaries and learning resources. Help students study smarter!',
            'quote' => 'Good study guides today, confident learners tomorrow!',
            'icon' => 'fa-book-open-reader',
            'tips' => self::commonTips(),
            'guidelines' => [
                'Topic-wise study guides',
                'Revision guides',
                'Exam preparation guides',
                'MCQ practice guides',
            ],
            'not_allowed' => self::commonNotAllowed(),
            'preview_label' => 'Preview (Sample Study Guide)',
            'preview_caption' => 'Example study guide preview card.',
            'footer' => 'Knowledge Shared is Knowledge Multiplied!',
            'file_hint' => 'Supported formats: PDF, DOC, DOCX, PPT, PPTX, JPG, PNG | Max file size: 50 MB',
            'accept' => '.pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png',
            'options' => [
                'public_visible' => 'Make this guide publicly visible (recommended)',
                'allow_download' => 'Allow download',
                'original_content' => 'This is my original content',
                'summary_points' => 'Include summary / key points',
                'practice_questions' => 'Include practice questions',
                'table_of_contents' => 'Add table of contents (if available)',
                'featured' => 'Set as featured content (request)',
            ],
        ];
    }

    private static function videos(): array
    {
        return [
            'key' => 'videos',
            'title' => 'Upload Education Video & Audio Lesson',
            'subtitle' => 'Share your knowledge through videos or audio lessons. Help students learn anytime, anywhere!',
            'quote' => 'Knowledge shared today, empowers tomorrow!',
            'icon' => 'fa-circle-play',
            'tips' => self::commonTips(),
            'guidelines' => [
                'Concept explanation videos',
                'Lecture recordings',
                'Audio lessons and podcasts',
                'Career guidance sessions',
            ],
            'not_allowed' => array_merge(self::commonNotAllowed(), [
                'Copyrighted videos without permission',
            ]),
            'preview_label' => 'Preview (Example Lesson)',
            'preview_caption' => 'Example lesson card preview.',
            'footer' => 'Share. Educate. Empower!',
            'file_hint' => 'Supported formats: MP4, MP3, WAV, WEBM | Max file size: 500 MB',
            'accept' => '.mp4,.mp3,.wav,.webm,.m4a',
            'options' => [
                'public_visible' => 'Make this lesson publicly visible',
                'allow_download' => 'Allow download',
                'original_content' => 'This is my original content',
                'captions' => 'Include captions / subtitles',
                'pdf_notes' => 'Include PDF / Notes',
                'featured' => 'Set as featured content',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private static function commonTips(): array
    {
        return [
            'Use a clear and descriptive title',
            'Select the correct class, subject and type',
            'Upload clear and readable files',
            'Add relevant keywords for better search visibility',
            'Do not upload copyrighted content without permission',
        ];
    }

    /**
     * @return list<string>
     */
    private static function commonNotAllowed(): array
    {
        return [
            'Copyrighted content without permission',
            'Fake or fabricated content',
            'Offensive or inappropriate material',
            'Personal contact details inside files',
            'Poor quality or unreadable files',
        ];
    }
}
