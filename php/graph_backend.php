<?php 

require __DIR__ . '/../connection.php';

include_once('parameter_query.php');

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
