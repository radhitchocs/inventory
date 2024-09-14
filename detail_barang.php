<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$barang_id = $_GET['barang_id'];
$stmt = $conn->prepare("SELECT i.*, s.nama_gudang 
                        FROM inventory i 
                        LEFT JOIN storage_unit s ON i.lokasi_gudang_id = s.gudang_id
                        WHERE i.barang_id = ?");
$stmt->bind_param("i", $barang_id);
$stmt->execute();
$barang = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Barang</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-4xl mx-auto bg-white shadow-lg rounded-lg p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Detail Barang</h1>
        <div class="space-y-4">
            <div class="flex justify-between">
                <span class="font-semibold text-gray-700">Nama Barang:</span>
                <span><?php echo htmlspecialchars($barang['nama_barang']); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="font-semibold text-gray-700">Jenis Barang:</span>
                <span><?php echo htmlspecialchars($barang['jenis_barang']); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="font-semibold text-gray-700">Kuantitas Stok:</span>
                <span><?php echo htmlspecialchars($barang['kuantitas_stok']); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="font-semibold text-gray-700">Lokasi Gudang:</span>
                <span><?php echo htmlspecialchars($barang['nama_gudang']); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="font-semibold text-gray-700">Harga:</span>
                <span><?php echo htmlspecialchars($barang['harga']); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="font-semibold text-gray-700">Barcode:</span>
                <span><?php echo htmlspecialchars($barang['barcode']); ?></span>
            </div>
        </div>
        <a href="index.php" class="mt-6 inline-block px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Kembali ke Daftar Barang</a>
    </div>
</body>
</html>