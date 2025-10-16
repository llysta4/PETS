<?php
header('Content-Type: application/json');

// ===========================================
// 1. KONFIGURASI DATABASE (WAJIB DIGANTI!)
// ===========================================
$servername = "localhost";
$username = "root";     // Username default XAMPP
$password = "";         // Password default XAMPP
$dbname = "nilai-pe-1 & 2"; // <<< GANTI DENGAN NAMA DATABASE ANDA (Contoh: 'pets')

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    // Tampilkan error jika koneksi gagal
    echo json_encode(["success" => false, "message" => "Koneksi database gagal: " . $conn->connect_error]);
    exit();
}

// ===========================================
// 2. QUERY UNTUK MENGAMBIL SEMUA DATA
// ===========================================
$sql = "SELECT * FROM `pets` ORDER BY nama_siswa ASC"; // Ganti 'pets' sesuai nama tabel Anda
$result = $conn->query($sql);

$reports = [];

if ($result === false) {
    // Tampilkan error jika query gagal (misal: nama tabel salah)
    echo json_encode(["success" => false, "message" => "Error saat menjalankan query: " . $conn->error]);
    $conn->close();
    exit();
}

// ===========================================
// 3. AMBIL DATA DAN KONVERSI KE ARRAY
// ===========================================
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Data diambil sebagai array asosiatif (sudah termasuk JSON string untuk nilai mapel)
        $reports[] = $row;
    }
    // Kirim seluruh array data sebagai respons JSON
    echo json_encode(["success" => true, "data" => $reports]);
} else {
    // Tidak ada data (tetapi ini dianggap berhasil, hanya datanya kosong)
    echo json_encode(["success" => true, "data" => []]);
}

$conn->close();
?>