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

    var activePacInput = null;
    var pacPositionWatchTimer = null;
    var pacDomObserver = null;
    var pacLayoutMutating = false;
    var pacObserverDebounceTimer = null;

    function markGooglePlacesActive() {
        if (document.body) {
            document.body.classList.add('soilnwater-google-places-active');
        }
    }

    function countPacItems(pac) {
        return pac ? pac.querySelectorAll('.pac-item').length : 0;
    }

    function hidePacContainer(pac) {
        if (!pac) {
            return;
        }

        pac.classList.remove('soilnwater-pac-fixed');
        pac.dataset.soilnwaterPacDismissed = 'true';
        pac.style.display = 'none';
        pac.style.visibility = 'hidden';
        pac.style.opacity = '0';
        pac.style.pointerEvents = 'none';
    }

    function hideOrphanPacContainers() {
        document.querySelectorAll('.pac-container').forEach(function (pac) {
            if (countPacItems(pac) > 0) {
                return;
            }

            hidePacContainer(pac);

            if (pac.parentNode) {
                pac.parentNode.removeChild(pac);
            }
        });
    }

    function dedupePacContainers() {
        var pacs = Array.prototype.slice.call(document.querySelectorAll('.pac-container'));

        if (pacs.length <= 1) {
            return pacs[0] || null;
        }

        pacs.sort(function (a, b) {
            return countPacItems(b) - countPacItems(a);
        });

        var primary = pacs[0];

        for (var i = 1; i < pacs.length; i++) {
            hidePacContainer(pacs[i]);

            if (pacs[i].parentNode) {
                pacs[i].parentNode.removeChild(pacs[i]);
            }
        }

        return primary;
    }

    function shouldMovePacToBody(pac) {
        var node = pac ? pac.parentElement : null;

        while (node && node !== document.body) {
            var style = window.getComputedStyle(node);

            if (style.transform !== 'none' || style.filter !== 'none' || style.perspective !== 'none') {
                return true;
            }

            if (/(auto|scroll|hidden)/.test(style.overflow + style.overflowY + style.overflowX)) {
                return true;
            }

            node = node.parentElement;
        }

        return false;
    }

    function ensurePacDomObserver() {
        if (pacDomObserver || !window.MutationObserver || !document.body) {
            return;
        }

        pacDomObserver = new MutationObserver(function () {
            if (pacLayoutMutating) {
                return;
            }

            if (pacObserverDebounceTimer) {
                window.clearTimeout(pacObserverDebounceTimer);
            }

            pacObserverDebounceTimer = window.setTimeout(function () {
                pacObserverDebounceTimer = null;
                hideOrphanPacContainers();

                if (activePacInput) {
                    dedupePacContainers();
                    positionActivePacContainer(activePacInput);
                }
            }, 20);
        });

        pacDomObserver.observe(document.body, {
            childList: true,
            subtree: true,
        });
    }

    function isInputVisibleForPlaces(input) {
        if (!input || !input.isConnected) {
            return false;
        }

        var modal = input.closest('.modal');
        if (modal && !modal.classList.contains('show')) {
            return false;
        }

        var rect = input.getBoundingClientRect();

        return rect.width > 0 && rect.height > 0;
    }

    function hidePacContainers() {
        document.querySelectorAll('.pac-container').forEach(function (container) {
            hidePacContainer(container);
        });
    }

    function stopPacPositionWatch() {
        if (pacPositionWatchTimer) {
            window.clearInterval(pacPositionWatchTimer);
            pacPositionWatchTimer = null;
        }
    }

    function dismissPacDropdown(input) {
        var target = input || activePacInput;

        stopPacPositionWatch();

        if (target && typeof target.blur === 'function') {
            target.blur();
        }

        if (activePacInput === target) {
            activePacInput = null;
        }

        hidePacContainers();

        [50, 150, 350].forEach(function (delay) {
            window.setTimeout(hidePacContainers, delay);
        });
    }

    function isPacDropdownOpen(pac) {
        if (!pac || !pac.isConnected) {
            return false;
        }

        if (pac.dataset.soilnwaterPacDismissed === 'true') {
            return false;
        }

        var style = window.getComputedStyle(pac);

        if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') {
            return false;
        }

        return pac.querySelector('.pac-item') !== null;
    }

    function positionActivePacContainer(input) {
        if (!input || activePacInput !== input) {
            return;
        }

        hideOrphanPacContainers();
        dedupePacContainers();

        window.requestAnimationFrame(function () {
            if (activePacInput !== input || pacLayoutMutating) {
                return;
            }

            pacLayoutMutating = true;

            hideOrphanPacContainers();

            var rect = input.getBoundingClientRect();

            if (rect.width <= 0 || rect.height <= 0) {
                pacLayoutMutating = false;
                return;
            }

            var anchored = dedupePacContainers();

            document.querySelectorAll('.pac-container').forEach(function (pac) {
                if (!isPacDropdownOpen(pac)) {
                    return;
                }

                if (anchored && pac !== anchored) {
                    hidePacContainer(pac);
                    return;
                }

                if (shouldMovePacToBody(pac) && document.body && pac.parentNode !== document.body) {
                    document.body.appendChild(pac);
                }

                pac.classList.add('soilnwater-pac-fixed');
                pac.style.position = 'fixed';
                pac.style.top = Math.round(rect.bottom + 4) + 'px';
                pac.style.left = Math.round(rect.left) + 'px';
                pac.style.width = Math.max(Math.round(rect.width), 240) + 'px';
                pac.style.right = 'auto';
                pac.style.bottom = 'auto';
                pac.style.zIndex = '20000';
                pac.style.visibility = 'visible';
                pac.style.opacity = '1';
                pac.style.pointerEvents = 'auto';
                pac.style.display = 'block';
                delete pac.dataset.soilnwaterPacDismissed;
            });

            hideOrphanPacContainers();
            pacLayoutMutating = false;
        });
    }

    function startPacPositionWatch(input) {
        stopPacPositionWatch();

        if (!input) {
            return;
        }

        var ticks = 0;
        pacPositionWatchTimer = window.setInterval(function () {
            if (activePacInput !== input) {
                stopPacPositionWatch();
                return;
            }

            positionActivePacContainer(input);
            ticks += 1;

            if (ticks >= 48) {
                stopPacPositionWatch();
            }
        }, 50);
    }

    function schedulePacReposition(input) {
        positionActivePacContainer(input);
        [0, 50, 120, 250, 400, 650, 900].forEach(function (delay) {
            window.setTimeout(function () {
                positionActivePacContainer(input);
            }, delay);
        });
        startPacPositionWatch(input);
    }

    function bindPacViewportReposition(input) {
        if (!input || input.dataset.pacViewportScrollBound === 'true') {
            return;
        }

        input.dataset.pacViewportScrollBound = 'true';

        var handler = function () {
            if (activePacInput === input) {
                positionActivePacContainer(input);
            }
        };

        window.addEventListener('scroll', handler, true);
        window.addEventListener('resize', handler, { passive: true });
    }

    function watchPacPositionWhileActive(input) {
        if (!input) {
            return;
        }

        var modalBody = input.closest('.modal-body');
        if (!modalBody || modalBody.dataset.pacScrollBound === 'true') {
            return;
        }

        modalBody.dataset.pacScrollBound = 'true';
        modalBody.addEventListener('scroll', function () {
            if (activePacInput === input) {
                positionActivePacContainer(input);
            }
        }, { passive: true });
    }

    function trackPacInput(input) {
        if (!input || input.dataset.pacInputTrackingBound === 'true') {
            return;
        }

        input.dataset.pacInputTrackingBound = 'true';

        input.addEventListener('focus', function () {
            if (activePacInput && activePacInput !== input) {
                hidePacContainers();
            }

            activePacInput = input;

            document.querySelectorAll('.pac-container').forEach(function (pac) {
                delete pac.dataset.soilnwaterPacDismissed;
            });

            schedulePacReposition(input);
            bindPacViewportReposition(input);
            watchPacPositionWhileActive(input);
        });

        input.addEventListener('input', function () {
            activePacInput = input;

            document.querySelectorAll('.pac-container').forEach(function (pac) {
                delete pac.dataset.soilnwaterPacDismissed;
            });

            schedulePacReposition(input);
        });

        input.addEventListener('blur', function () {
            window.setTimeout(function () {
                var active = document.activeElement;

                if (active && active.closest && active.closest('.pac-container')) {
                    return;
                }

                dismissPacDropdown(input);
            }, 200);
        });
    }

    function ensurePacContainerModalSupport() {
        if (window._soilnwaterPacContainerBound) {
            return;
        }

        window._soilnwaterPacContainerBound = true;

        markGooglePlacesActive();
        ensurePacDomObserver();

        document.addEventListener('mousedown', function (event) {
            if (event.target.closest('.pac-item')) {
                event.preventDefault();
            }

            if (event.target.closest('.pac-container')) {
                event.stopPropagation();
            }
        }, true);

        document.addEventListener('touchstart', function (event) {
            if (event.target.closest('.pac-container')) {
                event.stopPropagation();
            }
        }, true);

        document.addEventListener('click', function (event) {
            if (!event.target.closest('.pac-item')) {
                return;
            }

            var input = activePacInput;
            window.setTimeout(function () {
                dismissPacDropdown(input);
            }, 50);
            window.setTimeout(function () {
                dismissPacDropdown(input);
            }, 250);
        }, true);
    }

    function unbindAutocomplete(input) {
        if (!input) {
            return;
        }

        var autocomplete = input._soilnwaterPlacesAutocomplete;

        if (autocomplete && window.google && google.maps && google.maps.event) {
            google.maps.event.clearInstanceListeners(autocomplete);
        }

        delete input._soilnwaterPlacesAutocomplete;
        input.dataset.googlePlacesReady = 'false';
        input.dataset.googlePlacesPending = 'false';
        input.dataset.googlePlacesAttempts = '0';
    }

    function bindSchoolInstituteInput(input) {
        if (!input) {
            return;
        }

        if (!isInputVisibleForPlaces(input)) {
            input.dataset.googlePlacesPending = 'true';
            return;
        }

        if (input.dataset.googlePlacesReady === 'true') {
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
        trackPacInput(input);

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
            if (input.dataset.googlePlacesPending === 'true' && isInputVisibleForPlaces(input)) {
                unbindAutocomplete(input);
            }

            bindSchoolInstituteInput(input);
        });
    }

    function bindAutocomplete(input, options) {
        options = options || {};

        if (!input || !window.google || !google.maps || !google.maps.places) {
            return null;
        }

        ensurePacContainerModalSupport();

        if (input.dataset.googlePlacesReady === 'true') {
            return input._soilnwaterPlacesAutocomplete || null;
        }

        var autocomplete = new google.maps.places.Autocomplete(input, buildOptions(options));

        input.dataset.googlePlacesReady = 'true';
        input._soilnwaterPlacesAutocomplete = autocomplete;
        trackPacInput(input);

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
        unbindAutocomplete: unbindAutocomplete,
        initSchoolInstituteSearchFields: initSchoolInstituteSearchFields,
        dismissPacDropdown: dismissPacDropdown,
        hidePacContainers: hidePacContainers,
        positionActivePacContainer: positionActivePacContainer,
    };
})(window);
