<?php
ob_start();
session_start();

$host = 'localhost';
$db = 'crud';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$alertScript = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['action'] == 'add_item') {
        $id = $_POST['id'];
        $namabarang = $_POST['namabarang'];
        $kategori = $_POST['kategori'];
        $harga = $_POST['harga'];
        $qty = $_POST['qty'];

        $stmt = $conn->prepare("INSERT INTO barang (id, namabarang, kategori, harga, qty) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $id, $namabarang, $kategori, $harga, $qty);
        $stmt->execute();
        $stmt->close();

        $alertScript = "
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data berhasil ditambahkan'
                });
            </script>
        ";
    } elseif ($_POST['action'] == 'edit_item') {
        $id = $_POST['id'];
        $namabarang = $_POST['namabarang'];
        $kategori = $_POST['kategori'];
        $harga = $_POST['harga'];
        $qty = $_POST['qty'];

        $stmt = $conn->prepare("UPDATE barang SET namabarang = ?, kategori = ?, harga = ?, qty = ? WHERE id = ?");
        $stmt->bind_param('ssssi', $namabarang, $kategori, $harga, $qty, $id);
        $stmt->execute();
        $stmt->close();

        $alertScript = "
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data berhasil diupdate'
                });
            </script>
        ";
    } elseif ($_POST['action'] == 'delete_item') {
        $id = $_POST['id'];

        $stmt = $conn->prepare("DELETE FROM barang WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        $alertScript = "
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Terhapus!',
                    text: 'Data berhasil dihapus'
                }).then((result) => {
                    location.reload();
                });
            </script>
        ";
    } elseif ($_POST['action'] == 'add_stock') {
        $id = $_POST['id'];
        $qty = $_POST['qty'];

        $stmt = $conn->prepare("UPDATE barang SET qty = qty + ? WHERE id = ?");
        $stmt->bind_param('ii', $qty, $id);
        $stmt->execute();
        $stmt->close();

        $alertScript = "
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Stok berhasil ditambahkan'
                });
            </script>
        ";
    } elseif ($_POST['action'] == 'remove_stock') {
        $id = $_POST['id'];
        $qty = $_POST['qty'];

        $stmt = $conn->prepare("UPDATE barang SET qty = qty - ? WHERE id = ?");
        $stmt->bind_param('ii', $qty, $id);
        $stmt->execute();
        $stmt->close();

        $alertScript = "
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Stok berhasil dikurangi'
                });
            </script>
        ";
    }
}

$query = "SELECT DISTINCT kategori FROM barang";
$result = $conn->query($query);
$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row['kategori'];
}

$query = "SELECT * FROM barang";
$result = $conn->query($query);
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$conn->close();
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Toko Jualin Aja</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <ul class="nav justify-content-center btn-group">
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#" data-bs-toggle="modal"
                data-bs-target="#dashboardModal">Tambah Barang</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#stokMasukModal">Stok Masuk</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#stokKeluarModal">Stok Keluar</a>
        </li>
    </ul>

    <!-- Tambah Barang Modal -->
    <div class="modal fade" id="dashboardModal" tabindex="-1" aria-labelledby="dashboardModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dashboardModalLabel">Dashboard</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createItemForm" action="dashboard.php" method="POST">
                        <input type="hidden" name="action" value="add_item">
                        <div class="mb-3">
                            <label for="id" class="form-label">Id Barang:</label>
                            <input type="text" class="form-control" id="id" name="id">
                        </div>
                        <div class="mb-3">
                            <label for="namabarang" class="form-label">Nama Produk:</label>
                            <input type="text" class="form-control" id="namabarang" name="namabarang">
                        </div>

                        <!-- Tabel di Container -->
                        <div class="mb-3">
                            <label for="kategori" class="form-label">Kategori:</label>
                            <select class="form-control" id="kategori" name="kategori">
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category); ?>">
                                        <?php echo htmlspecialchars($category); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="harga" class="form-label">Harga:</label>
                            <input type="text" class="form-control" id="harga" name="harga">
                        </div>
                        <div class="mb-3">
                            <label for="qty" class="form-label">Qty:</label>
                            <input type="text" class="form-control" id="qty" name="qty">
                        </div>
                        <button type="submit" class="btn btn-outline-success">Submit</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stok Masuk Modal -->
    <div class="modal fade" id="stokMasukModal" tabindex="-1" aria-labelledby="stokMasukModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="stokMasukModalLabel">Stok Masuk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="stokMasukForm" action="dashboard.php" method="POST">
                        <input type="hidden" name="action" value="add_stock">
                        <div class="mb-3">
                            <label for="id" class="form-label">Nama Barang:</label>
                            <select class="form-control" id="id" name="id">
                                <?php foreach ($data as $item): ?>
                                    <option value="<?php echo htmlspecialchars($item['id']); ?>">
                                        <?php echo htmlspecialchars($item['namabarang']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="qty" class="form-label">Qty:</label>
                            <input type="text" class="form-control" id="qty" name="qty">
                        </div>
                        <button type="submit" class="btn btn-outline-success">Submit</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stok Keluar Modal -->
    <div class="modal fade" id="stokKeluarModal" tabindex="-1" aria-labelledby="stokKeluarModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="stokKeluarModalLabel">Stok Keluar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="stokKeluarForm" action="dashboard.php" method="POST">
                        <input type="hidden" name="action" value="remove_stock">
                        <div class="mb-3">
                            <label for="id" class="form-label">ID Barang:</label>
                            <select class="form-control" id="id" name="id">
                                <?php foreach ($data as $item): ?>
                                    <option value="<?php echo htmlspecialchars($item['id']); ?>">
                                        <?php echo htmlspecialchars($item['namabarang']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="qty" class="form-label">Qty:</label>
                            <input type="text" class="form-control" id="qty" name="qty">
                        </div>
                        <button type="submit" class="btn btn-outline-success">Submit</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <h3 class="text-center">Data Produk</h3>
        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID Barang</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Qty</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $item): ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($item['id']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($item['namabarang']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($item['kategori']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($item['harga']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($item['qty']); ?>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#editModal<?php echo $item['id']; ?>">Edit</button>
                                <form action="dashboard.php" method="POST" class="d-inline-block">
                                    <input type="hidden" name="action" value="delete_item">
                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Anda yakin ingin menghapus data ini?')">Delete</button>
                                </form>
                            </div>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal<?php echo $item['id']; ?>" tabindex="-1"
                                aria-labelledby="editModalLabel<?php echo $item['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel<?php echo $item['id']; ?>">Edit
                                                Produk</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="dashboard.php" method="POST">
                                                <input type="hidden" name="action" value="edit_item">
                                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                <div class="mb-3">
                                                    <label for="namabarang" class="form-label">Nama Produk:</label>
                                                    <input type="text" class="form-control" id="namabarang"
                                                        name="namabarang"
                                                        value="<?php echo htmlspecialchars($item['namabarang']); ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="kategori" class="form-label">Kategori:</label>
                                                    <input type="text" class="form-control" id="kategori" name="kategori"
                                                        value="<?php echo htmlspecialchars($item['kategori']); ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="harga" class="form-label">Harga:</label>
                                                    <input type="text" class="form-control" id="harga" name="harga"
                                                        value="<?php echo htmlspecialchars($item['harga']); ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="qty" class="form-label">Qty:</label>
                                                    <input type="text" class="form-control" id="qty" name="qty"
                                                        value="<?php echo htmlspecialchars($item['qty']); ?>">
                                                </div>
                                                <button type="submit" class="btn btn-outline-success">Submit</button>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php echo $alertScript; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>