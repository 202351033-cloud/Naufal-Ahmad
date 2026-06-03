<?php
// Ganti dengan detail database Anda
$host = "localhost";
$user = "root"; // Username default XAMPP/Laragon
$pass = ""; // Password default XAMPP/Laragon (kosong)
$db = "db_pengaduan_online"; // Nama database Anda

// Buat koneksi
$koneksi = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Atur set karakter ke utf8
$koneksi->set_charset("utf8");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>