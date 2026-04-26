@extends('frontend.layouts.app')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">

                @if (!empty($termsContent))
                    {!! html_entity_decode($termsContent) !!}
                @else
                    <p class="mb-0">Terms and conditions are not available right now.</p>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
