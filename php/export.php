<?php
require __DIR__ . '/../connection.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=wafer_data.csv');

$output = fopen('php://output', 'w');

// Add column headers
$columns = ['Wafer_Sequence', 'Lot_Sequence', 'Wafer_ID', 'Wafer_Start_Time', 'Wafer_Finish_Time'];
fputcsv($output, $columns);

// Fetch all data from the WAFER table
$tsql = "SELECT * FROM WAFER";
$stmt = sqlsrv_query($conn, $tsql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $csv_row = [];
    foreach ($columns as $column) {
        $value = $row[$column] ?? '';
        if ($value instanceof DateTime) {
            $csv_row[] = $value->format('Y-m-d H:i:s');
        } else {
            $csv_row[] = $value;
        }
    }
    fputcsv($output, $csv_row);
}

fclose($output);

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
