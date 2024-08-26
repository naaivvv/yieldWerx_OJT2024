<?php
require __DIR__ . '/../connection.php';
include_once('parameter_query.php');

// Function to check if a column exists in a table
function columnExists($conn, $tableName, $columnName) {
    $check_sql = "SELECT 1 
                  FROM INFORMATION_SCHEMA.COLUMNS 
                  WHERE TABLE_NAME = ? AND COLUMN_NAME = ?";
    $params = [$tableName, $columnName];
    $check_stmt = sqlsrv_query($conn, $check_sql, $params);

    if ($check_stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    
    $exists = sqlsrv_fetch_array($check_stmt) ? true : false;
    sqlsrv_free_stmt($check_stmt);
    
    return $exists;
}

// Generate dynamic aliases for the device tables
$join_clauses = [];
$previousAlias = null; // Initialize the previous alias
$aliasIndex = 1; // Start alias index

// This array will store the column alias mappings
$columnAliasMap = [];

foreach ($device_tables as $table) {
    $alias = "d$aliasIndex";

    if (substr($table, -4) === '_001') {
        $join_clauses[] = "LEFT JOIN $table $alias ON w.Wafer_Sequence = $alias.Wafer_Sequence";
    } else {
        if ($previousAlias) {
            $join_clauses[] = "LEFT JOIN $table $alias ON $previousAlias.Die_Sequence = $alias.Die_Sequence";
        } else {
            $join_clauses[] = "LEFT JOIN $table $alias ON w.Wafer_Sequence = $alias.Wafer_Sequence";
        }
    }

    foreach ($filters['tm.Column_Name'] as $columnName) {
        if (columnExists($conn, $table, $columnName)) {
            $columnAliasMap[$columnName][] = "$alias.$columnName";
        }
    }

    $previousAlias = $alias;
    $aliasIndex++;
}

$join_clause = implode(' ', $join_clauses);

$column_list = !empty($filters['tm.Column_Name'])
    ? implode(', ', array_map(function($col) use ($columnAliasMap) {
        if (isset($columnAliasMap[$col]) && !empty($columnAliasMap[$col])) {
            $aliasList = implode(', ', $columnAliasMap[$col]);
            if (count($columnAliasMap[$col]) > 1) {
                return "COALESCE($aliasList) AS $col";
            } else {
                return "$aliasList AS $col";
            }
        }
        return null;
    }, $filters['tm.Column_Name']))
    : '*';

$column_list = implode(', ', array_filter(explode(', ', $column_list)));

$tsql = "SELECT l.Facility_ID, l.Work_Center, l.Part_Type, l.Program_Name, l.Test_Temprature, l.Lot_ID,
                w.Wafer_ID, w.Wafer_Start_Time, w.Wafer_Finish_Time, d1.Unit_Number, d1.X, d1.Y, d1.Head_Number,
                d1.Site_Number, d1.HBin_Number, d1.SBin_Number,
                tm.Column_Name, tm.Test_Name, $column_list
         FROM LOT l
         LEFT JOIN WAFER w ON l.Lot_Sequence = w.Lot_Sequence 
         $join_clause
         LEFT JOIN TEST_PARAM_MAP tm ON tm.Lot_Sequence = l.Lot_Sequence
         LEFT JOIN ProbingSequenceOrder p ON p.probing_sequence = w.probing_sequence
         $where_clause
         $orderByClause";

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
sqlsrv_free_stmt($stmt);

$columns = [
    'Facility_ID', 'Work_Center', 'Part_Type', 'Program_Name', 'Test_Temprature', 'Lot_ID',
    'Wafer_ID', 'Wafer_Start_Time', 'Wafer_Finish_Time', 'Unit_Number', 'X', 'Y', 'Head_Number',
    'Site_Number', 'HBin_Number', 'SBin_Number'
];
$all_columns = array_merge($columns, $filters['tm.Column_Name']);
$headers = array_map(function($column) use ($column_to_test_name_map) {
    return isset($column_to_test_name_map[$column]) ? $column_to_test_name_map[$column] : $column;
}, $all_columns);

// Set the headers to force download
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="extracted_data.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Output the column headings
fputcsv($output, $headers);

// Re-execute query to fetch data for export
$stmt = sqlsrv_query($conn, $tsql, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Output each row of the data
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $csv_row = [];
    foreach ($all_columns as $column) {
        $value = isset($row[$column]) ? $row[$column] : '';
        if ($value instanceof DateTime) {
            $value = $value->format('Y-m-d H:i:s');
        } elseif (is_numeric($value) && floor($value) != $value) {
            $value = number_format($value, 2);
        }
        $csv_row[] = $value;
    }
    fputcsv($output, $csv_row);
}

// Close the output stream
fclose($output);

// Free the statement
sqlsrv_free_stmt($stmt);

// Close the database connection
sqlsrv_close($conn);
?>
