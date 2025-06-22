<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_code'] !== 'ADM') {
    header('Location: ../auth/login.php');
    exit;
}
include '../shared/koneksi.php';

// --- Proses Delete ---
if (isset($_GET['delete_id'])) {
    $pdo->prepare("DELETE FROM data_buku WHERE id = ?")
        ->execute([(int)$_GET['delete_id']]);
    header("Location: data-buku.php");
    exit;
}

// --- Ambil data untuk Edit ---
$editBuku = null;
if (isset($_GET['edit_id'])) {
    $stm = $pdo->prepare("
      SELECT id, judul, kategori_id, supplier_id, stok_awal
      FROM data_buku WHERE id = ?");
    $stm->execute([(int)$_GET['edit_id']]);
    $editBuku = $stm->fetch(PDO::FETCH_ASSOC);
}

// --- Load dropdown ---
$kategoris = $pdo->query("SELECT * FROM kategori ORDER BY nama")->fetchAll(PDO::FETCH_ASSOC);
$suppliers = $pdo->query("SELECT * FROM supplier ORDER BY nama")->fetchAll(PDO::FETCH_ASSOC);

// --- Load data Buku ---
$bukus = $pdo->query("
    SELECT b.id, b.judul, k.nama AS kategori, b.stok_awal AS stok
    FROM data_buku b
    LEFT JOIN kategori k ON b.kategori_id = k.id
    ORDER BY b.id
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>B-LOG - Data Buku</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="../assets/css/admin-styles.css" rel="stylesheet">
</head>
<body>
  <!-- Navbar -->
  <?php include '../shared/components/navbar-admin.php'; ?>

  <!-- Page Header -->
  <div class="page-header">
    <div class="container-compact">
      <div class="d-flex justify-content-between align-items-center">
        <h1 class="page-title">Data Buku</h1>
      </div>
    </div>
  </div>

  <!-- Main Content -->
<div class="container-compact">

  <!-- Tombol Tambah Buku -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0 fw-semibold"></h4>
    <button class="btn btn-primary-compact d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#tambahBukuModal">
      <i class="bi bi-plus-circle"></i> Tambah Buku
    </button>
  </div>

  <!-- Tabel Buku -->
  <div class="card-compact shadow-sm">
    <div class="table-responsive">
      <table class="table table-compact align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 50px;">#</th>
            <th>Judul Buku</th>
            <th style="width: 150px;">Kategori</th>
            <th style="width: 80px;">Stok</th>
            <th style="width: 100px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
<?php foreach($bukus as $i => $b): ?>
<tr class="align-middle">
  <td class="text-center"><?= $i + 1 ?></td>

  <td class="fw-medium"><?= htmlspecialchars($b['judul']) ?></td>

  <td>
    <span class="badge bg-secondary bg-opacity-10 text-secondary badge-compact">
      <?= htmlspecialchars($b['kategori']) ?>
    </span>
  </td>

  <td>
    <span class="badge bg-primary bg-opacity-10 text-primary badge-compact">
      <?= (int)$b['stok'] ?>
    </span>
  </td>

<td class="text-nowrap">
  <div class="d-flex align-items-center gap-1">
    <a href="?edit_id=<?= $b['id'] ?>" class="btn btn-warning btn-sm btn-compact" title="Edit Buku">
      <i class="bi bi-pencil"></i>
    </a>
    <a href="?delete_id=<?= $b['id'] ?>" class="btn btn-danger btn-sm btn-compact" onclick="return confirm('Hapus buku ini?')" title="Hapus Buku">
      <i class="bi bi-trash"></i>
    </a>
  </div>
</td>
</tr>
<?php endforeach; ?>

        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Spacer bawah -->
<div style="height: 40px;"></div>


  <!-- Modal Tambah Buku -->
  <div class="modal fade modal-compact" id="tambahBukuModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="actions/add_buku.php" method="post">
          <div class="modal-header">
            <h6 class="modal-title fw-medium">Tambah Buku Baru</h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label form-label-compact">Judul Buku</label>
              <input type="text" name="judul" class="form-control form-control-compact" required>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label form-label-compact">Kategori</label>
                <select name="kategori_id" class="form-select form-control-compact" required>
                  <option value="">-- Pilih Kategori --</option>
                  <?php foreach($kategoris as $k): ?>
                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label form-label-compact">Supplier</label>
                <select name="supplier_id" class="form-select form-control-compact" required>
                  <option value="">-- Pilih Supplier --</option>
                  <?php foreach($suppliers as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label form-label-compact">Jumlah Stok Awal</label>
              <input type="number" name="stok_awal" class="form-control form-control-compact" min="1" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light btn-compact" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary-compact">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Edit Buku -->
  <?php if($editBuku): ?>
  <div class="modal fade modal-compact show" id="editBukuModal" style="display:block" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="actions/edit_buku.php" method="post">
          <div class="modal-header">
            <h6 class="modal-title fw-medium">Edit Buku #<?= $editBuku['id'] ?></h6>
            <a href="data-buku.php" class="btn-close"></a>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label form-label-compact">Judul Buku</label>
              <input type="text" name="judul" class="form-control form-control-compact"
                     value="<?= htmlspecialchars($editBuku['judul']) ?>" required>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label form-label-compact">Kategori</label>
                <select name="kategori_id" class="form-select form-control-compact" required>
                  <option value="">-- Pilih Kategori --</option>
                  <?php foreach($kategoris as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= $editBuku['kategori_id']==$k['id']?'selected':''?>>
                      <?= htmlspecialchars($k['nama']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label form-label-compact">Supplier</label>
                <select name="supplier_id" class="form-select form-control-compact" required>
                  <option value="">-- Pilih Supplier --</option>
                  <?php foreach($suppliers as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $editBuku['supplier_id']==$s['id']?'selected':''?>>
                      <?= htmlspecialchars($s['nama']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label form-label-compact">Jumlah Stok Awal</label>
              <input type="number" name="stok_awal" class="form-control form-control-compact" min="1"
                     value="<?= (int)$editBuku['stok_awal'] ?>" required>
            </div>
            <input type="hidden" name="id" value="<?= $editBuku['id'] ?>">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light btn-compact" onclick="window.location='data-buku.php'">Batal</button>
            <button type="submit" class="btn btn-primary-compact">Update</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script>
    new bootstrap.Modal(document.getElementById('editBukuModal')).show();
  </script>
  <?php endif; ?>

  <!-- Logout Modal -->
  <div class="modal fade" id="logoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content border-0">
        <div class="modal-body text-center p-4">
          <i class="bi bi-question-circle text-warning" style="font-size: 48px;"></i>
          <h6 class="mt-3 mb-2">Konfirmasi Logout</h6>
          <p class="text-muted" style="font-size: 13px;">Apakah Anda yakin ingin keluar?</p>
          <div class="d-flex gap-2 justify-content-center mt-3">
            <button type="button" class="btn btn-light btn-compact" data-bs-dismiss="modal">Batal</button>
            <a href="../auth/logout.php" class="btn btn-danger btn-compact">Keluar</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
