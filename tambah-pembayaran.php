<?php
//script php dimodifikasi berdasarkan script
//http://www.phpeasystep.com/phptu/18.html

//koneksi ke database
include 'koneksidb.php';
include_once 'fungsi.php';

//menangkap posting dari field input form


$id_user 	  = $_POST['id_user'];
$id_transaksi = $_POST['idtrx'];
$tgl		  = $_POST['tgl'];
$bank      	  = $_POST['bank'];
$rekening	  = $_POST['rekening'];
$nama	      = $_POST['nama_user'];
$status		  = $_POST['status'];
$lokasi_file  = $_FILES['file']['tmp_name'];
$nama_file    = $_FILES['file']['name'];
$folder       = "petugas/library/files/$nama_file";

if (!empty($_FILES["file"]["tmp_name"]))
{
    $jenis_gambar=$_FILES['file']['type']; //memeriksa format gambar
    if($jenis_gambar=="image/jpeg" || $jenis_gambar=="image/jpg" || $jenis_gambar=="image/gif" || $jenis_gambar=="image/png")
    {           
        $lampiran = basename($_FILES['file']['name']);  
        
        //mengupload gambar dan update row table database dengan path folder dan nama image		
        if(move_uploaded_file($lokasi_file,"$folder")){
			// ambil total, pastikan NULL menjadi 0
$res = $mysqli->query("SELECT COALESCE(SUM(subtotal), 0) AS total FROM tbl_sementaratrf");
if (!$res) {
    die("Query error (sum): " . $mysqli->error);
}
$row = $res->fetch_assoc();
$tot = $row['total']; // pasti ada, minimal 0

// cek prepare berhasil
$tambah = $mysqli->prepare("INSERT INTO tbl_transfer
    (id_transfer, id_user, nama, no_rekening, nama_bank, jumlah, tgl_transfer, image, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
if (!$tambah) {
    die("Prepare failed: " . $mysqli->error);
}

// pastikan tipe param sesuai: jika jumlah (jumlah) numeric (INT) -> gunakan 'i'
// contoh: id_transfer string, id_user string, nama string, no_rekening string, nama_bank string, jumlah int, tgl string, image string, status string
// => "sssssis s" -> actually without space: "sssssisss"
$tambah->bind_param(
    "sssssisss",
    $id_transaksi,
    $id_user,
    $nama,
    $rekening,
    $bank,
    $tot,       // numeric -> gunakan i (atau d jika decimal)
    $tgl,
    $lampiran,
    $status
);

// execute dan cek
if ($tambah->execute()) {
    // insert detail transfer dari tabel sementara
    $ress = $mysqli->query("SELECT id_transfer, id_kategori, subtotal FROM tbl_sementaratrf");
    if (!$ress) {
        die("Query error (detail): " . $mysqli->error);
    }
    while ($r = $ress->fetch_row()) {
        // pastikan urutan kolom di tbl_detailtransfer sesuai, atau gunakan explicit column list
        $mysqli->query("INSERT INTO tbl_detailtransfer (id_transfer, id_kategori, subtotal) VALUES ('{$r[0]}', '{$r[1]}', '{$r[2]}')");
    }

    $mysqli->query("TRUNCATE TABLE tbl_sementaratrf");
    echo "<script>window.alert('Transaksi Berhasil'); window.location=('home.php')</script>";
} else {
    // tampilkan error yang detail supaya mudah debug
    echo "Salah. Execute error: " . $tambah->error;
}

		}
   } else {
        echo "Jenis gambar yang anda kirim salah. Harus .jpg .gif .png";
   }
} else {
    echo "Anda belum memilih gambar";
}

?>

		<br/>
		<br/>
		<a href="home.php"><b>Kembali<b></a>
		<br/>
		<br/>

		
	</body>
</html>