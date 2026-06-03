<?php
session_start();
include 'koneksi.php';

// 1. Cek User dan Laporan ID
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard_user.php?error=ID laporan tidak valid");
    exit();
}

$laporan_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// 2. Cek Kepemilikan dan Status Laporan
$sql_check = "SELECT status, user_id FROM laporan WHERE id = ?";
$stmt_check = $koneksi->prepare($sql_check);
$stmt_check->bind_param("i", $laporan_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$laporan = $result_check->fetch_assoc();

if (!$laporan) {
    header("Location: dashboard_user.php?error=Laporan tidak ditemukan");
    exit();
}

// Pastikan yang menghapus adalah pemilik laporan dan statusnya 'Menunggu'
if ($laporan['user_id'] != $user_id || $laporan['status'] != 'Menunggu') {
    header("Location: dashboard_user.php?error=Anda tidak memiliki izin untuk menghapus laporan ini atau status sudah diproses.");
    exit();
}

// 3. Proses Penghapusan
$sql_delete = "DELETE FROM laporan WHERE id = ?";
$stmt_delete = $koneksi->prepare($sql_delete);
$stmt_delete->bind_param("i", $laporan_id);

if ($stmt_delete->execute()) {
    // Redirect kembali ke dashboard dengan pesan sukses
    header("Location: dashboard_user.php?success=Laporan berhasil dihapus.");
    exit();
} else {
    // Redirect dengan pesan error
    header("Location: dashboard_user.php?error=Gagal menghapus laporan dari database.");
    exit();
}
?>