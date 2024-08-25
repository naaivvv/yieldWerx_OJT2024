<?php
require __DIR__ . '/../connection.php';

// Get values from GET parameters or session
$facilityIDValue = $_GET['facility'] ?? $_SESSION['facility'] ?? null;
$workCenterValue = $_GET['work_center'] ?? $_SESSION['work_center'] ?? null;
$deviceNameValue = $_GET['device_name'] ?? $_SESSION['device_name'] ?? null;
$testProgramValue = $_GET['test_program'] ?? $_SESSION['test_program'] ?? null;
$lotIDValue = $_GET['lot'] ?? $_SESSION['lot'] ?? null;
$waferIDValue = $_GET['wafer'] ?? $_SESSION['wafer'] ?? null;
$parameterType = $_GET['parameter-x'] ?? $_SESSION['parameter-x'] ?? null;
$type = $_GET['type'];

switch ($type) {
    case 'work_center':
        $query = "SELECT DISTINCT Work_Center FROM lot WHERE Facility_ID IN ('" . implode("','", $facilityIDValue) . "')";
        break;
    case 'device_name':
        $query = "SELECT DISTINCT Part_Type FROM lot WHERE Work_Center IN ('" . implode("','", $workCenterValue) . "')";
        break;
    case 'test_program':
        $query = "SELECT DISTINCT tm.Program_Name
                    FROM lot l
                    JOIN TEST_PARAM_MAP tm ON tm.Lot_Sequence = l.Lot_Sequence
                    WHERE Part_Type IN ('" . implode("','", $deviceNameValue) . "')
                    ORDER BY tm.Program_Name ASC";
        break;
    case 'lot':
        $query = "SELECT DISTINCT l.Lot_ID
                    FROM lot l
                    JOIN TEST_PARAM_MAP tm ON tm.Lot_Sequence = l.Lot_Sequence
                    WHERE l.Program_Name IN ('" . implode("','", $testProgramValue) . "')";
        break;
    case 'wafer':
        $query = "SELECT DISTINCT w.Wafer_ID 
                  FROM wafer w
                  JOIN lot l ON l.Lot_Sequence = w.Lot_Sequence 
                  WHERE l.Lot_ID IN ('" . implode("','", $lotIDValue) . "')
                  ORDER BY w.Wafer_ID";
        break;
    case 'parameter-x':
    case 'parameter-y':
        $query = "SELECT DISTINCT tm.Column_Name, tm.Test_Name, 
                    CAST(SUBSTRING(tm.Column_Name, 2, LEN(tm.Column_Name)) AS INT) AS Column_Num
                    FROM TEST_PARAM_MAP tm 
                    JOIN wafer w ON w.Lot_Sequence = tm.Lot_Sequence 
                    JOIN lot l ON l.Lot_Sequence = w.Lot_Sequence 
                    WHERE w.Wafer_ID IN ('" . implode("','", $waferIDValue) . "') 
                    ORDER BY Column_Num ASC"; 
        break;
    default:
        $query = "";
}

$options = [];
if ($query) {
    $stmt = sqlsrv_query($conn, $query);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        if ($type == 'parameter-x' || $type == 'parameter-y') {
            $options[] = ['value' => $row['Column_Name'], 'display' => $row['Test_Name']];
        } else {
            $options[] = array_values($row)[0];
        }
    }
    sqlsrv_free_stmt($stmt);
}

echo json_encode($options);

sqlsrv_close($conn);
?>
