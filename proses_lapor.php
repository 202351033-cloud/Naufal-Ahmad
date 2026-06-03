<?php
include 'koneksi.php';

// Pastikan hanya user yang login yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit();
}

if (isset($_POST['kirim_laporan'])) {
    
    // 1. Ambil dan Sanitasi Data dari Form
    $user_id = $_SESSION['user_id'];
    $judul = $_POST['judul'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $kategori = $_POST['kategori'] ?? ''; // Menerima nilai ENUM (Laporan, Keluhan, Aspirasi)
    $tgl_kirim = date('Y-m-d H:i:s');
    $status = 'Menunggu';
    
    // Variabel untuk upload file
    $nama_file_baru = NULL;
    $upload_dir = 'dokumen_laporan/';
    $max_size = 2 * 1024 * 1024; // Maksimal 2 MB

    // 2. Validasi Input Dasar
    if (empty($judul) || empty($deskripsi) || empty($kategori)) {
        header("Location: dashboard_user.php?error=Judul, Deskripsi, dan Kategori harus diisi.");
        exit();
    }
    
    // Validasi nilai ENUM
    $allowed_categories = ['laporan', 'keluhan', 'aspirasi'];
    if (!in_array($kategori, $allowed_categories)) {
        header("Location: dashboard_user.php?error=Kategori tidak valid."); // <--- Ini yang memicu error Anda
        exit();
    }

    // 3. Penanganan Upload Dokumen (Foto/PDF)
    if (isset($_FILES['dokumen']) && $_FILES['dokumen']['error'] == 0) {
        $file_name = $_FILES['dokumen']['name'];
        $file_tmp = $_FILES['dokumen']['tmp_name'];
        $file_size = $_FILES['dokumen']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];

        // Cek ukuran file
        if ($file_size > $max_size) {
            header("Location: dashboard_user.php?error=Ukuran file melebihi batas (Maks. 2MB).");
            exit();
        }
        
        // Cek ekstensi file
        if (!in_array($file_ext, $allowed_ext)) {
            header("Location: dashboard_user.php?error=Format file tidak diizinkan. Gunakan JPG, PNG, atau PDF.");
            exit();
        }

        // Buat nama file unik
        $nama_file_baru = uniqid('dok_') . '.' . $file_ext;
        $file_target = $upload_dir . $nama_file_baru;

        // Pindahkan file yang diupload ke folder target
        if (!move_uploaded_file($file_tmp, $file_target)) {
            header("Location: dashboard_user.php?error=Gagal mengunggah dokumen.");
            exit();
        }
    }

    // 4. Masukkan Data ke Database (Prepared Statement)
    
    try {
        if ($nama_file_baru !== NULL) {
            // Jika ada dokumen pendukung
            $sql = "INSERT INTO laporan (user_id, judul, kategori, deskripsi, dokumen_pendukung, tgl_kirim, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $koneksi->prepare($sql);
            // Binding: i=integer, s=string, s=string, s=string, s=string, s=string, s=string
            $stmt->bind_param("issssss", $user_id, $judul, $kategori, $deskripsi, $nama_file_baru, $tgl_kirim, $status);
        } else {
            // Jika tanpa dokumen pendukung
            $sql = "INSERT INTO laporan (user_id, judul, kategori, deskripsi, tgl_kirim, status) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $koneksi->prepare($sql);
            // Binding: i=integer, s=string, s=string, s=string, s=string, s=string
            $stmt->bind_param("isssss", $user_id, $judul, $kategori, $deskripsi, $tgl_kirim, $status);
        }

        if ($stmt->execute()) {
            // Berhasil
            header("Location: dashboard_user.php?success=Laporan berhasil dikirim dan sedang menunggu verifikasi.");
            exit();
        } else {
            // Gagal
            header("Location: dashboard_user.php?error=Gagal menyimpan laporan ke database: " . $stmt->error);
            exit();
        }
        $stmt->close();

    } catch (Exception $e) {
        // Tangani error database umum
        header("Location: dashboard_user.php?error=Terjadi kesalahan sistem: " . $e->getMessage());
        exit();
    }
} else {
    // Jika diakses tanpa submit form
    header("Location: dashboard_user.php");
    exit();
}
?>