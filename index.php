<?php
session_start();
include 'db.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header('Location: login.php');
    exit;
}

// Tentukan halaman yang akan dimuat
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
// Fitur pencarian
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$search_results = [];

if (!empty($search_query)) {
    $stmt = $conn->prepare("SELECT * FROM inventory WHERE nama_barang LIKE ? OR jenis_barang LIKE ?");
    $search_param = "%$search_query%";
    $stmt->bind_param("ss", $search_param, $search_param);
    $stmt->execute();
    $search_results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Cek untuk barang dengan stok rendah
$low_stock_threshold = 10; // Setel ambang batas yang diinginkan
$low_stock_items = [];
$stmt = $conn->prepare("SELECT * FROM inventory WHERE kuantitas_stok <= ?");
$stmt->bind_param("i", $low_stock_threshold);
$stmt->execute();
$low_stock_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen bg-blue-50">
    <div class="w-64 bg-blue-200 text-blue-800">
        <div class="p-4">
            <ul class="space-y-2">
                <li>
                    <a href="index.php?page=dashboard" class="flex items-center p-3 hover:bg-blue-300 rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 mr-2">
                            <rect x="3" y="3" width="7" height="9"/>
                            <rect x="14" y="3" width="7" height="5"/>
                            <rect x="14" y="12" width="7" height="9"/>
                            <rect x="3" y="16" width="7" height="5"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=inventory" class="flex items-center p-3 hover:bg-blue-300 rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 mr-2">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                        </svg>
                        <span>Daftar Barang</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=laporan" class="flex items-center p-3 hover:bg-blue-300 rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 mr-2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                        <span>Buat Laporan</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=storage_unit" class="flex items-center p-3 hover:bg-blue-300 rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 mr-2">
                            <path d="M3 3h18v18H3zM3 9h18M3 15h18M9 9v12M15 9v12"/>
                        </svg>
                        <span>Manajemen Gudang</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=transaksi_barang" class="flex items-center p-3 hover:bg-blue-300 rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 mr-2">
                            <line x1="12" y1="1" x2="12" y2="23"/>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            <polyline points="16 2 12 6 8 2"/>
                            <polyline points="8 22 12 18 16 22"/>
                        </svg>
                        <span>Transaksi Barang</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=vendor" class="flex items-center p-3 hover:bg-blue-300 rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 mr-2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                            <path d="M12 15v3m-3-3h6"/>
                        </svg>
                        <span>Manajemen Vendor</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php" class="flex items-center p-3 hover:bg-blue-300 rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 mr-2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="flex-grow p-6 bg-white">
        <!-- Form pencarian -->
        <form action="index.php" method="GET" class="mb-6">
            <div class="flex items-center">
                <input type="text" name="search" placeholder="Cari barang..." value="<?php echo htmlspecialchars($search_query); ?>" class="p-2 w-full border rounded-md">
                <button type="submit" class="ml-2 bg-blue-600 text-white p-2 rounded-md">Cari</button>
            </div>
        </form>

        <!-- Tampilkan hasil pencarian -->
        <?php if (!empty($search_query)): ?>
            <h2 class="text-xl mb-4">Hasil Pencarian untuk "<?php echo htmlspecialchars($search_query); ?>"</h2>
            <?php if (!empty($search_results)): ?>
                <ul class="list-disc pl-6">
                    <?php foreach ($search_results as $item): ?>
                        <li class="mb-2">
                            <a href="detail_barang.php?barang_id=<?php echo $item['barang_id']; ?>" class="text-blue-600 hover:underline">
                                <?php echo htmlspecialchars($item['nama_barang']); ?> 
                                (Stok: <?php echo $item['kuantitas_stok']; ?>)
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-red-500">Tidak ada hasil yang ditemukan.</p>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Peringatan stok rendah -->
        <?php if (!empty($low_stock_items)): ?>
            <div class="alert bg-red-500 text-white p-4 rounded-md mb-6">
                <strong>Peringatan Stok Rendah:</strong>
                <ul class="list-disc pl-6">
                    <?php foreach ($low_stock_items as $item): ?>
                        <li class="mb-2">
                            <a href="detail_barang.php?barang_id=<?php echo $item['barang_id']; ?>" class="text-white hover:underline">
                                <?php echo htmlspecialchars($item['nama_barang']); ?> 
                                (Stok: <?php echo $item['kuantitas_stok']; ?>)
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php
        switch ($page) {
            case 'dashboard':
                include 'dashboard.php';
                break;
            case 'inventory':
                include 'inventory.php';
                break;
            case 'laporan':
                include 'laporan.php';
                break;
            case 'storage_unit':
                include 'storage_unit.php';
                break;
            case 'transaksi_barang':
                include 'transaksi_barang.php';
                break;
            case 'vendor':
                include 'vendor.php';
                break;
            case 'detail_barang':
                include 'detail_barang.php';
                break;
            default:
                include 'dashboard.php';
                break;
        }
        ?>
    </div>
</body>
</html>
