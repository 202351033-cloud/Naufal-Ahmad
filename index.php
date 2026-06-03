<?php
include 'koneksi.php';

$pesan_login = "";

if (isset($_SESSION['user_id'])) {
    // Jika sudah login, redirect ke dashboard yang sesuai
    if ($_SESSION['role'] == 'admin') {
        header("Location: dashboard_admin.php");
    } else {
        header("Location: dashboard_user.php");
    }
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepared Statement
    $stmt = $koneksi->prepare("SELECT id, password, role, nama FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verifikasi password yang di-hash
        if (password_verify($password, $user['password'])) {
            // Login Berhasil
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['role'] = $user['role']; // 'user' atau 'admin'

            if ($user['role'] == 'admin') {
                header("Location: dashboard_admin.php"); // Admin Dashboard
            } else {
                header("Location: dashboard_user.php"); // User Dashboard
            }
            exit();
        } else {
            $pesan_login = "Login Gagal. Email atau Password Salah.";
        }
    } else {
        $pesan_login = "Login Gagal. Email atau Password Salah.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pengaduan Online</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Login Sistem Pengaduan</h2>
        
        <?php 
        if (isset($_GET['status']) && $_GET['status'] == 'success') {
            echo '<p class="success-message">Pendaftaran Berhasil! Silakan masuk.</p>';
        }
        if (!empty($pesan_login)): ?>
            <p class="error-message"><?php echo $pesan_login; ?></p>
        <?php endif; ?>

        <form action="index.php" method="POST">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>
        <p>Belum punya akun? <a href="registrasi.php">Daftar sekarang</a></p>
    </div>
</body>
</html>