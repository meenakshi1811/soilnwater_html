(function ($) {
    'use strict';

    if (!$) {
        return;
    }

    var routes = window.eduAffiliationRoutes || {};
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var searchTimer = null;
    var selectedInstitute = null;

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

    function renderResults(results) {
        var box = $('#eduAffiliationSearchResults');
        if (!box.length) {
            return;
        }
        if (!results.length) {
            box.removeClass('d-none').html(
                '<p class="edu-affiliation-search-results__empty mb-0 small text-muted px-2 py-1">No matching approved schools or institutes. Try another name or city.</p>'
            );
            return;
        }
        var html = results.map(function (item) {
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
        }).join('');
        box.html(html).removeClass('d-none');
    }

    function runSearch(query) {
        $.getJSON(routes.search, { q: query })
            .done(function (response) {
                renderResults(response.results || []);
            })
            .fail(function () {
                renderResults([]);
            });
    }

    function scheduleSearch() {
        var query = $('#eduAffiliationSearch').val().trim();
        clearTimeout(searchTimer);
        setSelected(null);
        searchTimer = setTimeout(function () {
            runSearch(query);
        }, 220);
    }

    $('#eduAffiliationSearch').on('input', scheduleSearch);

    $('#eduAffiliationSearch').on('focus', function () {
        var query = $(this).val().trim();
        if (query.length === 0) {
            runSearch('');
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
        $('#eduAffiliationSearch').val(btn.data('name'));
        renderResults([]);
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
                $('#eduAffiliationSearch').val('');
                $('#eduAffiliationRole').val('');
                $('#eduAffiliationSubject').val('');
                setSelected(null);
            })
            .fail(function (xhr) {
                var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to link institution.';
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
        if (!$(event.target).closest('#eduAffiliationSearch, #eduAffiliationSearchResults').length) {
            $('#eduAffiliationSearchResults').addClass('d-none').empty();
        }
    });
})(window.jQuery);
