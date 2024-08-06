<?php
require __DIR__ . '/../connection.php';

// Filters from URL parameters
$filters = [
    "l.Facility_ID" => isset($_GET['facility']) ? $_GET['facility'] : [],
    "l.work_center" => isset($_GET['work_center']) ? $_GET['work_center'] : [],
    "l.part_type" => isset($_GET['device_name']) ? $_GET['device_name'] : [],
    "l.program_name" => isset($_GET['test_program']) ? $_GET['test_program'] : [],
    "l.lot_ID" => isset($_GET['lot']) ? $_GET['lot'] : [],
    "w.wafer_ID" => isset($_GET['wafer']) ? $_GET['wafer'] : [],
    "tm.Column_Name" => isset($_GET['parameter']) ? $_GET['parameter'] : [],
    "p.probing_sequence" => isset($_GET['abbrev']) ? $_GET['abbrev'] : []
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

// Create the WHERE clause if filters exist
$where_clause = '';
if (!empty($sql_filters)) {
    $where_clause = 'WHERE ' . implode(' AND ', $sql_filters);
}

// Dynamically construct the column part of the SQL query
$column_list = !empty($filters['tm.Column_Name']) ? implode(', ', array_map(function($col) { return "d1.$col"; }, $filters['tm.Column_Name'])) : '*';

// Initialize the data and abbrev_map arrays
$data = [];
$abbrev_map = [];
$abbrev_data = isset($_GET['abbrev']) ? $_GET['abbrev'] : [];
$xLabel = $filters['tm.Column_Name'][0] ?? 'X';
$yLabel = $filters['tm.Column_Name'][1] ?? 'Y';
$xTestName = '';
$yTestName = '';

if (!empty($abbrev_data)) {
    // Query to fetch abbrev mapping for all provided probing_sequence values
    $abbrev_sql = "SELECT p.probing_sequence, p.abbrev
                   FROM ProbingSequenceOrder p
                   WHERE p.probing_sequence IN (" . implode(',', array_fill(0, count($abbrev_data), '?')) . ")";
    $abbrev_stmt = sqlsrv_query($conn, $abbrev_sql, $abbrev_data);
    if ($abbrev_stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Populate the abbrev_map
    while ($row = sqlsrv_fetch_array($abbrev_stmt, SQLSRV_FETCH_ASSOC)) {
        $abbrev_map[$row['probing_sequence']] = $row['abbrev'];
    }
    sqlsrv_free_stmt($abbrev_stmt);

    foreach ($abbrev_data as $abbrev) {
        // Append probing_sequence filter for each abbrev
        $abbrev_where_clause = $where_clause ? $where_clause . ' AND p.probing_sequence = ?' : 'WHERE p.probing_sequence = ?';
        $abbrev_params = array_merge($params, [$abbrev]);

        // Query to fetch data for the current abbrev
        $tsql = "SELECT d1.{$filters['tm.Column_Name'][0]} AS X, d1.{$filters['tm.Column_Name'][1]} AS Y, tm.Test_Name, p.probing_sequence
                 FROM DEVICE_1_CP1_V1_0_001 d1
                 JOIN WAFER w ON w.Wafer_Sequence = d1.Wafer_Sequence
                 JOIN LOT l ON l.Lot_Sequence = w.Lot_Sequence
                 JOIN TEST_PARAM_MAP tm ON tm.Lot_Sequence = l.Lot_Sequence
                 JOIN DEVICE_1_CP1_V1_0_002 d2 ON d1.Die_Sequence = d2.Die_Sequence
                 JOIN ProbingSequenceOrder p ON p.probing_sequence = w.probing_sequence
                 $abbrev_where_clause";

        $stmt_abbrev = sqlsrv_query($conn, $tsql, $abbrev_params);
        if ($stmt_abbrev === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        // Fetch and prepare data for Chart.js
        $data[$abbrev] = [];
        while ($row = sqlsrv_fetch_array($stmt_abbrev, SQLSRV_FETCH_ASSOC)) {
            if (!$xTestName) {
                $xTestName = $row['Test_Name'];
            } elseif (!$yTestName) {
                $yTestName = $row['Test_Name'];
            }
            $xValue = floatval($row['X']);
            $yValue = floatval($row['Y']);
            $data[$abbrev][] = ['x' => $xValue, 'y' => $yValue];
        }
        sqlsrv_free_stmt($stmt_abbrev);
    }
} else {
    // No abbrev filter, generate a single unfiltered chart
    $tsql = "SELECT d1.{$filters['tm.Column_Name'][0]} AS X, d1.{$filters['tm.Column_Name'][1]} AS Y, tm.Test_Name
             FROM DEVICE_1_CP1_V1_0_001 d1
             JOIN WAFER w ON w.Wafer_Sequence = d1.Wafer_Sequence
             JOIN LOT l ON l.Lot_Sequence = w.Lot_Sequence
             JOIN TEST_PARAM_MAP tm ON tm.Lot_Sequence = l.Lot_Sequence
             JOIN DEVICE_1_CP1_V1_0_002 d2 ON d1.Die_Sequence = d2.Die_Sequence
             $where_clause";

    $stmt = sqlsrv_query($conn, $tsql, $params);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Fetch and prepare data for Chart.js
    $data['all'] = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        if (!$xTestName) {
            $xTestName = $row['Test_Name'];
        } elseif (!$yTestName) {
            $yTestName = $row['Test_Name'];
        }
        $xValue = floatval($row['X']);
        $yValue = floatval($row['Y']);
        $data['all'][] = ['x' => $xValue, 'y' => $yValue];
    }
    sqlsrv_free_stmt($stmt);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Dashboard</title>
   <link rel="stylesheet" href="../src/output.css">
   <script src="../path/to/flowbite/dist/flowbite.min.js"></script>
   <link href="https://cdn.jsdelivr.net/npm/flowbite@2.4.1/dist/flowbite.min.css" rel="stylesheet" />
   <script src="https://cdn.jsdelivr.net/npm/flowbite@2.4.1/dist/flowbite.min.js"></script>
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
   <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
<?php include('admin_components.php'); ?>
<div class="p-4 sm:ml-64">
    <div class="p-4 rounded-lg dark:border-gray-700 mt-14">
        <h1 class="text-center text-2xl font-bold mb-4 w-full">XY Scatter Plot</h1>
        <div class="grid grid-cols-<?php echo !empty($abbrev_data) ? '2' : '1'; ?> gap-4 px-32 max-h-[50rem]">
          <?php if (!empty($abbrev_data)): ?>
            <?php foreach ($abbrev_data as $abbrev): ?>
                <div class="mb-6">
                    <h2 class="text-center text-xl font-semibold mb-4"><?php echo htmlspecialchars($abbrev_map[$abbrev] ?? $abbrev); ?></h2>
                    <canvas id="chartXY-<?php echo htmlspecialchars($abbrev); ?>"></canvas>
                </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="mb-6">
                <h2 class="text-center text-xl font-semibold mb-4">Unfiltered Data</h2>
                <canvas id="chartXY-all"></canvas>
            </div>
          <?php endif; ?>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const data = <?php echo json_encode($data); ?>;
            const xTestName = <?php echo json_encode($xTestName); ?>;
            const yTestName = <?php echo json_encode($yTestName); ?>;
            
            <?php if (!empty($abbrev_data)): ?>
                <?php foreach ($abbrev_data as $abbrev): ?>
                    new Chart(document.getElementById('chartXY-<?php echo htmlspecialchars($abbrev); ?>').getContext('2d'), {
                        type: 'scatter',
                        data: {
                            datasets: [{
                                label: xTestName + ' vs. ' + yTestName + ' (<?php echo htmlspecialchars($abbrev_map[$abbrev] ?? $abbrev); ?>)',
                                data: data[<?php echo json_encode($abbrev); ?>].map(d => ({ x: d.x, y: d.y })),
                                backgroundColor: 'rgba(192, 192, 75, 0.6)'
                            }]
                        },
                        options: {
                            scales: {
                                x: {
                                    type: 'linear',
                                    position: 'bottom',
                                    title: {
                                        display: true,
                                        text: xTestName
                                    }
                                },
                                y: {
                                    type: 'linear',
                                    title: {
                                        display: true,
                                        text: yTestName
                                    }
                                }
                            }
                        }
                    });
                <?php endforeach; ?>
            <?php else: ?>
                new Chart(document.getElementById('chartXY-all').getContext('2d'), {
                    type: 'scatter',
                    data: {
                        datasets: [{
                            label: xTestName + ' vs. ' + yTestName + ' (Unfiltered Data)',
                            data: data['all'].map(d => ({ x: d.x, y: d.y })),
                            backgroundColor: 'rgba(192, 192, 75, 0.6)'
                        }]
                    },
                    options: {
                        scales: {
                            x: {
                                type: 'linear',
                                position: 'bottom',
                                title: {
                                    display: true,
                                    text: xTestName
                                }
                            },
                            y: {
                                type: 'linear',
                                title: {
                                    display: true,
                                    text: yTestName
                                }
                            }
                        }
                    }
                });
            <?php endif; ?>
        });
        </script>
    </div>
</div>
</body>
</html>
