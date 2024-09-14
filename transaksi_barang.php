<?php
session_start();
include 'db.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$success_message = $error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $barang_id = $_POST['barang_id'];
    $jumlah = $_POST['jumlah'];
    $tipe_transaksi = $_POST['tipe_transaksi'];

    // Validasi input
    if ($jumlah <= 0) {
        $error_message = "Jumlah barang harus lebih dari 0.";
    } else {
        // Mulai transaksi database
        $conn->begin_transaction();
        try {
            // Periksa stok saat ini
            $stmt = $conn->prepare("SELECT kuantitas_stok, nama_barang FROM inventory WHERE barang_id = ?");
            $stmt->bind_param("i", $barang_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $inventory = $result->fetch_assoc();
            $current_stock = $inventory['kuantitas_stok'];
            $nama_barang = $inventory['nama_barang'];

            if ($tipe_transaksi === 'keluar') {
                if ($jumlah > $current_stock) {
                    throw new Exception("Stok barang '$nama_barang' tidak mencukupi untuk transaksi keluar.");
                }
                $new_stock = $current_stock - $jumlah;
            } else {
                $new_stock = $current_stock + $jumlah;
            }

            // Update stok barang
            $stmt = $conn->prepare("UPDATE inventory SET kuantitas_stok = ? WHERE barang_id = ?");
            $stmt->bind_param("ii", $new_stock, $barang_id);
            $stmt->execute();

            // Insert transaksi barang ke database
            $stmt = $conn->prepare("INSERT INTO transaksi_inventory (barang_id, jumlah, tipe_transaksi, admin_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iisi", $barang_id, $jumlah, $tipe_transaksi, $_SESSION['admin_id']);
            $stmt->execute();

            // Commit transaksi
            $conn->commit();
            $success_message = "Transaksi berhasil disimpan.";
        } catch (Exception $e) {
            // Rollback jika terjadi error
            $conn->rollback();
            $error_message = "Gagal menyimpan transaksi: " . $e->getMessage();
        }
    }
}

// Query untuk mendapatkan semua barang dari inventory
$result = $conn->query("SELECT barang_id, nama_barang FROM inventory");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Barang</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-3xl mx-auto">
        <!-- Form Transaksi Barang -->
        <div class="bg-white p-6 rounded-lg shadow-lg mb-8">
            <h1 class="text-2xl font-bold text-gray-700 mb-4">Transaksi Barang</h1>

            <form method="POST" class="space-y-4">
                <div>
                    <label for="barang_id" class="block text-gray-600">Pilih Barang</label>
                    <select name="barang_id" class="w-full p-2 border border-gray-300 rounded-lg" required>
                        <option value="">Pilih Barang</option>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                        <option value="<?php echo htmlspecialchars($row['barang_id']); ?>">
                            <?php echo htmlspecialchars($row['nama_barang']); ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>
                <div>
                    <label for="jumlah" class="block text-gray-600">Jumlah</label>
                    <input type="number" name="jumlah" class="w-full p-2 border border-gray-300 rounded-lg" placeholder="Masukkan jumlah" required>
                </div>
                <div>
                    <label for="tipe_transaksi" class="block text-gray-600">Tipe Transaksi</label>
                    <select name="tipe_transaksi" class="w-full p-2 border border-gray-300 rounded-lg" required>
                        <option value="masuk">Barang Masuk</option>
                        <option value="keluar">Barang Keluar</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg">Simpan Transaksi</button>

                <!-- Pesan Sukses atau Error -->
                <?php if ($success_message) { ?>
                    <p class="text-green-500 mt-2"><?php echo $success_message; ?></p>
                <?php } ?>
                <?php if ($error_message) { ?>
                    <p class="text-red-500 mt-2"><?php echo $error_message; ?></p>
                <?php } ?>
            </form>
        </div>

        <!-- Daftar Transaksi -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-bold text-gray-700 mb-4">Riwayat Transaksi</h2>
            <table class="w-full border-collapse bg-white text-left">
                <thead>
                    <tr class="border-b">
                        <th class="p-4 text-gray-700">Nama Barang</th>
                        <th class="p-4 text-gray-700">Jumlah</th>
                        <th class="p-4 text-gray-700">Tipe Transaksi</th>
                        <th class="p-4 text-gray-700">Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $transaksi_result = $conn->query("
                        SELECT t.*, i.nama_barang, a.nama AS nama_admin 
                        FROM transaksi_inventory t
                        JOIN inventory i ON t.barang_id = i.barang_id
                        JOIN admin a ON t.admin_id = a.admin_id
                    ");
                    while ($transaksi = $transaksi_result->fetch_assoc()) { ?>
                    <tr class="border-b">
                        <td class="p-4"><?php echo htmlspecialchars($transaksi['nama_barang']); ?></td>
                        <td class="p-4"><?php echo htmlspecialchars($transaksi['jumlah']); ?></td>
                        <td class="p-4"><?php echo htmlspecialchars(ucfirst($transaksi['tipe_transaksi'])); ?></td>
                        <td class="p-4"><?php echo htmlspecialchars($transaksi['nama_admin']); ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
