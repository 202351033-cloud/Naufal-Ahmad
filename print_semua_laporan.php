<?php
include 'koneksi.php';

// Cek Admin (Hanya Admin yang boleh mencetak semua laporan)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak. Hanya Admin yang bisa mencetak semua laporan.");
}

// Ambil data semua laporan beserta nama pelapor
$sql_laporan = "SELECT 
    l.id, l.judul, l.tgl_kirim, l.status, u.nama AS pelapor_nama, u.nik AS pelapor_nik
    FROM laporan l
    JOIN user u ON l.user_id = u.id
    ORDER BY l.tgl_kirim DESC";
$result_laporan = $koneksi->query($sql_laporan);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Semua Laporan - Pengaduan Online</title>
    <link rel="stylesheet" href="styles.css">
    
    <style>
        /* Sembunyikan tombol cetak dan navigasi saat mode cetak */
        @media print {
            .no-print, header {
                display: none !important;
            }
            /* Pastikan tabel terlihat jelas */
            body {
                margin: 0;
                padding: 0;
                font-size: 10pt; /* Ukuran font lebih kecil untuk kertas */
            }
            .container {
                width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
                border: none;
            }
            table, th, td {
                border: 1px solid #000 !important;
            }
        }
        
        /* Style untuk tampilan normal (sebelum print) */
        .print-area {
            margin-top: 20px;
        }
        .header-print {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <header class="no-print">
        <nav><a href="dashboard_admin.php">&larr; Kembali ke Dashboard</a></nav>
    </header>

    <div class="container">
        
        <div class="header-print">
            <h1>DATA SEMUA LAPORAN PENGADUAN</h1>
            <p>Dicetak oleh Admin: <?php echo $_SESSION['nama']; ?> pada tanggal <?php echo date('d F Y H:i:s'); ?></p>
        </div>
        
        <div class="print-area">
            <?php if ($result_laporan->num_rows > 0): ?>
                <table border="1" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tgl Kirim</th>
                            <th>Judul</th>
                            <th>Pelapor</th>
                            <th>NIK</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_laporan->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo date('d-m-Y', strtotime($row['tgl_kirim'])); ?></td>
                            <td><?php echo htmlspecialchars($row['judul']); ?></td>
                            <td><?php echo htmlspecialchars($row['pelapor_nama']); ?></td>
                            <td><?php echo htmlspecialchars($row['pelapor_nik']); ?></td>
                            <td><?php echo $row['status']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Tidak ada laporan yang tersedia untuk dicetak.</p>
            <?php endif; ?>
        </div>

        <div class="no-print" style="text-align: center; margin-top: 30px;">
            <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; background-color: #007bff; color: white;">
                Klik Untuk Mencetak Laporan
            </button>
            <p style="margin-top: 10px;">Atau tekan Ctrl + P (Command + P di Mac).</p>
        </div>
    </div>
</body>
</html>