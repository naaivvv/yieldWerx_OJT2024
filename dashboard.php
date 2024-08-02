<?php
require __DIR__ . '/connection.php';

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
    <style>
        .table-container {
            max-width: 100%;
            overflow-x: auto;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <h1>WAFER Table Dashboard</h1>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <!-- Header Row -->
                    <?php
                    $columns = [
                        'Wafer_Sequence', 'Lot_Sequence', 'Wafer_ID', 'Wafer_Start_Time', 'Wafer_Finish_Time'
                    ];
                    foreach ($columns as $column) {
                        echo "<th>$column</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
                <tr>
                    <?php foreach ($columns as $column): ?>
                    <td>
                        <?php 
                        $value = $row[$column] ?? '';
                        if ($value instanceof DateTime) {
                            // Format the DateTime object to a readable string
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

    <?php
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    ?>
</body>
</html>
