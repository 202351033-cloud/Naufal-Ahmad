<?php
include 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$pesan = "";

// 1. PROSES UPDATE DATA PROFIL
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profil'])) {
    $nama = $_POST['nama'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $email = $_POST['email'] ?? '';
    $nik = $_POST['nik'] ?? '';

    // Validasi
    if (empty($nama) || empty($email) || empty($nik) || empty($jenis_kelamin)) {
        $pesan = "<p class='error-message'>Semua kolom harus diisi.</p>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = "<p class='error-message'>Format email tidak valid.</p>";
    } elseif (strlen($nik) !== 16 || !is_numeric($nik)) {
        $pesan = "<p class='error-message'>NIK harus 16 digit angka.</p>";
    } else {
        // Cek apakah email atau NIK sudah digunakan oleh user lain
        $stmt_check = $koneksi->prepare("SELECT id FROM user WHERE (email = ? OR nik = ?) AND id != ?");
        $stmt_check->bind_param("ssi", $email, $nik, $user_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $pesan = "<p class='error-message'>Email atau NIK sudah digunakan oleh akun lain.</p>";
        } else {
            // Lakukan update data
            $sql_update = "UPDATE user SET nama = ?, jenis_kelamin = ?, email = ?, nik = ? WHERE id = ?";
            $stmt_update = $koneksi->prepare($sql_update);
            $stmt_update->bind_param("ssssi", $nama, $jenis_kelamin, $email, $nik, $user_id);

            if ($stmt_update->execute()) {
                // Berhasil: Redirect ke halaman tampil profil dengan status sukses
                $_SESSION['nama'] = $nama; // Update sesi nama
                header("Location: tampil_profil_user.php?status=success");
                exit();
            } else {
                // Gagal: Redirect ke halaman tampil profil dengan status error
                header("Location: tampil_profil_user.php?status=error&message=Gagal memperbarui profil: " . urlencode($stmt_update->error));
                exit();
            }
            $stmt_update->close();
        }
        $stmt_check->close();
    }
}

// 2. AMBIL DATA PROFIL SAAT INI (Diperlukan untuk mengisi form sebelum submit)
$sql_user = "SELECT nama, jenis_kelamin, nik, email FROM user WHERE id = ?";
$stmt_user = $koneksi->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$data_user = $result_user->fetch_assoc();
$stmt_user->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Profil</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Ubah Data Profil</h1>
        <nav>
            <a href="tampil_profil_user.php">&larr; Kembali ke Profil</a> | 
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <h2>Perbarui Data Akun</h2>
        
        <?php echo $pesan; ?>

        <form action="ubah_profil_user.php" method="POST">
            
            <label for="nama">Nama Lengkap:</label>
            <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($data_user['nama']); ?>" required>

            <label for="nik">NIK (Nomor Induk Kependudukan):</label>
            <input type="text" id="nik" name="nik" value="<?php echo htmlspecialchars($data_user['nik']); ?>" maxlength="16" required>
            <small>NIK harus 16 digit angka.</small>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($data_user['email']); ?>" required>
            
            <label for="jenis_kelamin">Jenis Kelamin:</label>
            <select id="jenis_kelamin" name="jenis_kelamin" required>
                <option value="Laki-laki" <?php echo ($data_user['jenis_kelamin'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                <option value="Perempuan" <?php echo ($data_user['jenis_kelamin'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
            </select>

            <button type="submit" name="update_profil" class="btn-primary">Simpan Perubahan</button>
        </form>
    </div>
</body>
</html>