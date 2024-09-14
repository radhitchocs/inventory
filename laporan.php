<?php
session_start();
include 'db.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $periode_awal = $_POST['periode_awal'];
    $periode_akhir = $_POST['periode_akhir'];

    // Insert laporan ke database
    $stmt = $conn->prepare("INSERT INTO laporan_inventory (admin_id, periode_awal, periode_akhir) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $_SESSION['admin_id'], $periode_awal, $periode_akhir);
    
    if ($stmt->execute()) {
        $success_message = "Laporan berhasil dibuat!";
    } else {
        $error_message = "Gagal membuat laporan. Error: " . $stmt->error;
    }
}

// Query untuk mengambil laporan dari database
$query = "
    SELECT l.*, a.nama AS nama_admin
    FROM laporan_inventory l
    LEFT JOIN admin a ON l.admin_id = a.admin_id
    ORDER BY l.tanggal_laporan DESC
";
$laporan_result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Laporan Inventory</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

    <div class="max-w-5xl mx-auto">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h1 class="text-2xl font-bold text-gray-700 mb-6">Buat Laporan</h1>

            <!-- Form untuk membuat laporan baru -->
            <form method="POST" class="space-y-4">
                <div>
                    <label for="periode_awal" class="block text-gray-600">Periode Awal</label>
                    <input type="date" name="periode_awal" class="w-full border border-gray-300 p-2 rounded-lg" required>
                </div>
                <div>
                    <label for="periode_akhir" class="block text-gray-600">Periode Akhir</label>
                    <input type="date" name="periode_akhir" class="w-full border border-gray-300 p-2 rounded-lg" required>
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg">Buat Laporan</button>

                <!-- Pesan sukses atau error -->
                <?php if (isset($success_message)) { ?>
                    <p class="text-green-500 mt-2"><?php echo $success_message; ?></p>
                <?php } ?>
                <?php if (isset($error_message)) { ?>
                    <p class="text-red-500 mt-2"><?php echo $error_message; ?></p>
                <?php } ?>
            </form>

            <!-- Tabel untuk menampilkan laporan yang sudah dibuat -->
            <h2 class="text-xl font-bold text-gray-700 mt-10 mb-4">Daftar Laporan</h2>
            <table class="min-w-full bg-white border border-gray-300">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-6 py-3 border-b text-left text-sm font-semibold text-gray-700">ID Laporan</th>
                        <th class="px-6 py-3 border-b text-left text-sm font-semibold text-gray-700">Admin</th>
                        <th class="px-6 py-3 border-b text-left text-sm font-semibold text-gray-700">Periode Awal</th>
                        <th class="px-6 py-3 border-b text-left text-sm font-semibold text-gray-700">Periode Akhir</th>
                        <th class="px-6 py-3 border-b text-left text-sm font-semibold text-gray-700">Tanggal Laporan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($laporan = $laporan_result->fetch_assoc()) { ?>
                    <tr class="hover:bg-gray-100">
                        <td class="px-6 py-4 border-b border-gray-300"><?php echo htmlspecialchars($laporan['laporan_id']); ?></td>
                        <td class="px-6 py-4 border-b border-gray-300"><?php echo htmlspecialchars($laporan['nama_admin']); ?></td>
                        <td class="px-6 py-4 border-b border-gray-300"><?php echo htmlspecialchars($laporan['periode_awal']); ?></td>
                        <td class="px-6 py-4 border-b border-gray-300"><?php echo htmlspecialchars($laporan['periode_akhir']); ?></td>
                        <td class="px-6 py-4 border-b border-gray-300"><?php echo htmlspecialchars($laporan['tanggal_laporan']); ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
