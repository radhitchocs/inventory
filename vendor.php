<?php

include 'db.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$success_message = $error_message = '';

// Handle Add Vendor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['update_vendor']) && !isset($_POST['delete_vendor'])) {
    $nama_vendor = $_POST['nama_vendor'];
    $kontak = $_POST['kontak'];
    $nomor_invoice = $_POST['nomor_invoice'];
    $barang_id = $_POST['nama_barang']; // Ambil ID barang yang dipilih (asumsi hanya satu barang)

    // Insert data vendor ke database
    $stmt = $conn->prepare("INSERT INTO vendor (nama_vendor, kontak, nomor_invoice, barang_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $nama_vendor, $kontak, $nomor_invoice, $barang_id);

    if ($stmt->execute()) {
        $success_message = "Vendor berhasil ditambahkan!";
    } else {
        $error_message = "Gagal menambahkan vendor: " . $stmt->error;
    }
    $stmt->close();
}

// Handle Update Vendor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_vendor'])) {
    $vendor_id = $_POST['vendor_id'];
    $nama_vendor = $_POST['nama_vendor'];
    $kontak = $_POST['kontak'];
    $nomor_invoice = $_POST['nomor_invoice'];
    $barang_id = $_POST['nama_barang']; // Ambil ID barang yang dipilih (asumsi hanya satu barang)

    // Update data vendor
    $stmt = $conn->prepare("UPDATE vendor SET nama_vendor = ?, kontak = ?, nomor_invoice = ?, barang_id = ? WHERE vendor_id = ?");
    $stmt->bind_param("sssii", $nama_vendor, $kontak, $nomor_invoice, $barang_id, $vendor_id);

    if ($stmt->execute()) {
        $success_message = "Vendor berhasil diperbarui!";
    } else {
        $error_message = "Gagal memperbarui vendor: " . $stmt->error;
    }
    $stmt->close();
}

// Handle Delete Vendor
if (isset($_POST['delete_vendor']) && isset($_POST['delete_vendor_id'])) {
    $vendor_id = intval($_POST['delete_vendor_id']);

    // Check if vendor is referenced in inventory
    $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM inventory WHERE vendor_id = ?");
    $check_stmt->bind_param("i", $vendor_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();
    $is_referenced = $row['count'] > 0;
    $check_stmt->close();

    if (!$is_referenced) {
        // Proceed with deletion
        $stmt = $conn->prepare("DELETE FROM vendor WHERE vendor_id = ?");
        $stmt->bind_param("i", $vendor_id);
        $stmt->execute();
        $stmt->close();

        $success_message = "Vendor berhasil dihapus!";
    } else {
        $error_message = "Vendor tidak dapat dihapus karena masih digunakan.";
    }
}

// Query untuk mendapatkan semua vendor
$result = $conn->query("SELECT * FROM vendor");

// Ambil semua barang dari tabel inventory
$inventory_result = $conn->query("SELECT barang_id, nama_barang FROM inventory");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Vendor</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
<script>
    function openModal(id, name, contact, invoiceNumber, barangId) {
        document.getElementById('modal').style.display = 'flex';
        document.getElementById('update_vendor_id').value = id;
        document.getElementById('update_nama_vendor').value = name;
        document.getElementById('update_kontak').value = contact;
        document.getElementById('update_nomor_invoice').value = invoiceNumber;

        // Set selected barang id
        let selectElement = document.getElementById('update_nama_barang');
        selectElement.value = barangId; // Set the selected value
    }

    function closeModal() {
        document.getElementById('modal').style.display = 'none';
    }
</script>

<div class="max-w-3xl mx-auto">
    <!-- Formulir Tambah Vendor -->
    <div class="bg-white p-6 rounded-lg shadow-lg mb-8">
        <h1 class="text-2xl font-bold text-gray-700 mb-4">Manajemen Vendor</h1>

        <form method="POST" class="space-y-4">
            <div>
                <label for="nama_vendor" class="block text-gray-600">Nama Vendor</label>
                <input type="text" name="nama_vendor" class="w-full p-2 border border-gray-300 rounded-lg" placeholder="Nama Vendor" required>
            </div>
            <div>
                <label for="kontak" class="block text-gray-600">Kontak Vendor</label>
                <input type="text" name="kontak" class="w-full p-2 border border-gray-300 rounded-lg" placeholder="Kontak Vendor" required>
            </div>
            <div>
                <label for="nama_barang" class="block text-gray-600">Nama Barang</label>
                <select name="nama_barang" class="w-full p-2 border border-gray-300 rounded-lg" required>
                    <?php while ($inv_row = $inventory_result->fetch_assoc()) { ?>
                        <option value="<?php echo $inv_row['barang_id']; ?>"><?php echo htmlspecialchars($inv_row['nama_barang']); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div>
                <label for="nomor_invoice" class="block text-gray-600">Nomor Invoice</label>
                <input type="text" name="nomor_invoice" class="w-full p-2 border border-gray-300 rounded-lg" placeholder="Nomor Invoice" required>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg">Tambah Vendor</button>

            <!-- Pesan Sukses atau Error -->
            <?php if ($success_message) { ?>
                <p class="text-green-500 mt-2"><?php echo $success_message; ?></p>
            <?php } ?>
            <?php if ($error_message) { ?>
                <p class="text-red-500 mt-2"><?php echo $error_message; ?></p>
            <?php } ?>
        </form>
    </div>

    <!-- Daftar Vendor -->
    <div class="bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-xl font-bold text-gray-700 mb-4">Daftar Vendor</h2>
        <table class="w-full border-collapse bg-white text-left">
            <thead>
                <tr class="border-b">
                    <th class="p-4 text-gray-700">Nama Vendor</th>
                    <th class="p-4 text-gray-700">Kontak</th>
                    <th class="p-4 text-gray-700">Nama Barang</th>
                    <th class="p-4 text-gray-700">Nomor Invoice</th>
                    <th class="p-4 text-gray-700">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { 
                    // Ambil nama barang berdasarkan barang_id
                    $barang_stmt = $conn->prepare("SELECT nama_barang FROM inventory WHERE barang_id = ?");
                    $barang_stmt->bind_param("i", $row['barang_id']);
                    $barang_stmt->execute();
                    $barang_result = $barang_stmt->get_result();
                    $barang_row = $barang_result->fetch_assoc();
                    $barang_name = htmlspecialchars($barang_row['nama_barang']);
                    $barang_stmt->close();
                ?>
                    <tr>
                        <td class="p-4"><?php echo htmlspecialchars($row['nama_vendor']); ?></td>
                        <td class="p-4"><?php echo htmlspecialchars($row['kontak']); ?></td>
                        <td class="p-4"><?php echo $barang_name; ?></td>
                        <td class="p-4"><?php echo htmlspecialchars($row['nomor_invoice']); ?></td>
                        <td class="p-4">
                            <button onclick="openModal(<?php echo $row['vendor_id']; ?>, '<?php echo htmlspecialchars($row['nama_vendor']); ?>', '<?php echo htmlspecialchars($row['kontak']); ?>', '<?php echo htmlspecialchars($row['nomor_invoice']); ?>', <?php echo $row['barang_id']; ?>)" class="text-blue-500 hover:underline">Edit</button>
                            <form method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus vendor ini?');">
                                <input type="hidden" name="delete_vendor_id" value="<?php echo $row['vendor_id']; ?>">
                                <button type="submit" name="delete_vendor" class="text-red-500 hover:underline ml-2">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit Vendor -->
<div id="modal" class="modal">
    <div class="modal-content">
        <h2 class="text-xl font-bold text-gray-700 mb-4">Edit Vendor</h2>
        <form method="POST">
            <input type="hidden" name="update_vendor" value="1">
            <input type="hidden" id="update_vendor_id" name="vendor_id">
            <div>
                <label for="update_nama_vendor" class="block text-gray-600">Nama Vendor</label>
                <input type="text" id="update_nama_vendor" name="nama_vendor" class="w-full p-2 border border-gray-300 rounded-lg" required>
            </div>
            <div>
                <label for="update_kontak" class="block text-gray-600">Kontak Vendor</label>
                <input type="text" id="update_kontak" name="kontak" class="w-full p-2 border border-gray-300 rounded-lg" required>
            </div>
            <div>
                <label for="update_nama_barang" class="block text-gray-600">Nama Barang</label>
                <select id="update_nama_barang" name="nama_barang" class="w-full p-2 border border-gray-300 rounded-lg" required>
                    <?php 
                    // Reset pointer dan ambil lagi data inventory untuk modal
                    $inventory_result->data_seek(0); 
                    while ($inv_row = $inventory_result->fetch_assoc()) { ?>
                        <option value="<?php echo $inv_row['barang_id']; ?>"><?php echo htmlspecialchars($inv_row['nama_barang']); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div>
                <label for="update_nomor_invoice" class="block text-gray-600">Nomor Invoice</label>
                <input type="text" id="update_nomor_invoice" name="nomor_invoice" class="w-full p-2 border border-gray-300 rounded-lg" required>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-500 text-white rounded-lg mr-2">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg">Simpan</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
