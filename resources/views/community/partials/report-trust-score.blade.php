@php
    $trustScore = $post->reportTrustScore();
    $trustBreakdown = $post->reportTrustBreakdown();
    $compact = $compact ?? false;
@endphp

@if($post->isReportContent() && $trustBreakdown !== [])
    <div class="report-trust-score {{ $post->reportTrustBadgeClass() }} {{ $compact ? 'report-trust-score--compact' : '' }}">
        <div class="report-trust-score__header">
            <div>
                <span class="report-trust-score__kicker">Unique SoilnWater Feature</span>
                <h4 class="report-trust-score__title mb-0">Report Trust Score</h4>
            </div>
            <div class="report-trust-score__value-wrap">
                <span class="report-trust-score__value">{{ $trustScore }}%</span>
            </div>
        </div>

        @unless($compact)
            <p class="report-trust-score__intro mb-3">
                Automatically calculated from evidence, location, documents, community support, and admin verification.
            </p>
        @endunless

        <ul class="report-trust-score__factors list-unstyled mb-0">
            @foreach($trustBreakdown as $factor)
                <li class="report-trust-score__factor {{ $factor['met'] ? 'is-met' : '' }}">
                    <span class="report-trust-score__factor-icon" aria-hidden="true">
                        <i class="fa-solid {{ $factor['met'] ? 'fa-circle-check' : 'fa-circle' }}"></i>
                    </span>
                    <span class="report-trust-score__factor-body">
                        <strong>{{ $factor['label'] }}</strong>
                        @unless($compact)
                            <span class="d-block small text-muted">{{ $factor['detail'] }}</span>
                        @endunless
                    </span>
                    <span class="report-trust-score__factor-points">{{ (int) $factor['points'] }}/{{ $factor['max'] }}</span>
                </li>
            @endforeach
        </ul>
    </div>
@endif
