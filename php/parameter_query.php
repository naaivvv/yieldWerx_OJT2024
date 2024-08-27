<?php 
// Retrieve or set xIndex
if (isset($_POST['x'])) {
    $_SESSION['xIndex'] = $_POST['x'];
}
$xIndex = isset($_SESSION['xIndex']) ? $_SESSION['xIndex'] : null;

// Retrieve or set yIndex
if (isset($_POST['y'])) {
    $_SESSION['yIndex'] = $_POST['y'];
}
$yIndex = isset($_SESSION['yIndex']) ? $_SESSION['yIndex'] : null;

// Retrieve or set orderX
if (isset($_POST['order-x'])) {
    $_SESSION['orderX'] = $_POST['order-x'];
}
$orderX = isset($_SESSION['orderX']) ? $_SESSION['orderX'] : null;

// Retrieve or set orderY
if (isset($_POST['order-y'])) {
    $_SESSION['orderY'] = $_POST['order-y'];
}
$orderY = isset($_SESSION['orderY']) ? $_SESSION['orderY'] : null;

$chart = isset($_POST['chart']) ? $_POST['chart'] : (isset($_SESSION['chart']) ? $_SESSION['chart'] : null);

// Retrieve parameter-x and parameter-y values and store them in the session
$parameterX = isset($_POST['parameter-x']) ? $_POST['parameter-x'] : (isset($_SESSION['parameter-x']) ? $_SESSION['parameter-x'] : []);
$parameterY = isset($_POST['parameter-y']) ? $_POST['parameter-y'] : (isset($_SESSION['parameter-y']) ? $_SESSION['parameter-y'] : []);

$_SESSION['parameter-x'] = $parameterX;
$_SESSION['parameter-y'] = $parameterY;

// Combine parameter-x and parameter-y into a single array for filters and reindex it
$combinedParameters = array_values(array_unique(array_merge($parameterX, $parameterY)));

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
    "l.Facility_ID" => isset($_POST['facility']) ? $_POST['facility'] : (isset($_SESSION['facility']) ? $_SESSION['facility'] : []),
    "l.work_center" => isset($_POST['work_center']) ? $_POST['work_center'] : (isset($_SESSION['work_center']) ? $_SESSION['work_center'] : []),
    "l.part_type" => isset($_POST['device_name']) ? $_POST['device_name'] : (isset($_SESSION['device_name']) ? $_SESSION['device_name'] : []),
    "l.Program_Name" => isset($_POST['test_program']) ? $_POST['test_program'] : (isset($_SESSION['test_program']) ? $_SESSION['test_program'] : []),
    "l.lot_ID" => isset($_POST['lot']) ? $_POST['lot'] : (isset($_SESSION['lot']) ? $_SESSION['lot'] : []),
    "w.wafer_ID" => isset($_POST['wafer']) ? $_POST['wafer'] : (isset($_SESSION['wafer']) ? $_SESSION['wafer'] : []),
    "tm.Column_Name" => !empty($combinedParameters) ? $combinedParameters : [],
    "p.abbrev" => isset($_POST['abbrev']) ? $_POST['abbrev'] : (isset($_SESSION['abbrev']) ? $_SESSION['abbrev'] : []),
    "d1.HBin_Number" => isset($_POST['hbin']) ? $_POST['hbin'] : (isset($_SESSION['hbin']) ? $_SESSION['hbin'] : []),
    "d1.SBin_Number" => isset($_POST['sbin']) ? $_POST['sbin'] : (isset($_SESSION['sbin']) ? $_SESSION['sbin'] : []),
    "d1.Site_Number" => isset($_POST['site']) ? $_POST['site'] : (isset($_SESSION['site']) ? $_SESSION['site'] : []),
    "l.Test_Temprature" => isset($_POST['temp']) ? $_POST['temp'] : (isset($_SESSION['temp']) ? $_SESSION['temp'] : []),
    "d1.Test_Time" => isset($_POST['time']) ? $_POST['time'] : (isset($_SESSION['time']) ? $_SESSION['time'] : []) 
];


// echo "<pre>" . print_r($filters, true) . "</pre>";


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
?>