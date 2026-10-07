(function ($) {
    'use strict';

    if (!$) {
        return;
    }

    $(function () {
        var routes = window.eduAffiliationRoutes || {};
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        var searchTimer = null;
        var selectedInstitute = null;
        var $searchInput = $('#eduAffiliationSearch');
        var $resultsBox = $('#eduAffiliationSearchResults');

        if (!$searchInput.length || !$resultsBox.length) {
            return;
        }

        function notify(type, message) {
            if (window.toastr) {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right',
                    timeOut: 3500,
                };
                toastr[type](message);
            }
        }

        function syncProfileLocationFromInstitute(institute) {
            if (!institute) {
                return;
            }
            var name = institute.name || '';
            var city = institute.city || '';
            var state = institute.state || '';
            var $assoc = $('#associated_institute');
            var $city = $('#educator_teaching_city');
            var $state = $('#educator_teaching_state');
            if ($assoc.length && name) {
                $assoc.val(name);
            }
            if ($city.length && city) {
                $city.val(city);
            }
            if ($state.length && state) {
                $state.val(state);
            }
        }

        function setSelected(institute) {
            selectedInstitute = institute;
            var label = $('#eduAffiliationSelectedLabel');
            if (!label.length) {
                return;
            }
            if (!institute) {
                label.text('No institution selected.');
                $('#eduAffiliationInstituteId').val('');
                return;
            }
            var parts = [institute.name];
            if (institute.city) {
                parts.push(institute.city);
            }
            if (institute.state) {
                parts.push(institute.state);
            }
            label.text('Selected: ' + parts.join(' · '));
            $('#eduAffiliationInstituteId').val(String(institute.id));
        }

        function hideResults() {
            $resultsBox.addClass('d-none').empty();
        }

        function showResultsMessage(message, className) {
            $resultsBox
                .removeClass('d-none')
                .html(
                    '<p class="edu-affiliation-search-results__empty mb-0 small px-2 py-2 ' +
                        (className || 'text-muted') +
                        '">' +
                        $('<div>').text(message).html() +
                        '</p>'
                );
        }

        function renderResults(results) {
            if (!results.length) {
                showResultsMessage(
                    'No matching approved schools or institutes. Try another name or city.'
                );
                return;
            }
            var html = results
                .map(function (item) {
                    return (
                        '<button type="button" class="edu-affiliation-search-results__item" data-id="' +
                        item.id +
                        '" data-name="' +
                        $('<div>').text(item.name).html() +
                        '" data-city="' +
                        $('<div>').text(item.city || '').html() +
                        '" data-state="' +
                        $('<div>').text(item.state || '').html() +
                        '">' +
                        '<strong>' +
                        $('<div>').text(item.name).html() +
                        '</strong>' +
                        '<span>' +
                        item.type +
                        (item.city ? ' · ' + $('<div>').text(item.city).html() : '') +
                        (item.state ? ' · ' + $('<div>').text(item.state).html() : '') +
                        '</span></button>'
                    );
                })
                .join('');
            $resultsBox.html(html).removeClass('d-none');
        }

        function runSearch(query) {
            if (!routes.search) {
                showResultsMessage('Institute search is not configured on this page.', 'text-danger');
                return;
            }

            showResultsMessage('Searching…');

            $.ajax({
                url: routes.search,
                method: 'GET',
                data: { q: query },
                dataType: 'json',
                headers: { Accept: 'application/json' },
            })
                .done(function (response) {
                    renderResults(response.results || []);
                })
                .fail(function (xhr) {
                    if (xhr.status === 403 || xhr.status === 401) {
                        showResultsMessage(
                            'Your session may have expired. Refresh the page and try again.',
                            'text-danger'
                        );
                        return;
                    }
                    showResultsMessage(
                        'Unable to load institutes right now. Please try again.',
                        'text-danger'
                    );
                });
        }

        function scheduleSearch() {
            var query = $searchInput.val().trim();
            clearTimeout(searchTimer);
            setSelected(null);
            searchTimer = window.setTimeout(function () {
                runSearch(query);
            }, 220);
        }

        $searchInput.on('input', scheduleSearch);

        $searchInput.on('focus', function () {
            var query = $(this).val().trim();
            if (query.length === 0) {
                runSearch('');
            } else if ($resultsBox.hasClass('d-none')) {
                runSearch(query);
            }
        });

        $(document).on('click', '.edu-affiliation-search-results__item', function () {
            var btn = $(this);
            var institute = {
                id: btn.data('id'),
                name: btn.data('name'),
                city: btn.data('city') || '',
                state: btn.data('state') || '',
            };
            setSelected(institute);
            syncProfileLocationFromInstitute(institute);
            $searchInput.val(btn.data('name'));
            hideResults();
        });

        $('#eduAffiliationAddBtn').on('click', function () {
            var instituteId = $('#eduAffiliationInstituteId').val();
            if (!instituteId) {
                notify('warning', 'Please search and select a school or institute first.');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true);

            $.ajax({
                url: routes.store,
                method: 'POST',
                data: {
                    _token: csrfToken,
                    institute_id: instituteId,
                    role_title: $('#eduAffiliationRole').val(),
                    subject: $('#eduAffiliationSubject').val(),
                },
                headers: { Accept: 'application/json' },
            })
                .done(function (response) {
                    notify('success', response.message || 'Linked successfully.');
                    $('#eduAffiliationEmpty').remove();
                    $('#eduAffiliationList').prepend(response.html || '');
                    if (response.institute) {
                        syncProfileLocationFromInstitute(response.institute);
                    } else if (selectedInstitute) {
                        syncProfileLocationFromInstitute(selectedInstitute);
                    }
                    $searchInput.val('');
                    $('#eduAffiliationRole').val('');
                    $('#eduAffiliationSubject').val('');
                    setSelected(null);
                    hideResults();
                })
                .fail(function (xhr) {
                    var message =
                        xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'Unable to link institution.';
                    notify('error', message);
                })
                .always(function () {
                    $btn.prop('disabled', false);
                });
        });

        $(document).on('click', '.js-edu-affiliation-remove', function () {
            var $btn = $(this);
            var url = $btn.data('url');
            if (!url || !window.confirm('Remove this school / institute link?')) {
                return;
            }

            $.ajax({
                url: url,
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            })
                .done(function (response) {
                    notify('success', response.message || 'Removed.');
                    $btn.closest('.edu-affiliation-item').remove();
                })
                .fail(function () {
                    notify('error', 'Unable to remove association.');
                });
        });

        $(document).on('click', function (event) {
            if (!$(event.target).closest('.edu-affiliation-search-wrap').length) {
                hideResults();
            }
        });
    });
})(window.jQuery);
