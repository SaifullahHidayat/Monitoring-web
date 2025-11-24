<?php
// koneksi.php

// Konfigurasi Database
$servername = "localhost";
$username = "root";     // **Ganti** dengan username database Anda yang sebenarnya
$password = "";         // **Ganti** dengan password database Anda yang sebenarnya
$dbname = "db_aquarium";

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    // Jika koneksi gagal, hentikan skrip dan tampilkan pesan error
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Opsional: Atur zona waktu ke Asia/Jakarta agar timestamp sesuai
date_default_timezone_set('Asia/Jakarta');
?>