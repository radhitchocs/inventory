<?php
session_start();
include 'db.php';

// Handle Login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query database untuk cek login
    $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        // Verifikasi password
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            header('Location: index.php');
        } else {
            $login_error = "Password salah!";
        }
    } else {
        $login_error = "Email tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold text-center mb-6">Login Admin</h2>
        
        <!-- Form Login -->
        <form method="POST" class="space-y-4">
            <input type="email" name="email" placeholder="Email" required class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="password" name="password" placeholder="Password" required class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" name="login" class="w-full bg-blue-600 text-white p-2 rounded-md hover:bg-blue-700">Login</button>
            <?php if (isset($login_error)) echo "<p class='text-red-500 text-center mt-4'>$login_error</p>"; ?>
        </form>

        <!-- Link ke halaman registrasi -->
        <p class="text-center mt-4 text-gray-600">Belum punya akun?</p>
        <a href="register.php" class="block w-full bg-green-600 text-white p-2 rounded-md hover:bg-green-700 text-center mt-2">Daftar Sekarang</a>
    </div>
</body>
</html>