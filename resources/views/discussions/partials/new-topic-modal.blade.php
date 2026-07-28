<div class="modal fade" id="newTopicModal" tabindex="-1" aria-labelledby="newTopicModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <form id="newTopicForm" data-url="{{ route('discussions.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="newTopicModalLabel">Start a new chat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('discussions.partials.new-chat-compose-fields', [
                        'prefix' => 'newTopic',
                        'fieldClass' => 'mb-3',
                    ])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-comments"></i> Create chat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
