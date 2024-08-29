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
    case 'probe_sequence':
            $query = "SELECT DISTINCT p.abbrev 
                      FROM wafer w 
                      JOIN ProbingSequenceOrder p ON w.probing_sequence = p.probing_sequence 
                      WHERE w.Wafer_ID IN ('" . implode("','", $waferIDValue) . "') 
                      ORDER BY p.abbrev ASC";    
            break;
    case 'hbin_number':
            $query = "SELECT DISTINCT d.HBin_Number 
                        FROM DEVICE_1_CP1_V1_0_001 d
                        JOIN wafer w ON d.wafer_sequence = w.wafer_sequence
                        WHERE w.Wafer_ID IN ('" . implode("','", $waferIDValue) . "') 
                        ORDER BY d.HBin_Number ASC";     
            break;
    case 'sbin_number':
            $query = "SELECT DISTINCT d.SBin_Number 
                        FROM DEVICE_1_CP1_V1_0_001 d
                        JOIN wafer w ON d.wafer_sequence = w.wafer_sequence
                        WHERE w.Wafer_ID IN ('" . implode("','", $waferIDValue) . "') 
                        ORDER BY d.SBin_Number ASC";     
            break;
    case 'site_number':
            $query = "SELECT DISTINCT d.Site_Number 
                        FROM DEVICE_1_CP1_V1_0_001 d
                        JOIN wafer w ON d.wafer_sequence = w.wafer_sequence
                        WHERE w.Wafer_ID IN ('" . implode("','", $waferIDValue) . "') 
                        ORDER BY d.Site_Number ASC";     
            break;
    case 'test_temperature':
            $query = "SELECT DISTINCT l.Test_Temprature
                        FROM lot l
                        JOIN wafer w ON w.Lot_Sequence = l.Lot_Sequence 
                        WHERE w.Wafer_ID IN ('" . implode("','", $waferIDValue) . "')
                        ORDER BY l.Test_Temprature ASC";    
            break;
    case 'test_time':
            $query = "SELECT DISTINCT d.Test_Time 
                        FROM DEVICE_1_CP1_V1_0_001 d
                        JOIN wafer w ON d.wafer_sequence = w.wafer_sequence
                        WHERE w.Wafer_ID IN ('" . implode("','", $waferIDValue) . "') 
                        ORDER BY d.Test_Time ASC";     
            break;
    case 'tests_executed':
            $query = "SELECT DISTINCT d.Tests_Executed 
                        FROM DEVICE_1_CP1_V1_0_001 d
                        JOIN wafer w ON d.wafer_sequence = w.wafer_sequence
                        WHERE w.Wafer_ID IN ('" . implode("','", $waferIDValue) . "') 
                        ORDER BY d.Tests_Executed ASC";     
            break;
    case 'unit_number':
            $query = "SELECT DISTINCT d.Unit_Number 
                        FROM DEVICE_1_CP1_V1_0_001 d
                        JOIN wafer w ON d.wafer_sequence = w.wafer_sequence
                        WHERE w.Wafer_ID IN ('" . implode("','", $waferIDValue) . "') 
                        ORDER BY d.Unit_Number ASC";  
            break;
    default:
        $query = "";
}

if ($query) {
    $stmt = sqlsrv_query($conn, $query);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        if ($type == 'hbin_number') {
            $options[] = ['value' => $row['HBin_Number'], 'display' => $row['HBin_Number']];
        } elseif ($type == 'sbin_number') {
            $options[] = ['value' => $row['SBin_Number'], 'display' => $row['SBin_Number']];
        } elseif ($type == 'site_number') {
            $options[] = ['value' => $row['Site_Number'], 'display' => $row['Site_Number']];
        } elseif ($type == 'test_temperature') {
            $options[] = ['value' => $row['Test_Temprature'], 'display' => $row['Test_Temprature']];
        } elseif ($type == 'test_time') {
            $options[] = ['value' => $row['Test_Time'], 'display' => $row['Test_Time']];
        } elseif ($type == 'tests_executed') {  
            $options[] = ['value' => $row['Tests_Executed'], 'display' => $row['Tests_Executed']];
        } elseif ($type == 'unit_number') {
            $options[] = ['value' => $row['Unit_Number'], 'display' => $row['Unit_Number']];
        } elseif ($type == 'probe_sequence') {
            $options[] = ['value' => $row['abbrev'], 'display' => $row['abbrev']];
        } elseif ($type == 'parameter-x' || $type == 'parameter-y') {
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
