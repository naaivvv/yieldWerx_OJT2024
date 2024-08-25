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


$data = [];
$groupedData = [];

$combinations = [];
foreach ($parameterX as $xParam) {
    foreach ($parameterY as $yParam) {
        $combinations[] = [$xParam, $yParam];
    }
}

foreach ($combinations as $combination) {

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

// Dynamically construct the column part of the SQL query with alias or COALESCE
$column_list = !empty($filters['tm.Column_Name'])
    ? implode(', ', array_map(function($col) use ($columnAliasMap) {
        if (isset($columnAliasMap[$col]) && !empty($columnAliasMap[$col])) {
            $aliasList = implode(', ', $columnAliasMap[$col]);
            return count($columnAliasMap[$col]) > 1 ? "COALESCE($aliasList) AS $col" : "$aliasList AS $col";
        }
        return null;
    }, $filters['tm.Column_Name']))
    : '*';

// Remove any null entries from $column_list
$column_list = implode(', ', array_filter(explode(', ', $column_list)));


    $xLabel = $combination[0];
    $yLabel = $combination[1];
    $combinationKey = implode('_', $combination);

    // Generate test names for the labels
    $testNameQuery = "SELECT test_name FROM TEST_PARAM_MAP WHERE Column_Name = ?";
    $testNameStmtX = sqlsrv_query($conn, $testNameQuery, [$xLabel]);
    $testNameX = sqlsrv_fetch_array($testNameStmtX, SQLSRV_FETCH_ASSOC)['test_name'];

    $testNameStmtY = sqlsrv_query($conn, $testNameQuery, [$yLabel]);
    $testNameY = sqlsrv_fetch_array($testNameStmtY, SQLSRV_FETCH_ASSOC)['test_name'];

    sqlsrv_free_stmt($testNameStmtX);
    sqlsrv_free_stmt($testNameStmtY);

    // Construct the SQL query with dynamic aliasing for X and Y columns
    $tsql = "
    SELECT 
        " . (isset($columnAliasMap[$xLabel]) ? 
            (count($columnAliasMap[$xLabel]) > 1 ? "COALESCE(" . implode(', ', $columnAliasMap[$xLabel]) . ") AS X" : "{$columnAliasMap[$xLabel][0]} AS X") 
            : "{$xLabel} AS X") . ",
        " . (isset($columnAliasMap[$yLabel]) ? 
            (count($columnAliasMap[$yLabel]) > 1 ? "COALESCE(" . implode(', ', $columnAliasMap[$yLabel]) . ") AS Y" : "{$columnAliasMap[$yLabel][0]} AS Y") 
            : "{$yLabel} AS Y") . ", 
        " . ($xColumn ? "$xColumn AS xGroup" : "'No xGroup' AS xGroup") . ", 
        " . ($yColumn ? "$yColumn AS yGroup" : "'No yGroup' AS yGroup") . "
    FROM LOT l
    LEFT JOIN WAFER w ON w.Lot_Sequence = l.Lot_Sequence
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
?>
