<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$barang_id = $_GET['barang_id'];
$stmt = $conn->prepare("SELECT i.*, s.nama_gudang, v.nama_vendor 
                        FROM inventory i 
                        LEFT JOIN storage_unit s ON i.lokasi_gudang_id = s.gudang_id
                        LEFT JOIN vendor v ON i.vendor_id = v.vendor_id
                        WHERE i.barang_id = ?");
$stmt->bind_param("i", $barang_id);
$stmt->execute();
$barang = $stmt->get_result()->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Barang</title>
</head>
<body>
    <h1>Detail Barang</h1>
    <p>Nama: <?php echo htmlspecialchars($barang['nama_barang']); ?></p>
    <p>Jenis: <?php echo htmlspecialchars($barang['jenis_barang']); ?></p>
    <p>Stok: <?php echo htmlspecialchars($barang['kuantitas_stok']); ?></p>
    <p>Lokasi Gudang: <?php echo htmlspecialchars($barang['nama_gudang']); ?></p>
    <p>Harga: <?php echo htmlspecialchars($barang['harga']); ?></p>
    <p>Vendor: <?php echo htmlspecialchars($barang['nama_vendor']); ?></p>
    <p>Barcode: <?php echo htmlspecialchars($barang['barcode']); ?></p>

    <a href="index.php?page=inventory">Kembali ke Daftar Barang</a>
</body>
</html>