<?php
session_start();
include 'db.php';

// Handle Registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $kontak = $_POST['kontak'];

    // Cek apakah email sudah terdaftar
    $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $register_error = "Email sudah terdaftar!";
    } else {
        // Hash password sebelum disimpan
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Simpan data admin baru ke database
        $stmt = $conn->prepare("INSERT INTO admin (nama, email, password, kontak) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama, $email, $hashed_password, $kontak);

        if ($stmt->execute()) {
            $register_success = "Registrasi berhasil! Silakan login.";
        } else {
            $register_error = "Gagal mendaftar, silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold text-center mb-6">Register Admin Baru</h2>
        
        <!-- Form Registrasi -->
        <form method="POST" class="space-y-4">
            <input type="text" name="nama" placeholder="Nama Lengkap" required class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="email" name="email" placeholder="Email" required class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="password" name="password" placeholder="Password" required class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="text" name="kontak" placeholder="Nomor Kontak" required class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" name="register" class="w-full bg-green-600 text-white p-2 rounded-md hover:bg-green-700">Register</button>
            <?php if (isset($register_error)) echo "<p class='text-red-500 text-center mt-4'>$register_error</p>"; ?>
            <?php if (isset($register_success)) echo "<p class='text-green-500 text-center mt-4'>$register_success</p>"; ?>
        </form>

        <!-- Link kembali ke halaman login -->
        <p class="text-center mt-4 text-gray-600">Sudah punya akun?</p>
        <a href="login.php" class="block w-full bg-blue-600 text-white p-2 rounded-md hover:bg-blue-700 text-center mt-2">Kembali ke Login</a>
    </div>
</body>
</html>