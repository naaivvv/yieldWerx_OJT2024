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
                case 'parameter':
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
                    if (queryType === 'parameter') {
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
        fetchOptions(selectedWafer, $('#parameter'), 'parameter');
    });

    $('#parameter').change(function() {
        const selectedParameters = $(this).val(); 

        if (selectedParameters && selectedParameters.length === 1) {
            $('#scatter-plot').prop('hidden', true);
        } else {
            $('#scatter-plot').prop('hidden', false);
        }
    });

    // Reset button functionality
    $('#resetButton').click(function() {
        $('#criteriaForm')[0].reset();
        $('#work_center, #device_name, #test_program, #lot, #wafer, #parameter').html('');
    });
});
// Probe dropdown
document.getElementById('select-all-abbrev').addEventListener('change', function(e) {
    document.querySelectorAll('.filter-checkbox-abbrev').forEach(checkbox => checkbox.checked = e.target.checked);
});

