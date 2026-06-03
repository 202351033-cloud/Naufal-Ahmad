<?php
// Mulai atau lanjutkan sesi
session_start();

// Hapus semua variabel sesi
$_SESSION = array();

// Hancurkan sesi
session_destroy();

// Redirect ke halaman login
header("Location: index.html");
exit();
?>