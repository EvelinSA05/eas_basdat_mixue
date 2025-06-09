<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD kasir Mixue</title>
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
        <a href="../Kategori/kategori.php">Kategori</a>
        <a href="../Produk/produk.php">Produk</a>
        <a href="../Order/order.php">Order</a>
        <a href="../Order/order_detail.php">Order Detail</a>
        <a href="../Atribut/bahan_atribut.php">Bahan Atribut</a>
        <a href="../Atribut/ice_atribut.php">Ice Atribut</a>
        <a href="../Atribut/sugar_atribut.php">Sugar Atribut</a>
        <a href="../Atribut/topping_atribut.php">Topping Atribut</a>
        <a href="kasir.php">Kasir</a>
    </div>

    <div class="main">
        <h1>Data Kasir</h1>

        <div class="form-section">
            <form method="POST">
                <input type="hidden" name="id_kasir" id="id_kasir">
                <label>Nama Kasir:</label><br>
                <input type="text" name="nama_kasir" id="nama_kasir" required><br>
                <label>No Telepon:</label><br>
                <input type="text" name="no_telepon" id="no_telepon" required><br>
                <label>Alamat Jalan:</label><br>
                <input type="text" name="nama_jalan" id="nama_jalan" required><br>
                <label>Alamat Desa:</label><br>
                <input type="text" name="desa" id="desa" required><br>
                <label>Alamat Kabupaten:</label><br>
                <input type="text" name="kabupaten" id="kabupaten" required><br>
                <label>Alamat Provinsi:</label><br>
                <input type="text" name="provinsi" id="provinsi" required><br>
                <button type="submit" name="simpan">Simpan</button>
                <button type="button" onclick="resetForm()">Reset</button>
                <script>
                    function resetForm() {
                        document.getElementById('id_kasir').value = '';
                        document.getElementById('nama_kasir').value = '';
                        document.getElementById('no_telepon').value = '';
                        document.getElementById('nama_jalan').value = '';
                        document.getElementById('desa').value = '';
                        document.getElementById('kabupaten').value = '';
                        document.getElementById('provinsi').value = '';
                    }
                </script>

            </form>

        </div>

        <table>
            <tr>
                <th>ID</th>
                <th>Nama kasir</th>
                <th>Harga Satuan</th>
                <th>Alamat jalan</th>
                <th>Alamat Desa</th>
                <th>Alamat Kabupaten</th>
                <th>Alamat Provinsi</th>
                <th>Aksi</th>
            </tr>
            <?php
            include '../koneksi.php';

            if (isset($_POST['simpan'])) {
                $id = $_POST['id_kasir'];
                $nama = $_POST['nama_kasir'];
                $telp = $_POST['no_telepon'];
                $nama_jalan = $_POST['nama_jalan'];
                $desa = $_POST['desa'];
                $kabupaten = $_POST['kabupaten'];
                $provinsi = $_POST['provinsi'];

                if ($id == '') {
                    $conn->query("INSERT INTO kasir (nama_kasir, no_telepon, nama_jalan, desa, kabupaten, provinsi) VALUES ('$nama', '$telp', '$nama_jalan', '$desa', '$kabupaten', '$provinsi')");
                } else {
                    $conn->query("UPDATE kasir SET nama_kasir='$nama', no_telepon='$telp', nama_jalan='$nama_jalan', desa='$desa', kabupaten='$kabupaten', provinsi='$provinsi' WHERE id_kasir=$id");
                }

                // MENGHINDARI DUPLIKASI SAAT REFRESH
                header("Location: kasir.php");
                exit;
            }


            if (isset($_GET['hapus'])) {
                $conn->query("DELETE FROM kasir WHERE id_kasir=" . $_GET['hapus']);
            }

            $kasir = $conn->query("SELECT * FROM kasir ORDER BY id_kasir ASC");
            while ($row = $kasir->fetch_assoc()) {
                echo "<tr>
                <td>{$row['id_kasir']}</td>
                <td>{$row['nama_kasir']}</td>
                <td>{$row['no_telepon']}</td>
                <td>{$row['nama_jalan']}</td>
                <td>{$row['desa']}</td>
                <td>{$row['kabupaten']}</td>
                <td>{$row['provinsi']}</td>
                <td>
                  <a href='?edit={$row['id_kasir']}'>Edit</a> |
                  <a href='?hapus={$row['id_kasir']}' onclick=\"return confirm('Yakin hapus?')\">Hapus</a>
                </td>
              </tr>";
            }

            if (isset($_GET['edit'])) {
                $id = $_GET['edit'];
                $res = $conn->query("SELECT * FROM kasir WHERE id_kasir=$id");
                $data = $res->fetch_assoc();
                echo "<script>
          document.getElementById('id_kasir').value = '{$data['id_kasir']}';
          document.getElementById('nama_kasir').value = '{$data['nama_kasir']}';
          document.getElementById('no_telepon').value = '{$data['no_telepon']}';
          document.getElementById('nama_jalan').value = '{$data['nama_jalan']}';
          document.getElementById('desa').value = '{$data['desa']}';
          document.getElementById('kabupaten').value = '{$data['kabupaten']}';
          document.getElementById('provinsi').value = '{$data['provinsi']}';
        </script>";
            }
            ?>
        </table>
    </div>
</body>

</html>