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
    $nama_vendor = $_POST['nama_vendor'];
    $kontak = $_POST['kontak'];
    $nama_barang = $_POST['nama_barang'];
    $nomor_invoice = $_POST['nomor_invoice'];

    // Insert data vendor ke database
    $stmt = $conn->prepare("INSERT INTO vendor (nama_vendor, kontak, nama_barang, nomor_invoice) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama_vendor, $kontak, $nama_barang, $nomor_invoice);

    if ($stmt->execute()) {
        $success_message = "Vendor berhasil ditambahkan!";
    } else {
        $error_message = "Gagal menambahkan vendor: " . $stmt->error;
    }
    $stmt->close();
}

// Query untuk mendapatkan semua vendor
$result = $conn->query("SELECT * FROM vendor");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Vendor</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">
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
                    <input type="text" name="nama_barang" class="w-full p-2 border border-gray-300 rounded-lg" placeholder="Nama Barang" required>
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
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr class="border-b">
                        <td class="p-4"><?php echo htmlspecialchars($row['nama_vendor']); ?></td>
                        <td class="p-4"><?php echo htmlspecialchars($row['kontak']); ?></td>
                        <td class="p-4"><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                        <td class="p-4"><?php echo htmlspecialchars($row['nomor_invoice']); ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
