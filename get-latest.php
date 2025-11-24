<?php
// get-latest.php
include 'koneksi.php';
header('Content-Type: application/json'); // Penting: Memberitahu browser bahwa ini adalah respons JSON

// Query untuk mengambil data terbaru (1 data terakhir)
$sql = "SELECT tds, suhu, kualitas, timestamp FROM data_sensor ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Ambil data sebagai associative array
    $row = $result->fetch_assoc();
    
    // Konversi nilai desimal menjadi float/angka jika perlu (PHP menanganinya dengan baik)
    $data = [
        'tds' => (float)$row['tds'],
        'suhu' => (float)$row['suhu'],
        'kualitas' => (float)$row['kualitas'],
        'timestamp' => $row['timestamp']
    ];
    
    // Kirim data dalam format JSON
    echo json_encode($data);
} else {
    // Kirim data default jika database kosong
    echo json_encode([
        "tds" => 0.0, 
        "suhu" => 0.0, 
        "kualitas" => 0.0, 
        "timestamp" => date("Y-m-d H:i:s")
    ]);
}

$conn->close();
?>