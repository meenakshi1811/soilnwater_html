(function ($) {
    if (!$ || !window.FormHelper) {
        return;
    }

    var UsersAdmin = {
        table: null,
        modal: null,
        viewModal: null,
        createModal: null,

        isBusinessRole: function (role) {
            return role === 'vendor' || role === 'service_provider';
        },

        toggleCreateBusinessFields: function () {
            var role = $('#createRole').val();
            var showBusiness = this.isBusinessRole(role);
            var showProfileImage = role === 'user' || showBusiness;
            var showGst = showBusiness && $('input[name="has_gst"]:checked', '#createUserForm').val() === '1';

            $('#createBusinessFields').toggleClass('d-none', !showBusiness);
            $('#createProfileImageWrap').toggleClass('d-none', !showProfileImage);
            $('#createGstNumberWrap').toggleClass('d-none', !showGst);

            if (!showBusiness) {
                $('#createPanNumber, #createGstNumber, #createCertificateNumber').val('');
                $('#createHasGstNo').prop('checked', true);
            }

            if (!showGst) {
                $('#createGstNumber').val('');
            }
        },

        resetCreateForm: function () {
            var $form = $('#createUserForm');
            if (!$form.length) {
                return;
            }

            $form[0].reset();
            if ($form.data('validator')) {
                $form.validate().resetForm();
            }
            $form.find('.is-invalid').removeClass('is-invalid');
            $('#createHasGstNo').prop('checked', true);
            $('#createUserAlert').addClass('d-none').empty();
            this.toggleCreateBusinessFields();
        },

        initCreateForm: function () {
            var self = this;

            if (!$('#createUserForm').length) {
                return;
            }

            self.createModal = new bootstrap.Modal(document.getElementById('createUserModal'));

            $('#openCreateUserModalBtn').on('click', function () {
                self.resetCreateForm();
                self.createModal.show();
            });

            $('#createRole').on('change', function () {
                self.toggleCreateBusinessFields();
            });

            $('#createUserForm').on('input blur', '[name="phone_number"], [name="whatsapp_number"], [name="pincode"]', function () {
                $(this).val($.trim($(this).val() || '').replace(/\D+/g, ''));
            });

            $('#createUserForm').on('change', 'input[name="has_gst"]', function () {
                self.toggleCreateBusinessFields();
            });

            FormHelper.attachAjaxForm({
                formSelector: '#createUserForm',
                buttonSelector: '#createUserSubmitBtn',
                alertSelector: '#createUserAlert',
                defaultText: 'Create User',
                loadingText: 'Creating...',
                rules: {
                    fullname: { required: true, minlength: 3, maxlength: 255 },
                    email: { required: true, email: true },
                    phone_number: { required: true, digits: true, minlength: 10, maxlength: 15 },
                    whatsapp_number: { required: true, digits: true, minlength: 10, maxlength: 15 },
                    address: { required: true, minlength: 5, maxlength: 500 },
                    city: { required: true, maxlength: 120 },
                    pincode: { required: true, digits: true, minlength: 4, maxlength: 10 },
                    role: { required: true },
                    pan_number: {
                        required: function () {
                            return self.isBusinessRole($('#createRole').val());
                        },
                        maxlength: 20
                    },
                    has_gst: {
                        required: function () {
                            return self.isBusinessRole($('#createRole').val());
                        }
                    },
                    gst_number: {
                        required: function () {
                            return self.isBusinessRole($('#createRole').val())
                                && $('input[name="has_gst"]:checked', '#createUserForm').val() === '1';
                        },
                        maxlength: 20
                    },
                    government_certificate_number: { maxlength: 100 },
                    date_of_birth: { required: true, date: true },
                    password: { required: true, minlength: 8 },
                    password_confirmation: { required: true, equalTo: '#createPassword' }
                },
                messages: {
                    role: { required: 'Please select a role.' },
                    pan_number: { required: 'PAN number is required for vendor and service provider accounts.' },
                    has_gst: { required: 'Please select whether the account has a GST number.' },
                    gst_number: { required: 'GST number is required when GST is set to yes.' },
                    password_confirmation: { equalTo: 'Password confirmation does not match.' }
                },
                beforeSubmit: function () {
                    $('#createUserForm').find('[name="phone_number"], [name="whatsapp_number"], [name="pincode"]').each(function () {
                        $(this).val($.trim($(this).val() || '').replace(/\D+/g, ''));
                    });
                },
                onInvalid: function () {
                    FormHelper.showToast('warning', 'Please fix the highlighted fields and try again.');
                },
                onSuccess: function (response) {
                    FormHelper.showToast('success', response.message || 'User created successfully.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                    self.createModal.hide();
                },
                onValidationError: function (xhr, message) {
                    FormHelper.showToast('warning', message || 'Please fix the highlighted fields and try again.');
                },
                onError: function (xhr, message) {
                    if (xhr.status === 422) {
                        FormHelper.showToast('warning', message || 'Please fix the highlighted fields and try again.');
                        return;
                    }

                    FormHelper.showToast('danger', message || 'Unable to create user. Please try again.');
                }
            });

            self.toggleCreateBusinessFields();
        },

        initTable: function () {
            this.table = $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/admin/users/data'
                },
                columns: [
                    { data: 'name_display', name: 'name' },
                    { data: 'role_badge', name: 'role' },
                    { data: 'email_display', name: 'email' },
                    { data: 'phone_display', name: 'phone_number' },
                    { data: 'location', name: 'city', orderable: false },
                    { data: 'date_of_birth', name: 'date_of_birth' },
                    { data: 'status_badge', name: 'status_badge', orderable: false, searchable: true },
                    { data: 'status_toggle', name: 'status_toggle', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[8, 'desc']]
            });
        },

        escapeHtml: function (value) {
            if (value === null || value === undefined || value === '') {
                return '—';
            }

            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },

        stripHtml: function (value) {
            if (!value) {
                return '';
            }

            var div = document.createElement('div');
            div.innerHTML = String(value);
            return div.textContent || div.innerText || '';
        },

        field: function (label, value) {
            return '<div class="detail-field">'
                + '<div class="detail-field-label">' + this.escapeHtml(label) + '</div>'
                + '<div class="detail-field-value">' + this.escapeHtml(value) + '</div>'
                + '</div>';
        },

        section: function (title, icon, content) {
            if (!content) {
                return '';
            }

            return '<div class="detail-section-card mb-3">'
                + '<div class="detail-section-title"><i class="fa-solid ' + icon + '"></i><span>' + this.escapeHtml(title) + '</span></div>'
                + content
                + '</div>';
        },

        fieldGrid: function (fields) {
            var self = this;
            return '<div class="detail-grid">' + fields.map(function (item) {
                return self.field(item[0], item[1]);
            }).join('') + '</div>';
        },

        imageGrid: function (images) {
            var self = this;
            images = images || [];
            if (!images.length) {
                return '<p class="text-secondary mb-0">No images uploaded.</p>';
            }

            return '<div class="detail-image-grid">' + images.map(function (image) {
                var url = typeof image === 'string' ? image : image.url;
                return '<a class="detail-image-tile" href="' + self.escapeHtml(url) + '" target="_blank" rel="noopener">'
                    + '<img src="' + self.escapeHtml(url) + '" alt="Uploaded image">'
                    + '</a>';
            }).join('') + '</div>';
        },

        compactImages: function (images) {
            var self = this;
            images = images || [];
            if (!images.length) {
                return '';
            }

            return '<div class="d-flex flex-wrap gap-2 mt-2">' + images.slice(0, 4).map(function (image) {
                var url = typeof image === 'string' ? image : image.url;
                return '<a href="' + self.escapeHtml(url) + '" target="_blank" rel="noopener">'
                    + '<img src="' + self.escapeHtml(url) + '" alt="Item image" class="role-detail-thumb">'
                    + '</a>';
            }).join('') + '</div>';
        },

        renderHero: function (user) {
            var photo = user.profile_image_url
                ? '<img class="user-detail-photo" src="' + this.escapeHtml(user.profile_image_url) + '" alt="' + this.escapeHtml(user.name) + '">'
                : '<span class="user-detail-photo user-detail-photo-placeholder">' + this.escapeHtml((user.name || 'U').charAt(0).toUpperCase()) + '</span>';

            return '<div class="user-detail-hero mb-3">'
                + '<div class="d-flex flex-wrap align-items-center gap-3 position-relative">'
                + photo
                + '<div class="flex-grow-1">'
                + '<h3 class="mb-1">' + this.escapeHtml(user.name) + '</h3>'
                + '<p class="mb-3 opacity-75">' + this.escapeHtml(user.email) + '</p>'
                + '<div class="d-flex flex-wrap gap-2">'
                + '<span class="detail-chip"><i class="fa-solid fa-user-tag"></i>' + this.escapeHtml(user.role_label) + '</span>'
                + '<span class="detail-chip"><i class="fa-solid ' + (user.is_blocked ? 'fa-ban' : 'fa-circle-check') + '"></i>' + (user.is_blocked ? 'Blocked' : (user.is_active ? 'Active' : 'Inactive')) + '</span>'
                + '<span class="detail-chip"><i class="fa-solid fa-calendar-plus"></i>Joined ' + this.escapeHtml(user.created_at) + '</span>'
                + '</div>'
                + '</div>'
                + '</div>'
                + '</div>';
        },

        renderRoleProfile: function (details) {
            if (!details) {
                return this.section('Role-specific details', 'fa-layer-group', '<p class="text-secondary mb-0">No vendor, consultant or service provider profile is linked to this account.</p>');
            }

            var profile = details.profile || {};
            var content = '';
            var logo = profile.logo_url
                ? '<div class="mb-3"><img src="' + this.escapeHtml(profile.logo_url) + '" alt="Logo" class="user-detail-photo"></div>'
                : '';

            content += logo + this.fieldGrid([
                ['Company name', profile.company_name],
                ['Display name', profile.display_name],
                ['Contact person', profile.contact_person],
                ['Slug', profile.slug],
                ['Phone', profile.phone],
                ['WhatsApp', profile.whatsapp],
                ['Email', profile.email],
                ['City', profile.city],
                ['State', profile.state],
                ['Pincode', profile.pincode],
                ['PAN number', profile.pan_number],
                ['GST number', profile.gst_number],
                ['Government certificate no.', profile.government_certificate_number],
                ['Premium', profile.is_premium ? 'Yes' : 'No'],
                ['Approval status', profile.status],
                ['Public page status', profile.public_page_status],
                ['Approved at', profile.approved_at],
                ['Created at', profile.created_at],
                ['Updated at', profile.updated_at]
            ]);

            content += '<div class="detail-field mt-3"><div class="detail-field-label">Address</div><div class="detail-field-value">' + this.escapeHtml(profile.address) + '</div></div>';
            content += '<div class="detail-field mt-3"><div class="detail-field-label">Description</div><div class="detail-field-value">' + this.escapeHtml(this.stripHtml(profile.description)) + '</div></div>';

            return this.section(details.label + ' table details', 'fa-briefcase', content)
                + this.section('Gallery images', 'fa-images', this.imageGrid(details.gallery));
        },

        renderBranches: function (branches) {
            var self = this;
            branches = branches || [];
            if (!branches.length) {
                return this.section('Branches', 'fa-code-branch', '<p class="text-secondary mb-0">No branch records found.</p>');
            }

            var content = branches.map(function (branch) {
                var logo = branch.logo_url ? '<img src="' + self.escapeHtml(branch.logo_url) + '" alt="Branch logo" class="role-detail-thumb me-3">' : '';
                return '<div class="role-detail-item mb-2">'
                    + '<div class="d-flex gap-3 align-items-start flex-wrap">' + logo
                    + '<div class="flex-grow-1">'
                    + '<div class="d-flex justify-content-between flex-wrap gap-2 mb-2"><h6 class="mb-0">' + self.escapeHtml(branch.branch_name) + '</h6>'
                    + (branch.is_primary ? '<span class="badge text-bg-success">Primary</span>' : '') + '</div>'
                    + self.fieldGrid([
                        ['Contact person', branch.contact_person],
                        ['Occupation', branch.occupation],
                        ['Experience', branch.professional_experience],
                        ['Services offered', branch.services_offered],
                        ['Phone', branch.phone],
                        ['Alternate mobile', branch.alt_mobile_number],
                        ['WhatsApp', branch.whatsapp],
                        ['Email', branch.email],
                        ['City', branch.city],
                        ['State', branch.state],
                        ['Pincode', branch.pincode],
                        ['PAN number', branch.pan_number],
                        ['GST number', branch.gst_number]
                    ])
                    + '<div class="detail-field mt-2"><div class="detail-field-label">Address</div><div class="detail-field-value">' + self.escapeHtml(branch.address) + '</div></div>'
                    + '</div></div></div>';
            }).join('');

            return this.section('Branches', 'fa-code-branch', content);
        },

        renderSlides: function (slides) {
            slides = slides || [];
            return this.section('Banner images', 'fa-panorama', this.imageGrid(slides.map(function (slide) {
                return slide.image_url;
            })));
        },

        renderPageSections: function (sections) {
            var self = this;
            sections = sections || [];
            if (!sections.length) {
                return this.section('Public page sections', 'fa-table-list', '<p class="text-secondary mb-0">No public page sections found.</p>');
            }

            var content = sections.map(function (section) {
                var image = section.image_url ? '<a href="' + self.escapeHtml(section.image_url) + '" target="_blank" rel="noopener"><img src="' + self.escapeHtml(section.image_url) + '" alt="Section image" class="role-detail-thumb me-3"></a>' : '';
                return '<div class="role-detail-item mb-2">'
                    + '<div class="d-flex align-items-start gap-3 flex-wrap">' + image
                    + '<div class="flex-grow-1"><h6 class="mb-1">' + self.escapeHtml(section.title) + '</h6>'
                    + '<p class="text-secondary mb-0">' + self.escapeHtml(self.stripHtml(section.content)) + '</p></div>'
                    + '</div></div>';
            }).join('');

            return this.section('Public page sections', 'fa-table-list', content);
        },

        renderItems: function (details) {
            var self = this;
            if (!details) {
                return '';
            }
            var isProducts = Array.isArray(details.products);
            var items = isProducts ? details.products : (details.services || []);
            var title = isProducts ? 'Vendor products' : 'Services';

            if (!items.length) {
                return this.section(title, isProducts ? 'fa-boxes-stacked' : 'fa-screwdriver-wrench', '<p class="text-secondary mb-0">No records found.</p>');
            }

            var content = items.map(function (item) {
                var mainImage = item.image_url ? [{ url: item.image_url }] : (item.images || []);
                return '<div class="role-detail-item mb-2">'
                    + '<div class="d-flex justify-content-between flex-wrap gap-2 mb-2">'
                    + '<h6 class="mb-0">' + self.escapeHtml(item.name) + '</h6>'
                    + '<span class="badge text-bg-secondary">' + self.escapeHtml(item.status) + '</span>'
                    + '</div>'
                    + self.fieldGrid([
                        ['Brand', item.brand],
                        ['SKU', item.sku],
                        ['Category', item.category],
                        ['Subcategory', item.subcategory],
                        ['Child category', item.child_category],
                        ['Base price', item.base_price],
                        ['Discount %', item.discount_percent],
                        ['Final price', item.final_price || item.price],
                        ['Stock', item.stock_quantity],
                        ['Charges', item.charges],
                        ['Duration', item.duration],
                        ['Consultation type', item.consultation_type],
                        ['Business type', item.business_type],
                        ['Service area', item.service_area],
                        ['Location', item.location],
                        ['City', item.city],
                        ['Postal code', item.postal_code],
                        ['Service radius', item.service_radius],
                        ['Working hours', item.working_hours],
                        ['Online', item.is_online === true ? 'Yes' : (item.is_online === false ? 'No' : null)],
                        ['Updated at', item.updated_at]
                    ])
                    + '<div class="detail-field mt-2"><div class="detail-field-label">Short description / Description</div><div class="detail-field-value">' + self.escapeHtml(item.short_description || self.stripHtml(item.description)) + '</div></div>'
                    + self.compactImages(mainImage)
                    + (item.video_file_url ? '<div class="mt-2"><a href="' + self.escapeHtml(item.video_file_url) + '" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">View video file</a></div>' : '')
                    + (item.youtube_link ? '<div class="mt-2"><a href="' + self.escapeHtml(item.youtube_link) + '" target="_blank" rel="noopener" class="btn btn-sm btn-outline-danger">YouTube link</a></div>' : '')
                    + '</div>';
            }).join('');

            return this.section(title, isProducts ? 'fa-boxes-stacked' : 'fa-screwdriver-wrench', content);
        },

        renderUserDetails: function (response) {
            var user = response.user || {};
            var details = response.role_details || null;

            var html = this.renderHero(user)
                + this.section('Users table details', 'fa-id-card', this.fieldGrid([
                    ['User ID', user.id],
                    ['Name', user.name],
                    ['Email', user.email],
                    ['Email verified at', user.email_verified_at],
                    ['Phone number', user.phone_number],
                    ['Phone verified at', user.phone_verified_at],
                    ['WhatsApp number', user.whatsapp_number],
                    ['Role', user.role_label],
                    ['Date of birth', user.date_of_birth],
                    ['City', user.city],
                    ['Pincode', user.pincode],
                    ['Status', user.is_blocked ? 'Blocked' : (user.is_active ? 'Active' : 'Inactive')],
                    ['Created at', user.created_at],
                    ['Updated at', user.updated_at]
                ]) + '<div class="detail-field mt-3"><div class="detail-field-label">Address</div><div class="detail-field-value">' + this.escapeHtml(user.address) + '</div></div>')
                + this.renderRoleProfile(details);

            if (details) {
                html += this.renderBranches(details.branches)
                    + this.renderSlides(details.banner_slides)
                    + this.renderPageSections(details.page_sections)
                    + this.renderItems(details);
            }

            return html;
        },

        bindUi: function () {
            var self = this;
            self.modal = new bootstrap.Modal(document.getElementById('userModal'));
            self.viewModal = new bootstrap.Modal(document.getElementById('userViewModal'));

            $(document).on('click', '.js-view-user', function () {
                var id = $(this).data('id');
                $('#userViewContent').html('<div class="text-center py-5 text-secondary">Loading details...</div>');
                self.viewModal.show();

                $.get('/admin/users/' + id).done(function (response) {
                    $('#userViewContent').html(self.renderUserDetails(response));
                }).fail(function () {
                    $('#userViewContent').html('<div class="alert alert-danger mb-0">Unable to load user details.</div>');
                });
            });

            $(document).on('click', '.js-edit-user', function () {
                var id = $(this).data('id');
                $('#userForm')[0].reset();
                $('#userId').val(id);

                $.get('/admin/users/' + id).done(function (response) {
                    var user = response.user || {};
                    $('#userName').val(user.name || '');
                    $('#userEmail').val(user.email || '');
                    $('#userPhone').val(user.phone_number || '');
                    $('#userWhatsapp').val(user.whatsapp_number || '');
                    $('#userAddress').val(user.address || '');
                    $('#userCity').val(user.city || '');
                    $('#userPincode').val(user.pincode || '');
                    $('#userDateOfBirth').val(user.date_of_birth || '');
                    $('#userStatus').prop('checked', !!user.is_active);
                    $('#userForm').attr('action', '/admin/users/' + id).attr('method', 'POST');
                    self.modal.show();
                }).fail(function () {
                    FormHelper.showToast('danger', 'Unable to load user details.');
                });
            });

            $(document).on('change', '.js-toggle-block', function () {
                var $toggle = $(this);
                var id = $toggle.data('id');
                var willBlock = $toggle.is(':checked');

                $toggle.prop('disabled', true);

                $.ajax({
                    url: '/admin/users/' + id + '/toggle-block',
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    }
                }).done(function (response) {
                    FormHelper.showToast(response.is_blocked ? 'warning' : 'success', response.message || 'Updated.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                }).fail(function (xhr) {
                    $toggle.prop('checked', !willBlock).prop('disabled', false);
                    var message = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Unable to update block status.';
                    FormHelper.showToast('danger', message);
                });
            });

            $(document).on('change', '.js-toggle-status', function () {
                var $toggle = $(this);
                var id = $toggle.data('id');
                var willActivate = $toggle.is(':checked');

                $toggle.prop('disabled', true);

                $.ajax({
                    url: '/admin/users/' + id + '/toggle-status',
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    }
                }).done(function (response) {
                    FormHelper.showToast(response.is_active ? 'success' : 'warning', response.message || 'Updated.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                }).fail(function (xhr) {
                    $toggle.prop('checked', !willActivate).prop('disabled', false);
                    var message = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Unable to update status.';
                    FormHelper.showToast('danger', message);
                });
            });

            $(document).on('click', '.js-delete-user', function () {
                var id = $(this).data('id');
                if (!confirm('Delete this user? Related vendor, consultant or service provider records may also be removed.')) {
                    return;
                }

                $.ajax({
                    url: '/admin/users/' + id,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    }
                }).done(function (response) {
                    FormHelper.showToast('success', response.message || 'Deleted.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                }).fail(function (xhr) {
                    var message = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Unable to delete user.';
                    FormHelper.showToast('danger', message);
                });
            });
        },

        initForm: function () {
            var self = this;

            FormHelper.attachAjaxForm({
                formSelector: '#userForm',
                buttonSelector: '#userSubmitBtn',
                alertSelector: '#userAlert',
                defaultText: 'Save User',
                loadingText: 'Saving...',
                rules: {
                    name: { required: true, minlength: 3 },
                    email: { required: true, email: true },
                    phone_number: { required: true, digits: true, minlength: 10, maxlength: 15 },
                    whatsapp_number: { required: true, digits: true, minlength: 10, maxlength: 15 },
                    address: { required: true, maxlength: 500 },
                    city: { required: true, maxlength: 120 },
                    pincode: { required: true, digits: true, minlength: 4, maxlength: 10 },
                    date_of_birth: { required: false, date: true }
                },
                beforeSubmit: function () {
                    $('#userForm').find('input[name="_method"]').remove();
                    $('<input type="hidden" name="_method" value="PUT">').appendTo('#userForm');

                    $('#userForm').find('input[name="is_active"]').remove();
                    $('<input type="hidden" name="is_active">')
                        .val($('#userStatus').is(':checked') ? '1' : '0')
                        .appendTo('#userForm');
                },
                onSuccess: function (response) {
                    FormHelper.showToast('success', response.message || 'Saved.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                    self.modal.hide();
                },
                onError: function (xhr, message) {
                    if (xhr.status === 422) {
                        FormHelper.showToast('warning', 'Please fix the highlighted fields and try again.');
                        return;
                    }

                    FormHelper.showToast('danger', message);
                }
            });
        },

        init: function () {
            if (!$('#usersTable').length) {
                return;
            }

            this.initTable();
            this.bindUi();
            this.initForm();
            this.initCreateForm();
        }
    };

    $(function () {
        UsersAdmin.init();
    });
})(window.jQuery);
