<?php
require __DIR__ . '/../connection.php';

$xIndex = isset($_GET['x']) ? $_GET['x'] : null;
$yIndex = isset($_GET['y']) ? $_GET['y'] : null;

$orderX = isset($_GET['order-x']) ? $_GET['order-x'] : null;
$orderY = isset($_GET['order-y']) ? $_GET['order-y'] : null;

$columns = [
    'Facility_ID', 'Work_Center', 'Part_Type', 'Program_Name', 'Test_Temprature', 'Lot_ID',
    'Wafer_ID', 'Wafer_Start_Time', 'Wafer_Finish_Time', 'Unit_Number', 'X', 'Y', 'Head_Number',
    'Site_Number', 'HBin_Number', 'SBin_Number'
];

$xColumn = $xIndex !== null && isset($columns[$xIndex]) ? $columns[$xIndex] : null;
$yColumn = $yIndex !== null && isset($columns[$yIndex]) ? $columns[$yIndex] : null;

$chart = isset($_GET['chart']) ? $_GET['chart'] : null;

// Filters from selection_criteria.php
$filters = [
    "Facility_ID" => isset($_GET['facility']) ? $_GET['facility'] : [],
    "Work_Center" => isset($_GET['work_center']) ? $_GET['work_center'] : [],
    "Part_Type" => isset($_GET['device_name']) ? $_GET['device_name'] : [],
    "Program_Name" => isset($_GET['test_program']) ? $_GET['test_program'] : [],
    "Lot_ID" => isset($_GET['lot']) ? $_GET['lot'] : [],
    "Wafer_ID" => isset($_GET['wafer']) ? $_GET['wafer'] : [],
    "Column_Name" => isset($_GET['parameter']) ? $_GET['parameter'] : [],
    "abbrev" => isset($_GET['abbrev']) ? $_GET['abbrev'] : []
];

$sql_filters = [];
$params = [];

// Build dynamic WHERE clause
foreach ($filters as $column => $values) {
    if (!empty($values)) {
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $sql_filters[] = "$column IN ($placeholders)";
        $params = array_merge($params, $values);
    }
}

$whereClause = !empty($sql_filters) ? 'WHERE ' . implode(' AND ', $sql_filters) : '';

// Build dynamic ORDER BY clause
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

// Dynamically construct the column part of the SQL query
$column_list = !empty($filters['Column_Name']) ? implode(', ', array_map(function($col) { return "$col"; }, $filters['Column_Name'])) : '*';

// Retrieve all records with dynamic filters
$tsql = "
    SELECT *
    FROM (
        SELECT l.Facility_ID, l.Work_Center, l.Part_Type, tm.Program_Name, l.Test_Temprature, l.Lot_ID,
                w.Wafer_ID, w.Wafer_Start_Time, w.Wafer_Finish_Time, Unit_Number, X, Y, Head_Number,
                Site_Number, HBin_Number, SBin_Number, tm.Column_Name, tm.Test_Name, p.abbrev
         FROM WAFER w 
         JOIN DEVICE_1_CP1_V1_0_001 ON w.Wafer_Sequence = DEVICE_1_CP1_V1_0_001.Wafer_Sequence
         JOIN LOT l ON l.Lot_Sequence = w.Lot_Sequence
         JOIN TEST_PARAM_MAP tm ON tm.Lot_Sequence = l.Lot_Sequence
         JOIN ProbingSequenceOrder p on p.probing_sequence = w.probing_sequence

         UNION ALL

         SELECT l.Facility_ID, l.Work_Center, l.Part_Type, tm.Program_Name, l.Test_Temprature, l.Lot_ID,
                w.Wafer_ID, w.Wafer_Start_Time, w.Wafer_Finish_Time, Unit_Number, X, Y, Head_Number,
                Site_Number, HBin_Number, SBin_Number, tm.Column_Name, tm.Test_Name, p.abbrev
         FROM WAFER w 
         JOIN DEVICE_1_CP1_V1_0_002 ON w.Wafer_Sequence = DEVICE_1_CP1_V1_0_002.Wafer_Sequence
         JOIN LOT l ON l.Lot_Sequence = w.Lot_Sequence
         JOIN TEST_PARAM_MAP tm ON tm.Lot_Sequence = l.Lot_Sequence
         JOIN ProbingSequenceOrder p on p.probing_sequence = w.probing_sequence
    ) AS combined_results
    $whereClause
";

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
$all_columns = array_merge($columns, $filters['Column_Name']);
$headers = array_map(function($column) use ($column_to_test_name_map) {
    return isset($column_to_test_name_map[$column]) ? $column_to_test_name_map[$column] : $column;
}, $all_columns);
?>
