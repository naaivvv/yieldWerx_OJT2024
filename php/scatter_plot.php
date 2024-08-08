<?php
require __DIR__ . '/../connection.php';

// Initialize the group options and filters
$groupLot = isset($_GET['group_lot']) ? true : false;
$groupWafer = isset($_GET['group_wafer']) ? true : false;
$groupProbe = isset($_GET['group_probe']) ? true : false;

$groups = [
    'lot' => $groupLot,
    'wafer' => $groupWafer,
    'probe' => $groupProbe
];

$filters = [
    "l.Facility_ID" => isset($_GET['facility']) ? $_GET['facility'] : [],
    "l.work_center" => isset($_GET['work_center']) ? $_GET['work_center'] : [],
    "l.part_type" => isset($_GET['device_name']) ? $_GET['device_name'] : [],
    "l.program_name" => isset($_GET['test_program']) ? $_GET['test_program'] : [],
    "l.lot_ID" => isset($_GET['lot']) ? $_GET['lot'] : [],
    "w.wafer_ID" => isset($_GET['wafer']) ? $_GET['wafer'] : [],
    "tm.Column_Name" => isset($_GET['parameter']) ? $_GET['parameter'] : [],
    "p.abbrev" => isset($_GET['abbrev']) ? $_GET['abbrev'] : []
];

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

// Determine if we are working with one parameter or two
$isSingleParameter = count($filters['tm.Column_Name']) === 1;
$parameter = $filters['tm.Column_Name'][0] ?? '';

$data = [];
$xLabel = 'X';
$yLabel = 'Y';
$count = 0;

// Query to fetch data for the chart
if ($isSingleParameter) {
    $tsql = "SELECT w.Wafer_ID, d1.$parameter AS Y, p.abbrev
             FROM DEVICE_1_CP1_V1_0_001 d1
             JOIN WAFER w ON w.Wafer_Sequence = d1.Wafer_Sequence
             JOIN LOT l ON l.Lot_Sequence = w.Lot_Sequence
             JOIN TEST_PARAM_MAP tm ON tm.Lot_Sequence = l.Lot_Sequence
             JOIN DEVICE_1_CP1_V1_0_002 d2 ON d1.Die_Sequence = d2.Die_Sequence
             JOIN ProbingSequenceOrder p ON p.probing_sequence = w.probing_sequence
             $where_clause";
} else {
    $tsql = "SELECT w.Wafer_ID, d1.{$filters['tm.Column_Name'][0]} AS X, d1.{$filters['tm.Column_Name'][1]} AS Y, p.abbrev
             FROM DEVICE_1_CP1_V1_0_001 d1
             JOIN WAFER w ON w.Wafer_Sequence = d1.Wafer_Sequence
             JOIN LOT l ON l.Lot_Sequence = w.Lot_Sequence
             JOIN TEST_PARAM_MAP tm ON tm.Lot_Sequence = l.Lot_Sequence
             JOIN DEVICE_1_CP1_V1_0_002 d2 ON d1.Die_Sequence = d2.Die_Sequence
             JOIN ProbingSequenceOrder p ON p.probing_sequence = w.probing_sequence
             $where_clause";
}

$stmt = sqlsrv_query($conn, $tsql, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
$groupedData = [];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $waferID = $row['Wafer_ID'];
    $abbrev = $row['abbrev'];

    if ($isSingleParameter) {
        $yValue = floatval($row['Y']);
        
        if ($groupWafer && $groupProbe) {
            if (!isset($groupedData[$abbrev][$waferID])) {
                $count = 0;
            }
            $count++;
            $groupedData[$abbrev][$waferID][] = ['x' => $count, 'y' => $yValue];
        } elseif ($groupWafer) {
            if (!isset($groupedData[$waferID])) {
                $count = 0;
            }
            $count++;
            $groupedData[$waferID][] = ['x' => $count, 'y' => $yValue];
        } elseif ($groupProbe) {
            if (!isset($groupedData[$abbrev])) {
                $count = 0;
            }
            $count++;
            $groupedData[$abbrev][] = ['x' => $count, 'y' => $yValue];
        } else {
            if (!isset($groupedData['all'])) {
                $count = 0;
            }
            $count++;
            $groupedData['all'][] = ['x' => $count, 'y' => $yValue];
        }
    } else {
        $xValue = floatval($row['X']);
        $yValue = floatval($row['Y']);
        
        if ($groupWafer && $groupProbe) {
            $groupedData[$abbrev][$waferID][] = ['x' => $xValue, 'y' => $yValue];
        } elseif ($groupWafer) {
            $groupedData[$waferID][] = ['x' => $xValue, 'y' => $yValue];
        } elseif ($groupProbe) {
            $groupedData[$abbrev][] = ['x' => $xValue, 'y' => $yValue];
        } else {
            $groupedData['all'][] = ['x' => $xValue, 'y' => $yValue];
        }
    }
}

sqlsrv_free_stmt($stmt);

$numDistinctGroups = count($groupedData);

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>XY Scatter Plot</title>
   <link rel="stylesheet" href="../src/output.css">
   <link href="https://cdn.jsdelivr.net/npm/flowbite@2.4.1/dist/flowbite.min.css" rel="stylesheet" />
   <script src="https://cdn.jsdelivr.net/npm/flowbite@2.4.1/dist/flowbite.min.js"></script>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
   <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   <style>
       .chart-container {
           overflow: auto;
           /* max-height: 75vh; */
           max-width: 100%;
       }
       td {
           padding: 16px;
       }
       canvas {
           height: 400px;
           width: 450px;
       }
       .-rotate-90 {
            --tw-rotate: -90deg;
            transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
        }
        .grid-cols-6 {
            grid-template-columns: repeat(6, minmax(0, 1fr));
        }
        .grid-cols-7 {
            grid-template-columns: repeat(7, minmax(0, 1fr));
        }
        .grid-cols-8 {
            grid-template-columns: repeat(8, minmax(0, 1fr));
        }
        .grid-cols-9 {
            grid-template-columns: repeat(9, minmax(0, 1fr));
        }
        .grid-cols-10 {
            grid-template-columns: repeat(10, minmax(0, 1fr));
        }
        .grid-cols-11 {
            grid-template-columns: repeat(11, minmax(0, 1fr));
        }
        .grid-cols-12 {
            grid-template-columns: repeat(12, minmax(0, 1fr));
        }
   </style>
</head>
<body class="bg-gray-50">
<?php include('admin_components.php'); ?>
<div class="p-4">
    <div class="p-4 rounded-lg dark:border-gray-700 mt-14">
        <h1 class="text-center text-2xl font-bold mb-4 w-full">XY Scatter Plot</h1>

        <?php
        if ($groupWafer && $groupProbe) {
            $abbrevKeys = array_keys($groupedData);
            $lastAbbrev = end($abbrevKeys);
            foreach ($groupedData as $abbrev => $waferData) {
                echo '<div class="flex flex-row items-center justify-center w-full">';
                echo '<div><h2 class="text-center text-xl font-semibold mb-4 -rotate-90">' . $abbrev . '</h2></div>';
                echo '<div class="grid gap-2 grid-cols-' . count($waferData) . '">';

                foreach ($waferData as $waferID => $data) {
                    echo '<div class="flex items-center justify-center flex-col">';
                    echo '<canvas id="chartXY_' . $abbrev . '_' . $waferID . '"></canvas>';
                    if ($abbrev === $lastAbbrev) {
                        echo '<h3 class="text-center text-lg font-semibold">' . $waferID . '</h3>';
                    }
                    echo '</div>';
                }
                echo '</div></div>';
            }
        } elseif ($groupWafer) {
            echo '<div class="flex flex-row items-center justify-center w-full">';
            echo '<div class="grid gap-2 grid-cols-' . $numDistinctGroups . '">';
            foreach ($groupedData as $waferID => $data) {
                echo '<div class="flex items-center justify-center flex-col">';
                echo '<canvas id="chartXY_' . $waferID . '"></canvas>
                <h3 class="text-center text-lg font-semibold">' . $waferID . '</h3></div>';
            }
            echo '</div></div>';
        } elseif ($groupProbe) {
            echo '<div class="flex flex-row items-center justify-center w-full">';
            echo '<div class="grid gap-2 grid-cols-1">';
            echo '<div class="flex items-center justify-center flex-col">';
            foreach ($groupedData as $abbrev => $data) {
                echo '<div class="flex flex-row justify-center items-center">
                <div class="text-center">
                    <h2 class="text-center text-xl font-semibold mb-4 -rotate-90"">' . $abbrev . '</h2>
                    </div>';
                    echo '<canvas id="chartXY_' . $abbrev . '"></canvas>
                </div>';
            }
            echo '</div></div>';
        } else {
            echo '<div class="flex items-center justify-center w-full">';
            echo '<div><canvas id="chartXY_all"></canvas></div>';
            echo '</div>';
        }
        ?>
    </div>
</div>

<script>
    const groupedData = <?php echo json_encode($groupedData); ?>;
    const xLabel = '<?php echo $xLabel; ?>';
    const yLabel = '<?php echo $yLabel; ?>';
    const groupWafer = <?php echo json_encode($groupWafer); ?>;
    const groupProbe = <?php echo json_encode($groupProbe); ?>;
    const isSingleParameter = <?php echo json_encode($isSingleParameter); ?>;
    
</script>
<script src="../js/plot.js"></script>
</body>
</html>
