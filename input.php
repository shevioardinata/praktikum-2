<?php
header("Content-Type: application/json");
include "koneksi.php";

$suhu=$_GET['suhu'];
$hum=$_GET['hum'];
$bpm=$_GET['bpm'];
$pot_raw=$_GET['pot_raw'];
$pot_pct=$_GET['pot_pct'];

$stmt=$conn->prepare("INSERT INTO t_sensor (suhu, hum, bpm, pot_raw, pot_pct) VALUES (?,?,?,?,?)");
$stmt->bind_param("ddiii", $suhu,$hum,$bpm,$pot_raw,$pot_pct);
$stmt->execute();

echo json_encode(["status"=>"ok","msg"=>"disimpan"]);
?>