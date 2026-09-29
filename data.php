<?php
header('Content-Type: application/json; charset=utf-8');
include "koneksi.php";

// Ambil berapa baris terakhir (default 50)
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;

// Query ambil data terbaru (ascending berdasarkan waktu)
$sql = "SELECT id, waktu, suhu, hum, bpm, pot_raw, pot_pct
        FROM t_sensor
        ORDER BY waktu DESC
        LIMIT ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $limit);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    // format waktu jika perlu
    $data[] = [
        'id'     => (int)$row['id'],
        'waktu'  => $row['waktu'],
        'suhu'   => (float)$row['suhu'],
        'hum'    => (float)$row['hum'],
        'bpm'    => (int)$row['bpm'],
        'pot_raw'=> (int)$row['pot_raw'],
        'pot_pct'=> (float)$row['pot_pct'],
    ];
}

// kembalikan dalam urutan terendah->tertinggi waktu (chronological)
$data = array_reverse($data);

echo json_encode($data);
?>