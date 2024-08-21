<?php 
require __DIR__ . '/../connection.php';
include_once('parameter_query.php');

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

$parameters = $filters['tm.Column_Name'];
$data = [];
$groupedData = [];

foreach ($parameters as $parameter) {

   // Generate dynamic aliases for the device tables
    $join_clauses = [];
    $previousAlias = null; 
    $aliasIndex = 1;

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
    
    // Dynamically construct the column part of the SQL query with alias or COALESCE
    $parameterColumn = !empty($columnAliasMap[$parameter])
        ? (count($columnAliasMap[$parameter]) > 1 ? "COALESCE(" . implode(", ", $columnAliasMap[$parameter]) . ")" : implode(", ", $columnAliasMap[$parameter]))
        : $parameter;

    $globalCounters = [
        'total' => 0,
        'all' => 0,
        'xcol' => [],
        'ycol' => []
    ];

    // Modify to calculate total counts for each group
    $tempCounters = [
        'all' => 0,
        'xcol' => [],
        'ycol' => []
    ];

    $xLabel = $parameter;
    $yLabel = 'Percentage %';

    $testNameQuery = "SELECT test_name FROM TEST_PARAM_MAP WHERE Column_Name = ?";
    $testNameStmtX = sqlsrv_query($conn, $testNameQuery, [$xLabel]);
    $testNameX = sqlsrv_fetch_array($testNameStmtX, SQLSRV_FETCH_ASSOC)['test_name'];
    $testNameY = $yLabel;
    sqlsrv_free_stmt($testNameStmtX);

    $tsql = "
    SELECT 
        w.Wafer_ID, 
        $parameterColumn AS X, 
        " . ($xColumn ? "$xColumn AS xGroup" : "'No xGroup' AS xGroup") . ", 
        " . ($yColumn ? "$yColumn AS yGroup" : "'No yGroup' AS yGroup") . ",
        ROW_NUMBER() OVER(PARTITION BY " . ($xColumn ?: "'No xGroup'") . " ORDER BY w.Wafer_Sequence) AS row_num
    FROM LOT l
    LEFT JOIN WAFER w ON w.Lot_Sequence = l.Lot_Sequence
    $join_clause
    LEFT JOIN TEST_PARAM_MAP tm ON tm.Lot_Sequence = l.Lot_Sequence
    LEFT JOIN ProbingSequenceOrder p ON p.probing_sequence = w.probing_sequence
    $where_clause";

    // Check if $orderByClause has data
    if (!empty($orderByClause)) {
        $tsql .= " $orderByClause, X ASC";
    } else {
        $tsql .= " ORDER BY X ASC";
    }

    // echo "<pre>$tsql</pre>";
    $stmt = sqlsrv_query($conn, $tsql, $params);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $xGroup = $row['xGroup'];
        $yGroup = $row['yGroup'];
        $xValue = floatval($row['X']);

        $globalCounters['total']++;

        if ($xColumn && $yColumn) {
            $tempCounters['ycol'][$yGroup][$xGroup] = ($tempCounters['ycol'][$yGroup][$xGroup] ?? 0) + 1;
        } elseif ($xColumn && !$yColumn) {
            $tempCounters['xcol'][$xGroup] = ($tempCounters['xcol'][$xGroup] ?? 0) + 1;
        } elseif (!$xColumn && $yColumn) {
            $tempCounters['ycol'][$yGroup] = ($tempCounters['ycol'][$yGroup] ?? 0) + 1;
        } else {
            $tempCounters['all']++;
        }
    }
    sqlsrv_free_stmt($stmt);

    // Now calculate the percentages based on the total counts
    $stmt = sqlsrv_query($conn, $tsql, $params);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $xGroup = $row['xGroup'];
        $yGroup = $row['yGroup'];
        $xValue = floatval($row['X']);

        if ($xColumn && $yColumn) {
            $globalCounters['ycol'][$yGroup][$xGroup] = ($globalCounters['ycol'][$yGroup][$xGroup] ?? 0) + 1;
            $percentage = ($globalCounters['ycol'][$yGroup][$xGroup] / $tempCounters['ycol'][$yGroup][$xGroup]) * 100;
            $groupedData[$parameter][$yGroup][$xGroup][] = ['x' => $xValue, 'y' => $percentage];
        } elseif ($xColumn && !$yColumn) {
            $globalCounters['xcol'][$xGroup] = ($globalCounters['xcol'][$xGroup] ?? 0) + 1;
            $percentage = ($globalCounters['xcol'][$xGroup] / $tempCounters['xcol'][$xGroup]) * 100;
            $groupedData[$parameter][$xGroup][] = ['x' => $xValue, 'y' => $percentage];
        } elseif (!$xColumn && $yColumn) {
            $globalCounters['ycol'][$yGroup] = ($globalCounters['ycol'][$yGroup] ?? 0) + 1;
            $percentage = ($globalCounters['ycol'][$yGroup] / $tempCounters['ycol'][$yGroup]) * 100;
            $groupedData[$parameter][$yGroup][] = ['x' => $xValue, 'y' => $percentage];
        } else {
            $globalCounters['all']++;
            $percentage = ($globalCounters['all'] / $tempCounters['all']) * 100;
            $groupedData[$parameter]['all'][] = ['x' => $xValue, 'y' => $percentage];
        }
    }
    sqlsrv_free_stmt($stmt);
}
?>
