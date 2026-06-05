@include('frontend.store.partials.ads-rail', [
    'ads' => $ads ?? collect(),
    'railId' => $railId ?? null,
    'sliderOnly' => $sliderOnly ?? false,
])
