<?php 

require __DIR__ . '/../connection.php';

// Retrieve or set xIndex
if (isset($_GET['x'])) {
    $_SESSION['xIndex'] = $_GET['x'];
}
$xIndex = isset($_SESSION['xIndex']) ? $_SESSION['xIndex'] : null;

// Retrieve or set yIndex
if (isset($_GET['y'])) {
    $_SESSION['yIndex'] = $_GET['y'];
}
$yIndex = isset($_SESSION['yIndex']) ? $_SESSION['yIndex'] : null;

// Retrieve or set orderX
if (isset($_GET['order-x'])) {
    $_SESSION['orderX'] = $_GET['order-x'];
}
$orderX = isset($_SESSION['orderX']) ? $_SESSION['orderX'] : null;

// Retrieve or set orderY
if (isset($_GET['order-y'])) {
    $_SESSION['orderY'] = $_GET['order-y'];
}
$orderY = isset($_SESSION['orderY']) ? $_SESSION['orderY'] : null;

$columnsGroup = [
    'l.Facility_ID', 'd1.Head_Number', 'd1.HBin_Number', 'l.Lot_ID', 'l.Part_Type', 'p.abbrev', 'l.Program_Name', 
    'd1.SBin_Number', 'd1.Site_Number', 'l.Test_Temprature', 'd1.Test_Time', 'd1.Tests_Executed',
    'd1.Unit_Number', 'w.Wafer_Finish_Time', 'w.Wafer_ID', 'w.Wafer_Start_Time', 'l.Work_Center', 
    'd1.X', 'd1.Y', 'l.Program_Name'
];

$xColumn = $xIndex !== null && isset($columnsGroup[$xIndex]) ? $columnsGroup[$xIndex] : null;
$yColumn = $yIndex !== null && isset($columnsGroup[$yIndex]) ? $columnsGroup[$yIndex] : null;

// Retrieve or set filters
$filters = [
    "l.Facility_ID" => isset($_GET['facility']) ? $_GET['facility'] : (isset($_SESSION['facility']) ? $_SESSION['facility'] : []),
    "l.work_center" => isset($_GET['work_center']) ? $_GET['work_center'] : (isset($_SESSION['work_center']) ? $_SESSION['work_center'] : []),
    "l.part_type" => isset($_GET['device_name']) ? $_GET['device_name'] : (isset($_SESSION['device_name']) ? $_SESSION['device_name'] : []),
    "l.Program_Name" => isset($_GET['test_program']) ? $_GET['test_program'] : (isset($_SESSION['test_program']) ? $_SESSION['test_program'] : []),
    "l.lot_ID" => isset($_GET['lot']) ? $_GET['lot'] : (isset($_SESSION['lot']) ? $_SESSION['lot'] : []),
    "w.wafer_ID" => isset($_GET['wafer']) ? $_GET['wafer'] : (isset($_SESSION['wafer']) ? $_SESSION['wafer'] : []),
    "tm.Column_Name" => isset($_GET['parameter']) ? $_GET['parameter'] : (isset($_SESSION['parameter']) ? $_SESSION['parameter'] : []),
    "p.abbrev" => isset($_GET['abbrev']) ? $_GET['abbrev'] : (isset($_SESSION['abbrev']) ? $_SESSION['abbrev'] : [])
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

// Update session with the current filters
foreach ($filters as $key => $values) {
    if (!empty($values)) {
        $_SESSION[str_replace('.', '_', $key)] = $values;
    }
}

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

$parameters = $filters['tm.Column_Name'];
$data = [];
$groupedData = [];


$combinations = [];
foreach ($parameters as $i => $xParam) {
    for ($j = $i + 1; $j < count($parameters); $j++) {
        $combinations[] = [$xParam, $parameters[$j]];
    }
}

foreach ($combinations as $combination) {

    // Generate dynamic aliases for the device tables
    $join_clauses = [];
    $aliasIndex = 1; // Start alias index
    $select_columns = [];


    foreach ($device_tables as $table) {
        $alias = "d$aliasIndex";
        $join_clauses[] = "JOIN $table $alias ON w.Wafer_Sequence = $alias.Wafer_Sequence";
        $aliasIndex++;
    }
    
    $join_clause = implode(' ', $join_clauses);

    $globalCounters = [
        'all' => 0,
        'xcol' => [],
        'ycol' => []
    ];

    $xLabel = $combination[0];
    $yLabel = $combination[1];

    $combinationKey = implode('_', $combination);

    $testNameQuery = "SELECT test_name FROM TEST_PARAM_MAP WHERE Column_Name = ?";
    $testNameStmtX = sqlsrv_query($conn, $testNameQuery, [$xLabel]);
    $testNameX = sqlsrv_fetch_array($testNameStmtX, SQLSRV_FETCH_ASSOC)['test_name'];

    $testNameStmtY = sqlsrv_query($conn, $testNameQuery, [$yLabel]);
    $testNameY = sqlsrv_fetch_array($testNameStmtY, SQLSRV_FETCH_ASSOC)['test_name'];

    sqlsrv_free_stmt($testNameStmtX);
    sqlsrv_free_stmt($testNameStmtY);

    $tsql = "
    SELECT 
        {$xLabel} AS X, 
        {$yLabel} AS Y, 
        " . ($xColumn ? "$xColumn AS xGroup" : "'No xGroup' AS xGroup") . ", 
        " . ($yColumn ? "$yColumn AS yGroup" : "'No yGroup' AS yGroup") . "
    FROM wafer w
    $join_clause
    JOIN LOT l ON l.Lot_Sequence = w.Lot_Sequence
    JOIN TEST_PARAM_MAP tm ON tm.Lot_Sequence = l.Lot_Sequence
    JOIN ProbingSequenceOrder p ON p.probing_sequence = w.probing_sequence
    $where_clause
    $orderByClause";

    $stmt = sqlsrv_query($conn, $tsql, $params);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $xGroup = $row['xGroup'];
        $yGroup = $row['yGroup'];
        $xValue = floatval($row['X']);
        $yValue = floatval($row['Y']);

        if ($xColumn && $yColumn) {
            $groupedData[$combinationKey][$yGroup][$xGroup][] = ['x' => $xValue, 'y' => $yValue];
        } elseif ($xColumn && !$yColumn) {
            $groupedData[$combinationKey][$xGroup][$yGroup][] = ['x' => $xValue, 'y' => $yValue];
        } elseif (!$xColumn && $yColumn) {
            $groupedData[$combinationKey][$yGroup][] = ['x' => $xValue, 'y' => $yValue];
        } else {
            $groupedData[$combinationKey]['all'][] = ['x' => $xValue, 'y' => $yValue];
        }
    }

    sqlsrv_free_stmt($stmt);
}

$numDistinctGroups = count($groupedData);
?>
