<?php

namespace App\Support;

use App\Models\Institute;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class SchoolProfilePresenter
{
    public function __construct(public Institute $institute)
    {
        $this->institute->loadMissing([
            'user',
            'activeNotices',
            'achievements',
            'topPerformers',
            'schoolClasses',
            'books',
        ]);
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

    public function aboutText(): string
    {
        return trim((string) ($this->institute->about ?: $this->institute->description));
    }

    public function subtitleLine(): string
    {
        $parts = array_filter([
            $this->institute->board_affiliation ? $this->institute->board_affiliation.' Affiliated' : null,
            $this->meta('co_educational_label', 'Co-Educational Day School'),
            $this->gradesRangeLabel(),
        ]);

        return implode(' | ', $parts);
    }

    public function gradesRangeLabel(): string
    {
        $grades = collect($this->institute->grades_offered ?? []);

        if ($grades->isEmpty()) {
            return 'Classes: Nursery to XII';
        }

        return 'Classes: '.$grades->first().' to '.$grades->last();
    }

    public function establishedYear(): ?int
    {
        return $this->institute->establishedYear();
    }

    public function affiliationLabel(): string
    {
        $board = $this->institute->board_affiliation ?: 'CBSE';
        $number = $this->meta('affiliation_number', $this->institute->government_certificate_number);

        return $number ? $board.' ('.$number.')' : $board;
    }

    public function schoolCode(): string
    {
        return (string) ($this->meta('school_code') ?: $this->institute->government_certificate_number ?: '—');
    }

    public function schoolType(): string
    {
        return (string) ($this->meta('school_type', 'Day School'));
    }

    public function rating(): float
    {
        return (float) ($this->meta('rating', 4.8));
    }

    public function reviewCount(): int
    {
        return (int) ($this->meta('review_count', count($this->reviews())));
    }

    public function studentCount(): int
    {
        $fromMeta = $this->meta('student_count');

        if (is_numeric($fromMeta)) {
            return (int) $fromMeta;
        }

        $fromClasses = (int) $this->institute->schoolClasses->sum('strength');

        return $fromClasses > 0 ? $fromClasses : 512;
    }

    public function facultyCount(): int
    {
        $fromMeta = $this->meta('faculty_count');

        if (is_numeric($fromMeta)) {
            return (int) $fromMeta;
        }

        $teachers = $this->institute->schoolClasses
            ->pluck('class_teacher')
            ->filter()
            ->unique()
            ->count();

        return $teachers > 0 ? $teachers : 35;
    }

    public function boardResultPercent(): string
    {
        return (string) ($this->meta('board_result_percent', '98% Board Result ('.now()->subYear()->year.')'));
    }

    public function isPremium(): bool
    {
        return (bool) ($this->meta('is_premium', false));
    }

    /** @return list<array{icon: string, label: string, value: string}> */
    public function quickStats(): array
    {
        $defaults = [
            ['icon' => 'fa-chalkboard', 'label' => 'Smart Classrooms', 'value' => 'Yes'],
            ['icon' => 'fa-bus', 'label' => 'Transport', 'value' => 'Available'],
            ['icon' => 'fa-book', 'label' => 'Library', 'value' => '15,000+ Books'],
            ['icon' => 'fa-flask', 'label' => 'Laboratories', 'value' => '6 Labs'],
            ['icon' => 'fa-futbol', 'label' => 'Sports', 'value' => '20+ Activities'],
            ['icon' => 'fa-video', 'label' => 'CCTV Security', 'value' => '24x7'],
        ];

        $custom = $this->meta('quick_stats');

        return is_array($custom) && $custom !== [] ? $custom : $defaults;
    }

    /** @return list<array{icon: string, title: string, grades: string, description: string}> */
    public function courseWings(): array
    {
        $custom = $this->meta('course_wings');

        if (is_array($custom) && $custom !== []) {
            return $custom;
        }

        return [
            ['icon' => 'fa-child', 'title' => 'Primary Wing', 'grades' => 'Nursery to Class V', 'description' => 'Activity-based learning with language, numeracy, and creative play.'],
            ['icon' => 'fa-book-open', 'title' => 'Middle Wing', 'grades' => 'Class VI to VIII', 'description' => 'Concept building across science, maths, and humanities with project work.'],
            ['icon' => 'fa-user-graduate', 'title' => 'Secondary Wing', 'grades' => 'Class IX to X', 'description' => 'Board-focused curriculum with weekly assessments and remedial support.'],
            ['icon' => 'fa-graduation-cap', 'title' => 'Senior Secondary', 'grades' => 'Class XI to XII', 'description' => 'Specialized streams with career counselling and competitive exam prep.'],
        ];
    }

    /** @return list<string> */
    public function streams(): array
    {
        $streams = $this->meta('streams');

        return is_array($streams) && $streams !== []
            ? $streams
            : ['Science (PCM)', 'Science (PCB)', 'Commerce', 'Humanities'];
    }

    /** @return list<string> */
    public function aboutHighlights(): array
    {
        $custom = $this->meta('about_highlights');

        if (is_array($custom) && $custom !== []) {
            return $custom;
        }

        return [
            'Student-centric learning environment',
            'Safe, green and technology-enabled campus',
            'Experienced and dedicated faculty',
            'Strong focus on sports and co-curricular activities',
            'Regular parent engagement and progress tracking',
        ];
    }

    /** @return list<array{icon: string, label: string, value: string}> */
    public function atAGlance(): array
    {
        return [
            ['icon' => 'fa-location-dot', 'label' => 'Location', 'value' => $this->institute->locationLabel() ?: '—'],
            ['icon' => 'fa-tree', 'label' => 'Campus Area', 'value' => (string) $this->meta('campus_area', '5 Acres')],
            ['icon' => 'fa-language', 'label' => 'Medium of Instruction', 'value' => (string) $this->meta('medium_of_instruction', 'English')],
            ['icon' => 'fa-certificate', 'label' => 'Board', 'value' => $this->institute->board_affiliation ?: '—'],
            ['icon' => 'fa-layer-group', 'label' => 'Grades Offered', 'value' => $this->gradesRangeLabel()],
            ['icon' => 'fa-phone', 'label' => 'Contact Number', 'value' => $this->institute->phone ?: '—'],
        ];
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
        $custom = $this->meta('why_choose_us');

        if (is_array($custom) && $custom !== []) {
            return $custom;
        }

        return [
            'Quality Education & Academic Excellence',
            'Well-qualified and Experienced Faculty',
            'Modern Infrastructure & Smart Learning',
            'Holistic Development Programmes',
            'Safe & Secure Campus Environment',
            'Strong Board Results Year-on-Year',
        ];
    }

    /** @return list<array{name: string, role: string, subject: string}> */
    public function facultyMembers(): array
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

    /** @return list<array{name: string, image: ?string}> */
    public function facilityCards(): array
    {
        $gallery = $this->galleryImages();
        $facilities = collect($this->institute->facilities ?? []);

        return $facilities->map(function ($facility, $index) use ($gallery) {
            return [
                'name' => $facility,
                'image' => $gallery->get($index % max($gallery->count(), 1)),
            ];
        })->take(8)->values()->all();
    }

    /** @return list<array{id: int, title: string, excerpt: string, day: string, month: string}> */
    public function newsItems(): array
    {
        return $this->institute->activeNotices->map(function ($notice) {
            $date = $notice->created_at ?? now();

            return [
                'id' => $notice->id,
                'title' => $notice->displayTitle(),
                'excerpt' => $notice->excerpt(120),
                'day' => $date->format('d'),
                'month' => strtoupper($date->format('M')),
            ];
        })->values()->all();
    }

    /** @return list<array{id: int, title: string, schedule: string, day: string, month: string}> */
    public function upcomingEvents(): array
    {
        $custom = $this->meta('events');

        if (is_array($custom) && $custom !== []) {
            return $custom;
        }

        return $this->institute->activeNotices->map(function ($notice) {
            $date = $notice->expires_at ?? $notice->created_at ?? now();

            return [
                'id' => $notice->id,
                'title' => $notice->displayTitle(),
                'schedule' => $date->format('l, j F Y').' • 10:00 AM',
                'day' => $date->format('d'),
                'month' => strtoupper($date->format('M')),
            ];
        })->values()->all();
    }

    /** @return list<array{name: string, relation: string, rating: float, comment: string, ago: string, initial: string, tone: string}> */
    public function reviews(): array
    {
        $custom = $this->meta('reviews');

        if (! is_array($custom) || $custom === []) {
            return [];
        }

        return collect($custom)->map(function ($review) {
            $name = (string) ($review['name'] ?? 'Parent');

            return [
                'name' => $name,
                'relation' => (string) ($review['relation'] ?? 'Parent'),
                'rating' => (float) ($review['rating'] ?? 5),
                'comment' => (string) ($review['comment'] ?? ''),
                'ago' => (string) ($review['ago'] ?? 'Recently'),
                'initial' => Str::upper(Str::substr($name, 0, 1)),
                'tone' => (string) ($review['tone'] ?? 'blue'),
            ];
        })->all();
    }

    public function operatingHours(): string
    {
        return (string) ($this->meta('operating_hours', 'Mon – Sat: 08:00 AM – 04:00 PM'));
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
            ['id' => 'sch-gallery', 'label' => 'Gallery', 'icon' => 'fa-images'],
            ['id' => 'sch-achievements', 'label' => 'Achievements', 'icon' => 'fa-trophy'],
            ['id' => 'sch-notices', 'label' => 'Notice Board', 'icon' => 'fa-bullhorn'],
            ['id' => 'sch-events', 'label' => 'Events', 'icon' => 'fa-calendar-days'],
            ['id' => 'sch-news', 'label' => 'News & Announcements', 'icon' => 'fa-newspaper'],
            ['id' => 'sch-reviews', 'label' => 'Reviews & Ratings', 'icon' => 'fa-star'],
            ['id' => 'sch-results', 'label' => 'Placement / Results', 'icon' => 'fa-medal'],
            ['id' => 'sch-books', 'label' => 'Students Corner', 'icon' => 'fa-book-open-reader'],
            ['id' => 'sch-contact', 'label' => 'Enquiry & Contact', 'icon' => 'fa-envelope'],
        ];

        return array_values(array_filter($items, function ($item) {
            return match ($item['id']) {
                'sch-about' => filled($this->aboutText()),
                'sch-gallery' => $this->galleryImages()->count() > 1,
                'sch-achievements' => $this->institute->achievements->isNotEmpty(),
                'sch-notices', 'sch-events', 'sch-news' => $this->institute->activeNotices->isNotEmpty(),
                'sch-reviews' => $this->reviews() !== [],
                'sch-results' => $this->institute->topPerformers->isNotEmpty(),
                'sch-books' => $this->institute->books->isNotEmpty(),
                'sch-faculty' => $this->facultyMembers() !== [],
                'sch-facilities' => collect($this->institute->facilities ?? [])->isNotEmpty(),
                default => true,
            };
        }));
    }
}
