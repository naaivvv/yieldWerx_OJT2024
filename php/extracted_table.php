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

    // Check if the table name ends with '_001'
    if (substr($table, -4) === '_001') {
        // Join on Wafer_Sequence
        $join_clauses[] = "LEFT JOIN $table $alias ON w.Wafer_Sequence = $alias.Wafer_Sequence";
    } else {
        // Otherwise, join on Die_Sequence
        if ($previousAlias) {
            $join_clauses[] = "LEFT JOIN $table $alias ON $previousAlias.Die_Sequence = $alias.Die_Sequence";
        } else {
            // If there is no previous alias, join on Wafer_Sequence (fallback)
            $join_clauses[] = "LEFT JOIN $table $alias ON w.Wafer_Sequence = $alias.Wafer_Sequence";
        }
    }

    // Check and map all column names in the current table to the alias
    foreach ($filters['tm.Column_Name'] as $columnName) {
        if (columnExists($conn, $table, $columnName)) {
            $columnAliasMap[$columnName][] = "$alias.$columnName";
        }
    }

    // Update the previous alias and increment the index
    $previousAlias = $alias;
    $aliasIndex++;
}

$join_clause = implode(' ', $join_clauses);

// Dynamically construct the column part of the SQL query
$column_list = !empty($filters['tm.Column_Name'])
    ? implode(', ', array_map(function($col) use ($columnAliasMap) {
        if (isset($columnAliasMap[$col]) && !empty($columnAliasMap[$col])) {
            $aliasList = implode(', ', $columnAliasMap[$col]);
            // If there's only one alias, don't use COALESCE
            if (count($columnAliasMap[$col]) > 1) {
                return "COALESCE($aliasList) AS $col";
            } else {
                return "$aliasList AS $col";
            }
        }
        return null;
    }, $filters['tm.Column_Name']))
    : '*';

// Remove any null entries from $column_list
$column_list = implode(', ', array_filter(explode(', ', $column_list)));

// Count total number of records with filters
$count_sql = "SELECT COUNT(w.wafer_ID) AS total 
              FROM LOT l
              LEFT JOIN WAFER w ON w.Lot_Sequence = l.Lot_Sequence
              LEFT JOIN TEST_PARAM_MAP tm ON tm.Lot_Sequence = l.Lot_Sequence
              LEFT JOIN ProbingSequenceOrder p ON p.probing_sequence = w.probing_sequence
              $join_clause
              $where_clause";  // Append WHERE clause if it exists

$count_stmt = sqlsrv_query($conn, $count_sql, $params);
if ($count_stmt === false) {
    die('Query failed: ' . print_r(sqlsrv_errors(), true));
}
$total_rows = sqlsrv_fetch_array($count_stmt, SQLSRV_FETCH_ASSOC)['total'];
sqlsrv_free_stmt($count_stmt); // Free the count statement here

// Retrieve all records with filters
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

// echo "<pre>$tsql</pre>";
$stmt = sqlsrv_query($conn, $tsql, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Continue with the rest of the logic as before

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
?>


<style>
    .table-container {
        overflow-y: auto;
        overflow-x: auto;
        max-height: 65vh;
    }
    .max-w-5xl {
            max-width: 64rem /* 1024px */;
        }
</style>
<div class="max-w-5xl p-4 my-4 flex items-center justify-center mx-auto">
    <div class="w-full">
        <?php include('received_parameters.php'); ?>
    </div>
</div>
<div class="flex justify-center items-center h-full">
    <div class="w-full max-w-7xl p-6 rounded-lg shadow-lg bg-white mt-6">
        <div class="flex justify-between items-center">
            <div></div>
            <div class="mb-4 text-right">
                <?php if ($chart == 1): ?>
                    <a href="graph.php?<?php echo http_build_query($_GET); ?>" class="px-4 py-2 bg-yellow-400 text-white rounded mr-2">
                        <i class="fa-solid fa-chart-area"></i>&nbsp;XY Scatter Plot
                    </a>
                <?php else: ?>
                    <a href="line_chart.php?<?php echo http_build_query($_GET); ?>" class="px-4 py-2 bg-yellow-400 text-white rounded mr-2">
                        <i class="fa-solid fa-chart-line"></i>&nbsp;Line Chart
                    </a>
                <?php endif; ?>
                <a href="export.php?<?php echo http_build_query($_GET); ?>" class="px-5 py-2 bg-green-500 text-white rounded">
                    <i class="fa-regular fa-file-excel"></i>&nbsp;Export
                </a>
            </div>
        </div>
       
        <h1 class="text-start text-2xl font-bold mb-4">Data Extraction [Total: <?php echo $total_rows; ?>]</h1>
        <div class="table-container">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <?php
                        foreach ($headers as $header) {
                            echo "<th class='px-2 py-2 whitespace-nowrap border'>$header</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = sqlsrv_query($conn, $tsql, $params); // Re-execute query to fetch data for display
                    if ($stmt === false) {
                        die(print_r(sqlsrv_errors(), true));
                    }
                    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                        echo "<tr class='bg-white dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600'>";
                        foreach ($all_columns as $column) {
                            $value = isset($row[$column]) ? $row[$column] : '';
                            if ($value instanceof DateTime) {
                                $value = $value->format('Y-m-d H:i:s');
                            } elseif (is_numeric($value) && floor($value) != $value) {
                                $value = number_format($value, 2);
                            }
                            echo "<td class='px-2 py-2 whitespace-nowrap border'>$value</td>";
                        }
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
