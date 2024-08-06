<?php
require __DIR__ . '/../connection.php';

// Query to populate the initial facility options
$query = "SELECT Facility_ID FROM lot";
$facilities = [];
$stmt = sqlsrv_query($conn, $query);
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $facilities[] = $row['Facility_ID'];
}
sqlsrv_free_stmt($stmt);

// Query to populate filter options from ProbingSequenceOrder
$filterQuery = "SELECT abbrev, probing_sequence FROM ProbingSequenceOrder";
$filters = [];
$filterStmt = sqlsrv_query($conn, $filterQuery);
while ($row = sqlsrv_fetch_array($filterStmt, SQLSRV_FETCH_ASSOC)) {
    $filters[] = ['abbrev' => $row['abbrev'], 'probing_sequence' => $row['probing_sequence']];
}
sqlsrv_free_stmt($filterStmt);
?>

<style>
    select:not([size]) {
        background: white !important;
    }
</style>

<div class="container mx-auto p-6">
    <h1 class="text-center text-2xl font-bold mb-4 w-full">Selection Criteria</h1>
    <div class="flex w-full justify-end items-end">
    <button id="dropdownSearchButton" data-dropdown-toggle="dropdownSearch" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-indigo-700 rounded-lg hover:bg-indigo-800 focus:ring-4 focus:outline-none focus:ring-indigo-300 dark:bg-indigo-600 dark:hover:bg-indigo-700 dark:focus:ring-indigo-800" type="button">
        <i class="fa-solid fa-filter"></i>&nbsp;Filter 
        <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
        </svg>
    </button>

    <!-- Dropdown menu -->
    <div id="dropdownSearch" class="z-10 hidden bg-white rounded-lg shadow w-60 dark:bg-gray-700">
        <ul class="h-48 px-3 pb-3 overflow-y-auto text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownSearchButton">
            <li>
                <div class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                    <input id="select-all" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                    <label for="select-all" class="w-full ms-2 text-sm font-medium text-gray-900 rounded dark:text-gray-300">Select All</label>
                </div>
            </li>
            <?php foreach ($filters as $filter): ?>
            <li>
                <div class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                    <input id="checkbox-item-<?= htmlspecialchars($filter['abbrev']) ?>" type="checkbox" value="<?= htmlspecialchars($filter['probing_sequence']) ?>" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500 filter-checkbox">
                    <label for="checkbox-item-<?= htmlspecialchars($filter['abbrev']) ?>" class="w-full ms-2 text-sm font-medium text-gray-900 rounded dark:text-gray-300"><?= htmlspecialchars($filter['abbrev']) ?></label>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<script>
document.getElementById('select-all').addEventListener('change', function() {
    var checkboxes = document.querySelectorAll('.filter-checkbox');
    for (var checkbox of checkboxes) {
        checkbox.checked = this.checked;
    }
});
</script>
    <form action="dashboard.php" method="GET" id="criteriaForm">
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <label for="facility" class="block text-sm font-medium text-gray-700">Facility</label>
                <select id="facility" name="facility[]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple>
                    <?php foreach ($facilities as $facility): ?>
                        <option value="<?= $facility ?>"><?= $facility ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="work_center" class="block text-sm font-medium text-gray-700">Work Center</label>
                <select id="work_center" name="work_center[]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple>
                    <!-- Options will be populated based on facility selection -->
                </select>
            </div>

            <div>
                <label for="device_name" class="block text-sm font-medium text-gray-700">Device Name</label>
                <select id="device_name" name="device_name[]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple>
                    <!-- Options will be populated based on work center selection -->
                </select>
            </div>

            <div>
                <label for="test_program" class="block text-sm font-medium text-gray-700">Test Program</label>
                <select id="test_program" name="test_program[]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple>
                    <!-- Options will be populated based on device name selection -->
                </select>
            </div>

            <div>
                <label for="lot" class="block text-sm font-medium text-gray-700">Lot</label>
                <select id="lot" name="lot[]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple>
                    <!-- Options will be populated based on test program selection -->
                </select>
            </div>

            <div>
                <label for="wafer" class="block text-sm font-medium text-gray-700">Wafer</label>
                <select id="wafer" name="wafer[]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple>
                    <!-- Options will be populated based on lot selection -->
                </select>
            </div>

            <div class="col-span-3">
                <label for="parameter" class="block text-sm font-medium text-gray-700">Parameter</label>
                <select id="parameter" name="parameter[]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple>
                    <!-- Options will be populated based on wafer selection -->
                </select>
            </div>
        </div>
        <div class="text-center w-full flex justify-start gap-4">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">Execute</button>
            <button type="button" id="resetButton" class="px-4 py-2 bg-red-500 text-white rounded">Reset</button>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    // Function to fetch options based on previous selection
    function fetchOptions(selectedValue, targetElement, queryType) {
        $.ajax({
            url: 'fetch_options.php',
            method: 'GET',
            data: {
                value: selectedValue,
                type: queryType
            },
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
        let selectedValue = $(this).val();
        fetchOptions(selectedValue, $('#work_center'), 'work_center');
    });

    $('#work_center').change(function() {
        let selectedValue = $(this).val();
        fetchOptions(selectedValue, $('#device_name'), 'device_name');
    });

    $('#device_name').change(function() {
        let selectedValue = $(this).val();
        fetchOptions(selectedValue, $('#test_program'), 'test_program');
    });

    $('#test_program').change(function() {
        let selectedValue = $(this).val();
        fetchOptions(selectedValue, $('#lot'), 'lot');
    });

    $('#lot').change(function() {
        let selectedValue = $(this).val();
        fetchOptions(selectedValue, $('#wafer'), 'wafer');
    });

    $('#wafer').change(function() {
        let selectedValue = $(this).val();
        fetchOptions(selectedValue, $('#parameter'), 'parameter');
    });

    $('#resetButton').click(function() {
        $('#criteriaForm')[0].reset();
        $('select').html('');
    });
});
</script>
