(function (window) {
    'use strict';

    var DEFAULT_COUNTRY = 'in';

    function getComponent(components, type) {
        var match = (components || []).find(function (component) {
            return (component.types || []).indexOf(type) !== -1;
        });

        return match ? match.long_name : '';
    }

    function getSelectedAddress(place) {
        if (!place) {
            return '';
        }

        return place.formatted_address || place.name || '';
    }

    function getCity(components) {
        return getComponent(components, 'locality')
            || getComponent(components, 'postal_town')
            || getComponent(components, 'administrative_area_level_3')
            || getComponent(components, 'sublocality_level_1')
            || getComponent(components, 'sublocality')
            || getComponent(components, 'neighborhood')
            || getComponent(components, 'administrative_area_level_2');
    }

    function getState(components) {
        return getComponent(components, 'administrative_area_level_1');
    }

    function getPincode(components) {
        return getComponent(components, 'postal_code');
    }

    function buildFields(options) {
        options = options || {};
        var fields = ['name', 'formatted_address'];

        if (options.addressComponents !== false) {
            fields.push('address_components');
        }

        if (options.geometry) {
            fields.push('geometry');
        }

        if (options.placeId) {
            fields.push('place_id');
        }

        return fields.filter(function (value, index, array) {
            return array.indexOf(value) === index;
        });
    }

    function buildOptions(options) {
        options = options || {};
        var autocompleteOptions = {
            fields: buildFields(options),
        };

        if (options.country !== false) {
            autocompleteOptions.componentRestrictions = {
                country: options.country || DEFAULT_COUNTRY,
            };
        }

        return autocompleteOptions;
    }

    function bindAutocomplete(input, options) {
        options = options || {};

        if (!input || !window.google || !google.maps || !google.maps.places) {
            return null;
        }

        if (input.dataset.googlePlacesReady === 'true') {
            return input._soilnwaterPlacesAutocomplete || null;
        }

        var autocomplete = new google.maps.places.Autocomplete(input, buildOptions(options));

        input.dataset.googlePlacesReady = 'true';
        input._soilnwaterPlacesAutocomplete = autocomplete;

        autocomplete.addListener('place_changed', function () {
            var place = autocomplete.getPlace();

            if (typeof options.onPlaceChanged === 'function') {
                options.onPlaceChanged(place, autocomplete);
            }
        });

        return autocomplete;
    }

    window.SoilnWaterGooglePlaces = {
        getComponent: getComponent,
        getSelectedAddress: getSelectedAddress,
        getCity: getCity,
        getState: getState,
        getPincode: getPincode,
        buildFields: buildFields,
        buildOptions: buildOptions,
        bindAutocomplete: bindAutocomplete,
    };
})(window);
