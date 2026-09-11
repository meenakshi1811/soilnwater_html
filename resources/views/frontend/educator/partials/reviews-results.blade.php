@foreach($reviews as $item)
  @include('frontend.educator.partials.review-item', ['item' => $item])
@endforeach
