<?php
require __DIR__ . '/../connection.php';

// Adjusted SQL query to fetch relevant data
$tsql = "
    SELECT 
        l.Facility_ID, 
        l.Work_Center, 
        l.Part_Type, 
        l.Program_Name, 
        l.Test_Temprature, 
        l.Lot_ID, 
        w.Wafer_ID, 
        w.Wafer_Start_Time,
        w.Wafer_Finish_Time, 
        d1.Unit_Number, 
        d1.X, 
        d1.Y, 
        d1.Head_Number, 
        d1.Site_Number,
        d1.HBin_Number, 
        d1.SBin_Number, 
        d1.Tests_Executed, 
        d1.Test_Time, 
        d1.T4, 
        d1.T5, 
        d1.T6, 
        d1.T7
    FROM DEVICE_1_CP1_V1_0_001 d1
    JOIN WAFER w ON w.Wafer_Sequence = d1.Wafer_Sequence
    JOIN LOT l ON l.Lot_Sequence = w.Lot_Sequence
    JOIN TEST_PARAM_MAP tm ON tm.Lot_Sequence = l.Lot_Sequence
    JOIN DEVICE_1_CP1_V1_0_002 d2 ON d1.Die_Sequence = d2.Die_Sequence
    ORDER BY w.Wafer_ID
";

$stmt = sqlsrv_query($conn, $tsql);

$options = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $options[] = [
            'facility_id' => $row['Facility_ID'],
            'work_center' => $row['Work_Center'],
            'part_type' => $row['Part_Type'],
            'program_name' => $row['Program_Name'],
            'test_temperature' => $row['Test_Temprature'],
            'lot_id' => $row['Lot_ID'],
            'wafer_id' => $row['Wafer_ID'],
            'unit_number' => $row['Unit_Number'],
            'x' => $row['X'],
            'y' => $row['Y'],
            'head_number' => $row['Head_Number'],
            'site_number' => $row['Site_Number'],
            'hbin_number' => $row['HBin_Number'],
            'sbin_number' => $row['SBin_Number'],
            'tests_executed' => $row['Tests_Executed'],
            'test_time' => $row['Test_Time'],
            't4' => $row['T4'],
            't5' => $row['T5'],
            't6' => $row['T6'],
            't7' => $row['T7']
        ];
    }
}

echo json_encode($options);
?>
