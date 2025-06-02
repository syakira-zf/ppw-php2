<?php
include_once("config.php");
requireLogin();

// Cek apakah ada ID yang dikirimkan
if(isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Ambil data foto sebelum menghapus
    $result = mysqli_query($conn, "SELECT foto FROM mahasiswa WHERE id=$id");
    if(mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $foto = $row['foto'];
        
        // Hapus data dari database
        $delete_query = "DELETE FROM mahasiswa WHERE id=$id";
        if(mysqli_query($conn, $delete_query)) {
            // Hapus file foto jika ada
            if($foto) {
                deleteFile($foto);
            }
            $_SESSION['message'] = "Data berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Error: " . mysqli_error($conn);
        }
    }
}

// Redirect kembali ke halaman utama
header("Location: index.php");
exit();
?>