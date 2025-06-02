<?php
include_once("config.php");
requireLogin();

// Cek apakah ada ID yang dikirimkan
if(!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = (int)$_GET['id'];

// Ambil data mahasiswa berdasarkan ID
$result = mysqli_query($conn, "SELECT * FROM mahasiswa WHERE id=$id");

if(mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$row = mysqli_fetch_assoc($result);
$current_foto = $row['foto'];

// Cek apakah form telah di-submit
if(isset($_POST['update'])) {
    $nim = mysqli_real_escape_string($conn, $_POST['nim']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $jurusan = mysqli_real_escape_string($conn, $_POST['jurusan']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    
    $errors = array();
    $foto_filename = $current_foto;
    
    // Validasi form
    if(empty($nim)) $errors[] = "NIM tidak boleh kosong";
    if(empty($nama)) $errors[] = "Nama tidak boleh kosong";
    if(empty($jurusan)) $errors[] = "Jurusan tidak boleh kosong";
    if(empty($email)) $errors[] = "Email tidak boleh kosong";
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format email tidak valid";
    
    // Cek apakah NIM sudah ada (kecuali untuk data ini sendiri)
    $check_nim = mysqli_query($conn, "SELECT nim FROM mahasiswa WHERE nim = '$nim' AND id != $id");
    if(mysqli_num_rows($check_nim) > 0) {
        $errors[] = "NIM sudah terdaftar";
    }
    
    // Proses upload foto jika ada
    if(!empty($_FILES['foto']['name'])) {
        $upload_result = uploadFile($_FILES['foto']);
        if($upload_result['success']) {
            // Hapus foto lama jika ada
            if($current_foto) {
                deleteFile($current_foto);
            }
            $foto_filename = $upload_result['filename'];
        } else {
            $errors[] = $upload_result['message'];
        }
    }
    
    // Hapus foto jika diminta
    if(isset($_POST['hapus_foto']) && $_POST['hapus_foto'] == '1') {
        if($current_foto) {
            deleteFile($current_foto);
        }
        $foto_filename = null;
    }
    
    // Jika tidak ada error, update data
    if(empty($errors)) {
        $foto_sql = $foto_filename ? "'$foto_filename'" : "NULL";
        $query = "UPDATE mahasiswa SET 
                  nim='$nim', 
                  nama='$nama', 
                  jurusan='$jurusan', 
                  email='$email', 
                  alamat='$alamat',
                  foto=$foto_sql
                  WHERE id=$id";
        
        if(mysqli_query($conn, $query)) {
            $success = "Data berhasil diperbarui!";
            $current_foto = $foto_filename;
            // Ambil data yang sudah diperbarui
            $result = mysqli_query($conn, "SELECT * FROM mahasiswa WHERE id=$id");
            $row = mysqli_fetch_assoc($result);
        } else {
            $errors[] = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mahasiswa</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f7fa;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        h1 {
            color: #333;
            margin: 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }
        input[type="text"],
        input[type="email"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        .current-photo {
            text-align: center;
            margin: 15px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .current-photo img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .photo-actions {
            margin-top: 10px;
        }
        .photo-actions label {
            font-weight: normal;
            color: #dc3545;
            cursor: pointer;
        }
        .file-input-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        .file-input {
            opacity: 0;
            position: absolute;
            z-index: -1;
        }
        .file-input-label {
            display: block;
            padding: 12px;
            border: 2px dashed #667eea;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background-color: #f8f9fa;
        }
        .file-input-label:hover {
            background-color: #e9ecef;
        }
        .preview-container {
            margin-top: 10px;
            text-align: center;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #667eea;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 16px;
            margin-right: 10px;
        }
        .btn:hover {
            background-color: #5a67d8;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .form-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        .required {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✏️ Edit Mahasiswa</h1>
            <a href="index.php" class="btn btn-secondary">← Kembali</a>
        </div>
        
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>Error:</strong>
                <ul style="margin: 10px 0 0 20px;">
                    <?php foreach($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <strong><?php echo $success; ?></strong>
                <br><a href="index.php">Lihat semua data</a>
            </div>
        <?php endif; ?>
        
        <form action="edit.php?id=<?php echo $id; ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>📷 Foto Profil</label>
                
                <?php if ($current_foto && file_exists("uploads/mahasiswa/" . $current_foto)): ?>
                    <div class="current-photo">
                        <p><strong>Foto saat ini:</strong></p>
                        <img src="uploads/mahasiswa/<?php echo $current_foto; ?>" alt="Foto saat ini">
                        <div class="photo-actions">
                            <label>
                                <input type="checkbox" name="hapus_foto" value="1">
                                🗑️ Hapus foto ini
                            </label>
                        </div>
                    </div>
                <?php else: ?>
                    <p style="color: #6c757d; font-style: italic;">Tidak ada foto saat ini</p>
                <?php endif; ?>
                
                <div class="file-input-wrapper">
                    <input type="file" name="foto" id="foto" class="file-input" accept="image/*">
                    <label for="foto" class="file-input-label">
                        <span>📁 Upload foto baru</span>
                        <small style="display: block; margin-top: 5px; color: #6c757d;">
                            Format: JPG, JPEG, PNG, GIF (Max: 5MB)
                        </small>
                    </label>
                </div>
                <div class="preview-container" id="preview" style="display: none;"></div>
            </div>
            
            <div class="form-group">
                <label for="nim">NIM <span class="required">*</span></label>
                <input type="text" name="nim" id="nim" value="<?php echo htmlspecialchars($row['nim']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="nama">Nama Lengkap <span class="required">*</span></label>
                <input type="text" name="nama" id="nama" value="<?php echo htmlspecialchars($row['nama']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="jurusan">Jurusan <span class="required">*</span></label>
                <input type="text" name="jurusan" id="jurusan" value="<?php echo htmlspecialchars($row['jurusan']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="alamat">Alamat</label>
                <textarea name="alamat" id="alamat"><?php echo htmlspecialchars($row['alamat']); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="update" class="btn">💾 Update Data</button>
                <a href="index.php" class="btn btn-secondary">❌ Batal</a>
            </div>
        </form>
    </div>
    
    <script>
        // Preview image saat dipilih
        document.getElementById('foto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('preview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" class="preview-image"><p>Preview foto baru</p>';
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });
    </script>
</body>
</html>