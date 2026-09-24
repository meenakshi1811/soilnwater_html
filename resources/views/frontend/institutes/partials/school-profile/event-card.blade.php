<article class="sch-event-card">
  <div class="sch-event-card__date">
    <strong>{{ $event['day'] }}</strong>
    <span>{{ $event['month'] }}</span>
  </div>
  <h3 class="sch-event-card__title">{{ $event['title'] }}</h3>
  <p class="sch-event-card__schedule">
    <i class="fa-regular fa-clock" aria-hidden="true"></i>
    {{ $event['schedule'] }}
  </p>
</article>
