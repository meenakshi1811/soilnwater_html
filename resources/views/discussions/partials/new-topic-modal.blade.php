<div class="modal fade" id="newTopicModal" tabindex="-1" aria-labelledby="newTopicModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="newTopicForm" data-url="{{ route('discussions.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="newTopicModalLabel">Start a new topic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="topicTitle" class="form-label">Title</label>
                        <input type="text" name="title" id="topicTitle" class="form-control" maxlength="200" required placeholder="What would you like to discuss?">
                    </div>
                    <div class="mb-3">
                        <label for="topicBody" class="form-label">Details <span class="text-muted">(optional)</span></label>
                        <textarea name="body" id="topicBody" class="form-control" rows="4" maxlength="5000" placeholder="Add more context for your topic..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Post topic</button>
                </div>
            </form>
        </div>
    </div>
</div>
