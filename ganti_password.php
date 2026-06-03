<?php
include 'koneksi.php';

// Pastikan pengguna (User atau Admin) sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role_session = $_SESSION['role'];
$pesan = "";

// Tentukan dashboard kembali
$dashboard_link = ($role_session == 'admin') ? 'dashboard_admin.php' : 'dashboard_user.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // 1. Ambil hash password lama dari database
    $stmt_fetch = $koneksi->prepare("SELECT password FROM user WHERE id = ?");
    $stmt_fetch->bind_param("i", $user_id);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();
    $data_user = $result->fetch_assoc();
    $stmt_fetch->close();

    // 2. Cek apakah password lama yang dimasukkan benar
    if (!password_verify($password_lama, $data_user['password'])) {
        $pesan = "Gagal: Kata sandi lama yang Anda masukkan salah.";
    } 
    // 3. Cek apakah password baru dan konfirmasi cocok
    else if ($password_baru !== $konfirmasi_password) {
        $pesan = "Gagal: Konfirmasi kata sandi baru tidak cocok.";
    } 
    // 4. Cek kompleksitas password (minimal 8 karakter)
    else if (strlen($password_baru) < 8) {
        $pesan = "Gagal: Kata sandi baru minimal harus 8 karakter.";
    } 
    // 5. Proses update password
    else {
        // Hashing password baru
        $hashed_password_baru = password_hash($password_baru, PASSWORD_DEFAULT);

        // Update password di database
        $stmt_update = $koneksi->prepare("UPDATE user SET password = ? WHERE id = ?");
        $stmt_update->bind_param("si", $hashed_password_baru, $user_id);

        if ($stmt_update->execute()) {
            $pesan = "✅ Kata sandi berhasil diubah! Anda akan dialihkan ke halaman login.";
            
            // Hancurkan sesi untuk memaksa pengguna login ulang dengan password baru
            session_destroy();
            // Tunda redirect agar pesan terbaca
            header("Refresh: 3; url=index.php"); 
        } else {
            $pesan = "❌ Terjadi kesalahan saat menyimpan kata sandi baru.";
        }
        $stmt_update->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Kata Sandi</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Ganti Kata Sandi Akun</h1>
        <nav>
            <a href="dashboard_user.php">Kembali</a> | 
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <h2>Ubah Password</h2>
        
        <?php 
        // Tampilkan pesan sesuai jenisnya
        if (!empty($pesan)) {
            $class = (strpos($pesan, 'Gagal') !== false || strpos($pesan, '❌') !== false) ? 'error-message' : 'success-message';
            echo '<p class="' . $class . '">' . $pesan . '</p>';
        }
        ?>

        <?php 
        // Tampilkan form hanya jika sesi belum dihancurkan (password belum berhasil diganti)
        if (isset($_SESSION['user_id'])): 
        ?>
        <form action="ganti_password.php" method="POST">
            <label for="password_lama">Kata Sandi Lama:</label>
            <input type="password" id="password_lama" name="password_lama" required>

            <label for="password_baru">Kata Sandi Baru (Min. 8 karakter):</label>
            <input type="password" id="password_baru" name="password_baru" required>

            <label for="konfirmasi_password">Konfirmasi Kata Sandi Baru:</label>
            <input type="password" id="konfirmasi_password" name="konfirmasi_password" required>

            <button type="submit">Ubah Kata Sandi</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>