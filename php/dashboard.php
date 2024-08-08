<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Extracted Table</title>
   <link rel="stylesheet" href="../src/output.css">
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.4.1/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.4.1/dist/flowbite.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">
<?php include('admin_components.php'); ?>
<div class="p-4">
   <div class="p-6 rounded-lg dark:border-gray-700 mt-14">
   <h1 class="text-center text-2xl font-bold mb-4 w-full">Extracted Table</h1>
      <div>
      <?php include('extracted_table.php');?>
      </div>
   </div>
</div>
</body>
</html>