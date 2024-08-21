<?php
require __DIR__ . '/../connection.php';

// Check if the form was submitted and save the selected values in the session
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['abbrev'])) {
        $_SESSION['abbrev'] = $_GET['abbrev'];
    }
    if (isset($_GET['x'])) {
        $_SESSION['x'] = $_GET['x'];
    }
    if (isset($_GET['y'])) {
        $_SESSION['y'] = $_GET['y'];
    }
    if (isset($_GET['order-x'])) {
        $_SESSION['order-x'] = $_GET['order-x'];
    }
    if (isset($_GET['facility'])) {
        $_SESSION['facility'] = $_GET['facility'];
    }
    if (isset($_GET['work_center'])) {
        $_SESSION['work_center'] = $_GET['work_center'];
    }
    if (isset($_GET['device_name'])) {
        $_SESSION['device_name'] = $_GET['device_name'];
    }
    if (isset($_GET['test_program'])) {
        $_SESSION['test_program'] = $_GET['test_program'];
    }
    if (isset($_GET['lot'])) {
        $_SESSION['lot'] = $_GET['lot'];
    }
    if (isset($_GET['wafer'])) {
        $_SESSION['wafer'] = $_GET['wafer'];
    }
    if (isset($_GET['parameter'])) {
        $_SESSION['parameter'] = $_GET['parameter'];
    }
}

// Fetch previously selected values from the session, if any
$selectedAbbrev = $_SESSION['abbrev'] ?? [];
$selectedX = $_SESSION['x'] ?? null;
$selectedY = $_SESSION['y'] ?? null;
$selectedOrderX = $_SESSION['order-x'] ?? null;
$selectedFacility = $_SESSION['facility'] ?? [];
$selectedWorkCenter = $_SESSION['work_center'] ?? [];
$selectedDeviceName = $_SESSION['device_name'] ?? [];
$selectedTestProgram = $_SESSION['test_program'] ?? [];
$selectedLot = $_SESSION['lot'] ?? [];
$selectedWafer = $_SESSION['wafer'] ?? [];
$selectedParameter = $_SESSION['parameter'] ?? [];

// Query to populate the initial facility options
$query = "SELECT DISTINCT Facility_ID FROM lot";
$facilities = [];
$stmt = sqlsrv_query($conn, $query);
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $facilities[] = $row['Facility_ID'];
}
sqlsrv_free_stmt($stmt);

// Query to populate filter options from ProbingSequenceOrder
$filterQuery = "SELECT DISTINCT p.abbrev,  w.probing_sequence FROM ProbingSequenceOrder p JOIN wafer w on w.probing_sequence = p.probing_sequence ORDER BY p.abbrev ASC";
$abbrev = [];
$filterStmt = sqlsrv_query($conn, $filterQuery);
while ($row = sqlsrv_fetch_array($filterStmt, SQLSRV_FETCH_ASSOC)) {
    $abbrev[] = ['abbrev' => $row['abbrev'], 'probing_sequence' => $row['probing_sequence']];
}
sqlsrv_free_stmt($filterStmt);

$columns = [
    'Facility ID', 'Head Number', 'HBin Number', 'Lot ID', 'Part Type', 'Probe Count', 'Program Name',
    'SBin Number', 'Site Number', 'Test Temperature', 'Test Time', 'Tests Executed', 'Unit Number',
    'Wafer Finish Time', 'Wafer ID', 'Wafer Start Time', 'Work Center', 'X', 'Y',
];
?>


<style>
    .filter-text-header{
        margin-top:-28px;
    }

    .bg-cyan-700 {
        --tw-bg-opacity: 1;
        background-color: rgb(14 116 144 / var(--tw-bg-opacity)) /* #0e7490 */;
    }

    .px-12 {
        padding: 3rem /* 48px */;
    }
</style>

<div class="container mx-auto px-12 py-6 bg-white rounded-md shadow-md">
    <h1 class="text-center text-2xl font-bold mb-6 w-full">Selection Criteria</h1>
    <form action="dashboard.php" method="GET" id="criteriaForm">
    <div class="flex flex-row justify-between w-full gap-4">
        <div class="border-2 border-gray-200 rounded-lg p-4 mb-4 w-1/3">
                <h2 class="text-md italic mb-4 w-24 text-gray-500 bg-white filter-text-header text-center"><i class="fa-solid fa-filter"></i>&nbsp;Filter by</h2>
                <div class="flex w-full justify-start items-start gap-2">
                <!-- Probe Count Button and Dropdown -->
                <button id="dropdownSearchButtonProbe" data-dropdown-toggle="dropdownSearchProbe" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-gray-900 bg-gray-50 border border-gray-300 rounded-lg" type="button">
                    Probe Count
                    <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                    </svg>
                </button>

                <!-- Probe Count Dropdown menu -->
                <div id="dropdownSearchProbe" class="z-10 hidden bg-white rounded-lg shadow w-60 dark:bg-gray-700">
                    <ul class="h-48 px-3 pb-3 overflow-y-auto text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownSearchButtonProbe">
                        <li>
                            <div class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                                <input id="select-all-abbrev" type="checkbox" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                <label for="select-all-abbrev" class="w-full ms-2 text-sm font-medium text-gray-900 rounded dark:text-gray-300">Select All</label>
                            </div>
                        </li>
                        <?php foreach ($abbrev as $item): ?>
                        <li>
                            <div class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                                <input id="checkbox-item-<?= htmlspecialchars($item['abbrev']) ?>" name="abbrev[]" type="checkbox" value="<?= htmlspecialchars($item['abbrev']) ?>" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500 filter-checkbox-abbrev">
                                <label for="checkbox-item-<?= htmlspecialchars($item['abbrev']) ?>" class="w-full ms-2 text-sm font-medium text-gray-900 rounded dark:text-gray-300"><?= htmlspecialchars($item['abbrev']) ?></label>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>


            <!-- Group by Section -->
        <div class="border-2 border-gray-200 rounded-lg p-4 mb-4 w-1/3">
            <h2 class="text-md italic mb-4 w-24 text-gray-500 bg-white filter-text-header text-center"><i class="fa-solid fa-layer-group"></i>&nbsp;Group by</h2>
            <div class="flex w-full justify-start items-center gap-4">
                <div>
                <select id="x-axis-select" name="x" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm font-medium rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option selected disabled>X-Axis</option>
                    <?php foreach ($columns as $index => $column): ?>
                        <option value="<?= $index ?>" class="bg-white text-gray-900"><?= $column ?></option>
                    <?php endforeach; ?>
                </select>
                </div>
                <div>
                <select id="y-axis-select" name="y" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm font-medium rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option selected disabled>Y-Axis</option>
                    <?php foreach ($columns as $index => $column): ?>
                        <option value="<?= $index ?>" class="bg-white text-gray-900"><?= $column ?></option>
                    <?php endforeach; ?>
                </select>
                </div>
            </div>
        </div>

        <!-- Sort by Section -->
        <div class="border-2 border-gray-200 rounded-lg p-4 mb-4 w-1/3">
            <h2 class="text-md italic mb-4 w-20 text-gray-500 bg-white filter-text-header text-center"><i class="fa-solid fa-sort"></i>&nbsp;Sort by</h2>
            <div class="flex w-full justify-start items-center gap-4">

                <div>
                <select id="x-axis-select" name="order-x" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm font-medium rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option selected disabled>X-Axis</option>
                        <option value="0" class="bg-white text-gray-900">Ascending</option>
                        <option value="1" class="bg-white text-gray-900">Descending</option>
                </select>
                </div>

                <div>
                <select id="x-axis-select" name="order-y" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm font-medium rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option selected disabled>X-Axis</option>
                        <option value="0" class="bg-white text-gray-900">Ascending</option>
                        <option value="1" class="bg-white text-gray-900">Descending</option>
                </select>
                </div>
            </div>
        </div>
        

        </div>
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <label for="facility" class="block text-sm font-medium text-gray-700 multiple-select">Facility</label>
                <select size="5" id="facility" name="facility[]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple required>
                    <?php foreach ($facilities as $facility): ?>
                        <option value="<?= $facility ?>"><?= $facility ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="work_center" class="block text-sm font-medium text-gray-700 multiple-select">Work Center</label>
                <select size="5" id="work_center" name="work_center[]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple required>
                    <!-- Options will be populated based on facility selection -->
                </select>
            </div>

            <div>
                <label for="device_name" class="block text-sm font-medium text-gray-700 multiple-select">Device Name</label>
                <select size="5" id="device_name" name="device_name[]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple required>
                    <!-- Options will be populated based on work center selection -->
                </select>
            </div>

            <div>
                <label for="test_program" class="block text-sm font-medium text-gray-700 multiple-select">Test Program</label>
                <select size="5" id="test_program" name="test_program[]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple required>
                    <!-- Options will be populated based on device name selection -->
                </select>
            </div>

            <div>
                <label for="lot" class="block text-sm font-medium text-gray-700 multiple-select">Lot</label>
                <select size="5" id="lot" name="lot[]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple required>
                    <!-- Options will be populated based on test program selection -->
                </select>
            </div>

            <div>
                <label for="wafer" class="block text-sm font-medium text-gray-700 multiple-select">Wafer</label>
                <select size="5" id="wafer" name="wafer[]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple required>
                    <!-- Options will be populated based on lot selection -->
                </select>
            </div>

            <div class="col-span-3">
                <div class="flex justify-between gap-5">
                    <div class="flex flex-1 flex-col">
                        <label for="parameter" class="block text-sm font-medium text-gray-700 multiple-select">Parameter X</label>
                        <select size="5" id="parameter" name="parameter[]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple required>
                            <!-- Options will be populated based on wafer selection -->
                        </select>
                    </div>
                    <div class="flex flex-1 flex-col">
                        <label for="parameter-y" class="block text-sm font-medium text-gray-700 multiple-select">Parameter Y</label>
                        <select size="5" id="parameter-y" name="parameter-y[]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple>
                            <!-- Options will be populated based on wafer selection -->
                        </select>
                    </div>
                </div>
            </div>
            
        </div>

        <div class="text-center w-full flex justify-start gap-4">

            <!-- Modal toggle -->
            <button data-modal-target="select-modal" data-modal-toggle="select-modal" class="block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800" type="button">
            Submit&nbsp;<i class="fa-solid fa-arrow-right"></i>
            </button>

            <!-- <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg"><i class="fa-solid fa-play"></i>&nbsp;Execute</button> -->
            <button type="button" id="resetButton" class="px-4 py-2 bg-red-500 text-white rounded-lg">Reset&nbsp;<i class="fa-solid fa-delete-left"></i></button>
        </div>

        <?php include('chart_type_modal.php'); ?>
    </form>
</div>
<script src="../js/selection.js"></script>
<script>
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

</script>
