<?php
require __DIR__ . '/../connection.php';

include_once('parameter_query.php');

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

$columnsGroup = [
    'l.Facility_ID', 'd1.Head_Number', 'd1.HBin_Number', 'l.Lot_ID', 'l.Part_Type', 'p.abbrev', 'l.Program_Name', 
    'd1.SBin_Number', 'd1.Site_Number', 'l.Test_Temprature', 'd1.Test_Time', 'd1.Tests_Executed',
    'd1.Unit_Number', 'w.Wafer_Finish_Time', 'w.Wafer_ID', 'w.Wafer_Start_Time', 'l.Work_Center', 
    'd1.X', 'd1.Y', 'l.Program_Name'
];

// Count total number of records with filters
$count_sql = "SELECT COUNT(w.wafer_ID) AS total 
              FROM WAFER w
              $join_clause
              JOIN LOT l ON l.Lot_Sequence = w.Lot_Sequence
              JOIN TEST_PARAM_MAP tm ON tm.Lot_Sequence = l.Lot_Sequence
              JOIN ProbingSequenceOrder p on p.probing_sequence = w.probing_sequence
              $where_clause";  // Append WHERE clause if it exists

// echo "<pre>$count_sql</pre>";
$count_stmt = sqlsrv_query($conn, $count_sql, $params);
if ($count_stmt === false) {
    die('Query failed: ' . print_r(sqlsrv_errors(), true));
}
$total_rows = sqlsrv_fetch_array($count_stmt, SQLSRV_FETCH_ASSOC)['total'];
sqlsrv_free_stmt($count_stmt); // Free the count statement here

// Dynamically construct the column part of the SQL query
$column_list = !empty($filters['tm.Column_Name']) 
    ? implode(', ', array_map(function($col){ 
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
         $where_clause
         $orderByClause";
// echo "<pre>$tsql</pre>";
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
            <form id="search-form" class="flex items-center max-w-sm mb-4">   
                <label for="simple-search" class="sr-only">Search</label>
                <div class="relative w-full">
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-gray-500 dark:text-gray-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                        </svg>

                    </div>
                    <input type="text" id="simple-search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Search here..." required />
                </div>
                <button type="submit" class="p-2.5 ms-2 text-sm font-medium text-white bg-blue-700 rounded-lg border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                    </svg>
                    <span class="sr-only">Search</span>
                </button>
            </form>
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
