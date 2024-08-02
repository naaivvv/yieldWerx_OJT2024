<?php
require __DIR__ . '/../connection.php';

$tsql = "SELECT * FROM WAFER";
$stmt = sqlsrv_query($conn, $tsql);

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
    <div class="w-full max-w-6xl p-6rounded-lg shadow-lg">
        <h1 class="text-center text-2xl font-bold mb-4">Yieldwerx</h1>
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
    </div>

    <?php
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    ?>
</body>
</html>
