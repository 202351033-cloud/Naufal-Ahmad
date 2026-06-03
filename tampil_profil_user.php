<?php
include 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$pesan = "";

// Ambil pesan dari hasil ubah profil
if (isset($_GET['status']) && $_GET['status'] == 'success') {
    $pesan = "<p class='success-message'>Profil berhasil diperbarui!</p>";
} elseif (isset($_GET['status']) && $_GET['status'] == 'error') {
    $pesan = "<p class='error-message'>" . htmlspecialchars($_GET['message']) . "</p>";
}

// AMBIL DATA PROFIL SAAT INI
$sql_user = "SELECT nama, jenis_kelamin, nik, email, tgl_daftar FROM user WHERE id = ?";
$stmt_user = $koneksi->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$data_user = $result_user->fetch_assoc();
$stmt_user->close();

if (!$data_user) {
    header("Location: logout.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Profil Saya</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Lihat Profil Pengguna</h1>
        <nav>
            <a href="dashboard_user.php">Kembali</a> | 
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <h2>Data Akun Anda</h2>
        
        <?php echo $pesan; ?>

        <div class="profil-display">
            <table>
                <tr>
                    <th>Nama Lengkap</th>
                    <td><?php echo htmlspecialchars($data_user['nama']); ?></td>
                </tr>
                <tr>
                    <th>NIK</th>
                    <td><?php echo htmlspecialchars($data_user['nik']); ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo htmlspecialchars($data_user['email']); ?></td>
                </tr>
                <tr>
                    <th>Jenis Kelamin</th>
                    <td><?php echo htmlspecialchars($data_user['jenis_kelamin']); ?></td>
                </tr>
                <tr>
                    <th>Tanggal Daftar</th>
                    <td><?php echo date('d F Y', strtotime($data_user['tgl_daftar'])); ?></td>
                </tr>
            </table>
        </div>
        
        <hr style="margin-top: 30px;">
        
        <h3>Opsi Akun</h3>
        <p>Anda dapat mengubah data profil atau kata sandi Anda di sini.</p>
        <a href="ubah_profil_user.php"><button class="btn btn-info">Ubah Data Profil</button></a>
         <a href="ganti_password.php"><button class="btn btn-black">Ubah Kata Sandi</button></a>
        
    </div>
</body>
</html>