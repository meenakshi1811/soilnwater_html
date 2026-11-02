<?php

namespace App\Support;

use App\Models\Institute;
use App\Models\StudyMaterial;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class SchoolProfilePresenter
{
    /** @var array<string, int> */
    public const PREVIEW_LIMITS = [
        'notices' => 8,
        'courses' => 4,
        'class-details' => 6,
        'facilities' => 4,
        'facilities-carousel' => 8,
        'faculty' => 4,
        'school-notes' => 12,
        'gallery' => 6,
        'achievements' => 4,
        'results' => 6,
        'books' => 6,
        'notes-materials' => 6,
        'question-papers' => 6,
        'news' => 4,
        'articles' => 4,
        'events' => 3,
        'reviews' => 3,
        'jobs' => 4,
    ];

    /** @var Collection<int, StudyMaterial>|null the school's notes (loaded once) */
    private ?Collection $schoolNotes = null;

    private ?int $schoolNotesCount = null;

    public function __construct(public Institute $institute)
    {
        $this->institute->loadMissing([
            'user',
            'activeNotices',
            'achievements',
            'topPerformers',
            'schoolClasses',
            'books',
            'jobs' => fn ($query) => $query->open()->latest('published_at')->latest('id'),
        ]);
    }

    public function openJobs(?int $limit = null): Collection
    {
        $jobs = $this->institute->relationLoaded('jobs')
            ? $this->institute->jobs
            : $this->institute->jobs()->open()->latest('published_at')->latest('id')->get();

        if ($limit !== null) {
            return $jobs->take($limit)->values();
        }

        return $jobs;
    }

    public function openJobsCount(): int
    {
        if ($this->institute->relationLoaded('jobs')) {
            return $this->institute->jobs->count();
        }

        return (int) $this->institute->jobs()->open()->count();
    }

    public function meta(string $key, mixed $default = null): mixed
    {
        $stored = $this->institute->getAttributes()['profile_meta'] ?? null;
        if (is_string($stored)) {
            $stored = json_decode($stored, true);
        }

        $profileMeta = is_array($stored) ? $stored : [];

        if ($profileMeta === [] && $this->institute->slug === 'green-valley-international-school-demo') {
            $profileMeta = $this->demoProfileMeta();
        }

        return data_get($profileMeta, $key, $default);
    }

    /** @return array<string, mixed> */
    private function demoProfileMeta(): array
    {
        return [
            'school_code' => '81234',
            'affiliation_number' => '3330456',
            'school_type' => 'Day School',
            'co_educational_label' => 'Co-Educational Day School',
            'is_premium' => true,
            'rating' => 4.8,
            'review_count' => 128,
            'student_count' => 512,
            'faculty_count' => 35,
            'board_result_percent' => '98% Board Result (2024)',
            'campus_area' => '5 Acres',
            'medium_of_instruction' => 'English',
            'operating_hours' => 'Mon – Sat: 08:00 AM – 04:00 PM',
            'why_choose_us' => [
                'Quality Education & Academic Excellence',
                'Well-qualified and Experienced Faculty',
                'Modern Infrastructure & Smart Learning',
                'Holistic Development Programmes',
                'Safe & Secure Campus Environment',
                'Strong Board Results Year-on-Year',
            ],
            'reviews' => [
                [
                    'name' => 'Mrs. Anjali Shah',
                    'relation' => 'Parent',
                    'rating' => 5,
                    'comment' => 'Excellent school with dedicated teachers and a safe campus. My daughter has improved significantly in academics and confidence.',
                    'ago' => '2 weeks ago',
                    'tone' => 'blue',
                ],
                [
                    'name' => 'Mr. Rajesh Kumar',
                    'relation' => 'Parent',
                    'rating' => 5,
                    'comment' => 'Strong focus on discipline, values, and extracurricular activities. The admission process was smooth and transparent.',
                    'ago' => '1 month ago',
                    'tone' => 'green',
                ],
                [
                    'name' => 'Dr. Neha Desai',
                    'relation' => 'Parent',
                    'rating' => 4.5,
                    'comment' => 'Good infrastructure and responsive administration. Transport and communication with parents are well managed.',
                    'ago' => '2 months ago',
                    'tone' => 'purple',
                ],
            ],
        ];
    }

    public function displayName(): string
    {
        return $this->institute->displayName();
    }

    public function heroImage(): string
    {
        $gallery = collect($this->institute->galleryUrls());
        $logoUrl = $this->institute->logoUrl();

        $campusPhoto = $gallery->first(function ($url) use ($logoUrl) {
            return $logoUrl === null || $url !== $logoUrl;
        });

        return $campusPhoto ?: ($logoUrl ?: asset('assets/images/logo_soilnwater.webp'));
    }

    public function galleryImages(): Collection
    {
        return collect($this->institute->galleryUrls());
    }

    /** @return Collection<int, array{type: string, path: string, url: string}> */
    public function galleryItems(): Collection
    {
        return collect($this->institute->galleryMediaItems());
    }

    public function aboutText(): string
    {
        return trim((string) ($this->institute->about ?: $this->institute->description));
    }

    public function subtitleLine(): string
    {
        $parts = array_filter([
            $this->institute->board_affiliation ? $this->institute->board_affiliation.' Affiliated' : null,
            $this->meta('co_educational_label', InstituteProfileDetails::default('co_educational_label')),
            $this->gradesRangeLabel(),
        ]);

        return implode(' | ', $parts);
    }

    public function gradesRangeLabel(): string
    {
        // (the lowest to the highest class, whatever order they were entered in)
        $range = InstituteGrades::range($this->institute->gradesOfferedEntries());

        if ($range === null) {
            return 'Classes: Nursery to XII';
        }

        [$first, $last] = $range;

        return 'Classes: '.($first === $last ? $first : $first.' to '.$last);
    }

    public function establishedYear(): ?int
    {
        return $this->institute->establishedYear();
    }

    public function affiliationLabel(): string
    {
        $board = $this->institute->board_affiliation ?: 'CBSE';
        $number = $this->meta('affiliation_number') ?: $this->institute->government_certificate_number;

        return $number ? $board.' ('.$number.')' : $board;
    }

    public function schoolCode(): string
    {
        return (string) ($this->meta('school_code') ?: $this->institute->government_certificate_number ?: '—');
    }

    /** ('' when the school cleared it: hidden) */
    public function schoolType(): string
    {
        return trim((string) $this->meta('school_type', InstituteProfileDetails::default('school_type')));
    }

    /** The star rating, or null when cleared (the page then shows no rating) */
    public function rating(): ?float
    {
        $rating = $this->meta('rating', InstituteProfileDetails::default('rating'));

        return is_numeric($rating) ? (float) $rating : null;
    }

    public function reviewCount(): int
    {
        $saved = $this->meta('review_count');

        return is_numeric($saved) ? (int) $saved : count($this->reviews());
    }

    /** (0 when the school cleared it: hidden) */
    public function studentCount(): int
    {
        $fromMeta = $this->meta('student_count');

        if (is_numeric($fromMeta) || $fromMeta === '') {
            return (int) $fromMeta;
        }

        $fromClasses = (int) $this->institute->schoolClasses->sum('strength');

        return $fromClasses > 0 ? $fromClasses : InstituteProfileDetails::default('student_count');
    }

    /** (0 when the school cleared it: hidden) */
    public function facultyCount(): int
    {
        $fromMeta = $this->meta('faculty_count');

        if (is_numeric($fromMeta) || $fromMeta === '') {
            return (int) $fromMeta;
        }

        $teachers = $this->institute->schoolClasses
            ->pluck('class_teacher')
            ->filter()
            ->unique()
            ->count();

        return $teachers > 0 ? $teachers : InstituteProfileDetails::default('faculty_count');
    }

    public function boardResultPercent(): string
    {
        return trim((string) $this->meta('board_result_percent', InstituteProfileDetails::default('board_result_percent')));
    }

    public function isPremium(): bool
    {
        return (bool) ($this->meta('is_premium', false));
    }

    /** @return list<array{icon: string, label: string, value: string}> */
    public function quickStats(): array
    {
        return $this->savedList('quick_stats');
    }

    /** @return list<array{icon: string, title: string, grades: string, description: string}> */
    public function courseWings(?int $limit = null): array
    {
        $wings = $this->allCourseWings();

        if ($limit === null || count($wings) <= $limit) {
            return $wings;
        }

        return array_slice($wings, -$limit);
    }

    public function courseWingsCount(): int
    {
        return count($this->allCourseWings());
    }

    /** @return list<array{icon: string, title: string, grades: string, description: string}> */
    private function allCourseWings(): array
    {
        return InstituteCourseWings::wings($this->meta('course_wings'));
    }

    /** @return list<string> */
    public function streams(): array
    {
        return InstituteCourseWings::streams($this->meta('streams'));
    }

    /** @return list<string> */
    public function aboutHighlights(): array
    {
        return $this->savedList('about_highlights');
    }

    /** ('' when the school cleared it: hidden) */
    public function admissionLead(): string
    {
        return trim((string) $this->meta('admission.lead', InstituteProfileDetails::default('admission.lead')));
    }

    /** @return list<string> */
    public function admissionHighlights(): array
    {
        return array_values(array_filter(array_map(
            fn ($item) => trim((string) $item),
            $this->savedList('admission.highlights')
        )));
    }

    /**
     * A saved list (an emptied one stays empty), or the standard one until the school saves its own.
     *
     * @return list<mixed>
     */
    private function savedList(string $key): array
    {
        $saved = $this->meta($key);

        return is_array($saved) ? array_values($saved) : (array) InstituteProfileDetails::default($key);
    }

    /** @return list<string> */
    public function admissionPreviewHighlights(): array
    {
        return array_slice($this->admissionHighlights(), 0, 2);
    }

    public function admissionDetailsText(): string
    {
        return trim((string) $this->meta('admission.details', InstituteProfileDetails::default('admission.details')));
    }

    public function admissionHasMore(): bool
    {
        return count($this->admissionHighlights()) > count($this->admissionPreviewHighlights())
            || trim($this->admissionDetailsText()) !== '';
    }

    /** The school's fee structure (InstituteFeeStructure), or null when it hasn't added one */
    public function feeStructure(): ?array
    {
        return InstituteFeeStructure::fromMeta($this->meta('fee_structure'));
    }

    /** @return list<array{icon: string, label: string, value: string}> */
    public function atAGlance(): array
    {
        // (a line the school cleared is left out)
        return array_values(array_filter([
            ['icon' => 'fa-location-dot', 'label' => 'Location', 'value' => $this->institute->locationLabel() ?: '—'],
            ['icon' => 'fa-tree', 'label' => 'Campus Area', 'value' => trim((string) $this->meta('campus_area', InstituteProfileDetails::default('campus_area')))],
            ['icon' => 'fa-language', 'label' => 'Medium of Instruction', 'value' => trim((string) $this->meta('medium_of_instruction', InstituteProfileDetails::default('medium_of_instruction')))],
            ['icon' => 'fa-certificate', 'label' => 'Board', 'value' => $this->institute->board_affiliation ?: '—'],
            ['icon' => 'fa-layer-group', 'label' => 'Grades Offered', 'value' => $this->gradesRangeLabel()],
            ['icon' => 'fa-phone', 'label' => 'Contact Number', 'value' => implode(', ', $this->institute->phoneNumbersList()) ?: '—'],
        ], fn (array $item) => $item['value'] !== ''));
    }

    /** @return list<string> */
    public function highlights(): array
    {
        $custom = $this->meta('highlights');

        if (is_array($custom) && $custom !== []) {
            return $custom;
        }

        return collect($this->institute->facilities ?? [])->take(8)->values()->all();
    }

    /** @return list<string> */
    public function whyChooseUs(): array
    {
        return $this->savedList('why_choose_us');
    }

    /** @return list<array{name: string, role: string, subject: string, profile_url?: string, photo_url?: string|null}> */
    public function facultyMembers(?int $limit = null): array
    {
        $members = array_merge($this->linkedFacultyMembers(), $this->manualFacultyMembers());

        if ($limit !== null) {
            return array_slice($members, 0, $limit);
        }

        return $members;
    }

    /** @return list<array{name: string, role: string, subject: string, profile_url?: string, photo_url?: string|null}> */
    private function linkedFacultyMembers(): array
    {
        $educators = $this->institute->relationLoaded('affiliatedEducators')
            ? $this->institute->affiliatedEducators
            : $this->institute->affiliatedEducators()->approved()->get();

        return $educators->map(function ($educator) {
            $role = trim((string) ($educator->pivot->role_title ?? ''));
            if ($role === '') {
                $role = trim((string) ($educator->professional_headline ?? ''));
            }
            if ($role === '') {
                $role = $educator->roleLabel();
            }

            $subject = trim((string) ($educator->pivot->subject ?? ''));
            if ($subject === '') {
                $subject = $educator->primarySubject() ?? '';
            }

            return [
                'name' => $educator->display_name,
                'role' => $role,
                'subject' => $subject,
                'profile_url' => $educator->publicUrl(),
                'photo_url' => $educator->photoUrl(),
            ];
        })->all();
    }

    /** @return list<array{name: string, role: string, subject: string}> */
    private function manualFacultyMembers(): array
    {
        $custom = $this->meta('faculty');

        if (is_array($custom) && $custom !== []) {
            return $custom;
        }

        return $this->institute->schoolClasses
            ->filter(fn ($class) => filled($class->class_teacher))
            ->unique('class_teacher')
            ->map(fn ($class) => [
                'name' => $class->class_teacher,
                'role' => 'Class Teacher · '.$class->displayLabel(),
                'subject' => 'Academics',
            ])
            ->values()
            ->all();
    }

    public function facultyMembersCount(): int
    {
        return count($this->facultyMembers());
    }

    /**
     * The name this school's notes are filed under: the notes page stores a registered school's
     * own name on the notes uploaded for it (see StudyMaterialLocation).
     */
    public function schoolNotesName(): string
    {
        return trim((string) ($this->institute->display_name ?: $this->institute->institution_name));
    }

    /** Notes, question papers and other study material uploaded for this school, newest first */
    public function schoolNotes(): Collection
    {
        return $this->schoolNotes ??= $this->schoolNotesCount() > 0
            ? StudyMaterial::query()
                ->approved()
                ->publiclyListed()
                ->where('school_name', $this->schoolNotesName())
                ->latest()
                ->orderByDesc('id')
                ->limit(self::PREVIEW_LIMITS['school-notes'])
                ->get()
            : collect();
    }

    public function schoolNotesCount(): int
    {
        return $this->schoolNotesCount ??= $this->schoolNotesName() === ''
            ? 0
            : StudyMaterial::query()->approved()->publiclyListed()->where('school_name', $this->schoolNotesName())->count();
    }

    /** The notes page, showing this school's notes */
    public function schoolNotesUrl(): string
    {
        return route('study-materials.notes', ['school' => $this->schoolNotesName()]);
    }

    public function isQuestionPaperBook(object $book): bool
    {
        $hay = strtolower(trim($book->title.' '.($book->subject ?? '').' '.($book->publisher ?? '')));

        return str_contains($hay, 'question paper')
            || str_contains($hay, 'question-paper')
            || str_contains($hay, 'sample paper')
            || str_contains($hay, 'previous year')
            || (str_contains($hay, 'paper') && str_contains($hay, 'sample'));
    }

    /** @return \Illuminate\Support\Collection<int, \App\Models\InstituteBook> */
    public function notesMaterialBooks(?int $limit = null): Collection
    {
        $books = $this->institute->books->reject(fn ($book): bool => $this->isQuestionPaperBook($book))->values();
        if ($books->isEmpty()) {
            $books = $this->institute->books;
        }

        return $limit !== null ? $books->take($limit) : $books;
    }

    public function notesMaterialBooksCount(): int
    {
        return $this->notesMaterialBooks()->count();
    }

    /** @return \Illuminate\Support\Collection<int, \App\Models\InstituteBook> */
    public function questionPaperBooks(?int $limit = null): Collection
    {
        $books = $this->institute->books->filter(fn ($book): bool => $this->isQuestionPaperBook($book))->values();

        return $limit !== null ? $books->take($limit) : $books;
    }

    public function questionPaperBooksCount(): int
    {
        return $this->questionPaperBooks()->count();
    }

    /** @return list<array{name: string, image: ?string}> */
    public function facilityCards(?int $limit = null): array
    {
        $gallery = $this->galleryImages();
        $facilities = collect($this->institute->facilities ?? []);

        // The facility's own photo, or else one from the gallery
        $cards = $facilities->map(function ($facility, $index) use ($gallery) {
            return [
                'name' => $facility,
                'image' => $this->institute->facilityImageUrl((string) $facility)
                    ?? $gallery->get($index % max($gallery->count(), 1)),
            ];
        })->values();

        if ($limit !== null) {
            return $cards->take($limit)->all();
        }

        return $cards->all();
    }

    public function facilitiesCount(): int
    {
        return count($this->institute->facilities ?? []);
    }

    /** @return list<array{id: int, title: string, excerpt: string, message: string, day: string, month: string, expires: ?string, image: ?string}> */
    public function newsItems(?int $limit = null): array
    {
        $items = $this->institute->activeNotices->map(function ($notice) {
            $date = $notice->created_at ?? now();

            return [
                'id' => $notice->id,
                'title' => $notice->displayTitle(),
                'excerpt' => $notice->excerpt(120),
                'message' => $notice->message,
                'day' => $date->format('d'),
                'month' => strtoupper($date->format('M')),
                'expires' => $notice->expires_at?->format('d M Y'),
                'image' => $notice->imageUrl(),
            ];
        })->values();

        if ($limit !== null) {
            return $items->take($limit)->all();
        }

        return $items->all();
    }

    public function newsItemsCount(): int
    {
        return $this->institute->activeNotices->count();
    }

    /** @return list<array{id: int, title: string, schedule: string, day: string, month: string}> */
    public function upcomingEvents(?int $limit = null): array
    {
        $custom = $this->meta('events');

        if (is_array($custom) && $custom !== []) {
            $events = $this->savedEvents($custom);

            return $limit !== null ? array_slice($events, 0, $limit) : $events;
        }

        $items = $this->institute->activeNotices->map(function ($notice) {
            $date = $notice->expires_at ?? $notice->created_at ?? now();

            return [
                'id' => $notice->id,
                'title' => $notice->displayTitle(),
                'schedule' => $date->format('l, j F Y').' • 10:00 AM',
                'day' => $date->format('d'),
                'month' => strtoupper($date->format('M')),
            ];
        })->values();

        if ($limit !== null) {
            return $items->take($limit)->all();
        }

        return $items->all();
    }

    public function upcomingEventsCount(): int
    {
        $custom = $this->meta('events');

        if (is_array($custom) && $custom !== []) {
            return count($this->savedEvents($custom));
        }

        return $this->institute->activeNotices->count();
    }

    /**
     * Events saved on the profile form ({title, date, time}): today's and later ones, soonest first.
     * (Ones saved before with their own schedule line show as they are.)
     *
     * @param  array<int, mixed>  $saved
     * @return list<array{id: int, title: string, schedule: string, day: string, month: string}>
     */
    private function savedEvents(array $saved): array
    {
        $today = now()->startOfDay();

        return collect($saved)
            ->filter(fn ($event) => is_array($event))
            ->map(function (array $event, int $index) {
                if (! filled($event['date'] ?? null)) {
                    return $event + ['id' => $index + 1];
                }

                $date = \Illuminate\Support\Carbon::parse($event['date']);
                $time = trim((string) ($event['time'] ?? ''));

                return [
                    'id' => $index + 1,
                    'title' => (string) ($event['title'] ?? ''),
                    'schedule' => $date->format('l, j F Y').($time !== '' ? ' • '.$time : ''),
                    'day' => $date->format('d'),
                    'month' => strtoupper($date->format('M')),
                    'date' => $date,
                ];
            })
            ->reject(fn (array $event) => isset($event['date']) && $event['date']->lt($today))
            ->sortBy(fn (array $event) => isset($event['date']) ? $event['date']->timestamp : PHP_INT_MAX)
            ->map(fn (array $event) => array_diff_key($event, ['date' => true]))
            ->values()
            ->all();
    }

    /** @return list<array{name: string, relation: string, rating: float, comment: string, ago: string, initial: string, tone: string}> */
    public function reviews(?int $limit = null): array
    {
        $custom = $this->meta('reviews');

        if (! is_array($custom) || $custom === []) {
            return [];
        }

        $items = collect($custom)->map(function ($review) {
            $name = (string) ($review['name'] ?? 'Parent');

            return [
                'name' => $name,
                'relation' => (string) (($review['relation'] ?? '') ?: 'Parent'),
                'rating' => (float) ($review['rating'] ?? 5),
                'comment' => (string) ($review['comment'] ?? ''),
                // ("Recently" when the form's "when" was left blank)
                'ago' => (string) (($review['ago'] ?? '') ?: 'Recently'),
                'initial' => Str::upper(Str::substr($name, 0, 1)),
                'tone' => (string) (($review['tone'] ?? '') ?: 'blue'),
            ];
        });

        if ($limit !== null) {
            return $items->take($limit)->all();
        }

        return $items->all();
    }

    public function reviewsCount(): int
    {
        $custom = $this->meta('reviews');

        return is_array($custom) ? count($custom) : 0;
    }

    /** ('' when the school cleared it: hidden) */
    public function operatingHours(): string
    {
        return trim((string) $this->meta('operating_hours', InstituteProfileDetails::default('operating_hours')));
    }

    public function mapEmbedUrl(): ?string
    {
        if ($this->institute->latitude && $this->institute->longitude) {
            return 'https://maps.google.com/maps?q='.$this->institute->latitude.','.$this->institute->longitude.'&z=15&output=embed';
        }

        $address = urlencode($this->institute->formattedAddress());

        return filled($address) ? 'https://maps.google.com/maps?q='.$address.'&z=15&output=embed' : null;
    }

    public function directionsUrl(): ?string
    {
        if ($this->institute->latitude && $this->institute->longitude) {
            return 'https://www.google.com/maps/dir/?api=1&destination='.$this->institute->latitude.','.$this->institute->longitude;
        }

        $address = urlencode($this->institute->formattedAddress());

        return filled($address) ? 'https://www.google.com/maps/dir/?api=1&destination='.$address : null;
    }

    /** @return list<array{id: string, label: string, icon: string}> */
    public function navItems(): array
    {
        $items = [
            ['id' => 'sch-overview', 'label' => 'Profile Overview', 'icon' => 'fa-school'],
            ['id' => 'sch-about', 'label' => 'About Institution', 'icon' => 'fa-circle-info'],
            ['id' => 'sch-courses', 'label' => 'Courses & Programs', 'icon' => 'fa-book'],
            ['id' => 'sch-admission', 'label' => 'Admission Info', 'icon' => 'fa-door-open'],
            ['id' => 'sch-facilities', 'label' => 'Facilities', 'icon' => 'fa-building'],
            ['id' => 'sch-faculty', 'label' => 'Faculty', 'icon' => 'fa-chalkboard-user'],
            ['id' => 'sch-notes', 'label' => 'Notes', 'icon' => 'fa-file-lines'],
            ['id' => 'sch-gallery', 'label' => 'Gallery', 'icon' => 'fa-images'],
            ['id' => 'sch-achievements', 'label' => 'Achievements', 'icon' => 'fa-trophy'],
            ['id' => 'sch-notices', 'label' => 'Notice Board', 'icon' => 'fa-bullhorn'],
            [
                'id' => 'sch-diary',
                'label' => 'Diary',
                'icon' => 'fa-book-bookmark',
                'href' => $this->institute->publicDiaryUrl(),
            ],
            ['id' => 'sch-events', 'label' => 'Events', 'icon' => 'fa-calendar-days'],
            ['id' => 'sch-news', 'label' => 'News & Announcements', 'icon' => 'fa-newspaper'],
            ['id' => 'sch-reviews', 'label' => 'Reviews & Ratings', 'icon' => 'fa-star'],
            ['id' => 'sch-results', 'label' => 'Results', 'icon' => 'fa-medal'],
            ['id' => 'sch-jobs', 'label' => 'Jobs & Careers', 'icon' => 'fa-briefcase'],
            ['id' => 'sch-notes-material', 'label' => 'Notes & Material', 'icon' => 'fa-note-sticky'],
            ['id' => 'sch-question-papers', 'label' => 'Question Papers', 'icon' => 'fa-file-circle-question'],
            ['id' => 'sch-contact', 'label' => 'Enquiry & Contact', 'icon' => 'fa-envelope'],
        ];

        return array_values(array_filter($items, function ($item) {
            return match ($item['id']) {
                'sch-about' => filled($this->aboutText()),
                'sch-gallery' => $this->galleryItems()->count() > 1,
                'sch-achievements' => $this->institute->achievements->isNotEmpty(),
                'sch-notices', 'sch-events', 'sch-news' => $this->institute->activeNotices->isNotEmpty(),
                'sch-reviews' => $this->reviews() !== [],
                'sch-results' => $this->institute->topPerformers->isNotEmpty(),
                'sch-jobs' => $this->openJobsCount() > 0,
                'sch-notes-material' => $this->notesMaterialBooks()->isNotEmpty(),
                'sch-question-papers' => $this->questionPaperBooks()->isNotEmpty(),
                'sch-faculty' => $this->facultyMembers() !== [],
                'sch-notes' => $this->schoolNotesCount() > 0,
                'sch-facilities' => collect($this->institute->facilities ?? [])->isNotEmpty(),
                default => true,
            };
        }));
    }

    public static function isValidSection(string $section): bool
    {
        return array_key_exists($section, self::sectionCatalog());
    }

    /** @return array<string, array{title: string, lead: string}> */
    public static function sectionCatalog(): array
    {
        return [
            'notices' => [
                'title' => 'Notice Board',
                'lead' => 'All active notices and announcements from this institution.',
            ],
            'courses' => [
                'title' => 'Courses & Programs',
                'lead' => 'Browse all courses, streams, and class details.',
            ],
            'facilities' => [
                'title' => 'Facilities',
                'lead' => 'Explore campus facilities and infrastructure.',
            ],
            'faculty' => [
                'title' => 'Faculty',
                'lead' => 'Meet the teaching and academic team.',
            ],
            'gallery' => [
                'title' => 'Gallery',
                'lead' => 'Campus photos and gallery images.',
            ],
            'achievements' => [
                'title' => 'Achievements',
                'lead' => 'Awards, milestones, and institutional achievements.',
            ],
            'results' => [
                'title' => 'Results',
                'lead' => 'Student results, toppers, and academic highlights.',
            ],
            'books' => [
                'title' => 'Students Corner',
                'lead' => 'Textbooks and study resources across classes.',
            ],
            'notes-materials' => [
                'title' => 'Notes & Study Material',
                'lead' => 'Notes, textbooks, and study resources across classes.',
            ],
            'question-papers' => [
                'title' => 'Question Papers',
                'lead' => 'Sample papers and previous-year question papers.',
            ],
            'news' => [
                'title' => 'News & Announcements',
                'lead' => 'Latest news and updates.',
            ],
            'articles' => [
                'title' => 'Articles & News',
                'lead' => 'Articles, stories, and announcements from the institution.',
            ],
            'events' => [
                'title' => 'Upcoming Events',
                'lead' => 'Events and important dates.',
            ],
            'reviews' => [
                'title' => 'Reviews & Ratings',
                'lead' => 'What parents and students say.',
            ],
            'jobs' => [
                'title' => 'Jobs & Careers',
                'lead' => 'Open roles and career opportunities at this institution.',
            ],
        ];
    }
}
