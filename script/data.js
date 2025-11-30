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