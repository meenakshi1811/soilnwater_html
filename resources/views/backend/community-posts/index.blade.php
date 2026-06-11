@extends('backend.layouts.app')

@section('title', 'My Community Posts')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Community publishing</p>
            <h2 class="admin-title mb-1">My Community Posts</h2>
            <p class="mb-0 text-secondary">Post articles, news, stories, reports, discussions, and other community content.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="mb-0">Post listing</h5>
            <a href="{{ route('community.posts.create') }}" class="btn btn-primary ems-btn-primary">
                <i class="fa-solid fa-plus me-2"></i>Create Post
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Published</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                        <tr>
                            <td>{{ $post->title }}</td>
                            <td>{{ $post->typeLabel() }}</td>
                            <td>{{ $post->category }}</td>
                            <td><span class="badge {{ $post->status === 'published' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($post->status) }}</span></td>
                            <td>{{ $post->published_at?->format('Y-m-d H:i') ?? 'Draft' }}</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('community.posts.show', $post) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye"></i></a>
                                    <a href="{{ route('community.posts.edit', $post) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-pen"></i></a>
                                    <form method="POST" action="{{ route('community.posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No community posts yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $posts->links() }}
    </div>
</div>
@endsection
