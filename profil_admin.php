<?php
include 'koneksi.php';

// Pastikan hanya user yang berhak ('admin') yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$pesan = "";

// --- 1. PROSES UPDATE DATA ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profil'])) {
    $nama_baru = $_POST['nama'];
    $email_baru = $_POST['email'];

    // Prepared Statement untuk update data
    $stmt = $koneksi->prepare("UPDATE user SET nama = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nama_baru, $email_baru, $user_id);

    if ($stmt->execute()) {
        $_SESSION['nama'] = $nama_baru; // Update session name
        $pesan = "Profil Admin berhasil diperbarui.";
    } else {
        $pesan = "Gagal memperbarui profil: " . $stmt->error;
    }
    $stmt->close();
}

// --- 2. AMBIL DATA SAAT INI ---
$sql = "SELECT nama, nik, email, role, tgl_daftar FROM user WHERE id = ?";
$stmt_fetch = $koneksi->prepare($sql);
$stmt_fetch->bind_param("i", $user_id);
$stmt_fetch->execute();
$result = $stmt_fetch->get_result();

if ($result->num_rows === 0) {
    header("Location: logout.php"); 
    exit();
}

$data_admin = $result->fetch_assoc();
$stmt_fetch->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Profil Administrator</h1>
        <nav>
            <a href="dashboard_admin.php">Dashboard</a> | 
            <a href="profil_admin.php">Profil</a> | 
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <h2>Informasi Akun Admin</h2>
        
        <?php if (!empty($pesan)): ?>
            <p class="success-message"><?php echo $pesan; ?></p>
        <?php endif; ?>

        <form action="profil_admin.php" method="POST">
            <label for="nama">Nama Lengkap:</label>
            <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($data_admin['nama']); ?>" required>

            <label for="role">Role (Tidak dapat diubah):</label>
            <input type="text" id="role" name="role" value="<?php echo strtoupper(htmlspecialchars($data_admin['role'])); ?>" readonly style="background-color: #eee;">
            
            <label for="nik">NIK:</label>
            <input type="text" id="nik" name="nik" value="<?php echo htmlspecialchars($data_admin['nik']); ?>" readonly style="background-color: #eee;">

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($data_admin['email']); ?>" required>
            
            <p style="margin-top: 20px;">Akun terdaftar sejak: **<?php echo date('d F Y', strtotime($data_admin['tgl_daftar'])); ?>**</p>

            <button type="submit" name="update_profil">Simpan Perubahan Profil</button>
        </form>

        <hr>
        
        <h3>Ganti Password</h3>
        <p>Akses <a href="ganti_password.php">halaman Ganti Password</a> untuk mengubah kata sandi Anda.</p>
    </div>
</body>
</html>