@extends('frontend.layouts.app')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">
                <h1 class="h3 mb-4">{{ $pageTitle }}</h1>

                @if (!empty($termsContent))
                    {!! $termsContent !!}
                @else
                    <p class="mb-0">Terms and conditions are not available right now.</p>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
