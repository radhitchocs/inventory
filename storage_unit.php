<?php
session_start();
include 'db.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$success_message = $error_message = '';

// Handle Add Storage Unit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_storage'])) {
    $nama_gudang = $_POST['nama_gudang'];
    $lokasi = $_POST['lokasi'];

    $stmt = $conn->prepare("INSERT INTO storage_unit (nama_gudang, lokasi) VALUES (?, ?)");
    $stmt->bind_param("ss", $nama_gudang, $lokasi);
    
    if ($stmt->execute()) {
        $success_message = "Gudang berhasil ditambahkan!";
    } else {
        $error_message = "Gagal menambahkan gudang. Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle Delete Storage Unit
if (isset($_POST['delete_storage']) && isset($_POST['delete_storage_id'])) {
    $delete_storage_id = intval($_POST['delete_storage_id']);
    
    // Check if storage unit is referenced in inventory
    $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM inventory WHERE lokasi_gudang_id = ?");
    $check_stmt->bind_param("i", $delete_storage_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();
    $is_referenced = $row['count'] > 0;
    $check_stmt->close();

    if ($is_referenced) {
        $error_message = "Tidak dapat menghapus gudang karena masih terdapat barang di dalamnya.";
    } else {
        $stmt = $conn->prepare("DELETE FROM storage_unit WHERE gudang_id = ?");
        $stmt->bind_param("i", $delete_storage_id);
        if ($stmt->execute()) {
            $success_message = "Gudang berhasil dihapus!";
        } else {
            $error_message = "Gagal menghapus gudang. Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle Update Storage Unit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_storage'])) {
    $gudang_id = $_POST['gudang_id'];
    $nama_gudang = $_POST['nama_gudang'];
    $lokasi = $_POST['lokasi'];
    
    $stmt = $conn->prepare("UPDATE storage_unit SET nama_gudang = ?, lokasi = ? WHERE gudang_id = ?");
    $stmt->bind_param("ssi", $nama_gudang, $lokasi, $gudang_id);
    
    if ($stmt->execute()) {
        $success_message = "Gudang berhasil diperbarui!";
    } else {
        $error_message = "Gagal memperbarui gudang. Error: " . $stmt->error;
    }
    $stmt->close();
}

// Query untuk mendapatkan semua gudang
$result = $conn->query("SELECT * FROM storage_unit");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Gudang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function openModal(id, name, location) {
            document.getElementById('modal').style.display = 'flex';
            document.getElementById('update_gudang_id').value = id;
            document.getElementById('update_nama_gudang').value = name;
            document.getElementById('update_lokasi').value = location;
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }
    </script>
    <style>
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
</head>
<body class="bg-gray-100 p-8">

    <div class="max-w-3xl mx-auto">
        <!-- Formulir Tambah Gudang -->
        <div class="bg-white p-6 rounded-lg shadow-lg mb-8">
            <h1 class="text-2xl font-bold text-gray-700 mb-4">Tambah Gudang Baru</h1>

            <form method="POST" class="space-y-4">
                <div>
                    <label for="nama_gudang" class="block text-gray-600">Nama Gudang</label>
                    <input type="text" name="nama_gudang" class="w-full p-2 border border-gray-300 rounded-lg" placeholder="Masukkan nama gudang" required>
                </div>
                <div>
                    <label for="lokasi" class="block text-gray-600">Lokasi Gudang</label>
                    <input type="text" name="lokasi" class="w-full p-2 border border-gray-300 rounded-lg" placeholder="Masukkan lokasi gudang" required>
                </div>
                <button type="submit" name="add_storage" class="px-4 py-2 bg-blue-500 text-white rounded-lg">Tambah Gudang</button>

                <!-- Pesan Sukses atau Error -->
                <?php if ($success_message) { ?>
                    <p class="text-green-500 mt-2"><?php echo $success_message; ?></p>
                <?php } ?>
                <?php if ($error_message) { ?>
                    <p class="text-red-500 mt-2"><?php echo $error_message; ?></p>
                <?php } ?>
            </form>
        </div>

        <!-- Daftar Gudang -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-bold text-gray-700 mb-4">Daftar Gudang</h2>
            <!-- Header Table -->
            <div class="grid grid-cols-4 gap-4 mb-4 bg-gray-100 p-4 rounded-lg border border-gray-200">
                <span class="font-medium text-gray-600">Nama Gudang</span>
                <span class="font-medium text-gray-600">Lokasi</span>
                <span class="font-medium text-gray-600">Aksi</span>
                <span></span> <!-- Empty column for spacing -->
            </div>

            <!-- Body Table -->
            <div>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <div class="grid grid-cols-4 gap-4 border-b border-gray-200 p-4">
                        <span class="text-gray-600"><?php echo htmlspecialchars($row['nama_gudang']); ?></span>
                        <span class="text-gray-600"><?php echo htmlspecialchars($row['lokasi']); ?></span>
                        <div class="flex space-x-2">
                            <button onclick="openModal('<?php echo $row['gudang_id']; ?>', '<?php echo htmlspecialchars($row['nama_gudang']); ?>', '<?php echo htmlspecialchars($row['lokasi']); ?>')" class="text-blue-500 hover:underline">Update</button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="delete_storage_id" value="<?php echo $row['gudang_id']; ?>">
                                <button type="submit" name="delete_storage" class="text-red-500 hover:underline">Hapus</button>
                            </form>
                        </div>
                        <span></span> <!-- Empty column for spacing -->
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Modal Update -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <h2 class="text-2xl font-bold text-gray-700 mb-4">Update Gudang</h2>
            <form method="POST">
                <input type="hidden" id="update_gudang_id" name="gudang_id">
                <div class="mb-4">
                    <label for="update_nama_gudang" class="block text-gray-600">Nama Gudang</label>
                    <input type="text" id="update_nama_gudang" name="nama_gudang" class="w-full p-2 border border-gray-300 rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label for="update_lokasi" class="block text-gray-600">Lokasi Gudang</label>
                    <input type="text" id="update_lokasi" name="lokasi" class="w-full p-2 border border-gray-300 rounded-lg" required>
                </div>
                <button type="submit" name="update_storage" class="px-4 py-2 bg-blue-500 text-white rounded-lg">Update Gudang</button>
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg ml-2">Cancel</button>
            </form>
        </div>
    </div>

</body>
</html>