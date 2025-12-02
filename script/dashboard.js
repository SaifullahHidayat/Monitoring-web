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
                        hideWaterAlert(); // Sembunyikan alert jika kualitas baik
                    } else if (kualitasScore >= 60) {
                        statusText = 'BAIK';
                        statusClass = 'bg-status-good';
                        qualityProgress.className = 'progress-bar bg-status-good';
                        hideWaterAlert(); // Sembunyikan alert jika kualitas baik
                    } else if (kualitasScore >= 40) {
                        statusText = 'CUKUP';
                        statusClass = 'bg-status-fair';
                        qualityProgress.className = 'progress-bar bg-status-fair';
                        hideWaterAlert(); // Sembunyikan alert jika kualitas cukup
                    } else if (kualitasScore >= 20) {
                        statusText = 'BURUK';
                        statusClass = 'bg-status-poor';
                        qualityProgress.className = 'progress-bar bg-status-poor';
                        showWaterAlert('BURUK'); // Tampilkan alert untuk kualitas buruk
                    } else {
                        statusText = 'SANGAT BURUK';
                        statusClass = 'bg-dark';
                        qualityProgress.className = 'progress-bar bg-dark';
                        showWaterAlert('SANGAT BURUK'); // Tampilkan alert untuk kualitas sangat buruk
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
        // 4. Water Change Alert Functions
        // ===============================================
        
        function showWaterAlert(qualityStatus) {
            const alertElement = document.getElementById('waterChangeAlert');
            const alertMessage = document.getElementById('alertMessage');
            const instructionsElement = document.getElementById('waterChangeInstructions');
            
            // Customize alert message based on quality status
            if (qualityStatus === 'SANGAT BURUK') {
                alertElement.className = 'water-alert alert alert-danger fade-in';
                alertMessage.innerHTML = `
                    <strong>🚨 KONDISI DARURAT!</strong> Kualitas air akuarium dalam kondisi SANGAT BURUK (skor: <span id="currentScore"></span>/100). 
                    <strong>GANTI AIR SEGERA!</strong> Kondisi ini dapat membahayakan kesehatan ikan dalam waktu singkat.
                `;
            } else {
                alertElement.className = 'water-alert alert alert-warning fade-in';
                alertMessage.innerHTML = `
                    <strong>⚠️ PERINGATAN:</strong> Kualitas air akuarium dalam kondisi BURUK (skor: <span id="currentScore"></span>/100). 
                    Disarankan untuk mengganti 30-50% air akuarium dalam 24 jam ke depan untuk menjaga kesehatan ikan.
                `;
            }
            
            // Update current score in alert
            const currentScore = document.getElementById('cardKualitasScore').innerText;
            document.querySelector('#alertMessage span#currentScore').textContent = currentScore;
            
            // Show alert and instructions
            alertElement.classList.remove('d-none');
            instructionsElement.classList.remove('d-none');
            
            // Add animation
            alertElement.style.animation = 'slideInDown 0.5s ease-out';
            
            // Play notification sound (optional)
            playAlertSound();
        }
        
        function hideWaterAlert() {
            const alertElement = document.getElementById('waterChangeAlert');
            const instructionsElement = document.getElementById('waterChangeInstructions');
            
            alertElement.classList.add('d-none');
            instructionsElement.classList.add('d-none');
        }
        
        function playAlertSound() {
            // Create a simple notification sound
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
                oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1);
                
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.5);
            } catch (e) {
                console.log('Audio context not supported');
            }
        }

        // ===============================================
        // 5. Main Execution
        // ===============================================

        // Panggil fungsi saat halaman dimuat
        updateSensorCards();
        updateChartData(); 

        // Atur interval pembaruan data
        setInterval(updateSensorCards, 5000); 
        setInterval(updateChartData, 15000); 

        // ===============================================
        // 6. Konfirmasi Logout
        // ===============================================
        document.getElementById('logoutBtn').addEventListener('click', function() {
            const konfirmasi = confirm('Apakah Anda yakin ingin keluar dari sistem?');
            
            if (konfirmasi) {
                window.location.href = 'logout.php';
            }
        });

        // ===============================================
        // 7. Chart Period Toggle
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
                console.log('Selected period:', this.textContent);
            });
        });

        // ===============================================
        // 8. Close Alert Button
        // ===============================================
        document.getElementById('closeAlert').addEventListener('click', function() {
            hideWaterAlert();
        });

        // ===============================================
        // 9. Auto-hide alert after 30 seconds
        // ===============================================
        setInterval(() => {
            const alertElement = document.getElementById('waterChangeAlert');
            if (!alertElement.classList.contains('d-none')) {
                // Add fade out animation
                alertElement.style.animation = 'fadeOut 0.5s ease-out';
                setTimeout(() => {
                    hideWaterAlert();
                }, 500);
            }
        }, 30000); // 30 seconds