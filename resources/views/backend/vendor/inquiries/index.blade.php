@extends('backend.layouts.app')
@section('title','Product Inquiries')
@section('content')
<div class="container-fluid">
    <div class="card"><div class="card-body">
        <h4 class="mb-3">Product Inquiries</h4>
        <div class="table-responsive"><table class="table table-striped"><thead><tr><th>Date</th><th>Product</th><th>Email</th><th>Phone</th><th>Contact Via</th><th>Reason</th></tr></thead><tbody>
            @forelse($inquiries as $i)
                <tr><td>{{ $i->created_at->format('d M Y H:i') }}</td><td>{{ $i->product?->name }}</td><td>{{ $i->email }}</td><td>{{ $i->phone_number }}</td><td>{{ ucfirst($i->preferred_contact) }}</td><td>{{ $i->reason }}</td></tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">No inquiries yet.</td></tr>
            @endforelse
        </tbody></table></div>
        {{ $inquiries->links() }}
    </div></div>
</div>
@endsection
