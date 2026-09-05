(function ($) {
    if (!$ || !window.FormHelper) {
        return;
    }

    window.CreateAccount = {
        createModal: null,
        lockedRole: null,

        isBusinessRole: function (role) {
            return role === 'vendor' || role === 'consultant' || role === 'service_provider';
        },

        isEducatorRole: function (role) {
            return role === 'teacher' || role === 'tutor';
        },

        currentRole: function () {
            return this.lockedRole || $('#createRole').val() || '';
        },

        syncRoleField: function () {
            var role = this.currentRole();
            var $hidden = $('#createRoleHidden');
            var $select = $('#createRole');

            if (this.lockedRole) {
                $select.val(this.lockedRole);
                $hidden.val(this.lockedRole).prop('disabled', false);
                $select.prop('disabled', true).removeAttr('name');
                return;
            }

            $select.prop('disabled', false);
            $hidden.prop('disabled', true).removeAttr('name');
            $select.attr('name', 'role');
        },

        toggleCreateBusinessFields: function () {
            var role = this.currentRole();
            var showBusiness = this.isBusinessRole(role);
            var showProfileImage = role === 'user' || showBusiness || this.isEducatorRole(role);
            var showGst = showBusiness && $('input[name="has_gst"]:checked', '#createUserForm').val() === '1';

            $('#createBusinessFields').toggleClass('d-none', !showBusiness);
            $('#createDateOfBirthWrap').toggleClass('d-none', showBusiness);
            $('#createProfileImageWrap').toggleClass('d-none', !showProfileImage);
            $('#createGstNumberWrap').toggleClass('d-none', !showGst);
            $('#createRoleWrap').toggleClass('d-none', !!this.lockedRole);

            if (!showBusiness) {
                $('#createPanNumber, #createGstNumber, #createCertificateNumber, #createDateOfIncorporation').val('');
                $('#createHasGstNo').prop('checked', true);
            } else {
                $('#createDateOfBirth').val('');
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

            this.lockedRole = null;
            $form[0].reset();
            if ($form.data('validator')) {
                $form.validate().resetForm();
            }
            $form.find('.is-invalid').removeClass('is-invalid');
            $('#createLatitude, #createLongitude').val('');
            $('#createHasGstNo').prop('checked', true);
            $('#createUserAlert').addClass('d-none').empty();
            $('#createRole').prop('disabled', false).attr('name', 'role');
            $('#createRoleHidden').prop('disabled', true).val('');
            $('#createUserModalTitle').text('Add User');
            $('#createUserSubmitBtn .btn-text').text('Create User');
            this.toggleCreateBusinessFields();
        },

        reloadListingTables: function () {
            $('#vendorsTable, #consultantsTable, #service_providersTable, #educatorsTable, #usersTable').each(function () {
                if ($.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable().ajax.reload(null, false);
                }
            });
        },

        openModal: function (options) {
            options = options || {};
            this.resetCreateForm();

            if (options.role) {
                this.lockedRole = options.role;
                $('#createRole').val(options.role);
            }

            if (options.title) {
                $('#createUserModalTitle').text(options.title);
            }

            if (options.submitText) {
                $('#createUserSubmitBtn .btn-text').text(options.submitText);
            }

            this.syncRoleField();
            this.toggleCreateBusinessFields();
            this.createModal.show();
        },

        init: function () {
            var self = this;

            if (!$('#createUserForm').length) {
                return;
            }

            self.createModal = new bootstrap.Modal(document.getElementById('createUserModal'));

            $(document).on('click', '.js-open-create-account', function () {
                self.openModal({
                    role: $(this).data('role') || null,
                    title: $(this).data('modalTitle') || 'Add User',
                    submitText: $(this).data('submitText') || 'Create User'
                });
            });

            $('#openCreateUserModalBtn').on('click', function () {
                self.openModal({ title: 'Add User', submitText: 'Create User' });
            });

            document.getElementById('createUserModal').addEventListener('shown.bs.modal', function () {
                if (window.FormHelper && typeof window.FormHelper.initAdminCreateUserPlaceAutocomplete === 'function') {
                    window.FormHelper.initAdminCreateUserPlaceAutocomplete();
                }
            });

            $('#createAddress').on('input', function () {
                if (this.dataset.placeJustSelected === '1') {
                    delete this.dataset.placeJustSelected;
                    return;
                }

                $('#createLatitude, #createLongitude').val('');
            });

            $('#createRole').on('change', function () {
                self.syncRoleField();
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
                            return self.isBusinessRole(self.currentRole());
                        },
                        maxlength: 20
                    },
                    has_gst: {
                        required: function () {
                            return self.isBusinessRole(self.currentRole());
                        }
                    },
                    gst_number: {
                        required: function () {
                            return self.isBusinessRole(self.currentRole())
                                && $('input[name="has_gst"]:checked', '#createUserForm').val() === '1';
                        },
                        maxlength: 20
                    },
                    government_certificate_number: { maxlength: 100 },
                    date_of_birth: {
                        required: function () {
                            return self.currentRole() === 'user';
                        },
                        date: true
                    },
                    date_of_incorporation: {
                        required: function () {
                            return self.isBusinessRole(self.currentRole());
                        },
                        date: true
                    },
                    password: { required: true, minlength: 8 },
                    password_confirmation: { required: true, equalTo: '#createPassword' }
                },
                messages: {
                    role: { required: 'Please select a role.' },
                    pan_number: { required: 'PAN number is required for vendor, consultant, and service provider accounts.' },
                    has_gst: { required: 'Please select whether the account has a GST number.' },
                    gst_number: { required: 'GST number is required when GST is set to yes.' },
                    date_of_birth: { required: 'Date of birth is required for general user accounts.' },
                    date_of_incorporation: { required: 'Date of incorporation is required for vendor, consultant, and service provider accounts.' },
                    password_confirmation: { equalTo: 'Password confirmation does not match.' }
                },
                beforeSubmit: function () {
                    self.syncRoleField();
                    $('#createUserForm').find('[name="phone_number"], [name="whatsapp_number"], [name="pincode"]').each(function () {
                        $(this).val($.trim($(this).val() || '').replace(/\D+/g, ''));
                    });
                },
                onInvalid: function () {
                    FormHelper.showToast('warning', 'Please fix the highlighted fields and try again.');
                },
                onSuccess: function (response) {
                    FormHelper.showToast('success', response.message || 'User created successfully.');
                    self.reloadListingTables();
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
        }
    };

    $(function () {
        CreateAccount.init();
    });
})(window.jQuery);
