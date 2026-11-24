<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = mysqli_prepare($conn, "SELECT * FROM peminjaman WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$data) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode     = trim($_POST['kode_buku']);
    $judul    = trim($_POST['judul_buku']);
    $peminjam = trim($_POST['nama_peminjam']);
    $pinjam   = $_POST['tgl_pinjam'];
    $kembali  = $_POST['tgl_kembali'];
    $status   = $_POST['status'];

    $stmt_up = mysqli_prepare($conn, "UPDATE peminjaman SET kode_buku=?, judul_buku=?, nama_peminjam=?, tgl_pinjam=?, tgl_kembali=?, status=? WHERE id=?");
    mysqli_stmt_bind_param($stmt_up, "ssssssi", $kode, $judul, $peminjam, $pinjam, $kembali, $status, $id);
    mysqli_stmt_execute($stmt_up);

    $_SESSION['notif'] = "Data peminjaman berhasil diperbarui!";
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Peminjaman</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="card form-card">
            <h2>Edit Data Peminjaman</h2>
            <div id="domAlert"></div>
            <form method="POST" id="formPinjam">
                <div class="form-group">
                    <label>Kode Buku</label>
                    <input type="text" name="kode_buku" value="<?= htmlspecialchars($data['kode_buku']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Judul Buku</label>
                    <input type="text" name="judul_buku" value="<?= htmlspecialchars($data['judul_buku']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Nama Peminjam</label>
                    <input type="text" name="nama_peminjam" value="<?= htmlspecialchars($data['nama_peminjam']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Pinjam</label>
                    <input type="date" name="tgl_pinjam" id="tgl_pinjam" value="<?= $data['tgl_pinjam'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Jatuh Tempo Kembali</label>
                    <input type="date" name="tgl_kembali" id="tgl_kembali" value="<?= $data['tgl_kembali'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Status Peminjaman</label>
                    <select name="status">
                        <option value="Dipinjam" <?= $data['status'] === 'Dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                        <option value="Dikembalikan" <?= $data['status'] === 'Dikembalikan' ? 'selected' : '' ?>>Dikembalikan</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update Data</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>