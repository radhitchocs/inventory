<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$query = "
    SELECT i.nama_barang, i.kuantitas_stok, a.tanggal_alert
    FROM inventory i
    LEFT JOIN alert_stok_habis a ON i.barang_id = a.barang_id
    WHERE i.kuantitas_stok <= 0
";
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alert Stok Habis</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

    <div class="max-w-5xl mx-auto">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h1 class="text-2xl font-bold text-gray-700 mb-6">Barang Habis Stok</h1>
            
            <table class="min-w-full bg-white border border-gray-300">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-6 py-3 border-b text-left text-sm font-semibold text-gray-700">Nama Barang</th>
                        <th class="px-6 py-3 border-b text-left text-sm font-semibold text-gray-700">Stok Tersisa</th>
                        <th class="px-6 py-3 border-b text-left text-sm font-semibold text-gray-700">Tanggal Alert</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0) { ?>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr class="hover:bg-gray-100">
                            <td class="px-6 py-4 border-b border-gray-300"><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                            <td class="px-6 py-4 border-b border-gray-300"><?php echo htmlspecialchars($row['kuantitas_stok']); ?></td>
                            <td class="px-6 py-4 border-b border-gray-300"><?php echo htmlspecialchars($row['tanggal_alert']); ?></td>
                        </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">Tidak ada barang yang habis stok.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
