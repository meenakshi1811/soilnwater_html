@extends('backend.layouts.app')

@section('title', 'My Community Posts')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<style>
    .community-author-profile-card {
        padding: 1.25rem 1.5rem;
    }

    .community-author-profile-card__grid {
        align-items: center;
        display: grid;
        gap: 1.25rem 1.5rem;
        grid-template-columns: auto minmax(0, 1fr);
    }

    .community-author-profile-card__photo {
        align-items: center;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        text-align: center;
    }

    .community-author-profile-card__preview {
        align-items: center;
        background: linear-gradient(135deg, #1f66b4, #2e7d32);
        border: 3px solid #e8eef5;
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        font-size: 1.35rem;
        font-weight: 700;
        height: 88px;
        justify-content: center;
        overflow: hidden;
        width: 88px;
    }

    .community-author-profile-card__preview img {
        display: block;
        height: 100%;
        object-fit: cover;
        width: 100%;
    }

    .community-author-profile-card__main {
        display: grid;
        gap: 1rem;
    }

    .community-author-profile-card__head h6 {
        color: #12395f;
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .community-author-profile-card__head p {
        color: #64748b;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }

    .community-author-profile-card__url {
        align-items: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.65rem;
        color: #334155;
        display: inline-flex;
        font-size: 0.82rem;
        gap: 0.35rem;
        padding: 0.45rem 0.75rem;
        text-decoration: none;
        word-break: break-all;
    }

    .community-author-profile-card__url:hover {
        background: #f1f5f9;
        color: #12395f;
    }

    .community-author-profile-card__form {
        align-items: end;
        display: grid;
        gap: 0.75rem 1rem;
        grid-template-columns: minmax(0, 1fr) auto;
    }

    .community-author-profile-card__slug-label {
        color: #475569;
        font-size: 0.78rem;
        font-weight: 600;
        margin-bottom: 0.35rem;
    }

    .community-author-profile-card__actions {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    @media (max-width: 767.98px) {
        .community-author-profile-card__grid {
            grid-template-columns: 1fr;
            justify-items: center;
            text-align: center;
        }

        .community-author-profile-card__form {
            grid-template-columns: 1fr;
            width: 100%;
        }

        .community-author-profile-card__actions {
            justify-content: center;
        }
    }
</style>
@endpush

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

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $authorUser = auth()->user();
        $authorUniqueName = old('author_slug', $authorUser->authorUniqueName());
        $authorImageUrl = $authorUser->authorImageUrl();
        $authorInitials = $authorUser->authorInitials();
    @endphp
    <div class="chart-card mb-3 community-author-profile-card">
        <div class="community-author-profile-card__grid">
            <div class="community-author-profile-card__photo">
                <div class="community-author-profile-card__preview" id="authorImagePreview">
                    @if ($authorImageUrl)
                        <img src="{{ $authorImageUrl }}" alt="{{ $authorUser->authorDisplayName() }}">
                    @else
                        <span aria-hidden="true">{{ $authorInitials }}</span>
                    @endif
                </div>
                <label for="authorImageInput" class="btn btn-sm btn-outline-secondary mb-0">
                    <i class="fa-solid fa-camera me-1"></i>Upload photo
                </label>
            </div>

            <div class="community-author-profile-card__main">
                <div class="community-author-profile-card__head">
                    <h6>Author profile</h6>
                    <p class="mb-0">Set your public author photo and profile URL. Your photo appears on community posts instead of the initials circle.</p>
                    <a href="{{ route('community.authors.show', $authorUser->authorUniqueName()) }}" target="_blank" rel="noopener" class="community-author-profile-card__url">
                        <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                        /auther/{{ $authorUser->authorUniqueName() }}
                    </a>
                </div>

                <form method="POST" action="{{ route('community.posts.author-url.update') }}" enctype="multipart/form-data" class="community-author-profile-card__form">
                    @csrf
                    @method('PATCH')
                    <input type="file" name="author_image" id="authorImageInput" class="d-none @error('author_image') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                    @if ($authorImageUrl)
                        <input type="hidden" name="remove_author_image" id="removeAuthorImageInput" value="0">
                    @endif
                    <div>
                        <div class="community-author-profile-card__slug-label" for="authorSlug">Profile URL slug</div>
                        <input type="text" id="authorSlug" name="author_slug" class="form-control @error('author_slug') is-invalid @enderror" value="{{ $authorUniqueName }}" maxlength="80" pattern="[a-z0-9]+(-[a-z0-9]+)*" required>
                        <small class="text-muted">Lowercase letters, numbers, and hyphens only. e.g. john-doe</small>
                        @error('author_slug')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('author_image')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="community-author-profile-card__actions">
                        @if ($authorImageUrl)
                            <button type="button" class="btn btn-outline-danger" id="removeAuthorImageButton">Remove photo</button>
                        @endif
                        <button type="submit" class="btn btn-success">Save profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="chart-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="mb-0">Post listing</h5>
            <a href="{{ route('community.posts.create') }}" class="btn btn-primary ems-btn-primary">
                <i class="fa-solid fa-plus me-2"></i>Create Post
            </a>
        </div>

        <div class="table-responsive">
            <table id="myCommunityPostsTable" class="table table-bordered align-middle w-100"
                data-source-url="{{ route('community.posts.data') }}"
                data-delete-base-url="{{ url('/dashboard/community-posts') }}">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Trust</th>
                    <th>Published</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/community-posts.js') }}?v={{ now()->timestamp }}"></script>
<script>
    (function () {
        const fileInput = document.getElementById('authorImageInput');
        const preview = document.getElementById('authorImagePreview');
        const removeInput = document.getElementById('removeAuthorImageInput');
        const removeButton = document.getElementById('removeAuthorImageButton');
        const initials = @json($authorInitials);

        if (fileInput && preview) {
            fileInput.addEventListener('change', function () {
                const file = fileInput.files?.[0];
                if (!file) {
                    return;
                }

                if (removeInput) {
                    removeInput.value = '0';
                }

                const reader = new FileReader();
                reader.onload = function (event) {
                    preview.innerHTML = '<img src="' + event.target.result + '" alt="Author photo preview">';
                };
                reader.readAsDataURL(file);
            });
        }

        if (removeButton && preview) {
            removeButton.addEventListener('click', function () {
                if (removeInput) {
                    removeInput.value = '1';
                }

                if (fileInput) {
                    fileInput.value = '';
                }

                preview.innerHTML = '<span aria-hidden="true">' + initials + '</span>';
            });
        }
    })();
</script>
@endpush
