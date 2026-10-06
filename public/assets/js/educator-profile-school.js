(function () {
    'use strict';

    var checkbox = document.getElementById('associatedWithSchool');
    var fields = document.getElementById('eduSchoolAssociationFields');
    var affBlock = document.getElementById('eduSchoolAffiliationBlock');

    if (!checkbox && !fields && !affBlock) {
        return;
    }

    function syncSchoolSections() {
        var show = !checkbox || checkbox.checked;
        if (fields) {
            fields.classList.toggle('d-none', !show);
        }
        if (affBlock) {
            affBlock.classList.toggle('d-none', !show);
        }
    }

    if (checkbox) {
        checkbox.addEventListener('change', syncSchoolSections);
    }

    syncSchoolSections();
})();
