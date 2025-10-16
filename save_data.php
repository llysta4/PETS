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
// 2. PROSES PENGAMBILAN & EKSTRAK DATA
// ===========================================
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data)) {
    echo json_encode(["success" => false, "message" => "Data tidak diterima."]);
    exit();
}

// Ekstrak data Identitas
$nisnToEdit = $data['nisn_to_edit'] ?? '-1'; // Digunakan untuk menentukan mode UPDATE
$nama_siswa = $conn->real_escape_string($data['nama_siswa']);
$nis = $conn->real_escape_string($data['nis']);
$nisn = $conn->real_escape_string($data['nisn']);
$kelas = $conn->real_escape_string($data['kelas']);
$sekolah = $conn->real_escape_string($data['sekolah']);
$alamat_sekolah = $conn->real_escape_string($data['alamat_sekolah']);
$tahun_ajaran = $conn->real_escape_string($data['tahun_ajaran']);
$fase = $conn->real_escape_string($data['fase']);
$rata_rata_total = $conn->real_escape_string($data['rata_rata_total']);

// Ambil dan konversi data mapel (berisi array/objek JSON) ke JSON string untuk MySQL
$mapelData = $data['mapelData'];

$nilai_pabp = $conn->real_escape_string(json_encode($mapelData['pabp']));
$nilai_pp = $conn->real_escape_string(json_encode($mapelData['pp']));
$nilai_bindo = $conn->real_escape_string(json_encode($mapelData['bindo']));
$nilai_mat = $conn->real_escape_string(json_encode($mapelData['mat']));
$nilai_pjok = $conn->real_escape_string(json_encode($mapelData['pjok']));
$nilai_sbdp = $conn->real_escape_string(json_encode($mapelData['sbdp']));
$nilai_bsunda = $conn->real_escape_string(json_encode($mapelData['bsunda']));
$nilai_plh = $conn->real_escape_string(json_encode($mapelData['plh']));
$nilai_bing = $conn->real_escape_string(json_encode($mapelData['bing']));
$nilai_tik = $conn->real_escape_string(json_encode($mapelData['tik']));
$nilai_pramuka = $conn->real_escape_string(json_encode($mapelData['pramuka']));


// ===========================================
// 3. LOGIKA INSERT atau UPDATE
// ===========================================

// Jika nisnToEdit ditemukan DAN sama dengan nisn, maka mode murni UPDATE
if ($nisnToEdit !== '-1' && $nisnToEdit === $nisn) {
    $mode = "UPDATE";
    $sql = "UPDATE `pets` SET 
                nis = '$nis',
                nama_siswa = '$nama_siswa',
                kelas = '$kelas',
                sekolah = '$sekolah',
                alamat_sekolah = '$alamat_sekolah',
                tahun_ajaran = '$tahun_ajaran',
                fase = '$fase',
                rata_rata_total = '$rata_rata_total',
                nilai_pabp = '$nilai_pabp',
                nilai_pp = '$nilai_pp',
                nilai_b_indonesia = '$nilai_bindo',
                nilai_mat = '$nilai_mat',
                nilai_pjok = '$nilai_pjok',
                nilai_sbdp = '$nilai_sbdp',
                nilai_b_sunda = '$nilai_bsunda',
                nilai_plh = '$nilai_plh',
                nilai_b_inggris = '$nilai_bing',
                nilai_tik = '$nilai_tik',
                nilai_pramuka = '$nilai_pramuka'
            WHERE nisn = '$nisnToEdit'";
    $message = "Data siswa **$nama_siswa** berhasil diperbarui.";
    
} else {
    // Jika tidak dalam mode edit, atau nisn di form berubah saat edit, Cek duplikasi NISN
    
    // Cek apakah NISN sudah ada di database
    $check_sql = "SELECT nisn FROM `pets` WHERE nisn = '$nisn'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        // NISN sudah ada -> Lakukan UPDATE data lama dengan NISN yang sama
         $mode = "UPDATE_EXISTING";
         $sql = "UPDATE `pets` SET 
                nis = '$nis',
                nama_siswa = '$nama_siswa',
                kelas = '$kelas',
                sekolah = '$sekolah',
                alamat_sekolah = '$alamat_sekolah',
                tahun_ajaran = '$tahun_ajaran',
                fase = '$fase',
                rata_rata_total = '$rata_rata_total',
                nilai_pabp = '$nilai_pabp',
                nilai_pp = '$nilai_pp',
                nilai_b_indonesia = '$nilai_bindo',
                nilai_mat = '$nilai_mat',
                nilai_pjok = '$nilai_pjok',
                nilai_sbdp = '$nilai_sbdp',
                nilai_b_sunda = '$nilai_bsunda',
                nilai_plh = '$nilai_plh',
                nilai_b_inggris = '$nilai_bing',
                nilai_tik = '$nilai_tik',
                nilai_pramuka = '$nilai_pramuka'
            WHERE nisn = '$nisn'";
        $message = "Data siswa **$nama_siswa** berhasil diperbarui (NISN ditemukan).";

    } else {
        // NISN belum ada -> Lakukan INSERT data baru
        $mode = "INSERT";
        $sql = "INSERT INTO `pets` 
                (nis, nisn, nama_siswa, kelas, sekolah, alamat_sekolah, tahun_ajaran, fase, rata_rata_total, 
                 nilai_pabp, nilai_pp, nilai_b_indonesia, nilai_mat, nilai_pjok, nilai_sbdp, nilai_b_sunda, nilai_plh, nilai_b_inggris, nilai_tik, nilai_pramuka) 
                VALUES 
                ('$nis', '$nisn', '$nama_siswa', '$kelas', '$sekolah', '$alamat_sekolah', '$tahun_ajaran', '$fase', '$rata_rata_total', 
                 '$nilai_pabp', '$nilai_pp', '$nilai_bindo', '$nilai_mat', '$nilai_pjok', '$nilai_sbdp', '$nilai_bsunda', '$nilai_plh', '$nilai_bing', '$nilai_tik', '$nilai_pramuka')";
        $message = "Data siswa **$nama_siswa** berhasil disimpan (Insert Baru).";
    }
}

// ===========================================
// 4. EKSEKUSI QUERY
// ===========================================
if ($conn->query($sql) === TRUE) {
    echo json_encode(["success" => true, "message" => $message]);
} else {
    echo json_encode(["success" => false, "message" => "Error saat menyimpan data. Query: ($mode) | Error: " . $conn->error]);
}

$conn->close();
?>