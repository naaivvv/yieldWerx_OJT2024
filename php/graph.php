<?php
include('graph_backend.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Graphs</title>
   <link rel="stylesheet" href="../src/output.css">
   <link href="https://cdn.jsdelivr.net/npm/flowbite@2.4.1/dist/flowbite.min.css" rel="stylesheet" />
   <script src="https://cdn.jsdelivr.net/npm/flowbite@2.4.1/dist/flowbite.min.js"></script>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
   <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.0"></script>
   <style>
       .chart-container {
           overflow: auto;
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
        .mt-24 {
            margin-top: 6rem /* 96px */;
        }
        .max-w-fit {
            max-width: fit-content;
        }
        .customize-text-header{
            margin-top:-28px;
        }

        .right-4 {
            right: 2.5rem /* 16px */;
        }
        .top-24 {
            top: 6rem /* 96px */;
        }
        .ml-16 {
            margin-left: 4rem /* 64px */;
        }
   </style>
</head>
<body class="bg-gray-50">
<?php include('admin_components.php'); ?>
<?php include('settings.php'); ?>

<?php
if(!$isSingleParameter){
echo '<h1 class="text-center text-2xl font-bold w-full mb-6">XY Scatter Plot</h1>';
foreach ($combinations as $index => $combination) {
    $xLabel = $combination[0];
    $yLabel = $combination[1];

    $testNameQuery = "SELECT test_name FROM TEST_PARAM_MAP WHERE Column_Name = ?";
    $testNameStmtX = sqlsrv_query($conn, $testNameQuery, [$xLabel]);
    $testNameX = sqlsrv_fetch_array($testNameStmtX, SQLSRV_FETCH_ASSOC)['test_name'];

    $testNameStmtY = sqlsrv_query($conn, $testNameQuery, [$yLabel]);
    $testNameY = sqlsrv_fetch_array($testNameStmtY, SQLSRV_FETCH_ASSOC)['test_name'];

    sqlsrv_free_stmt($testNameStmtX);
    sqlsrv_free_stmt($testNameStmtY);

?>
<!-- Iterate this layout -->
<div class="p-4">
    <div class="dark:border-gray-700 flex flex-col items-center">
        <div class="max-w-fit p-6 border-b-2 border-2">
            <div class="mb-4 text-sm italic">
                <?php 
                echo 'Combination of <b>' . $testNameX . '</b>';
                echo ' and <b>' . $testNameY . '</b>';
                ?>
            </div>
            <?php
            if (isset($xColumn) && isset($yColumn)) {
                // Both X and Y parameters are set
                $yGroupKeys = array_keys($groupedData);
                $lastYGroup = end($yGroupKeys);
                foreach ($groupedData as $yGroup => $xGroupData) {
                    echo '<div class="flex flex-row items-center justify-center w-full">';
                    echo '<div><h2 class="text-center text-xl font-semibold mb-4 -rotate-90">' . $yGroup . '</h2></div>';
                    echo '<div class="grid gap-2 grid-cols-' . count($xGroupData) . '">';

                    foreach ($xGroupData as $xGroup => $data) {
                        echo '<div class="flex items-center justify-center flex-col">';
                        echo '<canvas id="chartXY_' . $yGroup . '_' . $xGroup . '"></canvas>';
                        if ($yGroup === $lastYGroup) {
                            echo '<h3 class="text-center text-lg font-semibold">' . $xGroup . '</h3>';
                        }
                        echo '</div>';
                    }
                    echo '</div></div>';
                }
            } elseif (isset($xColumn) && !isset($yColumn)) {
                // Only X parameter is set
                echo '<div class="flex flex-row items-center justify-center w-full">';
                echo '<div class="grid gap-2 grid-cols-' . $numDistinctGroups . '">';
                foreach ($groupedData as $xGroup => $data) {
                    echo '<div class="flex items-center justify-center flex-col">';
                    echo '<canvas id="chartXY_' . $xGroup . '"></canvas>';
                    echo '<h3 class="text-center text-lg font-semibold">' . $xGroup . '</h3></div>';
                }
                echo '</div></div>';
            } elseif (!isset($xColumn) && isset($yColumn)) {
                // Only Y parameter is set
                echo '<div class="flex flex-row items-center justify-center w-full">';
                echo '<div class="grid gap-2 grid-cols-1">';
                foreach ($groupedData as $yGroup => $data) {
                    echo '<div class="flex flex-row justify-center items-center">';
                    echo '<div class="text-center">
                        <h2 class="text-center text-xl font-semibold mb-4 -rotate-90">' . $yGroup . '</h2></div>';
                    echo '<canvas id="chartXY_' . $yGroup . '"></canvas>';
                    echo '</div>';
                }
                echo '</div></div>';
            } else {
                // Neither X nor Y parameters are set
                echo '<div class="flex items-center justify-center w-full">';
                echo '<div><canvas id="chartXY_all"></canvas></div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</div>
<script> 
    console.log(<?php echo json_encode($groupedData); ?>);
</script>
<!-- Iterate until here -->
<?php
    }
} else { ?>
<h1 class="text-center text-2xl font-bold w-full mb-6">XY Line Chart</h1>
 <div class="p-4">
    <div class="dark:border-gray-700 flex flex-col items-center">
        <div class="max-w-fit p-6 border-b-2 border-2">
            <div class="mb-4 text-sm italic">
                <?php 
                echo 'Combination of <b>' . $testNameX . '</b>';
                echo ' and <b>' . $testNameY . '</b>';
                ?>
            </div>
            <?php
            if (isset($xColumn) && isset($yColumn)) {
                // Both X and Y parameters are set
                $yGroupKeys = array_keys($groupedData);
                $lastYGroup = end($yGroupKeys);
                foreach ($groupedData as $yGroup => $xGroupData) {
                    echo '<div class="flex flex-row items-center justify-center w-full">';
                    echo '<div><h2 class="text-center text-xl font-semibold mb-4 -rotate-90">' . $yGroup . '</h2></div>';
                    echo '<div class="grid gap-2 grid-cols-' . count($xGroupData) . '">';

                    foreach ($xGroupData as $xGroup => $data) {
                        echo '<div class="flex items-center justify-center flex-col">';
                        echo '<canvas id="chartXY_' . $yGroup . '_' . $xGroup . '"></canvas>';
                        if ($yGroup === $lastYGroup) {
                            echo '<h3 class="text-center text-lg font-semibold">' . $xGroup . '</h3>';
                        }
                        echo '</div>';
                    }
                    echo '</div></div>';
                }
            } elseif (isset($xColumn) && !isset($yColumn)) {
                // Only X parameter is set
                echo '<div class="flex flex-row items-center justify-center w-full">';
                echo '<div class="grid gap-2 grid-cols-' . $numDistinctGroups . '">';
                foreach ($groupedData as $xGroup => $data) {
                    echo '<div class="flex items-center justify-center flex-col">';
                    echo '<canvas id="chartXY_' . $xGroup . '"></canvas>';
                    echo '<h3 class="text-center text-lg font-semibold">' . $xGroup . '</h3></div>';
                }
                echo '</div></div>';
            } elseif (!isset($xColumn) && isset($yColumn)) {
                // Only Y parameter is set
                echo '<div class="flex flex-row items-center justify-center w-full">';
                echo '<div class="grid gap-2 grid-cols-1">';
                foreach ($groupedData as $yGroup => $data) {
                    echo '<div class="flex flex-row justify-center items-center">';
                    echo '<div class="text-center">
                        <h2 class="text-center text-xl font-semibold mb-4 -rotate-90">' . $yGroup . '</h2></div>';
                    echo '<canvas id="chartXY_' . $yGroup . '"></canvas>';
                    echo '</div>';
                }
                echo '</div></div>';
            } else {
                // Neither X nor Y parameters are set
                echo '<div class="flex items-center justify-center w-full">';
                echo '<div><canvas id="chartXY_all"></canvas></div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</div>
<script> 
    console.log(<?php echo json_encode($groupedData); ?>);
</script>
<?php }
?>

<script>
    const groupedData = <?php echo json_encode($groupedData); ?>;
    const xLabel = '<?php echo $testNameX; ?>';
    const yLabel = '<?php echo $testNameY; ?>';
    const xColumn = <?php echo json_encode($xColumn); ?>;
    const yColumn = <?php echo json_encode($yColumn); ?>;
    const hasXColumn = <?php echo json_encode(isset($xColumn)); ?>;
    const hasYColumn = <?php echo json_encode(isset($yColumn)); ?>;
    const isSingleParameter = <?php echo json_encode($isSingleParameter); ?>;
</script>
<script src="../js/chart_scatter.js"></script>
</body>
</html>
