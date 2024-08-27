$(document).ready(function() {
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
            case 'parameter-y':
            case 'probe_sequence':
            case 'hbin_number':
            case 'sbin_number':
            case 'site_number':
            case 'test_temperature':
            case 'test_time':
                data.wafer = selectedValue;
                break;
            default:
                return;
        }
        data.type = queryType;

        $.ajax({
            url: 'fetch_options.php',
            method: 'GET',
            data: data,
            dataType: 'json',
            success: function(response) {
                let options = '';
                const queryTypeCheckboxMapping = {
                    'probe_sequence': 'abbrev[]',
                    'hbin_number': 'hbin[]',
                    'sbin_number': 'sbin[]',
                    'site_number': 'site[]',
                    'test_temperature': 'temp[]',
                    'test_time': 'time[]'
                };
        
                // Reusable function to generate checkbox HTML
                function generateCheckboxHTML(name, item) {
                    // Extract the base name without the square brackets
                    const baseName = name.replace(/\[\]$/, '');
                
                    return `
                        <li id="filter-checkbox">
                            <div class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                                <input id="checkbox-item-${item.value}" name="${name}" type="checkbox" value="${item.value}" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500 filter-checkbox-${baseName}">
                                <label for="checkbox-item-${item.value}" class="w-full ms-2 text-sm font-medium text-gray-900 rounded dark:text-gray-300">${item.display}</label>
                            </div>
                        </li>`;
                }                
        
                if (queryType === 'parameter-x' || queryType === 'parameter-y') {
                    $.each(response, function(index, item) {
                        options += `<option value="${item.value}">${item.display}</option>`;
                    });
                    targetElement.html(options);
                } else if (queryTypeCheckboxMapping.hasOwnProperty(queryType)) {
                    targetElement.find('li').not(':first').remove();
                    const name = queryTypeCheckboxMapping[queryType];
        
                    $.each(response, function(index, item) {
                        const liElement = generateCheckboxHTML(name, item);
                        targetElement.append(liElement);
                    });
                } else {
                    $.each(response, function(index, value) {
                        options += `<option value="${value}">${value}</option>`;
                    });
                    targetElement.html(options);
                }
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
        fetchOptions(selectedWafer, $('#parameter-y'), 'parameter-y');
        fetchOptions(selectedWafer, $('#dropdownSearchProbe ul'), 'probe_sequence');
        fetchOptions(selectedWafer, $('#dropdownSearchHBin ul'), 'hbin_number');
        fetchOptions(selectedWafer, $('#dropdownSearchSBin ul'), 'sbin_number');
        fetchOptions(selectedWafer, $('#dropdownSearchSite ul'), 'site_number');
        fetchOptions(selectedWafer, $('#dropdownSearchTemp ul'), 'test_temperature');
        fetchOptions(selectedWafer, $('#dropdownSearchTime ul'), 'test_time');
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
            $('#line-chart').prop('hidden', true);
        } else {
            $('#line-chart').prop('hidden', false);
        }
    
        // Hide #line-chart if only parameter-y is selected
        if (selectedY && selectedY.length > 0 && (!selectedX || selectedX.length === 0)) {
            $('#cumulative').prop('hidden', true);
        } else {
            $('#cumulative').prop('hidden', false);
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
        $('#work_center, #device_name, #test_program, #lot, #wafer, #parameter-x, #parameter-y, #chart-1, #chart-2, #chart-3, #filter-checkbox').html(''); // Include #parameter-y here
    });

    // Reset facility
    $('#resetFacility').click(function() {
        $('#facility').val([]);
        $('#work_center, #device_name, #test_program, #lot, #wafer, #parameter-x, #parameter-y').html('');
    });

    $('#resetWorkCenter').click(function() {
        $('#work_center').val([]);
        $('#device_name, #test_program, #lot, #wafer, #parameter-x, #parameter-y').html('');
    });

    $('#resetDeviceName').click(function() {
        $('#device_name').val([]);
        $('#test_program, #lot, #wafer, #parameter-x, #parameter-y').html('');
    });

    $('#resetTestProgram').click(function() {
        $('#test_program').val([]);
        $('#lot, #wafer, #parameter-x, #parameter-y').html('');
    });

    $('#resetLot').click(function() {
        $('#lot').val([]);
        $('#wafer, #parameter-x, #parameter-y').html('');
    });

    $('#resetWafer').click(function() {
        $('#wafer').val([]);
        $('#parameter-x, #parameter-y, #filter-checkbox').html('');
    });

    $('#resetParameterX').click(function() {
        $('#parameter-x').val([]);
    });

    $('#resetParameterY').click(function() {
        $('#parameter-y').val([]);
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

// Temp dropdown
document.getElementById('select-all-temp').addEventListener('change', function(e) {
    document.querySelectorAll('.filter-checkbox-temp').forEach(checkbox => checkbox.checked = e.target.checked);
});