function populateDropdowns(DivisionCode, DistrictCode, UpazilaCode, UnionWardCode, MauzaCode, VillageCode) {
    populateLocationDropdowns(DivisionCode, DistrictCode, UpazilaCode, UnionWardCode, MauzaCode, VillageCode);
}

// Populate the location dropdowns
function populateLocationDropdowns(DivisionCode, DistrictCode, UpazilaCode, UnionWardCode, MauzaCode, VillageCode) {
    if (!$('#DivisionCode').val()) {
        return;
    }

    if (DistrictCode === null) {
        console.log("DistrictCode is null, skipping related dropdowns.");
    }

    ShowDropDown('DivisionCode', 'DistrictDiv', 'ShowDistrict', 'ShowUpazila', DistrictCode)
        .then(function() {
            $("#geoDiv").show();
            if (!$('#DistrictCode').val()) {
                throw new Error("DistrictCode is empty, halting further execution.");
            }
            return ShowDropDown('DistrictCode', 'UpazilaDiv', 'ShowUpazila', 'Yes', UpazilaCode);
        })
        .then(function() {
            if (!$('#UpazilaCode').val()) {
                throw new Error("UpazilaCode is empty, halting further execution.");
            }
            return ShowDropDown1('DistrictCode', 'UpazilaCode', 'DivisionCode', 'UnionWardDiv', 'ShowUnionWard', 'ShowMauza', UnionWardCode);
        })
        .then(function() {
            if (!$('#UnionWardCode').val()) {
                throw new Error("UnionWardCode is empty, halting further execution.");
            }
            return ShowDropDown1('DistrictCode', 'UpazilaCode', 'UnionWardCode', 'MauzaDiv', 'ShowMauza', 'ShowVillage', MauzaCode);
        })
        .then(function() {
            if (!$('#MauzaCode').val()) {
                throw new Error("MauzaCode is empty, halting further execution.");
            }
            return ShowDropDown3('DistrictCode', 'UpazilaCode', 'UnionWardCode', 'MauzaCode', 'VillageDiv', 'ShowVillage', 'Yes', VillageCode);
        })
        .catch(function(error) {
            console.log("Error populating dropdowns:", error.message);
        });
}

// Remove the location dropdowns
function removeDropdowns(fieldIds) {
    console.log("Removing dropdowns:", fieldIds);
    fieldIds.forEach(function(fieldId) {
        $("#" + fieldId).hide();
        $("#" + fieldId).empty();
    });
}

$(document).ready(function() {
    $('#DivisionCode').on('change', function() {
        const divisionCode = this.value;
        const districtCode = $('#DistrictCode').val();
        const upazilaCode = $('#UpazilaCode').val();
        const unionWardCode = $('#UnionWardCode').val();
        const mauzaCode = $('#MauzaCode').val();
        const villageCode = $('#VillageCode').val();

        if (divisionCode > 1) {
            populateDropdowns(divisionCode, districtCode, upazilaCode, unionWardCode, mauzaCode, villageCode);
            removeDropdowns(['UpazilaDiv', 'UnionWardDiv', 'MauzaDiv', 'VillageDiv']);
        } else {
            removeDropdowns(['DistrictDiv', 'UpazilaDiv', 'UnionWardDiv', 'MauzaDiv', 'VillageDiv']);
        }
    });

    $(document).on('change', '#DistrictCode', function() {
        removeDropdowns(['UnionWardDiv', 'MauzaDiv', 'VillageDiv']);
        $("#UpazilaDiv").show();
    });

    $(document).on('change', '#UpazilaCode', function() {
        removeDropdowns(['MauzaDiv', 'VillageDiv']);
        $("#UnionWardDiv").show();
    });

    $(document).on('change', '#UnionWardCode', function() {
        removeDropdowns(['VillageDiv']);
        $("#MauzaDiv").show();
    });

    $(document).on('change', '#MauzaCode', function() {
        $("#VillageDiv").show();
    });

    $('#clearForm').on('click', function() {
        window.location.href = window.location.href;
    });
}); 