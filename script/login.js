 // Toggle visibility password
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Form validation dengan feedback visual
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                
                // Tambahkan animasi shake pada input yang kosong
                if (!username) {
                    document.getElementById('username').style.borderColor = '#e53e3e';
                    document.getElementById('username').classList.add('shake');
                    setTimeout(() => {
                        document.getElementById('username').classList.remove('shake');
                    }, 500);
                }
                
                if (!password) {
                    document.getElementById('password').style.borderColor = '#e53e3e';
                    document.getElementById('password').classList.add('shake');
                    setTimeout(() => {
                        document.getElementById('password').classList.remove('shake');
                    }, 500);
                }
            }
        });
        
        // Reset border color saat user mulai mengetik
        document.getElementById('username').addEventListener('input', function() {
            this.style.borderColor = '#e2e8f0';
        });
        
        document.getElementById('password').addEventListener('input', function() {
            this.style.borderColor = '#e2e8f0';
        });
        
        // Tambahkan CSS untuk animasi shake
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }
            .shake {
                animation: shake 0.3s ease-in-out;
            }
        `;
        document.head.appendChild(style);