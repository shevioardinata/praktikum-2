<style type="text/css">
    .header {
        background-color: orange;
    }
</style>

<?php
// Menyertakan file config.php untuk mendapatkan akses ke konstanta database
include_once("config.php");

// Koneksi ke database menggunakan mysqli_connect()
$connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

// Mengecek apakah koneksi berhasil
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Query untuk mengambil data dari tabel alat, dengan urutan descending berdasarkan id
$query = "SELECT * FROM alat ORDER BY id DESC";
$result = mysqli_query($connection, $query);

// Mengecek apakah query berhasil
if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}
?>

<html>
<head>    
    <title>Sim Rs</title>
</head>

<body>
    <b>Data Alat Elektromedis</b><br>
    <a href="add.php">Tambah Alat</a><br/><br/>

    <table width='80%' border=1>

    <tr class="header">
         <th>No</th><th>Nama Alat</th><th>Tahun</th><th>Merek</th><th>Lokasi</th><th>Aksi</th>
    </tr>
    <?php  
    $i = 1;
    while($user_data = mysqli_fetch_array($result)) {         
        echo "<tr>";
        echo "<td>".$i."</td>";
        echo "<td>".$user_data['nama_alat']."</td>";
        echo "<td>".$user_data['tahun']."</td>";
        echo "<td>".$user_data['merk']."</td>";    
        echo "<td>".$user_data['lokasi']."</td>"; 
        echo "<td><a href='edit.php?id=$user_data[id]'>Edit</a> | <a href='delete.php?id=$user_data[id]'>Delete</a></td>"; 
        echo "</tr>";
        $i++;       
    }
    ?>
    </table>
</body>
</html>
