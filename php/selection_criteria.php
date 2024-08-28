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
    .hover\:text-red-500:hover {
        --tw-text-opacity: 1;
        color: rgb(240 82 82 / var(--tw-text-opacity)) /* #f05252 */;
    }
</style>
<div class="container mx-auto">
    <h1 class="text-center text-2xl font-bold mb-6 w-full">Selection Criteria</h1>
    <form action="dashboard.php" method="POST" id="criteriaForm">
    <div class="flex flex-row justify-between gap-4">
        <div class="bg-white rounded-lg shadow-lg py-12 px-6 flex-1">

                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="text-gray-800">
                        <label for="facility" class="block text-md font-medium multiple-select">
                        <div class="flex items-center justify-between">Facility
                            <button type="button" id="resetFacility" class="bg-transparent rounded-lg hover:text-red-500"><i class="fa-solid fa-delete-left"></i></button>
                        </div>
                        </label>
                        <select size="5" id="facility" name="facility[]" class="text-gray-900 mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple required>
                            <?php foreach ($facilities as $facility): ?>
                                <option value="<?= $facility ?>"><?= $facility ?></option>
                            <?php endforeach; ?>
                        </select>
                        
                    </div>

                    <div class="text-gray-600 relative">
                    <svg id="loading-spinner-wc" aria-hidden="true" class="hidden absolute bottom-3 right-6 w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/></svg>
                    <label for="work_center" class="block text-md font-medium multiple-select">
                        <div class="flex items-center justify-between">Work Center
                            <button type="button" id="resetWorkCenter" class="bg-transparent rounded-lg hover:text-red-500"><i class="fa-solid fa-delete-left"></i></button>
                        </div>
                        </label>
                        <select size="5" id="work_center" name="work_center[]" class="text-gray-900 mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple required>
                            <!-- Options will be populated based on facility selection -->
                        </select>
                    </div>

                    <div class="text-blue-900 relative">
                    <svg id="loading-spinner-dn" aria-hidden="true" class="hidden absolute bottom-3 right-6 w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/></svg>
                        <label for="device_name" class="block text-md font-medium multiple-select">
                        <div class="flex items-center justify-between">Device Name
                            <button type="button" id="resetDeviceName" class="bg-transparent rounded-lg hover:text-red-500"><i class="fa-solid fa-delete-left"></i></button>
                        </div>
                        </label>
                        <select size="5" id="device_name" name="device_name[]" class="text-gray-900 mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple required>
                            <!-- Options will be populated based on work center selection -->
                        </select>
                    </div>

                    <div class="text-blue-800 relative">
                    <svg id="loading-spinner-tp" aria-hidden="true" class="hidden absolute bottom-3 right-6 w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/></svg>
                        <label for="test_program" class="block text-md font-medium multiple-select">
                        <div class="flex items-center justify-between">Test Program
                            <button type="button" id="resetTestProgram" class="bg-transparent rounded-lg hover:text-red-500"><i class="fa-solid fa-delete-left"></i></button>
                        </div>
                        </label>
                        <select size="5" id="test_program" name="test_program[]" class="text-gray-900 mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple required>
                            <!-- Options will be populated based on device name selection -->
                        </select>
                    </div>

                    <div class=" text-blue-700 relative">
                    <svg id="loading-spinner-l" aria-hidden="true" class="hidden absolute bottom-3 right-6 w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/></svg>
                        <label for="lot" class="block text-md font-medium multiple-select">
                        <div class="flex items-center justify-between">Lot
                            <button type="button" id="resetLot" class="bg-transparent  rounded-lg hover:text-red-500"><i class="fa-solid fa-delete-left"></i></button>
                        </div>
                        </label>
                        <select size="5" id="lot" name="lot[]" class="text-gray-900 mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple required>
                            <!-- Options will be populated based on test program selection -->
                        </select>
                    </div>

                    <div class=" text-blue-600 relative">
                    <svg id="loading-spinner-w" aria-hidden="true" class="hidden absolute bottom-3 right-6 w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/></svg>
                        <label for="wafer" class="block text-md font-medium multiple-select">
                        <div class="flex items-center justify-between">Wafer
                            <button type="button" id="resetWafer" class="bg-transparent rounded-lg hover:text-red-500"><i class="fa-solid fa-delete-left"></i></button>
                        </div>
                        </label>
                        <select size="5" id="wafer" name="wafer[]" class="text-gray-900 mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple required>
                            <!-- Options will be populated based on lot selection -->
                        </select>
                    </div>

                    <div class="col-span-3">
                        <div class="flex justify-between gap-5">
                            <div class="flex flex-1 flex-col text-blue-500 relative">
                            <svg id="loading-spinner-x" aria-hidden="true" class="hidden absolute bottom-3 right-6 w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/></svg>
                                <label for="parameter-x" class="block text-md font-medium multiple-select">
                                <div class="flex items-center justify-between">Parameter X
                                    <button type="button" id="resetParameterX" class="bg-transparent rounded-lg hover:text-red-500"><i class="fa-solid fa-delete-left"></i></button>
                                </div>
                                </label>
                                <select size="5" id="parameter-x" name="parameter-x[]" class="text-gray-900 mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple>
                                    <!-- Options will be populated based on wafer selection -->
                                </select>
                            </div>
                            <div class="flex flex-1 flex-col text-blue-400 relative">
                            <svg id="loading-spinner-y" aria-hidden="true" class="hidden absolute bottom-3 right-6 w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/></svg>
                                <label for="parameter-y" class="block text-md font-medium multiple-select">
                                <div class="flex items-center justify-between">Parameter Y
                                    <button type="button" id="resetParameterY" class="bg-transparent rounded-lg hover:text-red-500"><i class="fa-solid fa-delete-left"></i></button>
                                </div>
                                </label>
                                <select size="5" id="parameter-y" name="parameter-y[]" class="text-gray-900 mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" multiple>
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
                    <button type="button" id="resetButton" class="px-4 py-2 bg-red-500 dark:hover:bg-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 text-white rounded-lg">Reset&nbsp;<i class="fa-solid fa-delete-left"></i></button>
                </div>

                <?php include('chart_type_modal.php'); ?>
            
        </div>
        <div class="bg-white rounded-lg shadow-lg py-12 px-6">
            <div class="flex items-center justify-end mb-2">
            <button type="button" id="resetFilters" class="bg-transparent rounded-lg hover:text-red-500"><i class="fa-solid fa-delete-left"></i></button>
            </div>
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

