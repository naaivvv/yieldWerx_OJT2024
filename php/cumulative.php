<?php
include('cumulative_backend.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Graphs</title>
   <link rel="stylesheet" href="../src/output.css">
   <link rel="stylesheet" href="../node_modules/flowbite/dist/flowbite.min.css">
   <script src="../node_modules/flowbite/dist/flowbite.min.js" defer></script>
   <script src="../node_modules/jquery/dist/jquery.min.js" defer></script>
   <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
     
   <style>
       .chart-container {
            overflow-x: auto; /* Enables horizontal scroll when content overflows */
            width: 100%; /* or set a specific fixed width like 1000px */
            white-space: nowrap; /* Prevents content from wrapping to a new line */
            padding: 1rem;
        }

        .chart-container .grid {
            display: inline-flex; /* Makes sure the grid layout inside stays inline */
        }
       .-rotate-90 {
            --tw-rotate: -90deg;
            transform: rotate(var(--tw-rotate));
        }
        .mt-24 {
            margin-top: 6rem;
        }
        .max-w-fit {
            max-width: fit-content;
        }
        .customize-text-header {
            margin-top: -28px;
        }
        .right-4 {
            right: 2.5rem;
        }
        .top-24 {
            top: 6rem;
        }
        .ml-16 {
            margin-left: 4rem;
        }
        .max-w-5xl {
            max-width: 64rem /* 1024px */;
        }
        .w-custom {
            width: 32rem /* 512px */;
        }
        .mt-12 {
            margin-top: 3rem /* 48px */;
        }
        .min-w-custom {
            min-width: 32rem /* 384px */;
        }
   </style>
</head>
<body class="bg-gray-50">
<?php include('admin_components.php'); ?>
<?php include('settings.php'); ?>
<script>const groupedData = <?php echo json_encode($groupedData); ?>;</script>
<h1 class="text-center text-2xl font-bold w-full mb-6">Cumulative Probability Chart</h1>
<!-- Iterate and generate chart canvases -->
<div class="max-w-5xl p-4 my-4 flex items-center justify-center mx-auto">
    <div class="w-full">
        <?php include('received_parameters.php'); ?>
    </div>
</div>
<?php
foreach ($groupedData as $parameter => $data) {

    $xLabel = $parameter;
    $yLabel = 'Percentage %';

    $testNameQuery = "SELECT test_name FROM TEST_PARAM_MAP WHERE Column_Name = ?";
    $testNameStmtX = sqlsrv_query($conn, $testNameQuery, [$xLabel]);
    $testNameX = sqlsrv_fetch_array($testNameStmtX, SQLSRV_FETCH_ASSOC)['test_name'];
    $testNameY = $yLabel;
    sqlsrv_free_stmt($testNameStmtX);

    echo '<div class="p-4 m-6 flex flex-col">';
    echo '<div class="flex flex-row mx-auto border-b-2 border-2 bg-white shadow-md rounded-md pr-4 min-w-custom">';
    // echo '<div class="w-fit flex-grow-0"><div class="flex items-center justify-center h-full"><div><h2 class="text-center text-xl font-semibold -rotate-90 w-full whitespace-nowrap overflow-hidden text-ellipsis">' . $yLabel . '</h2></div></div></div>';
    echo '<div class="flex flex-col items-center w-full max-w-7xl">';
    echo '<div class="p-6 chart-container">';
    echo '<div class="my-4 text-sm italic">';
    echo 'Cumulative Probability Chart of <b>' . $testNameX . '</b>';
    echo '</div>';

    if (isset($xColumn) && isset($yColumn)) {
        $yGroupKeys = array_keys($data);
        $lastYGroup = end($yGroupKeys);
        foreach ($data as $yGroup => $xGroupData) {
            echo '<div class="flex flex-row items-center">';
            echo '<div><h2 class="text-center text-xl font-semibold mb-4 -rotate-90">' . $yGroup . '</h2></div>';
            echo '<div class="grid gap-1 grid-cols-' . count($xGroupData) . '">';
            foreach ($xGroupData as $xGroup => $chartData) {
                $chartId = "chartXY_{$parameter}_{$yGroup}_{$xGroup}";
                echo '<div class="flex items-center justify-center flex-col">';
                echo "<canvas id='{$chartId}' style='width: 250px !important; height: 160px !important;'></canvas>";
                if ($yGroup === $lastYGroup) {
                    echo '<h3 class="text-center text-lg font-semibold">' . $xGroup . '</h3>';
                }
                echo '</div>';
            }
            echo '</div></div>';
        }
    } elseif (isset($xColumn)) {
        echo '<div class="flex flex-row items-center">';
        echo '<div class="grid gap-2 grid-cols-' . count($data) . '">';
        foreach ($data as $xGroup => $chartData) {
            $chartId = "chartXY_{$parameter}_{$xGroup}";
            echo '<div class="flex items-center justify-center flex-col">';
            echo "<canvas id='{$chartId}' style='width: 250px !important; height: 160px !important;'></canvas>";
            echo '<h3 class="text-center text-lg font-semibold">' . $xGroup . '</h3></div>';
        }
        echo '</div></div>';
    } elseif (isset($yColumn)) {
        echo '<div class="flex flex-row items-center justify-center w-full p-2">';
        echo '<div class="grid gap-2 grid-cols-1">';
        echo '<div class="flex flex-col items-center">';
        foreach ($data as $yGroup => $chartData) {
            $chartId = "chartXY_{$parameter}_{$yGroup}";
            echo '<div class="flex flex-row justify-center items-center w-custom">';
            echo '<div class="text-center"><h2 class="text-center text-xl font-semibold mb-4 -rotate-90">' . $yGroup . '</h2></div>';
            echo "<canvas id='{$chartId}' style='width: 250px !important; height: 160px !important;'></canvas>";
            echo '</div>';
        }
        echo '</div></div>';
    } else {
        $chartId = "chartXY_{$parameter}_all";
        echo '<div class="flex items-center justify-center w-full">';
        echo "<canvas id='{$chartId}'></canvas></div>";
        echo '</div>';
    }

    echo '</div>';
    // echo '<div class="w-full my-8"><div class="flex items-center justify-center h-full"><h2 class="text-center text-xl font-semibold mb-4">' . $testNameX . '</h2></div></div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
?>

<!-- End Iterate here-->
<script>
    const xLabel = '<?php echo $testNameX; ?>';
    const yLabel = '<?php echo $testNameY; ?>';
    const xColumn = <?php echo json_encode($xColumn); ?>;
    const yColumn = <?php echo json_encode($yColumn); ?>;
    const hasXColumn = <?php echo json_encode(isset($xColumn)); ?>;
    const hasYColumn = <?php echo json_encode(isset($yColumn)); ?>;
</script>
<script src="../js/chart_cumulative.js"></script>
</body>
</html>
