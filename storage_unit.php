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
    $nama_gudang = $_POST['nama_gudang'];
    $lokasi = $_POST['lokasi'];

    // Insert data gudang ke database
    $stmt = $conn->prepare("INSERT INTO storage_unit (nama_gudang, lokasi) VALUES (?, ?)");
    $stmt->bind_param("ss", $nama_gudang, $lokasi);
    
    if ($stmt->execute()) {
        $success_message = "Gudang berhasil ditambahkan!";
    } else {
        $error_message = "Gagal menambahkan gudang. Error: " . $stmt->error;
    }
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
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg">Tambah Gudang</button>

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
            <ul class="space-y-3">
                <?php while ($row = $result->fetch_assoc()) { ?>
                <li class="flex justify-between items-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <span class="font-medium text-gray-700"><?php echo htmlspecialchars($row['nama_gudang']); ?></span>
                    <span class="text-gray-500"><?php echo htmlspecialchars($row['lokasi']); ?></span>
                </li>
                <?php } ?>
            </ul>
        </div>
    </div>

</body>
</html>
