<?php
function is_active_nav_admin($page_name) {
    if (basename($_SERVER['PHP_SELF']) == $page_name) {
        return 'active';
    }
    return '';
}
?>

<nav class="navbar navbar-expand-lg navbar-compact">
    <div class="container-fluid px-4">
      <a class="navbar-brand d-flex align-items-center" href="../admin/dashboard.php">
        <img src="../assets/images/logoblog-removebg-preview.png" alt="B-LOG" height="50" class="me-2">
      </a>
      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mx-auto">
          <li class="nav-item">
            <a class="nav-link nav-link-compact <?= is_active_nav_admin('dashboard.php') ?>" href="dashboard.php">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-compact <?= is_active_nav_admin('data-buku.php') ?>" href="data-buku.php">Data Buku</a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-compact <?= is_active_nav_admin('kategori.php') ?>" href="kategori.php">Kategori</a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-compact <?= is_active_nav_admin('supplier.php') ?>" href="supplier.php">Supplier</a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-compact <?= is_active_nav_admin('barang-masuk.php') ?>" href="barang-masuk.php">Barang Masuk</a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-compact <?= is_active_nav_admin('barang-keluar.php') ?>" href="barang-keluar.php">Barang Keluar</a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-compact <?= is_active_nav_admin('pengguna.php') ?>" href="pengguna.php">Pengguna</a>
          </li>
        </ul>
        <div class="dropdown">
          <a href="#" class="d-flex align-items-center text-decoration-none" data-bs-toggle="dropdown">
            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2 overflow-hidden" style="width: 36px; height: 36px;">
              <?php if (!empty($_SESSION['image'])): ?>
                <img src="<?= htmlspecialchars($_SESSION['image']) ?>" alt="Profil" class="profile-img-navbar">
              <?php else: ?>
                <i class="bi bi-person-fill text-white fs-5"></i>
              <?php endif; ?>
            </div>
            <span style="font-size: 14px; color: #343a40; font-weight: 500;"><?= htmlspecialchars($_SESSION['fullname'] ?? 'Admin') ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2">
            <li><a class="dropdown-item" href="profil.php">Profil</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="../auth/logout.php" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a></li>
            
          </ul>
        </div>
      </div>
    </div>
</nav>
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logoutModalLabel">Konfirmasi Logout</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Apakah Anda yakin ingin keluar dari sesi ini?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <a href="../auth/logout.php" class="btn btn-danger">Logout</a>
      </div>
    </div>
  </div>
</div>