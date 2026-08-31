(function ($) {
    if (!$) return;

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content')
            || $('input[name="_token"]').first().val()
            || (window.Laravel && window.Laravel.csrfToken)
            || '';
    }

    var table = null;
    var config = window.communityPostApprovalConfig || {};

    function toast(type, message) {
        if (window.toastr && typeof window.toastr[type] === 'function') {
            window.toastr[type](message);
            return;
        }

        alert(message);
    }

    function refreshTable() {
        if (table && typeof table.ajax !== 'undefined') {
            table.ajax.reload(null, false);
            return true;
        }

        return false;
    }

    function postAction(url, payload, successMessage, redirectUrl) {
        $.ajax({
            url: url,
            method: 'POST',
            data: Object.assign({ _token: csrfToken() }, payload || {}),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken()
            }
        })
            .done(function (response) {
                toast('success', response.message || successMessage);

                if (redirectUrl) {
                    window.location.href = redirectUrl;
                    return;
                }

                if (!refreshTable()) {
                    window.location.reload();
                }
            })
            .fail(function (xhr) {
                toast('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Unable to process request.');
            });
    }

    function actionUrl(slug, action) {
        var baseUrl = $('#communityPostsTable').data('action-base-url')
            || $('#communityPostsAllTable').data('action-base-url')
            || '/admin/community-posts';

        return baseUrl + '/' + slug + '/' + action;
    }

    function deleteBaseUrl() {
        return $('#communityPostsTable').data('delete-base-url')
            || $('#communityPostsAllTable').data('delete-base-url')
            || '/admin/community-posts';
    }

    function confirmDeletePost(slug) {
        var proceed = function () {
            $.ajax({
                url: deleteBaseUrl() + '/' + slug,
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken()
                },
                data: { _token: csrfToken() }
            })
                .done(function (response) {
                    toast('success', response.message || 'Community post deleted successfully.');

                    if (!refreshTable()) {
                        window.location.reload();
                    }
                })
                .fail(function (xhr) {
                    toast('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Unable to delete this post.');
                });
        };

        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                title: 'Delete this post?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    proceed();
                }
            });
            return;
        }

        if (confirm('Delete this community post?')) {
            proceed();
        }
    }

    function confirmApprove(slug) {
        var url = config.approveUrl || actionUrl(slug, 'approve');

        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                title: 'Approve this post?',
                text: 'The post will be published on the community hub.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Approve',
                confirmButtonColor: '#198754'
            }).then(function (result) {
                if (result.isConfirmed) {
                    postAction(url, {}, 'Community post approved.', config.redirectUrl || null);
                }
            });
            return;
        }

        if (confirm('Approve and publish this community post?')) {
            postAction(url, {}, 'Community post approved.', config.redirectUrl || null);
        }
    }

    function confirmReject(slug) {
        var url = config.rejectUrl || actionUrl(slug, 'reject');

        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                title: 'Reject this post?',
                input: 'textarea',
                inputLabel: 'Optional note for the author',
                inputPlaceholder: 'Share a short reason for rejecting...',
                showCancelButton: true,
                confirmButtonText: 'Reject',
                confirmButtonColor: '#dc3545'
            }).then(function (result) {
                if (result.isConfirmed) {
                    postAction(url, { review_note: result.value || '' }, 'Community post rejected.', config.redirectUrl || null);
                }
            });
            return;
        }

        var note = prompt('Optional note for the author:') || '';
        if (note !== null) {
            postAction(url, { review_note: note }, 'Community post rejected.', config.redirectUrl || null);
        }
    }

    function confirmDraft(slug) {
        var url = config.draftUrl || actionUrl(slug, 'draft');

        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                title: 'Move to draft?',
                text: 'The post will be removed from the public community hub.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Move to draft',
                confirmButtonColor: '#6c757d'
            }).then(function (result) {
                if (result.isConfirmed) {
                    postAction(url, {}, 'Community post moved to draft.', config.redirectUrl || null);
                }
            });
            return;
        }

        if (confirm('Move this community post to draft?')) {
            postAction(url, {}, 'Community post moved to draft.', config.redirectUrl || null);
        }
    }

    function confirmArchive(slug) {
        var url = config.archiveUrl || actionUrl(slug, 'archive');

        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                title: 'Archive this post?',
                text: 'Archived posts are hidden from the community hub.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Archive',
                confirmButtonColor: '#212529'
            }).then(function (result) {
                if (result.isConfirmed) {
                    postAction(url, {}, 'Community post archived.', config.redirectUrl || null);
                }
            });
            return;
        }

        if (confirm('Archive this community post?')) {
            postAction(url, {}, 'Community post archived.', config.redirectUrl || null);
        }
    }

    function togglePromotion(button, url, activeClass, outlineClass) {
        var enabled = button.data('enabled') === 1 || button.data('enabled') === '1';
        var nextEnabled = !enabled;

        postAction(url, { enabled: nextEnabled ? 1 : 0 }, 'Promotion flag updated.', null);

        button.data('enabled', nextEnabled ? '1' : '0');
        button.toggleClass(activeClass, nextEnabled);
        button.toggleClass(outlineClass, !nextEnabled);
    }

    $(document).on('click', '.js-approve', function () {
        confirmApprove($(this).data('slug'));
    });

    $(document).on('click', '.js-reject, .js-decline', function () {
        confirmReject($(this).data('slug'));
    });

    $(document).on('click', '.js-draft', function () {
        confirmDraft($(this).data('slug'));
    });

    $(document).on('click', '.js-archive', function () {
        confirmArchive($(this).data('slug'));
    });

    $(document).on('click', '.js-delete-community-post', function () {
        confirmDeletePost($(this).data('slug'));
    });

    $(document).on('click', '.js-feature', function () {
        var slug = $(this).data('slug');
        togglePromotion($(this), config.featureUrl || actionUrl(slug, 'feature'), 'btn-primary', 'btn-outline-primary');
    });

    $(document).on('click', '.js-sponsor', function () {
        var slug = $(this).data('slug');
        togglePromotion($(this), config.sponsorUrl || actionUrl(slug, 'sponsor'), 'btn-info', 'btn-outline-info');
    });

    $(document).on('click', '.js-highlight', function () {
        var slug = $(this).data('slug');
        togglePromotion($(this), config.highlightUrl || actionUrl(slug, 'highlight'), 'btn-warning', 'btn-outline-warning');
    });

    $(document).on('submit', '#communityQualityScoreForm', function (event) {
        event.preventDefault();

        postAction(config.qualityScoreUrl, {
            quality_score: $('#communityQualityScore').val()
        }, 'Quality score updated.', null);

        setTimeout(function () {
            window.location.reload();
        }, 700);
    });

    $(document).on('click', '.js-recalculate-score', function () {
        postAction(config.recalculateScoreUrl, {
            auto_assign_badges: $(this).data('auto-badges') === 0 ? 0 : 1
        }, 'Article score recalculated.', null);

        setTimeout(function () {
            window.location.reload();
        }, 700);
    });

    $(document).on('click', '.js-article-badge', function () {
        var button = $(this);
        var enabled = button.data('enabled') === 1 || button.data('enabled') === '1';
        var nextEnabled = !enabled;

        postAction(config.articleBadgeUrl, {
            badge: button.data('badge'),
            enabled: nextEnabled ? 1 : 0
        }, 'Article badge updated.', null);

        button.data('enabled', nextEnabled ? '1' : '0');
        button.toggleClass('btn-dark', nextEnabled);
        button.toggleClass('btn-outline-dark', !nextEnabled);
    });

    if ($('#communityPostsTable').length) {
        table = $('#communityPostsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: $('#communityPostsTable').data('source-url'),
            order: [[4, 'desc']],
            columns: [
                { data: 'title', name: 'title' },
                { data: 'type_label', name: 'content_type', orderable: false, searchable: false },
                { data: 'category', name: 'category' },
                { data: 'owner_name', name: 'user_id', orderable: false },
                { data: 'submitted_at', name: 'submitted_at' },
                { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
            ]
        });
    }

    if ($('#communityPostsAllTable').length) {
        table = $('#communityPostsAllTable').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            scrollX: true,
            ajax: {
                url: $('#communityPostsAllTable').data('source-url'),
                data: function (payload) {
                    payload.status = $('#statusFilter').val() || '';
                }
            },
            order: [[9, 'desc']],
            columns: [
                { data: 'title', name: 'title' },
                { data: 'type_label', name: 'content_type', orderable: false, searchable: false },
                { data: 'category_display', name: 'category' },
                { data: 'owner_name', name: 'user_id', orderable: false },
                { data: 'owner_role', name: 'user.role', orderable: false, searchable: false },
                { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                { data: 'article_score_display', name: 'article_score' },
                { data: 'trust_score_display', name: 'trust_score_display', orderable: false, searchable: false },
                { data: 'promotion_badges', name: 'is_featured', orderable: false, searchable: false },
                { data: 'published_display', name: 'published_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
            ]
        });

        $('#statusFilter').on('change', function () {
            table.ajax.reload();
        });

        table.on('draw', function () {
            table.columns.adjust();
        });
    }
})(window.jQuery);
