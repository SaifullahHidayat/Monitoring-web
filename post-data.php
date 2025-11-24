<?php
// post-data.php
include 'koneksi.php'; // Panggil file koneksi database

// Pastikan request menggunakan method POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Ambil data yang dikirim dari ESP32
    // Menggunakan operator null coalescing (??) untuk menghindari error jika data tidak ada
    $tds = $_POST['tds'] ?? null;
    $suhu = $_POST['suhu'] ?? null;
    $kualitas = $_POST['kualitas'] ?? null;

    // Cek apakah semua data penting sudah diterima
    if ($tds !== null && $suhu !== null && $kualitas !== null) {
        
        // Konversi data ke tipe float untuk memastikan validasi (ESP32 mengirim string)
        $tds_val = floatval($tds);
        $suhu_val = floatval($suhu);
        $kualitas_val = floatval($kualitas);

        // --- Mencegah SQL Injection dengan Prepared Statement ---
        $stmt = $conn->prepare("INSERT INTO data_sensor (tds, suhu, kualitas) VALUES (?, ?, ?)");
        
        // "ddd" = tiga variabel bertipe double (float)
        $stmt->bind_param("ddd", $tds_val, $suhu_val, $kualitas_val); 

        // Jalankan statement
        if ($stmt->execute()) {
            // Respons ke ESP32 bahwa data sukses disimpan
            http_response_code(200); // Set status OK
            echo "Data berhasil disimpan!"; 
        } else {
            // Respons error ke ESP32
            http_response_code(500); // Set status Internal Server Error
            echo "Error saat menyimpan data: " . $stmt->error;
        }
        
        $stmt->close();

    } else {
        // Respons error jika data yang dikirim tidak lengkap
        http_response_code(400); // Set status Bad Request
        echo "Error: Data sensor tidak lengkap (Kurang TDS, Suhu, atau Kualitas).";
    }

} else {
    // Respons jika diakses bukan dengan method POST (misalnya langsung dari browser)
    http_response_code(405); // Set status Method Not Allowed
    echo "Akses ditolak. Endpoint ini hanya menerima permintaan POST.";
}

$conn->close();
?>