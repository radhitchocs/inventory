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
    $vendor_id = $_POST['vendor_id']; // Ambil dari form input
    
    // Generate a unique barcode
    $barcode = uniqid();

    $stmt = $conn->prepare("INSERT INTO inventory (nama_barang, jenis_barang, kuantitas_stok, lokasi_gudang_id, barcode, harga, vendor_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiisdi", $nama_barang, $jenis_barang, $kuantitas_stok, $lokasi_gudang_id, $barcode, $harga, $vendor_id);
    
    if ($stmt->execute()) {
        $add_item_success = "Barang berhasil ditambahkan!";
    } else {
        $add_item_error = "Gagal menambahkan barang. Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle delete action
if (isset($_POST['delete_item']) && isset($_POST['delete_item_id'])) {
    $delete_item_id = intval($_POST['delete_item_id']);
    
    // Check if item is referenced in transactions (for logging purposes)
    $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM transaksi_inventory WHERE barang_id = ?");
    $check_stmt->bind_param("i", $delete_item_id);
    $check_stmt->execute(); 
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();
    $is_referenced = $row['count'] > 0;
    $check_stmt->close();

    // Proceed with deletion
    $stmt = $conn->prepare("DELETE FROM inventory WHERE barang_id = ?");
    $stmt->bind_param("i", $delete_item_id);
    $stmt->execute();
    $stmt->close();
}

// Handle update action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_item'])) {
    $barang_id = $_POST['barang_id'];
    $nama_barang = $_POST['nama_barang'];
    $jenis_barang = $_POST['jenis_barang'];
    $kuantitas_stok = $_POST['kuantitas_stok'];
    $lokasi_gudang_id = $_POST['lokasi_gudang_id'];
    $harga = $_POST['harga'];
    $vendor_id = $_POST['vendor_id']; // Ambil dari form input
    
    $stmt = $conn->prepare("UPDATE inventory SET nama_barang = ?, jenis_barang = ?, kuantitas_stok = ?, lokasi_gudang_id = ?, harga = ?, vendor_id = ? WHERE barang_id = ?");
    $stmt->bind_param("ssiidii", $nama_barang, $jenis_barang, $kuantitas_stok, $lokasi_gudang_id, $harga, $vendor_id, $barang_id);
    
    if ($stmt->execute()) {
        $add_item_success = "Barang berhasil diperbarui!";
    } else {
        $add_item_error = "Gagal memperbarui barang. Error: " . $stmt->error;
    }
    $stmt->close();
}

// Query untuk mendapatkan semua barang dari tabel inventory dan join dengan storage_unit dan vendor
$query = "
    SELECT i.*, s.nama_gudang, v.nama_vendor
    FROM inventory i
    LEFT JOIN storage_unit s ON i.lokasi_gudang_id = s.gudang_id
    LEFT JOIN vendor v ON i.vendor_id = v.vendor_id
";
$result = $conn->query($query);

// Query untuk mendapatkan daftar lokasi gudang dan vendor untuk dropdown
$storage_units_query = "SELECT * FROM storage_unit";
$storage_units_result = $conn->query($storage_units_query);

$vendors_query = "SELECT * FROM vendor";
$vendors_result = $conn->query($vendors_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang & Daftar Barang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Simple styles for modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-width: 500px;
        }
    </style>
    <script>
    function openModal(id, name, type, stock, location, price, vendor) {
        document.getElementById('modal').style.display = 'flex';
        document.getElementById('update_barang_id').value = id;
        document.getElementById('update_nama_barang').value = name;
        document.getElementById('update_jenis_barang').value = type;
        document.getElementById('update_kuantitas_stok').value = stock;
        document.getElementById('update_harga').value = price;

        // Set the selected location in the dropdown
        var locationDropdown = document.getElementById('update_lokasi_gudang_id');
        for (var i = 0; i < locationDropdown.options.length; i++) {
            if (locationDropdown.options[i].value == location) {
                locationDropdown.selectedIndex = i;
                break;
            }
        }

        // Set the selected vendor in the dropdown
        var vendorDropdown = document.getElementById('update_vendor_id');
        for (var i = 0; i < vendorDropdown.options.length; i++) {
            if (vendorDropdown.options[i].value == vendor) {
                vendorDropdown.selectedIndex = i;
                break;
            }
        }
    }

    function closeModal() {
        document.getElementById('modal').style.display = 'none';
    }

    function deleteItem(id) {
        if (confirm('Apakah Anda yakin ingin menghapus item ini?')) {
            $.post('', { delete_item_id: id }, function(response) {
                if (response.success) {
                    alert('Item berhasil dihapus');
                    location.reload();
                } else {
                    alert('Gagal menghapus item: ' + response.error);
                }
            }, 'json');
        }
    }
</script>

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
                
                <!-- Dropdown untuk Vendor -->
                <select name="vendor_id" required class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Pilih Vendor</option>
                    <?php while ($row = $vendors_result->fetch_assoc()) { ?>
                    <option value="<?php echo $row['vendor_id']; ?>"><?php echo htmlspecialchars($row['nama_vendor']); ?></option>
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
                        <th class="px-6 py-3 border-b border-gray-300 text-left text-sm font-semibold text-gray-700">Aksi</th>
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
                            <button onclick="openModal(<?php echo $row['barang_id']; ?>, '<?php echo htmlspecialchars($row['nama_barang']); ?>', '<?php echo htmlspecialchars($row['jenis_barang']); ?>', <?php echo $row['kuantitas_stok']; ?>, <?php echo $row['lokasi_gudang_id']; ?>, <?php echo $row['harga']; ?>, <?php echo $row['vendor_id']; ?>)" class="text-blue-500 hover:underline ml-2">Update</button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="delete_item_id" value="<?php echo $row['barang_id']; ?>">
                                <button type="submit" name="delete_item" class="text-red-500 hover:underline ml-2" onclick="return confirm('Apakah Anda yakin ingin menghapus item ini?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Update -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <h2 class="text-2xl font-bold text-gray-700 mb-4">Update Barang</h2>
            <form method="POST">
                <input type="hidden" id="update_barang_id" name="barang_id">
                <input type="text" id="update_nama_barang" name="nama_barang" placeholder="Nama Barang" required class="w-full px-4 py-2 border rounded-md mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="text" id="update_jenis_barang" name="jenis_barang" placeholder="Jenis Barang" required class="w-full px-4 py-2 border rounded-md mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="number" id="update_kuantitas_stok" name="kuantitas_stok" placeholder="Kuantitas Stok" required class="w-full px-4 py-2 border rounded-md mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500">
                
                <!-- Dropdown untuk Lokasi Gudang -->
                <select id="update_lokasi_gudang_id" name="lokasi_gudang_id" required class="w-full px-4 py-2 border rounded-md mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Pilih Lokasi Gudang</option>
                    <?php 
                    // Reset the result set pointer
                    $storage_units_result->data_seek(0); 
                    while ($row = $storage_units_result->fetch_assoc()) { 
                    ?>
                    <option value="<?php echo $row['gudang_id']; ?>"><?php echo htmlspecialchars($row['nama_gudang']); ?></option>
                    <?php } ?>
                </select>
                
                <!-- Dropdown untuk Vendor -->
                <select id="update_vendor_id" name="vendor_id" required class="w-full px-4 py-2 border rounded-md mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Pilih Vendor</option>
                    <?php 
                    // Reset the result set pointer
                    $vendors_result->data_seek(0); 
                    while ($row = $vendors_result->fetch_assoc()) { 
                    ?>
                    <option value="<?php echo $row['vendor_id']; ?>"><?php echo htmlspecialchars($row['nama_vendor']); ?></option>
                    <?php } ?>
                </select>
                
                <input type="number" id="update_harga" step="0.01" name="harga" placeholder="Harga" required class="w-full px-4 py-2 border rounded-md mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" name="update_item" class="w-full bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600">Update Barang</button>
                <button type="button" onclick="closeModal()" class="w-full bg-red-500 text-white py-2 rounded-md mt-2 hover:bg-red-600">Cancel</button>
            </form>
        </div>
    </div>

</body>
</html>
