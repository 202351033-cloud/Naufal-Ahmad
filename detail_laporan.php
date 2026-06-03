<?php
include 'koneksi.php';
// session_start(); // Jika belum ada di koneksi.php, pastikan diaktifkan di sini

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$laporan_id = $_GET['id'];
$user_id_session = $_SESSION['user_id'];
$role_session = $_SESSION['role'];
$pesan = "";
$upload_dir = 'dokumen_laporan/'; // DEFINISI FOLDER UPLOAD

// --- 1. PROSES UPDATE STATUS & PENGHAPUSAN (HANYA UNTUK ADMIN) ---
if ($role_session == 'admin' && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $status_baru = $_POST['status_laporan'];
    
    // Logika Penghapusan (ADM-01)
    if ($status_baru == 'Ditolak' && isset($_POST['hapus_data']) && $_POST['hapus_data'] == 'ya') {
        
        // 1. Ambil nama file dokumen (menggunakan dokumen_pendukung)
        $stmt_fetch_doc = $koneksi->prepare("SELECT dokumen_pendukung FROM laporan WHERE id = ?");
        $stmt_fetch_doc->bind_param("i", $laporan_id);
        $stmt_fetch_doc->execute();
        $result_doc = $stmt_fetch_doc->get_result();
        $data_doc = $result_doc->fetch_assoc();
        $stmt_fetch_doc->close();

        $file_to_delete = $data_doc['dokumen_pendukung'] ?? null; // KUNCI DIPERBAIKI
        $full_path = $upload_dir . $file_to_delete; 

        // Hapus laporan dari database
        $stmt_del = $koneksi->prepare("DELETE FROM laporan WHERE id = ?");
        $stmt_del->bind_param("i", $laporan_id);
        
        if ($stmt_del->execute()) {
            // Hapus file dokumen terkait jika ada dan ditemukan
            if (!empty($file_to_delete) && file_exists($full_path)) {
                // Tambahkan pengecekan unlink agar tidak terjadi error fatal jika gagal
                if (unlink($full_path)) {
                    $pesan_success = "Laporan, data database, dan dokumen pendukung berhasil dihapus (Laporan ditolak).";
                } else {
                    $pesan_success = "Laporan berhasil dihapus dari database, namun gagal menghapus file dokumen fisik. Cek izin folder.";
                }
            } else {
                $pesan_success = "Laporan dan data database berhasil dihapus (Tidak ada dokumen atau dokumen tidak ditemukan).";
            }
            // Redirect ke dashboard admin setelah berhasil dihapus
            header("Location: dashboard_admin.php?success=" . urlencode($pesan_success));
            exit();
        } else {
            $pesan = "Gagal menghapus laporan: " . $stmt_del->error;
        }
    } else {
        // Update status laporan
        $sql_update = "UPDATE laporan SET status = ?, tgl_proses = NOW() WHERE id = ?";
        $stmt_update = $koneksi->prepare($sql_update);
        $stmt_update->bind_param("si", $status_baru, $laporan_id);

        if ($stmt_update->execute()) {
            $pesan = "Status laporan berhasil diubah menjadi: **" . htmlspecialchars($status_baru) . "**";
        } else {
            $pesan = "Gagal mengubah status: " . $stmt_update->error;
        }
        $stmt_update->close();
    }
}


// --- 2. AMBIL DATA LAPORAN ---
$sql = "SELECT 
    l.*, u.nama AS pelapor_nama, u.email AS pelapor_email, u.nik AS pelapor_nik
    FROM laporan l
    JOIN user u ON l.user_id = u.id
    WHERE l.id = ?";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $laporan_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Laporan tidak ditemukan.");
}

$data_laporan = $result->fetch_assoc();
$stmt->close();

// Cek otorisasi: User hanya bisa melihat laporannya sendiri
if ($role_session == 'user' && $data_laporan['user_id'] != $user_id_session) {
    die("Anda tidak memiliki izin untuk melihat laporan ini.");
}

// Tentukan dashboard kembali
$dashboard_link = ($role_session == 'admin') ? 'dashboard_admin.php' : 'dashboard_user.php';
$dashboard_title = ($role_session == 'admin') ? 'Dashboard Admin' : 'Dashboard User';

// TENTUKAN JALUR DOKUMEN UNTUK TAMPILAN
$dokumen_nama = $data_laporan['dokumen_pendukung'] ?? ''; // KUNCI DIPERBAIKI
$dokumen_url = !empty($dokumen_nama) ? $upload_dir . $dokumen_nama : null; // Gunakan URL relatif
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Forms - Kaiadmin Bootstrap 5 Admin Dashboard</title>
    <meta
      content="width=device-width, initial-scale=1.0, shrink-to-fit=no"
      name="viewport"
    />
    <link
      rel="icon"
      href="assets/img/kaiadmin/favicon.ico"
      type="image/x-icon"
    />

    <!-- Fonts and icons -->
    <script src="assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
        custom: {
          families: [
            "Font Awesome 5 Solid",
            "Font Awesome 5 Regular",
            "Font Awesome 5 Brands",
            "simple-line-icons",
          ],
          urls: ["assets/css/fonts.min.css"],
        },
        active: function () {
          sessionStorage.fonts = true;
        },
      });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/plugins.min.css" />
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css" />

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link rel="stylesheet" href="assets/css/demo.css" />
  </head>
  <body>
    <div class="wrapper">
     <!-- Sidebar -->
      <div class="sidebar" data-background-color="dark">
        <div class="sidebar-logo">
          <!-- Logo Header -->
          <div class="logo-header" data-background-color="dark">
            <a href="index.html" class="logo">
              <img
                src="assets/img/kaiadmin/logo_light.svg"
                alt="navbar brand"
                class="navbar-brand"
                height="20"
              />
            </a>
            <div class="nav-toggle">
              <button class="btn btn-toggle toggle-sidebar">
                <i class="gg-menu-right"></i>
              </button>
              <button class="btn btn-toggle sidenav-toggler">
                <i class="gg-menu-left"></i>
              </button>
            </div>
            <button class="topbar-toggler more">
              <i class="gg-more-vertical-alt"></i>
            </button>
          </div>
          <!-- End Logo Header -->
        </div>
        <div class="sidebar-wrapper scrollbar scrollbar-inner">
          <div class="sidebar-content">
            <ul class="nav nav-secondary">
              <li class="nav-item active">
                <a
                  data-bs-toggle="collapse"
                  href="#dashboard"
                  class="collapsed"
                  aria-expanded="false"
                >
                  <i class="fas fa-home"></i>
                  <p>Dashboard</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="dashboard">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="dashboard_user.php">
                        <span class="sub-item">Dashboard</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>



              <li class="nav-item">
                <a data-bs-toggle="collapse" href="#forms">
                  <i class="fas fa-pen-square"></i>
                  <p>Buat Laporan</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="forms">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="tambah_laporan.php">
                        <span class="sub-item">From Laporan</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              
              



            </ul>
          </div>
        </div>
      </div>
      <!-- End Sidebar -->

      <div class="main-panel">
        <div class="main-header">
          <div class="main-header-logo">
            <!-- Logo Header -->
            <div class="logo-header" data-background-color="dark">
              <a href="index.html" class="logo">
                <img
                  src="assets/img/kaiadmin/logo_light.svg"
                  alt="navbar brand"
                  class="navbar-brand"
                />
              </a>
              <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                  <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                  <i class="gg-menu-left"></i>
                </button>
              </div>
              <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
              </button>
            </div>
            <!-- End Logo Header -->
          </div>
         <!-- Navbar Header -->
          <nav
            class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom"
          >
            <div class="container-fluid">
              <nav
                class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex"
              >
                <div class="input-group">
                  <div class="input-group-prepend">

                  </div>
 
                </div>
              </nav>

              <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                <li
                  class="nav-item topbar-icon dropdown hidden-caret d-flex d-lg-none"
                >
                  <a
                    class="nav-link dropdown-toggle"
                    data-bs-toggle="dropdown"
                    href="#"
                    role="button"
                    aria-expanded="false"
                    aria-haspopup="true"
                  >
                    <i class="fa fa-search"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-search animated fadeIn">
                    <form class="navbar-left navbar-form nav-search">
                      <div class="input-group">
                        <input
                          type="text"
                          placeholder="Search ..."
                          class="form-control"
                        />
                      </div>
                    </form>
                  </ul>
                </li>




                <li class="nav-item topbar-user dropdown hidden-caret">
                  <a
                    class="dropdown-toggle profile-pic"
                    data-bs-toggle="dropdown"
                    href="#"
                    aria-expanded="false"
                  >
                    <div class="avatar-sm">
                      
                    </div>
                    <span class="profile-username">
                       <p></p>
                    </span>
                  </a>
                  <ul class="dropdown-menu dropdown-user animated fadeIn">
                    <div class="dropdown-user-scroll scrollbar-outer">
                      <li>
                        <div class="user-box">
                          <div class="avatar-lg">
                            <img
                              src="assets/img/profile.jpg"
                              alt="image profile"
                              class="avatar-img rounded"
                            />
                          </div>
                          <div class="u-text">
                            <h4></h4>
                                                    
                            


                          </div>
                        </div>
                      </li>
                      <li>

                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php">Logout</a>
                      </li>
                    </div>
                  </ul>
                </li>
              </ul>
            </div>
          </nav>
          <!-- End Navbar -->
        </div>

        <
          <div class="page-inner">
            <div class="page-header">
              
             
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="card">
                  <div class="card-header">
                    <div class="card-title"><h2>Laporan ID: #<?php echo $laporan_id; ?></h2></div>
                  </div>
                   

            
                  <div class="card-body">
                    <div class="row">
                      <div class="container">
        
        
        
        <?php if (!empty($pesan)): ?>
            <p class="success-message"><?php echo $pesan; ?></p>
        <?php endif; ?>

        <div class="detail-section">
            <p><strong>Pelapor:</strong> <?php echo htmlspecialchars($data_laporan['pelapor_nama']); ?></p>
            <?php if ($role_session == 'admin'): // Data sensitif hanya untuk Admin ?>
                <p><strong>NIK Pelapor:</strong> <?php echo htmlspecialchars($data_laporan['pelapor_nik']); ?></p>
                <p><strong>Email Pelapor:</strong> <?php echo htmlspecialchars($data_laporan['pelapor_email']); ?></p>
            <?php endif; ?>
<p><strong>Kategori:</strong> <?php echo htmlspecialchars($data_laporan['kategori'] ?? 'N/A'); ?></p>
            <p><strong>Judul Laporan:</strong> <?php echo htmlspecialchars($data_laporan['judul']); ?></p>
            
            <p><strong>Tanggal Kirim:</strong> <?php echo $data_laporan['tgl_kirim']; ?></p>
            <p><strong>Status Saat Ini:</strong> <span class="status-<?php echo strtolower($data_laporan['status']); ?>"><?php echo $data_laporan['status']; ?></span></p>
        </div>

        <h3>Deskripsi Detail</h3>
        <p><?php echo nl2br(htmlspecialchars($data_laporan['deskripsi'])); ?></p>
        
        <h3>Dokumen Pendukung</h3>
        <?php if ($dokumen_url && file_exists($dokumen_url)): ?>
            <p>
                📎 <a href="<?php echo htmlspecialchars($dokumen_url); ?>" target="_blank" download>
                    Download / Lihat Dokumen (<?php echo htmlspecialchars($dokumen_nama); ?>)
                </a>
            </p>
            
            <?php 
            $file_ext = strtolower(pathinfo($dokumen_nama, PATHINFO_EXTENSION));
            if (in_array($file_ext, ['jpg', 'jpeg', 'png'])): ?>
                <h4 style="margin-top: 10px;">Pratinjau:</h4>
                <img src="<?php echo htmlspecialchars($dokumen_url); ?>" 
                     alt="Dokumen Pendukung" 
                     style="max-width: 400px; height: auto; border: 1px solid #ccc;">
            <?php endif; ?>

        <?php else: ?>
            <p style="color: #777;">Tidak ada dokumen pendukung dilampirkan.</p>
        <?php endif; ?>

        <?php if ($role_session == 'admin'): ?>
        <hr>
        <h3>🛠️ Proses Laporan (Admin)</h3>
        
        <form action="detail_laporan.php?id=<?php echo $laporan_id; ?>" method="POST">
            <label for="status_laporan">Ubah Status:</label>
            <select id="status_laporan" name="status_laporan" required>
                <option value="Menunggu" <?php echo ($data_laporan['status'] == 'Menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                <option value="Diproses" <?php echo ($data_laporan['status'] == 'Diproses') ? 'selected' : ''; ?>>Diproses</option>
                <option value="Selesai" <?php echo ($data_laporan['status'] == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                <option value="Ditolak" <?php echo ($data_laporan['status'] == 'Ditolak') ? 'selected' : ''; ?>>Ditolak (Laporan Tidak Valid)</option>
            </select>
            
            <p style="margin-top: 15px; font-weight: bold;">
                <input type="checkbox" id="hapus_data" name="hapus_data" value="ya"> 
                <label for="hapus_data" style="display: inline;">Konfirmasi penghapusan data (Hanya jika status Ditolak dan data tidak valid).</label>
            </p>

            <button type="submit" name="update_status">Update Status & Proses</button>
        </form>
        
        <?php endif; ?>
        
    </div>

                </div>
              </div>
            </div>
          </div>
        </div>

        <footer class="footer">
          <div class="container-fluid d-flex justify-content-between">
            <nav class="pull-left">
              <ul class="nav">
                <li class="nav-item">
                  <a class="nav-link" href="http://www.themekita.com">
                    ThemeKita
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="#"> Help </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="#"> Licenses </a>
                </li>
              </ul>
            </nav>
            <div class="copyright">
              2024, made with <i class="fa fa-heart heart text-danger"></i> by
              <a href="http://www.themekita.com">ThemeKita</a>
            </div>
            <div>
              Distributed by
              <a target="_blank" href="https://themewagon.com/">ThemeWagon</a>.
            </div>
          </div>
        </footer>
      </div>

      <!-- Custom template | don't include it in your project! -->
      <div class="custom-template">
        <div class="title">Settings</div>
        <div class="custom-content">
          <div class="switcher">
            <div class="switch-block">
              <h4>Logo Header</h4>
              <div class="btnSwitch">
                <button
                  type="button"
                  class="selected changeLogoHeaderColor"
                  data-color="dark"
                ></button>
                <button
                  type="button"
                  class="selected changeLogoHeaderColor"
                  data-color="blue"
                ></button>
                <button
                  type="button"
                  class="changeLogoHeaderColor"
                  data-color="purple"
                ></button>
                <button
                  type="button"
                  class="changeLogoHeaderColor"
                  data-color="light-blue"
                ></button>
                <button
                  type="button"
                  class="changeLogoHeaderColor"
                  data-color="green"
                ></button>
                <button
                  type="button"
                  class="changeLogoHeaderColor"
                  data-color="orange"
                ></button>
                <button
                  type="button"
                  class="changeLogoHeaderColor"
                  data-color="red"
                ></button>
                <button
                  type="button"
                  class="changeLogoHeaderColor"
                  data-color="white"
                ></button>
                <br />
                <button
                  type="button"
                  class="changeLogoHeaderColor"
                  data-color="dark2"
                ></button>
                <button
                  type="button"
                  class="changeLogoHeaderColor"
                  data-color="blue2"
                ></button>
                <button
                  type="button"
                  class="changeLogoHeaderColor"
                  data-color="purple2"
                ></button>
                <button
                  type="button"
                  class="changeLogoHeaderColor"
                  data-color="light-blue2"
                ></button>
                <button
                  type="button"
                  class="changeLogoHeaderColor"
                  data-color="green2"
                ></button>
                <button
                  type="button"
                  class="changeLogoHeaderColor"
                  data-color="orange2"
                ></button>
                <button
                  type="button"
                  class="changeLogoHeaderColor"
                  data-color="red2"
                ></button>
              </div>
            </div>
            <div class="switch-block">
              <h4>Navbar Header</h4>
              <div class="btnSwitch">
                <button
                  type="button"
                  class="changeTopBarColor"
                  data-color="dark"
                ></button>
                <button
                  type="button"
                  class="changeTopBarColor"
                  data-color="blue"
                ></button>
                <button
                  type="button"
                  class="changeTopBarColor"
                  data-color="purple"
                ></button>
                <button
                  type="button"
                  class="changeTopBarColor"
                  data-color="light-blue"
                ></button>
                <button
                  type="button"
                  class="changeTopBarColor"
                  data-color="green"
                ></button>
                <button
                  type="button"
                  class="changeTopBarColor"
                  data-color="orange"
                ></button>
                <button
                  type="button"
                  class="changeTopBarColor"
                  data-color="red"
                ></button>
                <button
                  type="button"
                  class="changeTopBarColor"
                  data-color="white"
                ></button>
                <br />
                <button
                  type="button"
                  class="changeTopBarColor"
                  data-color="dark2"
                ></button>
                <button
                  type="button"
                  class="selected changeTopBarColor"
                  data-color="blue2"
                ></button>
                <button
                  type="button"
                  class="changeTopBarColor"
                  data-color="purple2"
                ></button>
                <button
                  type="button"
                  class="changeTopBarColor"
                  data-color="light-blue2"
                ></button>
                <button
                  type="button"
                  class="changeTopBarColor"
                  data-color="green2"
                ></button>
                <button
                  type="button"
                  class="changeTopBarColor"
                  data-color="orange2"
                ></button>
                <button
                  type="button"
                  class="changeTopBarColor"
                  data-color="red2"
                ></button>
              </div>
            </div>
            <div class="switch-block">
              <h4>Sidebar</h4>
              <div class="btnSwitch">
                <button
                  type="button"
                  class="selected changeSideBarColor"
                  data-color="white"
                ></button>
                <button
                  type="button"
                  class="changeSideBarColor"
                  data-color="dark"
                ></button>
                <button
                  type="button"
                  class="changeSideBarColor"
                  data-color="dark2"
                ></button>
              </div>
            </div>
          </div>
        </div>
        <div class="custom-toggle">
          <i class="icon-settings"></i>
        </div>
      </div>
      <!-- End Custom template -->
    </div>
    <!--   Core JS Files   -->
    <script src="assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>

    <!-- jQuery Scrollbar -->
    <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>

    <!-- Chart JS -->
    <script src="assets/js/plugin/chart.js/chart.min.js"></script>

    <!-- jQuery Sparkline -->
    <script src="assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>

    <!-- Chart Circle -->
    <script src="assets/js/plugin/chart-circle/circles.min.js"></script>

    <!-- Datatables -->
    <script src="assets/js/plugin/datatables/datatables.min.js"></script>

    <!-- Bootstrap Notify -->
    <script src="assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>

    <!-- jQuery Vector Maps -->
    <script src="assets/js/plugin/jsvectormap/jsvectormap.min.js"></script>
    <script src="assets/js/plugin/jsvectormap/world.js"></script>

    <!-- Google Maps Plugin -->
    <script src="assets/js/plugin/gmaps/gmaps.js"></script>

    <!-- Sweet Alert -->
    <script src="assets/js/plugin/sweetalert/sweetalert.min.js"></script>

    <!-- Kaiadmin JS -->
    <script src="assets/js/kaiadmin.min.js"></script>

    <!-- Kaiadmin DEMO methods, don't include it in your project! -->
    <script src="assets/js/setting-demo2.js"></script>
  </body>
</html>
