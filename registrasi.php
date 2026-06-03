<?php
include 'koneksi.php';

$pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];
    $jk = $_POST['jenis_kelamin'];
    $nik = $_POST['nik'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validasi sederhana (Anda perlu validasi yang lebih ketat)
    if (empty($nama) || empty($email) || empty($password) || empty($nik)) {
        $pesan = "Semua field wajib diisi.";
    } else {
        // Hashing password untuk keamanan
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Cek apakah NIK atau Email sudah terdaftar (PENTING!)
        $stmt_check = $koneksi->prepare("SELECT id FROM user WHERE nik = ? OR email = ?");
        $stmt_check->bind_param("ss", $nik, $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $pesan = "NIK atau Email sudah terdaftar.";
        } else {
            // Prepared Statement untuk mencegah SQL Injection
            $stmt = $koneksi->prepare("INSERT INTO user (nama, jenis_kelamin, nik, email, password, role) VALUES (?, ?, ?, ?, ?, 'user')");
            $stmt->bind_param("sssss", $nama, $jk, $nik, $email, $hashed_password);

            if ($stmt->execute()) {
                $pesan = "Registrasi Berhasil! Silakan masuk.";
                // Redirect ke halaman login setelah registrasi berhasil
                header("Location: index.php?status=success");
                exit();
            } else {
                $pesan = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Pendaftaran Akun Baru</h2>
        
        <?php if (!empty($pesan)): ?>
            <p class="message"><?php echo $pesan; ?></p>
        <?php endif; ?>

        <form action="registrasi.php" method="POST">
            <label for="nama">Nama Lengkap:</label>
            <input type="text" id="nama" name="nama" required>

            <label for="jenis_kelamin">Jenis Kelamin:</label>
            <select id="jenis_kelamin" name="jenis_kelamin" required>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
            </select>

            <label for="nik">NIK:</label>
            <input type="text" id="nik" name="nik" required pattern="\d{16}" title="NIK harus 16 digit angka">

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Daftar</button>
        </form>
        <p>Sudah punya akun? <a href="index.php">Login di sini</a></p>
    </div>
</body>
</html>