CREATE TABLE admin (
    admin_id INT PRIMARY KEY AUTO_INCREMENT, -- ID unik untuk setiap admin
    nama VARCHAR(100) NOT NULL, -- Nama admin
    kontak VARCHAR(15), -- Nomor telepon admin
    email VARCHAR(100) UNIQUE NOT NULL, -- Email admin
    password VARCHAR(255) NOT NULL -- Password admin untuk login (hashed)
);

CREATE TABLE inventory (
    barang_id INT PRIMARY KEY AUTO_INCREMENT, -- ID unik untuk setiap barang
    nama_barang VARCHAR(100) NOT NULL, -- Nama barang
    jenis_barang VARCHAR(100), -- Jenis barang atau kategori
    kuantitas_stok INT DEFAULT 0, -- Jumlah stok barang
    lokasi_gudang_id INT, -- Referensi ke lokasi gudang
    barcode VARCHAR(50) UNIQUE NOT NULL, -- Barcode unik untuk barang
    harga DECIMAL(10, 2) NOT NULL, -- Harga barang
    vendor_id INT, -- Referensi ke vendor
    FOREIGN KEY (lokasi_gudang_id) REFERENCES storage_unit(gudang_id), -- Relasi ke tabel storage_unit
    FOREIGN KEY (vendor_id) REFERENCES vendor(vendor_id) -- Relasi ke tabel vendor
);

CREATE TABLE storage_unit (
    gudang_id INT PRIMARY KEY AUTO_INCREMENT, -- ID unik untuk setiap gudang
    nama_gudang VARCHAR(100) NOT NULL, -- Nama gudang
    lokasi VARCHAR(255) NOT NULL -- Alamat atau lokasi gudang
);

CREATE TABLE vendor (
    vendor_id INT PRIMARY KEY AUTO_INCREMENT, -- ID unik untuk setiap vendor
    nama_vendor VARCHAR(100) NOT NULL, -- Nama vendor
    kontak VARCHAR(15), -- Nomor telepon vendor
    nama_barang VARCHAR(100), -- Nama barang yang disediakan oleh vendor
    nomor_invoice VARCHAR(50) -- Nomor invoice untuk melakukan tracking transaksi dengan vendor
);

CREATE TABLE transaksi_inventory (
    transaksi_id INT PRIMARY KEY AUTO_INCREMENT, -- ID unik untuk setiap transaksi
    barang_id INT, -- Referensi ke barang
    jumlah INT NOT NULL, -- Jumlah barang yang ditambah/dikurangi
    tipe_transaksi ENUM('masuk', 'keluar') NOT NULL, -- Tipe transaksi: 'masuk' atau 'keluar'
    tanggal_transaksi DATETIME DEFAULT CURRENT_TIMESTAMP, -- Waktu transaksi
    admin_id INT, -- Referensi ke admin yang melakukan transaksi
    FOREIGN KEY (barang_id) REFERENCES inventory(barang_id), -- Relasi ke tabel inventory
    FOREIGN KEY (admin_id) REFERENCES admin(admin_id) -- Relasi ke tabel admin
);

CREATE TABLE alert_stok_habis (
    alert_id INT PRIMARY KEY AUTO_INCREMENT, -- ID unik untuk setiap alert
    barang_id INT, -- Referensi ke barang yang stoknya habis
    kuantitas_stok INT NOT NULL, -- Jumlah stok yang tersisa
    tanggal_alert DATETIME DEFAULT CURRENT_TIMESTAMP, -- Waktu alert dibuat
    FOREIGN KEY (barang_id) REFERENCES inventory(barang_id) -- Relasi ke tabel inventory
);

CREATE TABLE laporan_inventory (
    laporan_id INT PRIMARY KEY AUTO_INCREMENT, -- ID unik untuk setiap laporan
    admin_id INT, -- Referensi ke admin yang membuat laporan
    periode_awal DATE, -- Periode awal laporan
    periode_akhir DATE, -- Periode akhir laporan
    tanggal_laporan DATETIME DEFAULT CURRENT_TIMESTAMP, -- Tanggal laporan dibuat
    FOREIGN KEY (admin_id) REFERENCES admin(admin_id) -- Relasi ke tabel admin
);
