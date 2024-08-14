<?php include('table_query.php'); ?>
<style>
    .table-container {
        overflow-y: auto;
        overflow-x: auto;
        max-height: 65vh;
    }
    .max-w-5xl {
            max-width: 64rem /* 1024px */;
        }
</style>
<div class="max-w-5xl p-4 my-4 flex items-center justify-center mx-auto">
    <div class="w-full">
        <?php include('received_parameters.php'); ?>
    </div>
</div>
<div class="flex justify-center items-center h-full">
    <div class="w-full max-w-7xl p-6 rounded-lg shadow-lg bg-white mt-6">
        <div class="mb-4 text-right">
            <a href="selection_page.php" class="px-4 py-2 bg-orange-500 text-white rounded mr-2">
                <i class="fa-solid fa-list"></i>&nbsp;Selection Criteria
            </a>
            <?php if ($chart == 1): ?>
                <a href="graph.php?<?php echo http_build_query($_GET); ?>" target="_blank" class="px-4 py-2 bg-yellow-400 text-white rounded mr-2">
                    <i class="fa-solid fa-chart-area"></i>&nbsp;XY Scatter Plot
                </a>
            <?php else: ?>
                <a href="line_chart.php?<?php echo http_build_query($_GET); ?>" target="_blank" class="px-4 py-2 bg-yellow-400 text-white rounded mr-2">
                    <i class="fa-solid fa-chart-line"></i>&nbsp;Line Chart
                </a>
            <?php endif; ?>
            <a href="export.php?<?php echo http_build_query($_GET); ?>" class="px-5 py-2 bg-green-500 text-white rounded">
                <i class="fa-regular fa-file-excel"></i>&nbsp;Export
            </a>
        </div>
        <!-- <h1 class="text-start text-2xl font-bold mb-4">Data Extraction [Total: <?php // echo $total_rows; ?>]</h1> -->
        <div class="table-container">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <?php
                        foreach ($headers as $header) {
                            echo "<th class='px-2 py-2 whitespace-nowrap border'>$header</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = sqlsrv_query($conn, $tsql, $params); // Re-execute query to fetch data for display
                    if ($stmt === false) {
                        die(print_r(sqlsrv_errors(), true));
                    }
                    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                        echo "<tr class='bg-white dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600'>";
                        foreach ($all_columns as $column) {
                            $value = isset($row[$column]) ? $row[$column] : '';
                            if ($value instanceof DateTime) {
                                $value = $value->format('Y-m-d H:i:s');
                            } elseif (is_numeric($value) && floor($value) != $value) {
                                $value = number_format($value, 2);
                            }
                            echo "<td class='px-2 py-2 whitespace-nowrap border'>$value</td>";
                        }
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
