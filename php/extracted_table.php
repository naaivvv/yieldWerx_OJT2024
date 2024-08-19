<?php
require __DIR__ . '/../connection.php';

$xIndex = isset($_GET['x']) ? $_GET['x'] : (isset($_SESSION['xIndex']) ? $_SESSION['xIndex'] : null);
$yIndex = isset($_GET['y']) ? $_GET['y'] : (isset($_SESSION['yIndex']) ? $_SESSION['yIndex'] : null);

$orderX = isset($_GET['order-x']) ? $_GET['order-x'] : (isset($_SESSION['orderX']) ? $_SESSION['orderX'] : null);
$orderY = isset($_GET['order-y']) ? $_GET['order-y'] : (isset($_SESSION['orderY']) ? $_SESSION['orderY'] : null);

$chart = isset($_GET['chart']) ? $_GET['chart'] : (isset($_SESSION['chart']) ? $_SESSION['chart'] : null);

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

// echo "<pre>$table_sql</pre>";

$device_tables = [];
while ($table_row = sqlsrv_fetch_array($table_stmt, SQLSRV_FETCH_ASSOC)) {
    $device_tables[] = $table_row['table_name'];
}
sqlsrv_free_stmt($table_stmt);

// Generate dynamic aliases for the device tables
$join_clauses = [];
$aliasIndex = 1; // Start alias index

foreach ($device_tables as $table) {
    $alias = "d$aliasIndex";
    $join_clauses[] = "JOIN $table $alias ON w.Wafer_Sequence = $alias.Wafer_Sequence";
    $aliasIndex++; // Increment alias index for the next table
}

$join_clause = implode(' ', $join_clauses);

$columnsGroup = [
    'l.Facility_ID', 'd1.Head_Number', 'd1.HBin_Number', 'l.Lot_ID', 'l.Part_Type', 'p.abbrev', 'l.Program_Name', 
    'd1.SBin_Number', 'd1.Site_Number', 'l.Test_Temprature', 'd1.Test_Time', 'd1.Tests_Executed',
    'd1.Unit_Number', 'w.Wafer_Finish_Time', 'w.Wafer_ID', 'w.Wafer_Start_Time', 'l.Work_Center', 
    'd1.X', 'd1.Y', 'l.Program_Name'
];

$xColumn = $xIndex !== null && isset($columnsGroup[$xIndex]) ? $columnsGroup[$xIndex] : null;
$yColumn = $yIndex !== null && isset($columnsGroup[$yIndex]) ? $columnsGroup[$yIndex] : null;

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

$orderDirectionX = $orderX == 1 ? 'DESC' : 'ASC';
$orderDirectionY = $orderY == 1 ? 'DESC' : 'ASC';

$orderByClause = '';
if ($xColumn && $yColumn) {
    $orderByClause = "ORDER BY $xColumn $orderDirectionX, $yColumn $orderDirectionY";
} elseif ($xColumn && !$yColumn) {
    $orderByClause = "ORDER BY $xColumn $orderDirectionX";
} elseif (!$xColumn && $yColumn) {
    $orderByClause = "ORDER BY $yColumn $orderDirectionY";
}

// Count total number of records with filters
$count_sql = "SELECT COUNT(w.wafer_ID) AS total 
              FROM WAFER w
              $join_clause
              JOIN LOT l ON l.Lot_Sequence = w.Lot_Sequence
              JOIN TEST_PARAM_MAP tm ON tm.Lot_Sequence = l.Lot_Sequence
              JOIN ProbingSequenceOrder p on p.probing_sequence = w.probing_sequence
              $where_clause";  // Append WHERE clause if it exists

echo "<pre>$count_sql</pre>";
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
echo "<pre>$tsql</pre>";
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
