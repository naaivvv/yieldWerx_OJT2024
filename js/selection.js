$(document).ready(function() {
    // Function to fetch options based on previous selection
    function fetchOptions(selectedValue, targetElement, queryType) {
        let data = {};
        switch(queryType) {
            case 'work_center':
                data.facility = selectedValue;
                break;
            case 'device_name':
                data.work_center = selectedValue;
                break;
            case 'test_program':
                data.device_name = selectedValue;
                break;
            case 'lot':
                data.test_program = selectedValue;
                break;
            case 'wafer':
                data.lot = selectedValue;
                break;
            case 'parameter-x':
            case 'parameter-y': // Add this case
                data.wafer = selectedValue;
                break;
            default:
                return; // If an invalid queryType is passed, exit the function.
        }
        data.type = queryType; // Add the query type to the data object

        $.ajax({
            url: 'fetch_options.php',
            method: 'GET',
            data: data,
            dataType: 'json',
            success: function(response) {
                let options = '';
                if (queryType === 'parameter-x' || queryType === 'parameter-y') { // Update this condition
                    $.each(response, function(index, item) {
                        options += `<option value="${item.value}">${item.display}</option>`;
                    });
                } else {
                    $.each(response, function(index, value) {
                        options += `<option value="${value}">${value}</option>`;
                    });
                }
                targetElement.html(options);
            }
        });
    }

    // Event listeners for each select element
    $('#facility').change(function() {
        const selectedFacility = $(this).val();
        fetchOptions(selectedFacility, $('#work_center'), 'work_center');
    });

    $('#work_center').change(function() {
        const selectedWorkCenter = $(this).val();
        fetchOptions(selectedWorkCenter, $('#device_name'), 'device_name');
    });

    $('#device_name').change(function() {
        const selectedDeviceName = $(this).val();
        fetchOptions(selectedDeviceName, $('#test_program'), 'test_program');
    });

    $('#test_program').change(function() {
        const selectedTestProgram = $(this).val();
        fetchOptions(selectedTestProgram, $('#lot'), 'lot');
    });

    $('#lot').change(function() {
        const selectedLot = $(this).val();
        fetchOptions(selectedLot, $('#wafer'), 'wafer');
    });

    $('#wafer').change(function() {
        const selectedWafer = $(this).val();
        fetchOptions(selectedWafer, $('#parameter-x'), 'parameter-x');
        fetchOptions(selectedWafer, $('#parameter-y'), 'parameter-y'); // Add this line to populate parameter-y
    });

    function updateChartsVisibility() {
        const selectedX = $('#parameter-x').val();
        const selectedY = $('#parameter-y').val();
    
        // Show or hide #scatter-plot based on both #parameter-x and #parameter-y selections
        if (selectedX && selectedX.length > 0 && selectedY && selectedY.length > 0) {
            $('#scatter-plot').prop('hidden', false);
        } else {
            $('#scatter-plot').prop('hidden', true);
        }
    
        // Hide #cumulative if only parameter-x is selected
        if (selectedX && selectedX.length > 0 && (!selectedY || selectedY.length === 0)) {
            $('#cumulative').prop('hidden', true);
        } else {
            $('#cumulative').prop('hidden', false);
        }
    
        // Hide #line-chart if only parameter-y is selected
        if (selectedY && selectedY.length > 0 && (!selectedX || selectedX.length === 0)) {
            $('#line-chart').prop('hidden', true);
        } else {
            $('#line-chart').prop('hidden', false);
        }
    }
    
    $('#parameter-x').change(updateChartsVisibility);
    $('#parameter-y').change(updateChartsVisibility);
     
    function toggleRequired() {
        const parameterX = $('#parameter-x').val();
        const parameterY = $('#parameter-y').val();
        
        if (parameterX.length > 0 || parameterY.length > 0) {
            $('#parameter-x').removeAttr('required');
            $('#parameter-y').removeAttr('required');
        } else {
            $('#parameter-x').attr('required', 'required');
            $('#parameter-y').attr('required', 'required');
        }
    }

    // Call the function on change of either select element
    $('#parameter-x, #parameter-y').change(function() {
        toggleRequired();
    });

    // Initial check
    toggleRequired();

    // Reset button functionality
    $('#resetButton').click(function() {
        $('#criteriaForm')[0].reset();
        $('#work_center, #device_name, #test_program, #lot, #wafer, #parameter-x, #parameter-y, #chart-1, #chart-2, #chart-3').html(''); // Include #parameter-y here
    });
});

// Probe dropdown
document.getElementById('select-all-abbrev').addEventListener('change', function(e) {
    document.querySelectorAll('.filter-checkbox-abbrev').forEach(checkbox => checkbox.checked = e.target.checked);
});

// HBin dropdown
document.getElementById('select-all-hbin').addEventListener('change', function(e) {
    document.querySelectorAll('.filter-checkbox-hbin').forEach(checkbox => checkbox.checked = e.target.checked);
});

// SBin dropdown
document.getElementById('select-all-sbin').addEventListener('change', function(e) {
    document.querySelectorAll('.filter-checkbox-sbin').forEach(checkbox => checkbox.checked = e.target.checked);
});

// Site dropdown
document.getElementById('select-all-site').addEventListener('change', function(e) {
    document.querySelectorAll('.filter-checkbox-site').forEach(checkbox => checkbox.checked = e.target.checked);
});