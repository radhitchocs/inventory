<?php
// Query untuk mendapatkan total barang
$total_barang = $conn->query("SELECT COUNT(*) AS total FROM inventory")->fetch_assoc()['total'];

// Query untuk mendapatkan jumlah barang dengan stok 0
$alert_barang = $conn->query("SELECT COUNT(*) AS alert FROM inventory WHERE kuantitas_stok = 0")->fetch_assoc()['alert'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center">
    <div class="w-full max-w-4xl mx-auto p-8">
        <div class="bg-white shadow-lg rounded-lg p-6">
            <h1 class="text-3xl font-bold text-gray-800 text-center mb-8">Dashboard</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Card for Total Barang -->
                <div class="bg-blue-500 text-white p-6 rounded-lg shadow-md flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold">Total Barang</h2>
                        <p class="text-4xl font-bold"><?php echo $total_barang; ?></p>
                    </div>
                    <div class="text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z" />
                            <path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>

                <!-- Card for Barang Stok Habis -->
                <div class="bg-red-500 text-white p-6 rounded-lg shadow-md flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold">Barang Stok Habis</h2>
                        <p class="text-4xl font-bold"><?php echo $alert_barang; ?></p>
                    </div>
                    <div class="text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
