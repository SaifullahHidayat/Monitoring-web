<?php
// dashboard.php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Jika belum login, redirect ke halaman login (index.php)
    header('Location: index.php');
    exit;
}

// Tambahkan variabel username untuk ditampilkan (Opsional)
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin';

// Tambahkan variabel untuk menyimpan status alert
$show_water_change_alert = false;
$alert_message = '';
$alert_type = 'warning'; // warning, danger, success, info
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Monitoring Kualitas Air</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/dashboard.css">
    <link rel="icon" href="assets/aquarium.png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
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
                    <a href="dashboard.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="data_historis.php" class="nav-link">
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
                    <h1>Dashboard Monitoring</h1>
                    <p>Sistem Pemantauan Kualitas Air Akuarium</p>
                </div>
                
                <div class="last-update">
                    <i class="fas fa-sync-alt"></i>
                    <span id="lastUpdate">Memuat data...</span>
                </div>
            </nav>
            
            <!-- Main Content -->
            <div class="main-content">
                <!-- Water Change Alert (akan muncul jika kualitas buruk) -->
                <div id="waterChangeAlert" class="water-alert alert alert-danger d-none fade-in">
                    <div class="d-flex align-items-center">
                        <div class="alert-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">⚠️ PERINGATAN: Kualitas Air Buruk!</div>
                            <div class="alert-message" id="alertMessage">
                                Kualitas air akuarium dalam kondisi buruk. Disarankan untuk segera mengganti air akuarium untuk menjaga kesehatan ikan.
                            </div>
                        </div>
                        <button class="alert-close" id="closeAlert">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Stats Cards Row -->
                <div class="row fade-in">
                    <!-- TDS Card -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="card-icon primary">
                                <i class="fas fa-tint"></i>
                            </div>
                            <div class="card-value text-primary" id="cardTds">-- ppm</div>
                            <div class="card-label">Total Dissolved Solids</div>
                            <div class="card-trend up">
                                <i class="fas fa-arrow-up me-1"></i>
                                <span>Stabil</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Temperature Card -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="card-icon warning">
                                <i class="fas fa-thermometer-half"></i>
                            </div>
                            <div class="card-value text-warning" id="cardSuhu">-- °C</div>
                            <div class="card-label">Suhu Air</div>
                            <div class="card-trend up">
                                <i class="fas fa-arrow-up me-1"></i>
                                <span>Normal</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quality Card -->
                    <div class="col-lg-4 col-md-12 mb-4">
                        <div class="quality-card">
                            <div class="quality-header">
                                <div>
                                    <div class="card-label">Kualitas Air</div>
                                    <div class="quality-score" id="cardKualitasScore">--</div>
                                </div>
                                <span class="quality-badge bg-status-excellent text-white" id="cardKualitasStatus">Memuat...</span>
                            </div>
                            
                            <div class="quality-progress">
                                <div class="progress">
                                    <div class="progress-bar bg-status-excellent" id="qualityProgress" style="width: 0%"></div>
                                </div>
                                <div class="quality-footer">
                                    <span>Buruk</span>
                                    <span>Baik</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Chart Section -->
                <div class="chart-container fade-in">
                    <div class="chart-header">
                        <h3 class="chart-title">Grafik Data Historis (10 Data Terakhir)</h3>
                        <div class="chart-actions">
                            <button class="chart-action-btn active">Hari Ini</button>
                            <button class="chart-action-btn">Minggu Ini</button>
                            <button class="chart-action-btn">Bulan Ini</button>
                        </div>
                    </div>
                    <div class="chart-area">
                        <canvas id="realtimeChart"></canvas>
                    </div>
                </div>
                
                <!-- Water Change Instructions (hidden by default) -->
                <div id="waterChangeInstructions" class="mt-4 d-none">
                    <div class="card border-info">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Panduan Penggantian Air Akuarium</h5>
                        </div>
                        <div class="card-body">
                            <ol>
                                <li>Matikan semua peralatan listrik (filter, heater, dll)</li>
                                <li>Siapkan air baru yang sudah diendapkan minimal 24 jam</li>
                                <li>Hilangkan klorin dengan dechlorinator jika diperlukan</li>
                                <li>Ganti 20-30% air akuarium secara bertahap</li>
                                <li>Jaga suhu air baru mendekati suhu akuarium</li>
                                <li>Nyalakan kembali peralatan setelah penggantian</li>
                                <li>Pantau kondisi ikan setelah penggantian air</li>
                            </ol>
                            <div class="alert alert-warning mt-2">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Jangan mengganti lebih dari 50% air sekaligus untuk menghindari stres pada ikan.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script/dashboard.js"></script>
   
</body>
</html>