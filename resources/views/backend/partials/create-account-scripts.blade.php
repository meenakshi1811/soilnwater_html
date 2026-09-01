<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    if (window.toastr) {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 4500
        };
    }
</script>
<script src="{{ asset('assets/js/admin-create-account.js') }}?v={{ now()->timestamp }}"></script>
<script>
    window.initAdminCreateUserLocationAutocomplete = function () {
        if (window.FormHelper && typeof window.FormHelper.initAdminCreateUserPlaceAutocomplete === 'function') {
            window.FormHelper.initAdminCreateUserPlaceAutocomplete();
        }
    };
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initAdminCreateUserLocationAutocomplete"></script>
