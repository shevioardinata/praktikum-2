<?php
// include database connection file
include_once("config.php");

// Check if form is submitted for user update, then redirect to homepage after update
if(isset($_POST['update']))
{   
    $id = $_POST['id'];
   $nama_alat= $_POST['nama_alat'];
        $mrek = $_POST['merk'];
        $type = $_POST['type'];
        $no_seri = $_POST['no_seri'];
        $tahun = $_POST['tahun'];
        $pt_distributor = $_POST['pt_distributor'];
        $lokasi = $_POST['lokasi'];

    // update user data
    $result = mysqli_query($mysqli, "UPDATE alat SET nama_alat='$nama_alat',tahun='$tahun',merek='$merek',lokasi='$lokasi' WHERE id=$id");

    // Redirect to homepage to display updated user in list
    header("Location: index.php");
}
?>
<?php
// Display selected user data based on id
// Getting id from url
$id = $_GET['id'];

// Fetech user data based on id
$result = mysqli_query($mysqli, "SELECT * FROM alat WHERE id=$id");

while($user_data = mysqli_fetch_array($result))
{
    $nama_alat = $user_data['nama_alat'];
    $mrek = $user_data['mrek'];
    $type = $user_data['type'];
    $no_seri = $user_data['no_seri'];
    $tahun = $user_data['tahun'];
    $pt_distributor = $user_data['pt_distributor'];
    $lokasi = $user_data['lokasi'];
}
?>
<html>
<head>  
    <title>Edit User Data</title>
</head>

<body>
    <a href="index.php">Home</a>
    <br/><br/>

    <form name="update_user" method="post" action="edit.php">
        <table border="0">
            <tr> 
                <td>Nama Alat</td>
                <td><input type="text" name="nama_alat" value=<?php echo $nama_alat;?>></td>
            </tr>
            <tr> 
                <td>Merk</td>
                <td><input type="text" name="merk" value=<?php echo $merk;?>></td>
            </tr>
            <tr> 
                <td>Type</td>
                <td><input type="text" name="type" value=<?php echo $type;?>></td>
            </tr>
             <tr> 
                <td>No_seri</td>
                <td><input type="text" name="no_seri" value=<?php echo $no_seri;?>></td>
            </tr>
            <tr>
            <td>Tahun</td>
                <td><input type="text" name="tahun" value=<?php echo $tahun;?>></td>
            </tr>
            <tr> 
                <td>Pt_distributor</td>
                <td><input type="text" name="pt_distributor" value=<?php echo $pt_distributor;?>></td>
            </tr>
             <tr> 
                <td>Lokasi</td>
                <td><input type="text" name="lokasi" value=<?php echo $lokasi;?>></td>
            </tr>
            <tr>
                <td><input type="hidden" name="id" value=<?php echo $_GET['id'];?>></td>
                <td><input type="submit" name="update" value="Update"></td>
            </tr>
        </table>
    </form>
</body>
</html>