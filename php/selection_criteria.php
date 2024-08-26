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
    if (isset($_GET['parameter-x'])) {
        $_SESSION['parameter-x'] = $_GET['parameter-x'];
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
$selectedParameter = $_SESSION['parameter-x'] ?? [];

// Query to populate the initial facility options
$query = "SELECT DISTINCT Facility_ID FROM lot";
$facilities = [];
$stmt = sqlsrv_query($conn, $query);
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $facilities[] = $row['Facility_ID'];
}
sqlsrv_free_stmt($stmt);

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
    select[multiple] option:checked {
        background-color: #3b82f6; /* Tailwind's blue-500 */
        color: white;
    }
    .border-orange-400 {
        --tw-border-opacity: 1;
        border-color: rgb(255 138 76 / var(--tw-border-opacity)) /* #ff8a4c */;
    }
</style>
<div class="container mx-auto">
    <h1 class="text-center text-2xl font-bold mb-6 w-full">Selection Criteria</h1>
    <form action="dashboard.php" method="GET" id="criteriaForm">
    <div class="flex flex-row justify-between gap-4">
        <div class="bg-white rounded-lg shadow-lg py-12 px-6 flex-1">

                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <label for="facility" class="block text-md font-medium text-blue-400 multiple-select">
                        <div class="flex items-center justify-between">Facility
                            <svg class="w-3 h-3 ms-2 sm:ms-4 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 12 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m7 9 4-4-4-4M1 9l4-4-4-4"/>
                            </svg>
                        </div>
                        </label>
                        <select size="5" id="facility" name="facility[]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple required>
                            <?php foreach ($facilities as $facility): ?>
                                <option value="<?= $facility ?>"><?= $facility ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                    <label for="work_center" class="block text-md font-medium text-blue-500 multiple-select">
                        <div class="flex items-center justify-between">Work Center
                            <svg class="w-3 h-3 ms-2 sm:ms-4 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 12 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m7 9 4-4-4-4M1 9l4-4-4-4"/>
                            </svg>
                        </div>
                        </label>
                        <select size="5" id="work_center" name="work_center[]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple required>
                            <!-- Options will be populated based on facility selection -->
                        </select>
                    </div>

                    <div>
                        <label for="device_name" class="block text-md font-medium text-blue-600 multiple-select">
                        <div class="flex items-center justify-between">Device Name
                            <svg class="w-3 h-3 ms-2 sm:ms-4 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 12 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m7 9 4-4-4-4M1 9l4-4-4-4"/>
                            </svg>
                        </div>
                        </label>
                        <select size="5" id="device_name" name="device_name[]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple required>
                            <!-- Options will be populated based on work center selection -->
                        </select>
                    </div>

                    <div>
                        <label for="test_program" class="block text-md font-medium text-blue-700 multiple-select">
                        <div class="flex items-center justify-between">Test Program
                            <svg class="w-3 h-3 ms-2 sm:ms-4 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 12 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m7 9 4-4-4-4M1 9l4-4-4-4"/>
                            </svg>
                        </div>
                        </label>
                        <select size="5" id="test_program" name="test_program[]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple required>
                            <!-- Options will be populated based on device name selection -->
                        </select>
                    </div>

                    <div>
                        <label for="lot" class="block text-md font-medium text-blue-800 multiple-select">
                        <div class="flex items-center justify-between">Lot
                            <svg class="w-3 h-3 ms-2 sm:ms-4 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 12 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m7 9 4-4-4-4M1 9l4-4-4-4"/>
                            </svg>
                        </div>
                        </label>
                        <select size="5" id="lot" name="lot[]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple required>
                            <!-- Options will be populated based on test program selection -->
                        </select>
                    </div>

                    <div>
                        <label for="wafer" class="block text-md font-medium text-blue-900 multiple-select">
                        <div class="flex items-center justify-between">Wafer
                            <svg class="w-3 h-3 ms-2 sm:ms-4 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 12 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m7 9 4-4-4-4M1 9l4-4-4-4"/>
                            </svg>
                        </div>
                        </label>
                        <select size="5" id="wafer" name="wafer[]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple required>
                            <!-- Options will be populated based on lot selection -->
                        </select>
                    </div>

                    <div class="col-span-3">
                        <div class="flex justify-between gap-5">
                            <div class="flex flex-1 flex-col">
                                <label for="parameter-x" class="block text-md font-medium text-gray-700 multiple-select">
                                <div class="flex items-center justify-between">Parameter X
                                    <svg class="w-3 h-3 ms-2 sm:ms-4 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 12 10">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m7 9 4-4-4-4M1 9l4-4-4-4"/>
                                    </svg>
                                </div>
                                </label>
                                <select size="5" id="parameter-x" name="parameter-x[]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple>
                                    <!-- Options will be populated based on wafer selection -->
                                </select>
                            </div>
                            <div class="flex flex-1 flex-col">
                                <label for="parameter-y" class="block text-md font-medium text-gray-800 multiple-select">
                                <div class="flex items-center">Parameter Y
                                </div>
                                </label>
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
            
        </div>
        <div class="bg-white rounded-lg shadow-lg py-12 px-6">
        <div class="flex flex-col justify-between w-full gap-4">
                    <!-- Group by Section -->
                <div class="border-2 border-gray-200 rounded-lg p-4 mb-4 w-full">
                    <h2 class="text-md italic mb-4 w-24 text-green-500 bg-white filter-text-header text-center"><i class="fa-solid fa-layer-group"></i>&nbsp;Group by</h2>
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
                <div class="border-2 border-gray-200 rounded-lg p-4 mb-4 w-full">
                    <h2 class="text-md italic mb-4 w-20 text-yellow-400 bg-white filter-text-header text-center"><i class="fa-solid fa-sort"></i>&nbsp;Sort by</h2>
                    <div class="flex w-full justify-start items-center gap-4">

                        <div class="w-full">
                        <select id="x-axis-select" name="order-x" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm font-medium rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option selected disabled>X-Axis</option>
                                <option value="0" class="bg-white text-gray-900">Ascending</option>
                                <option value="1" class="bg-white text-gray-900">Descending</option>
                        </select>
                        </div>

                        <div class="w-full">
                        <select id="x-axis-select" name="order-y" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm font-medium rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option selected disabled>Y-Axis</option>
                                <option value="0" class="bg-white text-gray-900">Ascending</option>
                                <option value="1" class="bg-white text-gray-900">Descending</option>
                        </select>
                        </div>
                    </div>
                </div>

                <!-- Filter by Section -->
                 

                <div class="border-2 border-gray-200 rounded-lg p-4 w-full gap-4">
                        <h2 class="text-md italic mb-4 w-24 text-orange-500 bg-white filter-text-header text-center"><i class="fa-solid fa-filter"></i>&nbsp;Filter by</h2>
                        <div class="flex w-full justify-center items-center gap-2 mb-2">
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
                                <!-- This section will be populated dynamically by JavaScript -->
                            </ul>
                        </div>




                        <!-- HBin Number Button and Dropdown -->
                        <button id="dropdownSearchButtonHBin" data-dropdown-toggle="dropdownSearchHBin" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-gray-900 bg-gray-50 border border-gray-300 rounded-lg" type="button">
                            HBin Number
                            <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                            </svg>
                        </button>

                        <!-- HBin Number Dropdown menu -->
                        <div id="dropdownSearchHBin" class="z-10 hidden bg-white rounded-lg shadow w-60 dark:bg-gray-700">
                            <ul class="h-48 px-3 pb-3 overflow-y-auto text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownSearchButtonHBin">
                                <li>
                                    <div class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                                        <input id="select-all-hbin" type="checkbox" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                        <label for="select-all-hbin" class="w-full ms-2 text-sm font-medium text-gray-900 rounded dark:text-gray-300">Select All</label>
                                    </div>
                                </li>
                                <!-- This section will be populated dynamically by JavaScript -->
                            </ul>
                        </div>
                    </div>

                    <div class="flex w-full justify-center items-center gap-2 mb-2">
                    <!-- SBin Number Button and Dropdown -->
                    <button id="dropdownSearchButtonSBin" data-dropdown-toggle="dropdownSearchSBin" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-gray-900 bg-gray-50 border border-gray-300 rounded-lg" type="button">
                        SBin Number
                        <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                        </svg>
                    </button>

                    <!-- SBin Number Dropdown menu -->
                    <div id="dropdownSearchSBin" class="z-10 hidden bg-white rounded-lg shadow w-60 dark:bg-gray-700">
                        <ul class="h-48 px-3 pb-3 overflow-y-auto text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownSearchButtonSBin">
                            <li>
                                <div class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <input id="select-all-sbin" type="checkbox" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                    <label for="select-all-sbin" class="w-full ms-2 text-sm font-medium text-gray-900 rounded dark:text-gray-300">Select All</label>
                                </div>
                            </li>
                            <!-- This section will be populated dynamically by JavaScript -->
                        </ul>
                    </div>

                    <!-- Site Number Button and Dropdown -->
                    <button id="dropdownSearchButtonSite" data-dropdown-toggle="dropdownSearchSite" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-gray-900 bg-gray-50 border border-gray-300 rounded-lg" type="button">
                        Site Number
                        <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                        </svg>
                    </button>

                    <!-- Site Number Dropdown menu -->
                    <div id="dropdownSearchSite" class="z-10 hidden bg-white rounded-lg shadow w-60 dark:bg-gray-700">
                        <ul class="h-48 px-3 pb-3 overflow-y-auto text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownSearchButtonSite">
                            <li>
                                <div class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <input id="select-all-site" type="checkbox" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                    <label for="select-all-site" class="w-full ms-2 text-sm font-medium text-gray-900 rounded dark:text-gray-300">Select All</label>
                                </div>
                            </li>
                            <!-- This section will be populated dynamically by JavaScript -->
                        </ul>
                    </div>
                </div>
                
                <div class="flex w-full justify-center items-center gap-2 mb-2">
                    <!-- Test Temperature Button and Dropdown -->
                    <button id="dropdownSearchButtonTemp" data-dropdown-toggle="dropdownSearchTemp" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-gray-900 bg-gray-50 border border-gray-300 rounded-lg" type="button">
                        Test Temperature
                        <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                        </svg>
                    </button>

                    <!-- Test Temperature Dropdown menu -->
                    <div id="dropdownSearchTemp" class="z-10 hidden bg-white rounded-lg shadow w-60 dark:bg-gray-700">
                        <ul class="h-48 px-3 pb-3 overflow-y-auto text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownSearchButtonTemp">
                            <li>
                                <div class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <input id="select-all-temp" type="checkbox" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                    <label for="select-all-temp" class="w-full ms-2 text-sm font-medium text-gray-900 rounded dark:text-gray-300">Select All</label>
                                </div>
                            </li>
                            <!-- This section will be populated dynamically by JavaScript -->
                        </ul>
                    </div>

                    <!-- Test Time Button and Dropdown -->
                    <button id="dropdownSearchButtonTime" data-dropdown-toggle="dropdownSearchTime" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-gray-900 bg-gray-50 border border-gray-300 rounded-lg" type="button">
                        Test Time
                        <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                        </svg>
                    </button>

                    <!-- Test Time Dropdown menu -->
                    <div id="dropdownSearchTime" class="z-10 hidden bg-white rounded-lg shadow w-60 dark:bg-gray-700">
                        <ul class="h-48 px-3 pb-3 overflow-y-auto text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownSearchButtonTime">
                            <li>
                                <div class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <input id="select-all-time" type="checkbox" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                    <label for="select-all-time" class="w-full ms-2 text-sm font-medium text-gray-900 rounded dark:text-gray-300">Select All</label>
                                </div>
                            </li>
                            <!-- This section will be populated dynamically by JavaScript -->
                        </ul>
                    </div>
                </div>

                </div>
        </div>
    </div>
</div>
</form>
<script src="../js/selection.js"></script>

