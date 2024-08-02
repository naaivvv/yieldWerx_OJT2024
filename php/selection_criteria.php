<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3x3 Layout with Tailwind CSS and jQuery</title>
    <link rel="stylesheet" href="../src/output.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <div class="grid grid-cols-3 gap-4">
            <!-- Row 1 -->
            <div class="col-span-1">
                <label for="facility" class="block mb-2 text-sm font-medium text-gray-900">Facility</label>
                <select id="facility" name="facility" multiple class="block w-full p-2.5 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></select>
            </div>
            <div class="col-span-1">
                <label for="work_center" class="block mb-2 text-sm font-medium text-gray-900">Work Center</label>
                <select id="work_center" name="work_center" class="block w-full p-2.5 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></select>
            </div>
            <div class="col-span-1">
                <label for="device_name" class="block mb-2 text-sm font-medium text-gray-900">Device Name</label>
                <select id="device_name" name="device_name" class="block w-full p-2.5 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></select>
            </div>
            <!-- Row 2 -->
            <div class="col-span-1">
                <label for="test_program" class="block mb-2 text-sm font-medium text-gray-900">Test Program</label>
                <select id="test_program" name="test_program" class="block w-full p-2.5 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></select>
            </div>
            <div class="col-span-1">
                <label for="lot" class="block mb-2 text-sm font-medium text-gray-900">Lot</label>
                <select id="lot" name="lot" class="block w-full p-2.5 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></select>
            </div>
            <div class="col-span-1">
                <label for="wafer" class="block mb-2 text-sm font-medium text-gray-900">Wafer</label>
                <select id="wafer" name="wafer" class="block w-full p-2.5 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></select>
            </div>
            <!-- Row 3 -->
            <div class="col-span-3">
                <label for="parameter" class="block mb-2 text-sm font-medium text-gray-900">Parameter</label>
                <select id="parameter" name="parameter[]" multiple class="block w-full p-2.5 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></select>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#parameter').select2();

            // Fetch options from the server
            $.ajax({
                url: 'fetch_options.php',
                type: 'GET',
                success: function(data) {
                    var options = JSON.parse(data);
                    options.forEach(function(option) {
                        $('#facility').append(new Option(option.facility_id, option.facility_id));
                        $('#work_center').append(new Option(option.work_center, option.work_center));
                        $('#device_name').append(new Option(option.part_type, option.part_type));
                        $('#test_program').append(new Option(option.program_name, option.program_name));
                        $('#lot').append(new Option(option.lot_id, option.lot_id));
                        $('#wafer').append(new Option(option.wafer_id, option.wafer_id));
                    });
                }
            });
        });
    </script>
</body>
</html>
