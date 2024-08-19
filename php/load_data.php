<?php
require __DIR__ . '/../connection.php';

$offset = isset($_POST['offset']) ? (int) $_POST['offset'] : 0;
$limit = isset($_POST['limit']) ? (int) $_POST['limit'] : 100;

$xIndex = isset($_POST['x']) ? $_POST['x'] : null;
$yIndex = isset($_POST['y']) ? $_POST['y'] : null;

$orderX = isset($_POST['order-x']) ? $_POST['order-x'] : null;
$orderY = isset($_POST['order-y']) ? $_POST['order-y'] : null;

$filters = isset($_POST['filters']) ? $_POST['filters'] : [];

// Prepare the SQL query using offset and limit for batching
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
         $orderByClause
         OFFSET $offset ROWS FETCH NEXT $limit ROWS ONLY";

$stmt = sqlsrv_query($conn, $tsql, $params);
if ($stmt === false) {
    die(json_encode(['error' => print_r(sqlsrv_errors(), true)]));
}

$data = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $row_data = [];
    foreach ($all_columns as $column) {
        $value = isset($row[$column]) ? $row[$column] : '';
        if ($value instanceof DateTime) {
            $value = $value->format('Y-m-d H:i:s');
        } elseif (is_numeric($value) && floor($value) != $value) {
            $value = number_format($value, 2);
        }
        $row_data[] = $value;
    }
    $data[] = $row_data;
}
sqlsrv_free_stmt($stmt);

echo json_encode($data);
?>