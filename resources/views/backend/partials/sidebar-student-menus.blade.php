<li>
    <a href="{{ route('study-materials.library') }}" target="_blank" rel="noopener">
        <i class="fa-solid fa-book-open"></i>
        <span>Browse Study Materials</span>
    </a>
</li>
@if($user->canPublishStudyMaterials())
<li>
    <a class="{{ request()->routeIs('child.materials.*') ? 'active' : '' }}" href="{{ route('child.materials.index') }}">
        <i class="fa-solid fa-folder-open"></i>
        <span>My Study Materials</span>
    </a>
</li>
<li>
    <a class="{{ request()->routeIs('child.materials.create') ? 'active' : '' }}" href="{{ route('child.materials.create') }}">
        <i class="fa-solid fa-cloud-arrow-up"></i>
        <span>Upload Study Material</span>
    </a>
</li>
@endif
<li>
    <a href="{{ route('educator.index') }}" target="_blank" rel="noopener">
        <i class="fa-solid fa-chalkboard-user"></i>
        <span>Teachers &amp; Guidance</span>
    </a>
</li>
<li>
    <a class="{{ request()->routeIs('community.saved.*') ? 'active' : '' }}" href="{{ route('community.saved.index') }}">
        <i class="fa-solid fa-bookmark"></i>
        <span>Saved &amp; Bookmarks</span>
    </a>
</li>
