<?php
include('graph_backend.php');
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
        .bg-cyan-custom{
            background-color: rgba(75, 192, 192, 1);
        }
        .bg-cyan-custom:hover {
            background-color: rgba(45, 122, 122, 1);
        }
   </style>
</head>
<body class="bg-gray-50">
<?php include('admin_components.php'); ?>
<div class="p-4">
<h1 class="text-center text-2xl font-bold w-full mt-24 mb-6">XY Scatter Plot</h1>
                <div class="flex w-full justify-center items-center gap-2">
                <!-- Probe Count Button and Dropdown -->
                <button id="dropdownSearchButtonProbe" data-dropdown-toggle="dropdownSearchProbe" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-cyan-custom rounded-lg focus:ring-4 focus:outline-none focus:ring-indigo-300 dark:bg-indigo-600 dark:hover:bg-indigo-700 dark:focus:ring-indigo-800" type="button">
                    <i class="fa-solid fa-gear"></i>&nbsp;Customize
                    <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                    </svg>
                </button>

                <!-- Probe Count Dropdown menu -->
                <div id="dropdownSearchProbe" class="z-10 hidden bg-white rounded-lg shadow w-60 dark:bg-gray-700">
                    <ul class="h-48 px-3 pb-3 overflow-y-auto text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownSearchButtonProbe">
                        <li>
                            <div class="flex items-center justify-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                            <div class="flex flex-col items-center w-full">
                            <label for="marginRange" class="text-md font-semibold mb-2">Adjust Margin (%)</label>
                            <input type="range" id="marginRange" min="0" max="100" value="10" step="1" class="w-48 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                            <span id="rangeValue" class="text-sm font-semibold mt-2">5%</span>
                            </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
    <div class="p-4 rounded-lg dark:border-gray-700 flex flex-col items-center">
    <div class="max-w-fit p-6 border-b-2 border-2">
        <?php
        if (isset($xColumn) && isset($yColumn)) {
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
            echo '<div class="flex flex-row items-center justify-center w-full">';
            echo '<div class="grid gap-2 grid-cols-' . $numDistinctGroups . '">';
            foreach ($groupedData as $xGroup => $data) {
                echo '<div class="flex items-center justify-center flex-col">';
                echo '<canvas id="chartXY_' . $xGroup . '"></canvas>
                <h3 class="text-center text-lg font-semibold">' . $xGroup . '</h3></div>';
            }
            echo '</div></div>';
        } elseif (!isset($xColumn) && isset($yColumn)) {
            echo '<div class="flex flex-row items-center justify-center w-full">';
            echo '<div class="grid gap-2 grid-cols-1">';
            echo '<div class="flex items-center justify-center flex-col">';
            foreach ($groupedData as $yGroup => $data) {
                echo '<div class="flex flex-row justify-center items-center">
                <div class="text-center">
                    <h2 class="text-center text-xl font-semibold mb-4 -rotate-90"">' . $yGroup . '</h2>
                    </div>';
                    echo '<canvas id="chartXY_' . $yGroup . '"></canvas>
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
</div>

<script>
    const groupedData = <?php echo json_encode($groupedData); ?>;
    const xLabel = '<?php echo $testNameX; ?>';
    const yLabel = '<?php echo $testNameY; ?>';
    const hasXColumn = <?php echo json_encode(isset($xColumn)); ?>;
    const hasYColumn = <?php echo json_encode(isset($yColumn)); ?>;
    const isSingleParameter = <?php echo json_encode($isSingleParameter); ?>;
</script>
<script src="../js/chart_dynamic.js"></script>
</body>
</html>
