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

        if (options.types) {
            autocompleteOptions.types = options.types;
        }

        return autocompleteOptions;
    }

    function getPlaceName(place) {
        if (!place) {
            return '';
        }

        return place.name || getSelectedAddress(place);
    }

    function resolveInputTarget(input, datasetKey) {
        var target = input.dataset[datasetKey];

        if (!target) {
            return null;
        }

        if (input.form) {
            var formMatch = input.form.querySelector('#' + target)
                || input.form.querySelector('[name="' + target + '"]');

            if (formMatch) {
                return formMatch;
            }
        }

        return document.getElementById(target)
            || document.querySelector('[name="' + target + '"]');
    }

    function clearCoordinateTargets(input) {
        var latitudeInput = resolveInputTarget(input, 'latitudeTarget');
        var longitudeInput = resolveInputTarget(input, 'longitudeTarget');

        if (latitudeInput) {
            latitudeInput.value = '';
        }

        if (longitudeInput) {
            longitudeInput.value = '';
        }
    }

    function dismissPacDropdown(input) {
        if (input && typeof input.blur === 'function') {
            input.blur();
        }

        window.setTimeout(function () {
            document.querySelectorAll('.pac-container').forEach(function (container) {
                container.style.display = 'none';
            });
        }, 0);
    }

    function ensurePacContainerModalSupport() {
        if (window._soilnwaterPacContainerBound) {
            return;
        }

        window._soilnwaterPacContainerBound = true;

        document.addEventListener('mousedown', function (event) {
            if (event.target.closest('.pac-container')) {
                event.stopPropagation();
            }
        }, true);

        document.addEventListener('touchstart', function (event) {
            if (event.target.closest('.pac-container')) {
                event.stopPropagation();
            }
        }, true);
    }

    function bindSchoolInstituteInput(input) {
        if (!input || input.dataset.googlePlacesReady === 'true') {
            return;
        }

        if (!window.google || !google.maps || !google.maps.places) {
            var attempts = Number(input.dataset.googlePlacesAttempts || 0);

            if (attempts >= 20) {
                return;
            }

            input.dataset.googlePlacesAttempts = String(attempts + 1);
            window.setTimeout(function () {
                bindSchoolInstituteInput(input);
            }, 500);

            return;
        }

        var latitudeInput = resolveInputTarget(input, 'latitudeTarget');
        var longitudeInput = resolveInputTarget(input, 'longitudeTarget');
        var usesCoordinates = Boolean(latitudeInput && longitudeInput);

        ensurePacContainerModalSupport();

        bindAutocomplete(input, {
            types: ['establishment'],
            addressComponents: false,
            geometry: usesCoordinates,
            onPlaceChanged: function (place) {
                var placeName = getPlaceName(place);

                if (placeName) {
                    input.value = placeName;
                }

                if (usesCoordinates && place && place.geometry && place.geometry.location) {
                    latitudeInput.value = String(place.geometry.location.lat());
                    longitudeInput.value = String(place.geometry.location.lng());
                }

                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            },
        });

        if (!input.dataset.schoolSearchInputBound) {
            input.addEventListener('input', function () {
                if (!input.value.trim()) {
                    clearCoordinateTargets(input);
                }
            });

            input.dataset.schoolSearchInputBound = 'true';
        }
    }

    function initSchoolInstituteSearchFields(root) {
        var scope = root && typeof root.querySelectorAll === 'function' ? root : document;

        scope.querySelectorAll('.js-school-institute-search, .js-experience-organization').forEach(function (input) {
            bindSchoolInstituteInput(input);
        });
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

            if (!options.skipDismissPacDropdown) {
                dismissPacDropdown(input);
            }
        });

        return autocomplete;
    }

    window.SoilnWaterGooglePlaces = {
        getComponent: getComponent,
        getSelectedAddress: getSelectedAddress,
        getPlaceName: getPlaceName,
        getCity: getCity,
        getState: getState,
        getPincode: getPincode,
        buildFields: buildFields,
        buildOptions: buildOptions,
        bindAutocomplete: bindAutocomplete,
        initSchoolInstituteSearchFields: initSchoolInstituteSearchFields,
    };
})(window);
