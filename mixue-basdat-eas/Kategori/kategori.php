<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD kategori Mixue</title>
    <link rel="stylesheet" href="../style.css">
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('collapsed');
        }
    </script>
</head>

<body>
    <div class="sidebar">
        <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
        <h2 class="logo">Mixue</h2>
        <a href="kategori.php">Kategori</a>
        <a href="../Produk/produk.php">Produk</a>
        <a href="../Order/order.php">Order</a>
        <a href="../Order/order_detail.php">Order Detail</a>
        <a href="../Atribut/bahan_atribut.php">Bahan Atribut</a>
        <a href="../Atribut/ice_atribut.php">Ice Atribut</a>
        <a href="../Atribut/sugar_atribut.php">Sugar Atribut</a>
        <a href="../Atribut/topping_atribut.php">Topping Atribut</a>
        <a href="../Role/kasir.php">Kasir</a>
    </div>

    <div class="main">
        <h1>Data Kategori</h1>

        <div class="form-section">
            <form method="POST">
                <input type="hidden" name="id_kategori" id="id_kategori">
                <label>Nama Kategori:</label><br>
                <input type="text" name="nama_kategori" id="nama_kategori" required><br>
                <label>Deskripsi:</label><br>
                <input type="text" name="deskripsi" id="deskripsi" required><br>
                <button type="submit" name="simpan">Simpan</button>
                <button type="button" onclick="resetForm()">Reset</button>
                <script>
                    function resetForm() {
                        document.getElementById('id_kategori').value = '';
                        document.getElementById('nama_kategori').value = '';
                        document.getElementById('deskripsi').value = '';
                    }
                </script>

            </form>

        </div>

        <table>
            <tr>
                <th>ID</th>
                <th>Nama Kategori</th>
                <th>Deskripsi</th>
                <th>Aksi</th>
            </tr>
            <?php
            include '../koneksi.php';

            if (isset($_POST['simpan'])) {
                $id = $_POST['id_kategori'];
                $nama = $_POST['nama_kategori'];
                $deskripsi = $_POST['deskripsi'];

                if ($id == '') {
                    $conn->query("INSERT INTO kategori (nama_kategori, deskripsi) VALUES ('$nama', '$deskripsi')");
                } else {
                    $conn->query("UPDATE kategori SET nama_kategori='$nama', deskripsi='$deskripsi' WHERE id_kategori=$id");
                }

                // MENGHINDARI DUPLIKASI SAAT REFRESH
                header("Location: kategori.php");
                exit;
            }


            if (isset($_GET['hapus'])) {
                $conn->query("DELETE FROM kategori WHERE id_kategori=" . $_GET['hapus']);
            }

            $kategori = $conn->query("SELECT * FROM kategori ORDER BY id_kategori ASC");
            while ($row = $kategori->fetch_assoc()) {
                echo "<tr>
                <td>{$row['id_kategori']}</td>
                <td>{$row['nama_kategori']}</td>
                <td>{$row['deskripsi']}</td>
                <td>
                  <a href='?edit={$row['id_kategori']}'>Edit</a> |
                  <a href='?hapus={$row['id_kategori']}' onclick=\"return confirm('Yakin hapus?')\">Hapus</a>
                </td>
              </tr>";
            }

            if (isset($_GET['edit'])) {
                $id = $_GET['edit'];
                $res = $conn->query("SELECT * FROM kategori WHERE id_kategori=$id");
                $data = $res->fetch_assoc();
                echo "<script>
          document.getElementById('id_kategori').value = '{$data['id_kategori']}';
          document.getElementById('nama_kategori').value = '{$data['nama_kategori']}';
          document.getElementById('deskripsi').value = '{$data['deskripsi']}';
        </script>";
            }
            ?>
        </table>
    </div>
</body>

</html>