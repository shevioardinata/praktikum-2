<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body{
            font-family: Arial, sans-serif;
            padding:20px;
            background:#eef2f3;
        }
        .card{
            background:#fff;
            padding:15px;
            border-radius:8px;
            margin-bottom:20px;
            box-shadow:0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body>

<h2>Dashboard</h2>

<!-- CARD GRAFIK -->
<div class="card">
    <h3>Grafik Suhu</h3>
    <canvas id="grafikSuhu"></canvas>
</div>

<script>
// ======== AMBIL DATA DARI data.php DAN TAMPILKAN GRAFIK ========

async function loadGrafik() {
    try {
        const res = await fetch("data.php?limit=50");
        const data = await res.json();

        console.log("DATA:", data); // untuk debugging

        const labels = data.map(item => item.waktu);
        const suhu = data.map(item => item.suhu);

        const ctx = document.getElementById("grafikSuhu").getContext("2d");

        new Chart(ctx, {
            type: "line",
            data: {
                labels: labels,
                datasets: [{
                    label: "Suhu (°C)",
                    data: suhu,
                    borderColor: "red",
                    backgroundColor: "rgba(255, 0, 0, 0.2)",
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: false }
                }
            }
        });

    } catch (e) {
        console.error("Gagal memuat grafik:", e);
    }
}

loadGrafik();
</script>

</body>
</html>