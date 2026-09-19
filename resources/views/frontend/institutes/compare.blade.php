@extends('frontend.layouts.app')

@section('meta_title', ($listingContext === 'schools' ? 'Compare schools' : 'Compare institutes').' | SoilnWater')

@section('content')
<div class="container py-5">
    <h1 class="h3 fw-bold mb-2">{{ $listingContext === 'schools' ? 'Compare schools' : 'Compare institutes' }}</h1>
    <p class="text-secondary mb-4">Side-by-side view of profiles you added from the compare button (up to 3).</p>

    @if($items->isEmpty())
        <div class="alert alert-light border">Your compare list is empty. Open a profile and click <strong>Compare</strong>.</div>
        <a href="{{ route($listingContext.'.index') }}" class="btn btn-primary">Browse listings</a>
    @else
        <div class="table-responsive">
            <table class="table table-bordered align-middle bg-white">
                <tbody>
                <tr>
                    <th scope="row">Name</th>
                    @foreach($items as $item)
                        <td>
                            <a href="{{ $item->institute->publicUrl() }}" class="fw-semibold">{{ $item->institute->displayName() }}</a>
                        </td>
                    @endforeach
                </tr>
                <tr>
                    <th scope="row">Location</th>
                    @foreach($items as $item)
                        <td>{{ $item->institute->locationLabel() ?: '—' }}</td>
                    @endforeach
                </tr>
                <tr>
                    <th scope="row">Board / type</th>
                    @foreach($items as $item)
                        <td>{{ $item->institute->board_affiliation ?: $item->institute->institutionTypeLabel() }}</td>
                    @endforeach
                </tr>
                <tr>
                    <th scope="row">Established</th>
                    @foreach($items as $item)
                        <td>{{ $item->institute->establishedYear() ?: '—' }}</td>
                    @endforeach
                </tr>
                <tr>
                    <th scope="row">Contact</th>
                    @foreach($items as $item)
                        <td>
                            @if($item->institute->phone)<div>{{ $item->institute->phone }}</div>@endif
                            @if($item->institute->email)<div>{{ $item->institute->email }}</div>@endif
                        </td>
                    @endforeach
                </tr>
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
