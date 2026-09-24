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
        label.text('Selected: ' + institute.name + (institute.city ? ' · ' + institute.city : ''));
        $('#eduAffiliationInstituteId').val(String(institute.id));
    }

    function renderResults(results) {
        var box = $('#eduAffiliationSearchResults');
        if (!box.length) {
            return;
        }
        if (!results.length) {
            box.addClass('d-none').empty();
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
                '">' +
                '<strong>' +
                $('<div>').text(item.name).html() +
                '</strong>' +
                '<span>' +
                item.type +
                (item.city ? ' · ' + $('<div>').text(item.city).html() : '') +
                '</span></button>'
            );
        }).join('');
        box.html(html).removeClass('d-none');
    }

    $('#eduAffiliationSearch').on('input', function () {
        var query = $(this).val().trim();
        clearTimeout(searchTimer);
        setSelected(null);
        if (query.length < 2) {
            renderResults([]);
            return;
        }
        searchTimer = setTimeout(function () {
            $.getJSON(routes.search, { q: query })
                .done(function (response) {
                    renderResults(response.results || []);
                })
                .fail(function () {
                    renderResults([]);
                });
        }, 250);
    });

    $(document).on('click', '.edu-affiliation-search-results__item', function () {
        var btn = $(this);
        setSelected({
            id: btn.data('id'),
            name: btn.data('name'),
            city: btn.data('city') || '',
        });
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
            renderResults([]);
        }
    });
})(window.jQuery);
