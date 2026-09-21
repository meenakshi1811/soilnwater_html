<div class="edu-courses-summary__stats">
  <div class="edu-courses-summary__stat">
    <strong>{{ number_format($stats['total']) }}</strong>
    <span>Total materials</span>
  </div>
  <div class="edu-courses-summary__stat">
    <strong>{{ number_format($stats['free']) }}</strong>
    <span>Free</span>
  </div>
  <div class="edu-courses-summary__stat">
    <strong>{{ number_format($stats['paid']) }}</strong>
    <span>Paid</span>
  </div>
  <div class="edu-courses-summary__stat">
    <strong>{{ \App\Models\StudyMaterial::formatCompactCount($stats['downloads']) }}</strong>
    <span>Downloads</span>
  </div>
</div>
