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
                
                <!-- Logout Section - Tetap di dalam sidebar nav -->
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
                        </div>
                    </div>
                    <div class="chart-area">
                        <canvas id="realtimeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // ===============================================
        // 1. Sidebar Toggle
        // ===============================================
        document.getElementById('navToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // ===============================================
        // 2. Chart.js Setup
        // ===============================================
        const ctx = document.getElementById('realtimeChart').getContext('2d');
        let realtimeChart;

        function createChart(labels, tdsData, suhuData) {
            if (realtimeChart) {
                realtimeChart.destroy();
            }
            
            realtimeChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels, 
                    datasets: [
                        {
                            label: 'TDS (ppm)',
                            data: tdsData,
                            borderColor: 'rgba(44, 123, 229, 1)',
                            backgroundColor: 'rgba(44, 123, 229, 0.05)',
                            borderWidth: 3,
                            tension: 0.3,
                            fill: true,
                            yAxisID: 'y',
                            pointBackgroundColor: 'rgba(44, 123, 229, 1)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        },
                        {
                            label: 'Suhu (°C)',
                            data: suhuData,
                            borderColor: 'rgba(246, 195, 67, 1)',
                            backgroundColor: 'rgba(246, 195, 67, 0.05)',
                            borderWidth: 3,
                            tension: 0.3,
                            fill: true,
                            yAxisID: 'y1',
                            pointBackgroundColor: 'rgba(246, 195, 67, 1)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                font: {
                                    size: 13
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#2d3748',
                            bodyColor: '#2d3748',
                            borderColor: '#e2e8f0',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: true,
                            padding: 12,
                            boxPadding: 6,
                            usePointStyle: true
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: { 
                                display: true, 
                                text: 'TDS (ppm)',
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.03)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: { 
                                drawOnChartArea: false 
                            },
                            title: { 
                                display: true, 
                                text: 'Suhu (°C)',
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            }
                        }
                    }
                }
            });
        }

        // ===============================================
        // 3. AJAX Functions
        // ===============================================

        // Ambil data terbaru dan perbarui card
        function updateSensorCards() {
            fetch('get-latest.php')
                .then(response => response.json())
                .then(data => {
                    // Update Nilai Card
                    document.getElementById('cardTds').innerText = data.tds.toFixed(0) + ' ppm';
                    document.getElementById('cardSuhu').innerText = data.suhu.toFixed(1) + ' °C';
                    document.getElementById('cardKualitasScore').innerText = data.kualitas.toFixed(1);

                    // Update Waktu Terakhir
                    const dateOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', day: 'numeric', month: 'short' };
                    document.getElementById('lastUpdate').innerText = 'Update: ' + new Date(data.timestamp).toLocaleString('id-ID', dateOptions);
                    
                    // Logic Warna dan Status Kualitas Air (0-100)
                    const kualitasScore = data.kualitas;
                    const kualitasStatus = document.getElementById('cardKualitasStatus');
                    const qualityProgress = document.getElementById('qualityProgress');
                    
                    // Update progress bar
                    qualityProgress.style.width = kualitasScore + '%';
                    
                    let statusText = 'Memuat...';
                    let statusClass = 'bg-status-excellent';

                    if (kualitasScore >= 80) {
                        statusText = 'SANGAT BAIK';
                        statusClass = 'bg-status-excellent';
                        qualityProgress.className = 'progress-bar bg-status-excellent';
                    } else if (kualitasScore >= 60) {
                        statusText = 'BAIK';
                        statusClass = 'bg-status-good';
                        qualityProgress.className = 'progress-bar bg-status-good';
                    } else if (kualitasScore >= 40) {
                        statusText = 'CUKUP';
                        statusClass = 'bg-status-fair';
                        qualityProgress.className = 'progress-bar bg-status-fair';
                    } else if (kualitasScore >= 20) {
                        statusText = 'BURUK';
                        statusClass = 'bg-status-poor';
                        qualityProgress.className = 'progress-bar bg-status-poor';
                    } else {
                        statusText = 'SANGAT BURUK';
                        statusClass = 'bg-dark';
                        qualityProgress.className = 'progress-bar bg-dark';
                    }

                    // Terapkan Status
                    kualitasStatus.className = 'quality-badge ' + statusClass + ' text-white';
                    kualitasStatus.innerText = statusText;
                })
                .catch(error => console.error('Error fetching latest data:', error));
        }

        // Ambil data historis dan perbarui grafik
        function updateChartData() {
            fetch('get-history.php')
                .then(response => response.json())
                .then(data => {
                    // Siapkan data untuk Chart.js
                    const labels = data.map(item => new Date(item.timestamp).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }));
                    const tdsData = data.map(item => item.tds);
                    const suhuData = data.map(item => item.suhu);

                    createChart(labels, tdsData, suhuData);
                })
                .catch(error => console.error('Error fetching history data:', error));
        }

        // ===============================================
        // 4. Main Execution
        // ===============================================

        // Panggil fungsi saat halaman dimuat
        updateSensorCards();
        updateChartData(); 

        // Atur interval pembaruan data
        setInterval(updateSensorCards, 5000); 
        setInterval(updateChartData, 15000); 

        // ===============================================
        // 5. Konfirmasi Logout
        // ===============================================
        document.getElementById('logoutBtn').addEventListener('click', function() {
            const konfirmasi = confirm('Apakah Anda yakin ingin keluar dari sistem?');
            
            if (konfirmasi) {
                window.location.href = 'logout.php';
            }
        });

        // ===============================================
        // 6. Chart Period Toggle
        // ===============================================
        document.querySelectorAll('.chart-action-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.chart-action-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Here you would typically fetch new data based on the selected period
                // For now, we'll just log the selection
                console.log('Selected period:', this.textContent);
            });
        });
    </script>
</body>
</html>