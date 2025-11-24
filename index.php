<?php
// index.php (Halaman Login)
session_start();

// Jika user sudah login, arahkan langsung ke dashboard
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Include file koneksi
include 'koneksi.php';

$error = '';

// Proses ketika form login disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 1. Ambil data user dari database
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $db_username, $hashed_password);
        $stmt->fetch();

        // 2. Verifikasi Password
        // Kita menggunakan MD5 karena tabel user dibuat dengan MD5
        if (MD5($password) == $hashed_password) {
            
            // Login Berhasil!
            $_SESSION['loggedin'] = true;
            $_SESSION['id'] = $id;
            $_SESSION['username'] = $db_username;

            // Arahkan ke halaman dashboard
            header("location: dashboard.php");
            exit;
        } else {
            // Password salah
            $error = "Username atau Password salah.";
        }
    } else {
        // Username tidak ditemukan
        $error = "Username atau Password salah.";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aquarium Monitor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c7be5;
            --primary-dark: #1a68d1;
            --secondary-color: #00d97e;
            --text-dark: #2d3748;
            --text-light: #718096;
            --bg-light: #f9fbfd;
            --border-radius: 12px;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            color: var(--text-dark);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            line-height: 1.6;
        }
        
        .login-container {
            display: flex;
            width: 100%;
            max-width: 1000px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            min-height: 600px;
        }
        
        .login-illustration {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .login-illustration::before {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            top: -50px;
            right: -50px;
        }
        
        .login-illustration::after {
            content: '';
            position: absolute;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            bottom: -30px;
            left: -30px;
        }
        
        .illustration-content {
            text-align: center;
            z-index: 1;
            max-width: 400px;
        }
        
        .illustration-icon {
            font-size: 80px;
            margin-bottom: 20px;
            display: block;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .illustration-content h2 {
            font-size: 28px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .illustration-content p {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 30px;
        }
        
        .features-list {
            text-align: left;
            margin-top: 30px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 15px;
        }
        
        .feature-item i {
            margin-right: 10px;
            font-size: 18px;
            color: var(--secondary-color);
        }
        
        .login-form-container {
            flex: 1;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .logo-icon {
            font-size: 28px;
            color: var(--primary-color);
            margin-right: 10px;
        }
        
        .logo-text {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-dark);
        }
        
        .welcome-text h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text-dark);
        }
        
        .welcome-text p {
            color: var(--text-light);
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 18px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            transition: var(--transition);
            background-color: var(--bg-light);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(44, 123, 229, 0.1);
            outline: none;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            font-size: 18px;
        }
        
        .btn-login {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(44, 123, 229, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login i {
            margin-left: 8px;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        
        .alert-danger {
            background-color: rgba(245, 101, 101, 0.1);
            color: #e53e3e;
            border: 1px solid rgba(229, 62, 62, 0.2);
        }
        
        .alert i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 450px;
            }
            
            .login-illustration {
                padding: 30px 20px;
            }
            
            .illustration-content h2 {
                font-size: 24px;
            }
            
            .login-form-container {
                padding: 40px 30px;
            }
        }
        
        @media (max-width: 480px) {
            .login-form-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Bagian Ilustrasi -->
        <div class="login-illustration">
            <div class="illustration-content">
                <i class="fas fa-fish illustration-icon"></i>
                <h2>Aquarium Monitoring System</h2>
                <p>Sistem pemantauan akuarium canggih untuk menjaga kesehatan ekosistem air Anda</p>
                
                <div class="features-list">
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Pantau suhu dan kualitas air secara real-time</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Notifikasi otomatis untuk kondisi kritis</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Analisis data historis untuk perawatan optimal</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bagian Form Login -->
        <div class="login-form-container">
            <div class="logo">
                <i class="fas fa-water logo-icon"></i>
                <span class="logo-text">AquaMonitor</span>
            </div>
            
            <div class="welcome-text">
                <h1>Selamat Datang Kembali</h1>
                <p>Masuk ke akun Anda untuk melanjutkan</p>
            </div>
            
            <?php if (!empty($error)) : ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form action="index.php" method="post" id="loginForm">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username Anda" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password Anda" required>
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">
                    Masuk <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>

    <script>
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
    </script>
</body>
</html>