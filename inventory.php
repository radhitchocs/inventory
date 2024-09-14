<?php
session_start();
include 'db.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$add_item_success = $add_item_error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_item'])) {
    $nama_barang = $_POST['nama_barang'];
    $jenis_barang = $_POST['jenis_barang'];
    $kuantitas_stok = $_POST['kuantitas_stok'];
    $lokasi_gudang_id = $_POST['lokasi_gudang_id'];
    $harga = $_POST['harga'];
    
    // Generate a unique barcode
    $barcode = uniqid();
    
    $vendor_id = 1; // Replace with actual vendor_id or form input

    $stmt = $conn->prepare("INSERT INTO inventory (nama_barang, jenis_barang, kuantitas_stok, lokasi_gudang_id, barcode, harga, vendor_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiisdi", $nama_barang, $jenis_barang, $kuantitas_stok, $lokasi_gudang_id, $barcode, $harga, $vendor_id);
    
    if ($stmt->execute()) {
        $add_item_success = "Barang berhasil ditambahkan!";
    } else {
        $add_item_error = "Gagal menambahkan barang. Error: " . $stmt->error;
    }
    $stmt->close();
}

// Query untuk mendapatkan semua barang dari tabel inventory dan join dengan storage_unit untuk mendapatkan nama lokasi gudang
$query = "
    SELECT i.*, s.nama_gudang, v.nama_vendor
    FROM inventory i
    LEFT JOIN storage_unit s ON i.lokasi_gudang_id = s.gudang_id
    LEFT JOIN vendor v ON i.vendor_id = v.vendor_id
";
$result = $conn->query($query);

// Query untuk mendapatkan daftar lokasi gudang untuk dropdown
$storage_units_query = "SELECT * FROM storage_unit";
$storage_units_result = $conn->query($storage_units_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang & Daftar Barang</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

    <!-- Formulir Tambah Barang -->
    <div class="max-w-3xl mx-auto mb-8">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-bold text-gray-700 mb-6">Tambah Barang Baru</h2>
            <form method="POST" class="space-y-4">
                <input type="text" name="nama_barang" placeholder="Nama Barang" required class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="text" name="jenis_barang" placeholder="Jenis Barang" required class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="number" name="kuantitas_stok" placeholder="Kuantitas Stok" required class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                
                <!-- Dropdown untuk Lokasi Gudang -->
                <select name="lokasi_gudang_id" required class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Pilih Lokasi Gudang</option>
                    <?php while ($row = $storage_units_result->fetch_assoc()) { ?>
                    <option value="<?php echo $row['gudang_id']; ?>"><?php echo htmlspecialchars($row['nama_gudang']); ?></option>
                    <?php } ?>
                </select>
                
                <input type="number" step="0.01" name="harga" placeholder="Harga" required class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" name="add_item" class="w-full bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600">Tambah Barang</button>
                <?php if ($add_item_success) echo "<p class='text-green-500 mt-4'>$add_item_success</p>"; ?>
                <?php if ($add_item_error) echo "<p class='text-red-500 mt-4'>$add_item_error</p>"; ?>
            </form>
        </div>
    </div>

    <!-- Daftar Barang -->
    <div class="max-w-5xl mx-auto">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h1 class="text-2xl font-bold text-gray-700 mb-6">Daftar Barang</h1>
            <table class="min-w-full bg-white border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-6 py-3 border-b border-gray-300 text-left text-sm font-semibold text-gray-700">Nama Barang</th>
                        <th class="px-6 py-3 border-b border-gray-300 text-left text-sm font-semibold text-gray-700">Jenis</th>
                        <th class="px-6 py-3 border-b border-gray-300 text-left text-sm font-semibold text-gray-700">Stok</th>
                        <th class="px-6 py-3 border-b border-gray-300 text-left text-sm font-semibold text-gray-700">Lokasi Gudang</th>
                        <th class="px-6 py-3 border-b border-gray-300 text-left text-sm font-semibold text-gray-700">Harga</th>
                        <th class="px-6 py-3 border-b border-gray-300 text-left text-sm font-semibold text-gray-700">Vendor</th>
                        <th class="px-6 py-3 border-b border-gray-300 text-left text-sm font-semibold text-gray-700">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr class="hover:bg-gray-100">
                        <td class="px-6 py-4 border-b border-gray-300"><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                        <td class="px-6 py-4 border-b border-gray-300"><?php echo htmlspecialchars($row['jenis_barang']); ?></td>
                        <td class="px-6 py-4 border-b border-gray-300"><?php echo htmlspecialchars($row['kuantitas_stok']); ?></td>
                        <td class="px-6 py-4 border-b border-gray-300"><?php echo htmlspecialchars($row['nama_gudang']); ?></td>
                        <td class="px-6 py-4 border-b border-gray-300"><?php echo htmlspecialchars($row['harga']); ?></td>
                        <td class="px-6 py-4 border-b border-gray-300"><?php echo htmlspecialchars($row['nama_vendor']); ?></td>
                        <td class="px-6 py-4 border-b border-gray-300">
                            <a href="detail_barang.php?barang_id=<?php echo $row['barang_id']; ?>" class="text-blue-500 hover:underline">Detail</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    
</body>
</html>
