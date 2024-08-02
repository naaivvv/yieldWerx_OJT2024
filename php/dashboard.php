<?php
require __DIR__ . '/../connection.php';

// Pagination logic
$records_per_page = 10; // Number of records per page
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Count total number of records
$count_sql = "SELECT COUNT(*) AS total FROM WAFER";
$count_stmt = sqlsrv_query($conn, $count_sql);
$total_rows = sqlsrv_fetch_array($count_stmt, SQLSRV_FETCH_ASSOC)['total'];
$total_pages = ceil($total_rows / $records_per_page);

// Retrieve records for the current page
$tsql = "SELECT * FROM WAFER ORDER BY Wafer_Sequence OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";
$params = array($offset, $records_per_page);
$stmt = sqlsrv_query($conn, $tsql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WAFER Dashboard</title>
    <link rel="stylesheet" href="../src/output.css">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen text-black">
    <div class="w-full max-w-6xl p-6 rounded-lg shadow-lg">
        <h1 class="text-center text-2xl font-bold mb-4">Yieldwerx</h1>
        <div class="mb-4 text-right">
            <a href="export.php" class="px-4 py-2 bg-green-500 text-white rounded">Export to CSV</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse table-fixed p-4">
                <thead>
                    <tr class="bg-gray-200">
                        <?php
                        $columns = [
                            'Wafer_Sequence', 'Lot_Sequence', 'Wafer_ID', 'Wafer_Start_Time', 'Wafer_Finish_Time'
                        ];
                        foreach ($columns as $column) {
                            echo "<th class='border px-4 py-2'>$column</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
                    <tr class="even:bg-gray-100">
                        <?php foreach ($columns as $column): ?>
                        <td class="border px-4 py-2">
                            <?php 
                            $value = $row[$column] ?? '';
                            if ($value instanceof DateTime) {
                                echo htmlspecialchars($value->format('Y-m-d H:i:s'));
                            } else {
                                echo htmlspecialchars($value);
                            }
                            ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="mt-4 flex justify-center">
            <?php if ($current_page > 1): ?>
                <a href="?page=<?php echo $current_page - 1; ?>" class="px-4 py-2 bg-blue-500 text-white rounded-l">Previous</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="px-4 py-2 <?php echo $i == $current_page ? 'bg-blue-500 text-white' : 'bg-gray-200 text-black'; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            <?php if ($current_page < $total_pages): ?>
                <a href="?page=<?php echo $current_page + 1; ?>" class="px-4 py-2 bg-blue-500 text-white rounded-r">Next</a>
            <?php endif; ?>
        </div>
    </div>

    <?php
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    ?>
</body>
</html>
