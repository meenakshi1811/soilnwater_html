<li>
    <a href="{{ route('study-materials.library') }}" target="_blank" rel="noopener">
        <i class="fa-solid fa-book-open"></i>
        <span>Study Materials</span>
    </a>
</li>
<li>
    <a href="{{ route('frontend.index') }}" target="_blank" rel="noopener">
        <i class="fa-solid fa-graduation-cap"></i>
        <span>Courses &amp; Learning</span>
    </a>
</li>
<li>
    <a href="{{ route('frontend.index') }}" target="_blank" rel="noopener">
        <i class="fa-solid fa-file-circle-check"></i>
        <span>Tests &amp; Assessments</span>
    </a>
</li>
<li>
    <a href="{{ route('community.posts.create') }}">
        <i class="fa-solid fa-circle-question"></i>
        <span>Questions &amp; Answers</span>
    </a>
</li>
<li>
    <a href="{{ route('educator.index') }}" target="_blank" rel="noopener">
        <i class="fa-solid fa-chalkboard-user"></i>
        <span>Teachers &amp; Guidance</span>
    </a>
</li>
<li>
    <a class="{{ request()->routeIs('child.*') ? 'active' : '' }}" href="{{ route('child.dashboard') }}">
        <i class="fa-solid fa-bullseye"></i>
        <span>Goals &amp; Progress</span>
    </a>
</li>
<li>
    <a class="{{ request()->routeIs('child.*') ? 'active' : '' }}" href="{{ route('child.dashboard') }}">
        <i class="fa-solid fa-medal"></i>
        <span>Achievements</span>
    </a>
</li>
<li>
    <a class="{{ request()->routeIs('community.saved.*') ? 'active' : '' }}" href="{{ route('community.saved.index') }}">
        <i class="fa-solid fa-bookmark"></i>
        <span>Saved &amp; Bookmarks</span>
    </a>
</li>
