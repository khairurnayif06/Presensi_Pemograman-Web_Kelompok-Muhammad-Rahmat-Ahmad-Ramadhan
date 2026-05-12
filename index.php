<?php
$dataFile = __DIR__ . '/data-absensi.json';

function loadAbsensi($filePath)
{
    if (!file_exists($filePath)) {
        return [];
    }

    $json = file_get_contents($filePath);
    if ($json === false || trim($json) === '') {
        return [];
    }

    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function saveAbsensi($filePath, $data)
{
    $result = file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    if ($result === false) {
        throw new Exception('Gagal menyimpan data ke file.');
    }
}

$absensi = loadAbsensi($dataFile);
$message = '';
$editId = null;
$editValue = ['nama' => '', 'status' => ''];

if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    if (isset($absensi[$id])) {
        if ($_GET['action'] === 'delete') {
            unset($absensi[$id]);
            $absensi = array_values($absensi);
            try {
                saveAbsensi($dataFile, $absensi);
                header('Location: index.php?message=' . urlencode('Data absensi berhasil dihapus.'));
                exit;
            } catch (Exception $e) {
                header('Location: index.php?message=' . urlencode('Error: ' . $e->getMessage()));
                exit;
            }
        }
            exit;
        }

        if ($_GET['action'] === 'edit') {
            $editId = $id;
            $editValue = $absensi[$id];
        }
    }


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $status = trim($_POST['status'] ?? '');

    if ($nama !== '' && $status !== '') {
        $row = [
            'nama' => htmlspecialchars($nama, ENT_QUOTES, 'UTF-8'),
            'status' => htmlspecialchars($status, ENT_QUOTES, 'UTF-8'),
        ];

        if (isset($_POST['edit_id']) && $_POST['edit_id'] !== '') {
            $editIndex = (int) $_POST['edit_id'];
            if (isset($absensi[$editIndex])) {
                $absensi[$editIndex] = $row;
                $message = "Data mahasiswa {$row['nama']} berhasil diubah.";
            }
        } else {
            $absensi[] = $row;

            if ($status === 'Hadir') {
                $message = "Mahasiswa dengan nama {$row['nama']} hadir hari ini";
            } elseif ($status === 'Izin') {
                $message = "Mahasiswa dengan nama {$row['nama']} izin";
            } elseif ($status === 'Sakit') {
                $message = "Mahasiswa dengan nama {$row['nama']} sakit, semoga cepat sembuh";
            } elseif ($status === 'Tidak Hadir') {
                $message = "Mahasiswa dengan nama {$row['nama']} tidak hadir hari ini";
            } else {
                $message = "Data mahasiswa {$row['nama']} berhasil ditambahkan.";
            }
        }

        try {
            saveAbsensi($dataFile, $absensi);
            header('Location: index.php?message=' . urlencode($message));
            exit;
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
        }
    }

    $message = 'Nama dan status kehadiran wajib diisi.';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Form Absensi Mahasiswa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Form Absensi Mahasiswa</h2>
    <?php if ($message): ?>
        <div class="result">
            <p class="message"><?php echo $message; ?></p>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="edit_id" value="<?php echo $editId !== null ? $editId : ''; ?>">

        <label for="nama">Nama Mahasiswa:</label>
        <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($editValue['nama'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <div class="radio-group">
            <label>Status Kehadiran:</label><br>
            <label><input type="radio" name="status" value="Hadir" <?php echo $editValue['status'] === 'Hadir' ? 'checked' : ''; ?> required> Hadir</label><br>
            <label><input type="radio" name="status" value="Izin" <?php echo $editValue['status'] === 'Izin' ? 'checked' : ''; ?>> Izin</label><br>
            <label><input type="radio" name="status" value="Sakit" <?php echo $editValue['status'] === 'Sakit' ? 'checked' : ''; ?>> Sakit</label><br>
            <label><input type="radio" name="status" value="Tidak Hadir" <?php echo $editValue['status'] === 'Tidak Hadir' ? 'checked' : ''; ?>> Tidak Hadir</label><br>
        </div>

        <input type="submit" value="<?php echo $editId !== null ? 'Simpan Perubahan' : 'Submit'; ?>">
    </form>

    <?php if (!empty($absensi)): ?>
        <h3>Data Absensi</h3>
        <table class="absensi-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Mahasiswa</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($absensi as $index => $data): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo $data['nama']; ?></td>
                        <td><?php echo $data['status']; ?></td>
                        <td>
                            <a class="btn btn-edit" href="?action=edit&id=<?php echo $index; ?>">Edit</a>
                            <a class="btn btn-delete" href="?action=delete&id=<?php echo $index; ?>" onclick="return confirm('Hapus data absensi ini?');">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
