<?php
// data_historis.php

// === 1. SETUP SESI & KONEKSI ===
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin';

// Include file koneksi
include 'koneksi.php';

// === 2. FUNGSI EKSPOR CSV ===
if (isset($_POST['export_csv'])) {
    // Tangkap rentang waktu yang mungkin disubmit
    $start_date_export = $_POST['start_date'] ?? null;
    $end_date_export = $_POST['end_date'] ?? null;

    // Persiapkan nama file CSV
    $filename = "data_historis_aquarium_" . date('Ymd_His') . ".csv";
    
    // Header untuk memaksa download file
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    // Buka output stream
    $output = fopen('php://output', 'w');

    // Tulis header kolom ke CSV
    fputcsv($output, array('Timestamp', 'TDS (ppm)', 'Suhu (C)', 'Kualitas Air (Score)', 'Status Kualitas'));

    // Persiapkan Query Ekspor
    $sql_export = "SELECT timestamp, tds, suhu, kualitas FROM data_sensor ";
    $where_export = [];

    if (!empty($start_date_export)) {
        $where_export[] = "timestamp >= '" . $conn->real_escape_string($start_date_export) . " 00:00:00'";
    }
    if (!empty($end_date_export)) {
        $where_export[] = "timestamp <= '" . $conn->real_escape_string($end_date_export) . " 23:59:59'";
    }

    if (!empty($where_export)) {
        $sql_export .= " WHERE " . implode(" AND ", $where_export);
    }
    
    $sql_export .= " ORDER BY timestamp DESC";

    $result_export = $conn->query($sql_export);

    // Tulis data ke CSV
    if ($result_export->num_rows > 0) {
        while($row = $result_export->fetch_assoc()) {
            // Logic Status untuk CSV
            $statusText = '';
            if ($row["kualitas"] >= 80) {
                $statusText = 'SANGAT BAIK';
            } elseif ($row["kualitas"] >= 60) {
                $statusText = 'BAIK';
            } elseif ($row["kualitas"] >= 40) {
                $statusText = 'NORMAL';
            } elseif ($row["kualitas"] >= 20) {
                $statusText = 'BURUK';
            } else {
                $statusText = 'SANGAT BURUK';
            }
            
            // Format data sesuai urutan header
            fputcsv($output, array(
                date('d-M-Y H:i:s', strtotime($row["timestamp"])),
                round($row["tds"]),
                number_format($row["suhu"], 2),
                number_format($row["kualitas"], 2),
                $statusText
            ));
        }
    }

    fclose($output);
    exit;
}

// === 3. LOGIKA PAGINATION & FILTERING ===

$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Ambil input filter dari GET (bukan POST)
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Array untuk menampung kondisi WHERE
$where_clauses = [];

if (!empty($start_date)) {
    $where_clauses[] = "DATE(timestamp) >= '" . $conn->real_escape_string($start_date) . "'";
}
if (!empty($end_date)) {
    $where_clauses[] = "DATE(timestamp) <= '" . $conn->real_escape_string($end_date) . "'";
}

// Bangun string WHERE
$where_query = '';
if (!empty($where_clauses)) {
    $where_query = " WHERE " . implode(" AND ", $where_clauses);
}

// Query untuk Menghitung Total Data
$sql_total = "SELECT COUNT(id) AS total FROM data_sensor" . $where_query;
$result_total = $conn->query($sql_total);
$row_total = $result_total->fetch_assoc();
$total_records = $row_total['total'];
$total_pages = ceil($total_records / $limit);

// Query untuk Mengambil Data Halaman Saat Ini
$sql_data = "SELECT timestamp, tds, suhu, kualitas FROM data_sensor " . $where_query . " ORDER BY timestamp DESC LIMIT $start, $limit";
$result_data = $conn->query($sql_data);

// Dapatkan string query saat ini untuk mempertahankan filter di pagination link
$current_query_params = http_build_query(array_filter([
    'start_date' => $start_date,
    'end_date' => $end_date
]));

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Historis - Aquarium Monitor</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/data.css">
    
   
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="dashboard-sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-brand">
                    <i class="fas fa-fish"></i>
                    <span>AquaMonitor</span>
                </a>
            </div>
            
            <div class="sidebar-nav">
                <div class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="data_historis.php" class="nav-link active">
                        <i class="fas fa-history"></i>
                        <span>Data Historis</span>
                    </a>
                </div>
                
                <!-- Logout Section -->
                <div class="nav-item mt-auto">
                    <div class="sidebar-footer">
                        <div class="user-info">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($username, 0, 1)); ?>
                            </div>
                            <div class="user-details">
                                <div class="user-name"><?php echo $username; ?></div>
                                <div class="user-role">Administrator</div>
                            </div>
                        </div>
                        <button class="logout-btn" id="logoutBtn">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Keluar dari Sistem</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="dashboard-content">
            <!-- Top Navigation -->
            <nav class="top-navbar">
                <button class="nav-toggle" id="navToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="page-title">
                    <h1>Data Historis Sensor</h1>
                    <p>Riwayat lengkap pembacaan kualitas air akuarium</p>
                </div>
                
                <div class="last-update">
                    <span class="text-muted small">User: <?php echo $username; ?></span>
                </div>
            </nav>
            
            <!-- Main Content -->
            <div class="main-content">
                <!-- Filter Section -->
                <div class="filter-section fade-in">
                    <div class="filter-header">
                        <h3 class="filter-title">Filter Data</h3>
                        <span class="total-records"><?php echo number_format($total_records); ?> Data</span>
                    </div>
                    
                    <!-- Form Filter (GET Method) -->
                    <form method="GET" action="data_historis.php" class="row g-3 align-items-end">
                        <div class="col-md-4 col-lg-3">
                            <label for="start_date" class="form-label">Dari Tanggal:</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                        </div>
                        <div class="col-md-4 col-lg-3">
                            <label for="end_date" class="form-label">Sampai Tanggal:</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                        </div>
                        <div class="col-md-4 col-lg-6">
                            <div class="d-flex gap-2 filter-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Terapkan Filter
                                </button>
                                <a href="data_historis.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Form Ekspor CSV (POST Method) - Terpisah -->
                    <form method="POST" action="data_historis.php" class="mt-3">
                        <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                        <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                        <button type="submit" name="export_csv" class="btn btn-success">
                            <i class="fas fa-file-csv"></i> Ekspor ke CSV (<?php echo number_format($total_records); ?> Data)
                        </button>
                    </form>
                </div>
                
                <!-- Table Section -->
                <div class="table-container fade-in">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Timestamp</th>
                                    <th>TDS (ppm)</th>
                                    <th>Suhu (°C)</th>
                                    <th>Kualitas Air</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result_data->num_rows > 0) {
                                    $no = $start + 1;
                                    while($row = $result_data->fetch_assoc()) {
                                        // Logic Status untuk tabel
                                        $statusText = '';
                                        $statusClass = '';

                                        if ($row["kualitas"] >= 80) {
                                            $statusText = 'SANGAT BAIK';
                                            $statusClass = 'status-excellent';
                                        } elseif ($row["kualitas"] >= 60) {
                                            $statusText = 'BAIK';
                                            $statusClass = 'status-good';
                                        } elseif ($row["kualitas"] >= 40) {
                                            $statusText = 'NORMAL';
                                            $statusClass = 'status-normal';
                                        } elseif ($row["kualitas"] >= 20) {
                                            $statusText = 'BURUK';
                                            $statusClass = 'status-poor';
                                        } else {
                                            $statusText = 'SANGAT BURUK';
                                            $statusClass = 'status-very-poor';
                                        }
                                        
                                        $formattedTime = date('d-M-Y H:i:s', strtotime($row["timestamp"]));
                                        
                                        echo "<tr>";
                                        echo "<td class='fw-semibold'>" . $no++ . "</td>";
                                        echo "<td>" . $formattedTime . "</td>";
                                        echo "<td class='fw-bold text-primary'>" . round($row["tds"]) . "</td>";
                                        echo "<td class='fw-bold text-warning'>" . number_format($row["suhu"], 2) . "</td>";
                                        echo "<td class='fw-bold'>" . number_format($row["kualitas"], 2) . "</td>";
                                        echo "<td><span class='status-badge " . $statusClass . "'>" . $statusText . "</span></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center text-muted py-4'>";
                                    echo "<i class='fas fa-inbox fa-2x mb-3 d-block'></i>";
                                    echo "Tidak ada data yang ditemukan untuk filter ini.";
                                    echo "</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination-container fade-in">
                        <div class="pagination-info">
                            Menampilkan <?php echo min($limit, $result_data->num_rows); ?> dari <?php echo number_format($total_records); ?> data
                            (Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?>)
                        </div>
                        
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&<?php echo $current_query_params; ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>

                                <?php 
                                $start_loop = max(1, $page - 2);
                                $end_loop = min($total_pages, $page + 2);
                                
                                if ($start_loop > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?page=1&' . $current_query_params . '">1</a></li>';
                                    if ($start_loop > 2) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                }

                                for ($i = $start_loop; $i <= $end_loop; $i++): 
                                ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo $current_query_params; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php
                                if ($end_loop < $total_pages) {
                                    if ($end_loop < $total_pages - 1) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '&' . $current_query_params . '">' . $total_pages . '</a></li>';
                                }
                                ?>
                                
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&<?php echo $current_query_params; ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php $conn->close(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ===============================================
        // 1. Sidebar Toggle
        // ===============================================
        document.getElementById('navToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // ===============================================
        // 2. Konfirmasi Logout
        // ===============================================
        document.getElementById('logoutBtn').addEventListener('click', function() {
            const konfirmasi = confirm('Apakah Anda yakin ingin keluar dari sistem?');
            
            if (konfirmasi) {
                window.location.href = 'logout.php';
            }
        });

        // ===============================================
        // 3. Auto-set end date to start date if empty
        // ===============================================
        document.getElementById('start_date').addEventListener('change', function() {
            const endDate = document.getElementById('end_date');
            if (!endDate.value) {
                endDate.value = this.value;
            }
        });

        // ===============================================
        // 4. Validasi tanggal (end date tidak boleh sebelum start date)
        // ===============================================
        document.querySelector('form[method="GET"]').addEventListener('submit', function(e) {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
                e.preventDefault();
                alert('Tanggal akhir tidak boleh sebelum tanggal mulai!');
                document.getElementById('end_date').focus();
            }
        });
    </script>
</body>
</html>