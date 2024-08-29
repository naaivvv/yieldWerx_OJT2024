$(document).ready(function() {
    let currentAjaxRequests = {}; // Store current AJAX requests per query type

    function fetchOptions(selectedValue, targetElement, queryType) {
        let data = {};
        let loadingSpinner = ''; // Variable to hold the spinner ID

        switch(queryType) {
            case 'work_center':
                data.facility = selectedValue;
                loadingSpinner = '#loading-spinner-wc';
                break;
            case 'device_name':
                data.work_center = selectedValue;
                loadingSpinner = '#loading-spinner-dn';
                break;
            case 'test_program':
                data.device_name = selectedValue;
                loadingSpinner = '#loading-spinner-tp';
                break;
            case 'lot':
                data.test_program = selectedValue;
                loadingSpinner = '#loading-spinner-l';
                break;
            case 'wafer':
                data.lot = selectedValue;
                loadingSpinner = '#loading-spinner-w';
                break;
            case 'parameter-x':
                data.wafer = selectedValue;
                loadingSpinner = '#loading-spinner-x';
                break;
            case 'parameter-y':
                data.wafer = selectedValue;
                loadingSpinner = '#loading-spinner-y';
                break;
            case 'probe_sequence':
            case 'hbin_number':
            case 'sbin_number':
            case 'site_number':
            case 'test_temperature':
            case 'test_time':
            case 'tests_executed':
            case 'unit_number':
                data.wafer = selectedValue;
                break;
            default:
                return;
        }
        data.type = queryType;

        // Show the loading spinner
        if (loadingSpinner) {
            $(loadingSpinner).removeClass('hidden');
        }

        // Abort any previous request for this query type before sending a new one
        if (currentAjaxRequests[queryType]) {
            currentAjaxRequests[queryType].abort();
        }

        // Send new AJAX request and store it in the currentAjaxRequests object
        currentAjaxRequests[queryType] = $.ajax({
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
                    'test_time': 'time[]',
                    'tests_executed': 'exec[]',
                    'unit_number': 'unit[]'
                };

                function generateCheckboxHTML(name, item) {
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
                    $('#loading-spinner-x').addClass('hidden');
                    $('#loading-spinner-y').addClass('hidden');
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

                // Hide the loading spinner
                if (loadingSpinner) {
                    $(loadingSpinner).addClass('hidden');
                }

                // Clear the current request for this query type after success
                currentAjaxRequests[queryType] = null;
            },
            error: function(jqXHR, textStatus) {
                if (textStatus !== 'abort') {
                    console.error(`AJAX request for ${queryType} failed:`, textStatus);
                }
                // Hide the spinner even if the request fails
                if (loadingSpinner) {
                    $(loadingSpinner).addClass('hidden');
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
        fetchOptions(selectedWafer, $('#dropdownSearchExec ul'), 'tests_executed');
        fetchOptions(selectedWafer, $('#dropdownSearchUnit ul'), 'unit_number');
    });

    function updateChartsVisibility() {
        const selectedX = $('#parameter-x').val();
        const selectedY = $('#parameter-y').val();

        if (selectedX && selectedX.length > 0 && selectedY && selectedY.length > 0) {
            $('#scatter-plot').prop('hidden', false);
        } else {
            $('#scatter-plot').prop('hidden', true);
        }

        if (selectedX && selectedX.length > 0 && (!selectedY || selectedY.length === 0)) {
            $('#line-chart').prop('hidden', true);
        } else {
            $('#line-chart').prop('hidden', false);
        }

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

    $('#parameter-x, #parameter-y').change(function() {
        toggleRequired();
    });

    toggleRequired();

    $('#resetButton').click(function() {
        $.each(currentAjaxRequests, function(key, request) {
            if (request) {
                request.abort();
            }
        });

        $('#criteriaForm')[0].reset();
        $('#work_center, #device_name, #test_program, #lot, #wafer, #parameter-x, #parameter-y, #chart-1, #chart-2, #chart-3, #filter-checkbox').html('');
    });

    $('#resetFilters').click(function() {
        $.each(currentAjaxRequests, function(key, request) {
            if (request) {
                request.abort();
            }
        });
        $('#filter-checkbox').html('');
        $('#x-axis-select, #y-axis-select').val([]);
        $('#x-axis-select, #y-axis-select').val('').prop('selectedIndex', 0);
        updateSelectOptions();
    });

    $('#resetFacility').click(function() {
        $.each(currentAjaxRequests, function(key, request) {
            if (request) {
                request.abort();
            }
        });

        $('#facility').val([]);
        $('#work_center, #device_name, #test_program, #lot, #wafer, #parameter-x, #parameter-y').html('');
    });

    $('#resetWorkCenter').click(function() {
        $.each(currentAjaxRequests, function(key, request) {
            if (request) {
                request.abort();
            }
        });

        $('#work_center').val([]);
        $('#device_name, #test_program, #lot, #wafer, #parameter-x, #parameter-y').html('');
    });

    $('#resetDeviceName').click(function() {
        $.each(currentAjaxRequests, function(key, request) {
            if (request) {
                request.abort();
            }
        });

        $('#device_name').val([]);
        $('#test_program, #lot, #wafer, #parameter-x, #parameter-y').html('');
    });

    $('#resetTestProgram').click(function() {
        $.each(currentAjaxRequests, function(key, request) {
            if (request) {
                request.abort();
            }
        });

        $('#test_program').val([]);
        $('#lot, #wafer, #parameter-x, #parameter-y').html('');
    });

    $('#resetLot').click(function() {
        $.each(currentAjaxRequests, function(key, request) {
            if (request) {
                request.abort();
            }
        });

        $('#lot').val([]);
        $('#wafer, #parameter-x, #parameter-y').html('');
    });

    $('#resetWafer').click(function() {
        $.each(currentAjaxRequests, function(key, request) {
            if (request) {
                request.abort();
            }
        });

        $('#wafer').val([]);
        $('#parameter-x, #parameter-y').html('');
    });

    $('#resetParameterX').click(function() {
        $.each(currentAjaxRequests, function(key, request) {
            if (request) {
                request.abort();
            }
        });
        $('#parameter-x').val([]); // Reset the value of #parameter-x
    });

    $('#resetParameterY').click(function() {
        $.each(currentAjaxRequests, function(key, request) {
            if (request) {
                request.abort();
            }
        });
        $('#parameter-y').val([]); // Reset the value of #parameter-y
    });

    $('#criteriaForm').on('submit', function(e) {
        var selectedValue = $('input[name="chart"]:checked').val();
        
        if (selectedValue == '0') {
            $(this).attr('action', 'line_chart.php');
        } else if (selectedValue == '1') {
            $(this).attr('action', 'graph.php');
        } else if (selectedValue == '2') {
            $(this).attr('action', 'cumulative.php');
        } else if (selectedValue == '3') {
            $(this).attr('action', 'dashboard.php');
        }
    });

    function updateSelectOptions() {
        let xAxisValue = $('#x-axis-select').val();
        let yAxisValue = $('#y-axis-select').val();
    
        // Enable all options first
        $('#x-axis-select option').not(':first').prop('disabled', false);
        $('#y-axis-select option').not(':first').prop('disabled', false);
    
        // Explicitly disable the placeholder options
        $('#x-axis-select option:first').prop('disabled', true);
        $('#y-axis-select option:first').prop('disabled', true);
    
        // Disable the selected value in the other select
        if (xAxisValue && xAxisValue !== 'X-Axis') {
            $('#y-axis-select option[value="' + xAxisValue + '"]').prop('disabled', true);
        }
        if (yAxisValue && yAxisValue !== 'Y-Axis') {
            $('#x-axis-select option[value="' + yAxisValue + '"]').prop('disabled', true);
        }
    }
    
    // Attach change event listeners to both select elements
    $('#x-axis-select, #y-axis-select').change(updateSelectOptions);
    
    // Initial call to disable any pre-selected options
    updateSelectOptions();    

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

// Exec dropdown
document.getElementById('select-all-exec').addEventListener('change', function(e) {
    document.querySelectorAll('.filter-checkbox-exec').forEach(checkbox => checkbox.checked = e.target.checked);
});

// Unit dropdown
document.getElementById('select-all-unit').addEventListener('change', function(e) {
    document.querySelectorAll('.filter-checkbox-temp').forEach(checkbox => checkbox.checked = e.target.checked);
});