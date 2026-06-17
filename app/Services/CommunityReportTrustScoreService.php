<?php

namespace App\Services;

use App\Models\CommunityPost;

class CommunityReportTrustScoreService
{
    public const MAX_FACTOR_POINTS = 20;

    /** @var array<string, string> */
    public const FACTOR_LABELS = [
        'evidence_provided' => 'Evidence Provided',
        'location_verified' => 'Location Verified',
        'documents_attached' => 'Documents Attached',
        'community_support' => 'Community Support',
        'admin_verification' => 'Admin Verification',
    ];

    /** @var list<string> */
    private const SUPPORT_REACTIONS = ['Support', 'Vote', 'Helpful'];

    /** @var list<string> */
    private const DOCUMENT_EXTENSIONS = ['pdf', 'doc', 'docx'];

    public static function score(CommunityPost $post): int
    {
        return (int) round(collect(self::breakdown($post))->sum('points'));
    }

    /**
     * @return array<string, array{label: string, points: float, max: int, met: bool, detail: string}>
     */
    public static function breakdown(CommunityPost $post): array
    {
        return [
            'evidence_provided' => self::evidenceFactor($post),
            'location_verified' => self::locationFactor($post),
            'documents_attached' => self::documentsFactor($post),
            'community_support' => self::communitySupportFactor($post),
            'admin_verification' => self::adminVerificationFactor($post),
        ];
    }

    public static function syncToMeta(CommunityPost $post): CommunityPost
    {
        if ($post->content_type !== 'reports') {
            return $post;
        }

        $breakdown = self::breakdown($post);
        $meta = $post->meta ?? [];
        $meta['report_trust_score'] = self::score($post);
        $meta['report_trust_factors'] = collect($breakdown)
            ->mapWithKeys(fn (array $factor, string $key): array => [
                $key => [
                    'label' => $factor['label'],
                    'met' => $factor['met'],
                    'points' => $factor['points'],
                    'detail' => $factor['detail'],
                ],
            ])
            ->all();
        $meta['report_trust_calculated_at'] = now()->toIso8601String();

        $post->forceFill(['meta' => $meta])->saveQuietly();

        return $post->fresh();
    }

    public static function badgeHtml(CommunityPost $post): string
    {
        if ($post->content_type !== 'reports') {
            return '—';
        }

        $score = is_numeric(data_get($post->meta, 'report_trust_score'))
            ? (int) round((float) data_get($post->meta, 'report_trust_score'))
            : self::score($post);

        $class = match (true) {
            $score >= 80 => 'bg-success',
            $score >= 50 => 'bg-warning text-dark',
            default => 'bg-secondary',
        };

        return '<span class="badge '.$class.'">Trust '.$score.'%</span>';
    }

    /**
     * @return array{label: string, points: float, max: int, met: bool, detail: string}
     */
    private static function evidenceFactor(CommunityPost $post): array
    {
        $hasFeaturedImages = $post->featuredImages() !== [];
        $hasVideo = $post->hasVideo();
        $mediaAttachments = self::countMediaAttachments($post);
        $communityEvidence = (int) $post->reportEvidence()
            ->where(function ($query): void {
                $query->whereIn('type', ['image', 'video'])
                    ->orWhere('name', 'like', '%.jpg')
                    ->orWhere('name', 'like', '%.jpeg')
                    ->orWhere('name', 'like', '%.png')
                    ->orWhere('name', 'like', '%.webp')
                    ->orWhere('name', 'like', '%.mp4')
                    ->orWhere('name', 'like', '%.mov')
                    ->orWhere('name', 'like', '%.avi');
            })
            ->count();
        $met = $hasFeaturedImages || $hasVideo || $mediaAttachments > 0 || $communityEvidence > 0;

        $detail = match (true) {
            $hasFeaturedImages && $hasVideo => 'Featured images and video uploaded',
            $hasFeaturedImages => 'Featured images uploaded',
            $hasVideo => 'Supporting video provided',
            $mediaAttachments > 0 && $communityEvidence > 0 => $mediaAttachments.' author file(s) and '.$communityEvidence.' community evidence file(s)',
            $mediaAttachments > 0 => $mediaAttachments.' evidence file(s) attached',
            $communityEvidence > 0 => $communityEvidence.' community evidence file(s) contributed',
            default => 'Add photos, video, or evidence files',
        };

        return self::factor('evidence_provided', $met, $detail);
    }

    /**
     * @return array{label: string, points: float, max: int, met: bool, detail: string}
     */
    private static function locationFactor(CommunityPost $post): array
    {
        $hasCoordinates = filled($post->location_lat) && filled($post->location_lng);
        $detail = $hasCoordinates
            ? ($post->usesGpsLocation() ? 'GPS coordinates pinned on map' : 'Coordinates recorded for this report')
            : 'Add a GPS location or map pin';

        return self::factor('location_verified', $hasCoordinates, $detail);
    }

    /**
     * @return array{label: string, points: float, max: int, met: bool, detail: string}
     */
    private static function documentsFactor(CommunityPost $post): array
    {
        $documentCount = self::countDocumentAttachments($post);
        $communityDocuments = (int) $post->reportEvidence()
            ->where(function ($query): void {
                $query->where('type', 'application')
                    ->orWhere('name', 'like', '%.pdf')
                    ->orWhere('name', 'like', '%.doc')
                    ->orWhere('name', 'like', '%.docx');
            })
            ->count();
        $totalDocuments = $documentCount + $communityDocuments;
        $met = $totalDocuments > 0;
        $detail = $met
            ? $totalDocuments.' document(s) attached'
            : 'Attach PDF or document evidence';

        return self::factor('documents_attached', $met, $detail);
    }

    /**
     * @return array{label: string, points: float, max: int, met: bool, detail: string}
     */
    private static function communitySupportFactor(CommunityPost $post): array
    {
        $post->loadCount([
            'reactions as support_reactions_count' => fn ($query) => $query->whereIn('reaction', self::SUPPORT_REACTIONS),
            'saves',
            'comments',
            'reportSupports',
            'reportAgreements',
        ]);

        $supportReactions = (int) ($post->support_reactions_count ?? 0);
        $saves = (int) ($post->saves_count ?? 0);
        $comments = (int) ($post->comments_count ?? 0);
        $reportSupports = (int) ($post->report_supports_count ?? $post->reportSupports()->count());
        $reportAgreements = (int) ($post->report_agreements_count ?? $post->reportAgreements()->count());
        $engagementPoints = min(
            self::MAX_FACTOR_POINTS,
            ($supportReactions * 3) + ($saves * 3) + ($comments * 2) + ($reportSupports * 4) + ($reportAgreements * 2)
        );
        $met = $engagementPoints >= self::MAX_FACTOR_POINTS;
        $detail = $engagementPoints > 0
            ? sprintf(
                '%d supports, %d agreements, %d reactions, %d saves, %d comments',
                $reportSupports,
                $reportAgreements,
                $supportReactions,
                $saves,
                $comments
            )
            : 'Community support, agreement, reactions, saves, and comments increase trust';

        return [
            'label' => self::FACTOR_LABELS['community_support'],
            'points' => round($engagementPoints, 2),
            'max' => self::MAX_FACTOR_POINTS,
            'met' => $met,
            'detail' => $detail,
        ];
    }

    /**
     * @return array{label: string, points: float, max: int, met: bool, detail: string}
     */
    private static function adminVerificationFactor(CommunityPost $post): array
    {
        $met = $post->status === CommunityPost::STATUS_PUBLISHED && filled($post->reviewed_by);
        $detail = match (true) {
            $met => 'Verified and published by SoilnWater admin',
            $post->isPendingApproval() => 'Awaiting admin review',
            $post->status === CommunityPost::STATUS_DECLINED => 'Report was not approved',
            default => 'Published reports earn admin verification',
        };

        return self::factor('admin_verification', $met, $detail);
    }

    /**
     * @return array{label: string, points: float, max: int, met: bool, detail: string}
     */
    private static function factor(string $key, bool $met, string $detail): array
    {
        return [
            'label' => self::FACTOR_LABELS[$key],
            'points' => $met ? (float) self::MAX_FACTOR_POINTS : 0.0,
            'max' => self::MAX_FACTOR_POINTS,
            'met' => $met,
            'detail' => $detail,
        ];
    }

    private static function countMediaAttachments(CommunityPost $post): int
    {
        return collect((array) data_get($post->meta, 'issue_attachments', []))
            ->filter(function (mixed $attachment): bool {
                $type = strtolower((string) data_get($attachment, 'type', ''));
                $name = strtolower((string) data_get($attachment, 'name', ''));

                return in_array($type, ['image', 'video'], true)
                    || preg_match('/\.(jpe?g|png|webp|gif|mp4|mov|avi)$/i', $name) === 1;
            })
            ->count();
    }

    private static function countDocumentAttachments(CommunityPost $post): int
    {
        return collect((array) data_get($post->meta, 'issue_attachments', []))
            ->filter(function (mixed $attachment): bool {
                $type = strtolower((string) data_get($attachment, 'type', ''));
                $name = strtolower((string) data_get($attachment, 'name', ''));

                return $type === 'application'
                    || preg_match('/\.(pdf|docx?)$/i', $name) === 1;
            })
            ->count();
    }
}
