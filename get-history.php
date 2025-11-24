<?php
// get-history.php
include 'koneksi.php';
header('Content-Type: application/json');

// Query untuk mengambil 10 data terakhir
$sql = "SELECT tds, suhu, kualitas, timestamp FROM data_sensor ORDER BY id DESC LIMIT 10";
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Simpan dalam array. array_unshift digunakan agar data yang paling LAMA berada di indeks 0
        // sehingga grafik Chart.js ditampilkan secara kronologis (kiri ke kanan)
        array_unshift($data, [
            'tds' => (float)$row['tds'],
            'suhu' => (float)$row['suhu'],
            'kualitas' => (float)$row['kualitas'],
            'timestamp' => $row['timestamp']
        ]); 
    }
} 

echo json_encode($data);
$conn->close();
?>