<?php
require __DIR__ . '/../connection.php';

// Retrieve the form data
$tsql = isset($_POST['tsql']) ? $_POST['tsql'] : null;
$all_columns = isset($_POST['all_columns']) ? json_decode($_POST['all_columns'], true) : [];
$headers = isset($_POST['headers']) ? json_decode($_POST['headers'], true) : [];  // Added to capture headers
$xIndex = isset($_POST['xIndex']) ? $_POST['xIndex'] : null;
$yIndex = isset($_POST['yIndex']) ? $_POST['yIndex'] : null;
$orderX = isset($_POST['orderX']) ? $_POST['orderX'] : null;
$orderY = isset($_POST['orderY']) ? $_POST['orderY'] : null;
$parameterX = isset($_POST['parameterX']) ? json_decode($_POST['parameterX'], true) : [];
$parameterY = isset($_POST['parameterY']) ? json_decode($_POST['parameterY'], true) : [];
$filters = isset($_POST['filters']) ? json_decode($_POST['filters'], true) : [];
$device_tables = isset($_POST['deviceTables']) ? json_decode($_POST['deviceTables'], true) : [];
$where_clause = isset($_POST['whereClause']) ? $_POST['whereClause'] : '';
$orderByClause = isset($_POST['orderByClause']) ? $_POST['orderByClause'] : '';
$params = isset($_POST['params']) ? json_decode($_POST['params'], true) : [];

// Validate params are correctly populated
if (!$params) {
    die('Params are not defined or empty.');
}

// Prepare the SQL query statement with the provided parameters
$stmt = sqlsrv_query($conn, $tsql, $params);

if ($stmt === false) {
    die('Query failed: ' . print_r(sqlsrv_errors(), true));
}

// Prepare the CSV output
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="export.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Output the headers
fputcsv($output, $headers);  // Use the headers array

// Output the rows
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $dataRow = [];
    foreach ($all_columns as $all_column) {
        $value = isset($row[$all_column]) ? $row[$all_column] : '';
        
        // Check if the value is a DateTime object and format it
        if ($value instanceof DateTime) {
            $value = $value->format('Y-m-d H:i:s');
        }

        $dataRow[] = $value;
    }
    fputcsv($output, $dataRow);
}

// Close the statement and the output stream
sqlsrv_free_stmt($stmt);
fclose($output);
?>
