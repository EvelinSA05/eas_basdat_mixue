<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>CRUD Ice Atribut Mixue</title>
    <link rel="stylesheet" href="../style.css" />
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('collapsed');
        }

        function resetForm() {
            document.getElementById('id_ice').value = '';
            document.getElementById('level_ice').value = '';
            document.getElementById('jumlah_gram').value = '';
            document.getElementById('id_bahan_atribut').value = '';
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
        <a href="bahan_atribut.php">Bahan Atribut</a>
        <a href="ice_atribut.php">Ice Atribut</a>
        <a href="sugar_atribut.php">Sugar Atribut</a>
        <a href="topping_atribut.php">Topping Atribut</a>
        <a href="../Role/kasir.php">Kasir</a>
    </div>

    <div class="main">
        <h1>Ice Atribut</h1>

        <div class="form-section">
            <form method="POST">
                <input type="hidden" name="id_ice" id="id_ice" />
                <label>Level Ice:</label><br />
                <input type="text" name="level_ice" id="level_ice" required /><br />

                <label>Jumlah (Gram):</label><br />
                <input type="number" name="jumlah_gram" id="jumlah_gram" required /><br />

                <label>Bahan Atribut (untuk stok):</label><br />
                <select name="id_bahan_atribut" id="id_bahan_atribut" required>
                    <option value="">-- Pilih Bahan --</option>
                    <?php
                    include '../koneksi.php';
                    $bahan = $conn->query("SELECT * FROM bahan_atribut ORDER BY nama_bahan ASC");
                    while ($rowBahan = $bahan->fetch_assoc()) {
                        echo "<option value='{$rowBahan['id_bahan_atribut']}'>{$rowBahan['nama_bahan']} ({$rowBahan['stok']} {$rowBahan['satuan']})</option>";
                    }
                    ?>
                </select><br /><br />

                <button type="submit" name="simpan">Simpan</button>
                <button type="button" onclick="resetForm()">Reset</button>
            </form>
        </div>

        <table>
            <tr>
                <th>ID</th>
                <th>Level Ice</th>
                <th>Jumlah Gram</th>
                <th>Bahan</th>
                <th>Aksi</th>
            </tr>
            <?php
            // Proses simpan data
            if (isset($_POST['simpan'])) {
                $id = $_POST['id_ice'];
                $level = $_POST['level_ice'];
                $jumlah = $_POST['jumlah_gram'];
                $id_bahan = $_POST['id_bahan_atribut'];

                if ($id == '') {
                    $conn->query("INSERT INTO ice_atribut (level_ice, jumlah_gram, id_bahan_atribut) VALUES ('$level', $jumlah, $id_bahan)");
                } else {
                    $conn->query("UPDATE ice_atribut SET level_ice='$level', jumlah_gram=$jumlah, id_bahan_atribut=$id_bahan WHERE id_ice=$id");
                }

                // Mencegah form submit ulang (refresh)
                header("Location: ice_atribut.php");
                exit;
            }

            // Proses hapus data
            if (isset($_GET['hapus'])) {
                $conn->query("DELETE FROM ice_atribut WHERE id_ice=" . $_GET['hapus']);
                header("Location: ice_atribut.php");
                exit;
            }

            // Menampilkan data
            $result = $conn->query("SELECT ia.*, ba.nama_bahan FROM ice_atribut ia LEFT JOIN bahan_atribut ba ON ia.id_bahan_atribut = ba.id_bahan_atribut ORDER BY ia.id_ice ASC");
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                <td>{$row['id_ice']}</td>
                <td>{$row['level_ice']}</td>
                <td>{$row['jumlah_gram']}</td>
                <td>{$row['nama_bahan']}</td>
                <td>
                  <a href='?edit={$row['id_ice']}'>Edit</a> |
                  <a href='?hapus={$row['id_ice']}' onclick=\"return confirm('Yakin hapus?')\">Hapus</a>
                </td>
              </tr>";
            }

            // Edit form: isi form dengan data yang dipilih
            if (isset($_GET['edit'])) {
                $id = $_GET['edit'];
                $res = $conn->query("SELECT * FROM ice_atribut WHERE id_ice=$id");
                $data = $res->fetch_assoc();

                echo "<script>
                  document.getElementById('id_ice').value = '{$data['id_ice']}';
                  document.getElementById('level_ice').value = '{$data['level_ice']}';
                  document.getElementById('jumlah_gram').value = '{$data['jumlah_gram']}';
                  document.getElementById('id_bahan_atribut').value = '{$data['id_bahan_atribut']}';
                </script>";
            }
            ?>
        </table>
    </div>
</body>

</html>
