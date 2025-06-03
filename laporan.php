<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

$filter = $_GET['filter'] ?? 'harian';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Penjualan</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 p-6">

  <!-- Container laporan -->
  <div id="laporanContainer" class="space-y-6">

    <!-- Header QuickPay -->
    <div class="flex justify-center items-center space-x-3 bg-[#DDEB9D] px-6 py-4 rounded shadow mx-auto w-max">
      <svg class="w-9 h-9 text-green-700" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="50" cy="50" r="40" stroke="currentColor" stroke-width="10" fill="none"/>
        <path d="M65 65 L85 85" stroke="currentColor" stroke-width="10" stroke-linecap="round"/>
        <path d="M40 50 h20 a10 10 0 0 0 0 -20 h-20 a10 10 0 0 0 0 20 z" fill="currentColor"/>
      </svg>
      <h1 class="text-green-700 text-2xl font-bold">QuickPay</h1>
    </div>

    <!-- Filter -->
    <div class="flex items-center gap-4">
      <div>
        <label class="block text-sm font-semibold">Filter</label>
        <select id="filter" class="mt-1 p-2 border border-gray-300 rounded" onchange="ubahFilter()">
          <option value="harian" <?= $filter == 'harian' ? 'selected' : '' ?>>Harian</option>
          <option value="mingguan" <?= $filter == 'mingguan' ? 'selected' : '' ?>>Mingguan</option>
          <option value="bulanan" <?= $filter == 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
          <option value="tahunan" <?= $filter == 'tahunan' ? 'selected' : '' ?>>Tahunan</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-semibold">Tanggal</label>
        <input type="date" id="tanggal" class="mt-1 p-2 border border-gray-300 rounded" value="<?= $tanggal ?>" onchange="ubahFilter()" />
      </div>
    </div>

    <!-- Tabel -->
    <div class="bg-white shadow-md rounded-lg p-4">
      <h2 id="judulTabel" class="text-xl font-semibold mb-4">Laporan Penjualan Harian</h2>
      <table class="table-auto w-full text-sm text-gray-600">
        <thead>
          <tr>
            <th class="border-b py-2 px-4">Tanggal</th>
            <th class="border-b py-2 px-4">Produk</th>
            <th class="border-b py-2 px-4">Jumlah</th>
            <th class="border-b py-2 px-4">Total Penjualan</th>
            <th class="border-b py-2 px-4">Keuntungan</th>
          </tr>
        </thead>
        <tbody id="laporanTabel"></tbody>
      </table>
    </div>

    <!-- Grafik -->
    <div class="bg-white shadow-md rounded-lg p-4">
      <h2 id="judulGrafik" class="text-xl font-semibold mb-4">Grafik Keuntungan Harian</h2>
      <canvas id="grafikKeuntungan"></canvas>
    </div>

  </div>

  <!-- Tombol Download PDF -->
  <div class="flex justify-end items-center gap-2 mt-6">
  <a href="dashboard.php" class="text-sm bg-gray-300 text-gray-800 px-3 py-1 rounded hover:bg-gray-400 transition">
    ⬅ Kembali
  </a>
  <button onclick="window.print()" class="text-sm bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 transition">
    🖨 Download PDF
  </button>
</div>
  <!-- jsPDF & html2canvas -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

  <script>
    let chart;

    function ubahFilter() {
      const filter = document.getElementById('filter').value;
      const tanggal = document.getElementById('tanggal').value;
      const url = new URL(window.location.href);
      url.searchParams.set('filter', filter);
      url.searchParams.set('tanggal', tanggal);
      history.replaceState(null, '', url);
      ambilData();
    }

    function formatRupiah(angka) {
      return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function perbaruiJudul(filter) {
      const kapital = filter.charAt(0).toUpperCase() + filter.slice(1);
      document.getElementById('judulTabel').textContent = 'Laporan Penjualan ' + kapital;
      document.getElementById('judulGrafik').textContent = 'Grafik Keuntungan ' + kapital;
    }

    function tampilkanTabel(data) {
      const tabel = document.getElementById('laporanTabel');
      tabel.innerHTML = '';
      if (data.length === 0) {
        tabel.innerHTML = `<tr><td colspan="5" class="text-center py-2">Tidak ada data</td></tr>`;
        return;
      }
      data.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td class="border-b py-2 px-4">${item.tanggal}</td>
          <td class="border-b py-2 px-4">${item.produk}</td>
          <td class="border-b py-2 px-4">${item.jumlah}</td>
          <td class="border-b py-2 px-4">Rp${formatRupiah(item.total)}</td>
          <td class="border-b py-2 px-4">Rp${formatRupiah(item.keuntungan)}</td>
        `;
        tabel.appendChild(row);
      });
    }

    function tampilkanGrafik(data) {
      const ctx = document.getElementById('grafikKeuntungan').getContext('2d');
      const labels = data.map(item => item.label);
      const keuntungan = data.map(item => item.keuntungan);

      if (chart) chart.destroy();

      chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: 'Keuntungan',
            data: keuntungan,
            borderColor: 'rgba(75,192,192,1)',
            backgroundColor: 'rgba(75,192,192,0.2)',
            fill: true,
            tension: 0.3
          }]
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: value => 'Rp' + formatRupiah(value)
              }
            }
          }
        }
      });
    }

    function ambilData() {
      const filter = document.getElementById('filter').value;
      const tanggal = document.getElementById('tanggal').value;
      fetch(`ambil_data.php?filter=${filter}&tanggal=${tanggal}`)
        .then(res => res.json())
        .then(data => {
          tampilkanTabel(data.data);
          tampilkanGrafik(data.grafik);
          perbaruiJudul(filter);
        });
    }

    async function downloadPDF() {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF('p', 'pt', 'a4');
      const laporan = document.getElementById('laporanContainer');

      const canvas = await html2canvas(laporan, { scale: 2 });
      const imgData = canvas.toDataURL('image/png');
      const imgProps = doc.getImageProperties(imgData);
      const pdfWidth = doc.internal.pageSize.getWidth();
      const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

      doc.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
      doc.save('laporan-quickpay.pdf');
    }

    ambilData();
  </script>
</body>
</html>
