<?php
require __DIR__ . '/../connection.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=wafer_data.csv');

$output = fopen('php://output', 'w');

// Retrieve filters from session if they are not in the current GET request
$filters = [
    "l.Facility_ID" => isset($_GET['facility']) ? $_GET['facility'] : (isset($_SESSION['filters']['l.Facility_ID']) ? $_SESSION['filters']['l.Facility_ID'] : []),
    "l.work_center" => isset($_GET['work_center']) ? $_GET['work_center'] : (isset($_SESSION['filters']['l.work_center']) ? $_SESSION['filters']['l.work_center'] : []),
    "l.part_type" => isset($_GET['device_name']) ? $_GET['device_name'] : (isset($_SESSION['filters']['l.part_type']) ? $_SESSION['filters']['l.part_type'] : []),
    "l.Program_Name" => isset($_GET['test_program']) ? $_GET['test_program'] : (isset($_SESSION['filters']['l.Program_Name']) ? $_SESSION['filters']['l.Program_Name'] : []),
    "l.lot_ID" => isset($_GET['lot']) ? $_GET['lot'] : (isset($_SESSION['filters']['l.lot_ID']) ? $_SESSION['filters']['l.lot_ID'] : []),
    "w.wafer_ID" => isset($_GET['wafer']) ? $_GET['wafer'] : (isset($_SESSION['filters']['w.wafer_ID']) ? $_SESSION['filters']['w.wafer_ID'] : []),
    "tm.Column_Name" => isset($_GET['parameter']) ? $_GET['parameter'] : (isset($_SESSION['filters']['tm.Column_Name']) ? $_SESSION['filters']['tm.Column_Name'] : []),
    "p.abbrev" => isset($_GET['abbrev']) ? $_GET['abbrev'] : (isset($_SESSION['filters']['p.abbrev']) ? $_SESSION['filters']['p.abbrev'] : []),
];

// Ensure l.Program_Name is cast to an array
if (!is_array($filters['l.Program_Name'])) {
    $filters['l.Program_Name'] = (array)$filters['l.Program_Name'];
}

// Generate placeholders for the number of program names in the filter
$programNamePlaceholders = implode(',', array_fill(0, count($filters['l.Program_Name']), '?'));

// Update the table SQL to use IN clause for multiple program names
$table_sql = "SELECT DISTINCT table_name 
              FROM TEST_PARAM_MAP 
              WHERE program_name IN ($programNamePlaceholders)";

// Use the array of program names as parameters for the query
$table_stmt = sqlsrv_query($conn, $table_sql, $filters['l.Program_Name']);
if ($table_stmt === false) {
    die('Query failed: ' . print_r(sqlsrv_errors(), true));
}

$device_tables = [];
while ($table_row = sqlsrv_fetch_array($table_stmt, SQLSRV_FETCH_ASSOC)) {
    $device_tables[] = $table_row['table_name'];
}
sqlsrv_free_stmt($table_stmt);

// Generate dynamic aliases for the device tables
$join_clauses = [];
$previousAlias = null; // Initialize the previous alias
$aliasIndex = 1; // Start alias index

foreach ($device_tables as $table) {
    $alias = "d$aliasIndex";

    // If there is a previous alias, join on Die_Sequence instead of Wafer_Sequence
    if ($previousAlias) {
        $join_clauses[] = "JOIN $table $alias ON $previousAlias.Die_Sequence = $alias.Die_Sequence";
    } else {
        // For the first table, join on Wafer_Sequence
        $join_clauses[] = "JOIN $table $alias ON w.Wafer_Sequence = $alias.Wafer_Sequence";
    }

    // Update the previous alias and increment the index
    $previousAlias = $alias;
    $aliasIndex++;
}

$join_clause = implode(' ', $join_clauses);

// Prepare SQL filters
$sql_filters = [];
$params = [];
foreach ($filters as $key => $values) {
    if (!empty($values)) {
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $sql_filters[] = "$key IN ($placeholders)";
        $params = array_merge($params, $values);
    }
}

// Create the WHERE clause if filters exist
$where_clause = '';
if (!empty($sql_filters)) {
    $where_clause = 'WHERE ' . implode(' AND ', $sql_filters);
}

// Dynamically construct the column part of the SQL query
$column_list = !empty($filters['tm.Column_Name']) 
    ? implode(', ', array_map(function($col) { 
        return "$col"; 
      }, $filters['tm.Column_Name'])) 
    : '*';

// Retrieve all records with filters
$tsql = "SELECT l.Facility_ID, l.Work_Center, l.Part_Type, l.Program_Name, l.Test_Temprature, l.Lot_ID,
                w.Wafer_ID, w.Wafer_Start_Time, w.Wafer_Finish_Time, d1.Unit_Number, d1.X, d1.Y, d1.Head_Number,
                d1.Site_Number, d1.HBin_Number, d1.SBin_Number,
                tm.Column_Name, tm.Test_Name, $column_list
         FROM WAFER w 
         $join_clause
         JOIN LOT l ON l.Lot_Sequence = w.Lot_Sequence
         JOIN TEST_PARAM_MAP tm ON tm.Lot_Sequence = l.Lot_Sequence
         JOIN ProbingSequenceOrder p on p.probing_sequence = w.probing_sequence
         $where_clause";

$stmt = sqlsrv_query($conn, $tsql, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Create an array to map Column_Name to Test_Name
$column_to_test_name_map = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    if (!empty($row['Column_Name']) && !empty($row['Test_Name'])) {
        $column_to_test_name_map[$row['Column_Name']] = $row['Test_Name'];
    }
}
sqlsrv_free_stmt($stmt); // Free the statement here after fetching the mapping

// Merge static columns with dynamic columns and replace with test names
$columns = [
    'Facility_ID', 'Work_Center', 'Part_Type', 'Program_Name', 'Test_Temprature', 'Lot_ID',
    'Wafer_ID', 'Wafer_Start_Time', 'Wafer_Finish_Time', 'Unit_Number', 'X', 'Y', 'Head_Number',
    'Site_Number', 'HBin_Number', 'SBin_Number'
];
$all_columns = array_merge($columns, $filters['tm.Column_Name']);
$headers = array_map(function($column) use ($column_to_test_name_map) {
    return isset($column_to_test_name_map[$column]) ? $column_to_test_name_map[$column] : $column;
}, $all_columns);

// Output headers to CSV
fputcsv($output, $headers);

// Fetch all records and output to CSV
$stmt = sqlsrv_query($conn, $tsql, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $rowData = [];
    foreach ($all_columns as $column) {
        if ($row[$column] instanceof DateTime) {
            // Format DateTime objects as strings before adding them to the row
            $rowData[] = $row[$column]->format('Y-m-d H:i:s');
        } else {
            $rowData[] = isset($row[$column]) ? $row[$column] : '';
        }
    }
    fputcsv($output, $rowData);
}

sqlsrv_free_stmt($stmt); // Free the statement after fetching all rows

fclose($output);
exit;
?>
