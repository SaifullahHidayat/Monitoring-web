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