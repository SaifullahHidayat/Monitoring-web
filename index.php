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
    <link rel="stylesheet" href="assets/login.css">
    <link rel="icon" href="assets/aquarium.png">
   
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

    <script src = "script/login.js"></script>
</body>
</html>