<?php
session_start();
include "db_connect.php";

// Bukan admin
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$isAdmin = $_SESSION['user']['is_admin'];

// Filter pencarian
$search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
$filter = isset($_GET['filter']) ? (string)$_GET['filter'] : 'all';

// Menunjukkan tabel obat (hanya nilai yang dikenal yang diterima)
$table = isset($_GET['table']) ? (string)$_GET['table'] : 'medicine';
if (!in_array($table, ['medicine', 'transaction', 'customer'], true)) {
    $table = 'medicine';
}

// Pelanggan hanya melihat tabel obat
if (!$isAdmin) {
    $table = 'medicine';
}

$like = '%' . $search . '%';
$types = '';
$params = [];

// Filter
if ($table == 'transaction') {
    $query = "SELECT t.id, t.username, u.fullname, t.kode_obat, o.nama_obat, 
              DATE(t.tanggal_pembelian) as tanggal, t.jumlah, o.harga,
              (t.jumlah * o.harga) as total
              FROM transactions t
              JOIN users u ON t.username = u.username
              JOIN obat o ON t.kode_obat = o.kode_obat
              WHERE 1=1";
    
    if ($search !== '') {
        $query .= " AND (t.username LIKE ? 
                    OR u.fullname LIKE ? 
                    OR t.kode_obat LIKE ? 
                    OR o.nama_obat LIKE ?)";
        $types .= 'ssss';
        array_push($params, $like, $like, $like, $like);
    }
    
    $query .= " ORDER BY t.id ASC";
} elseif ($table == 'customer') {
    $query = "SELECT username, fullname, phone, is_admin FROM users WHERE 1=1";
    
    if ($search !== '') {
        $query .= " AND (username LIKE ? 
                    OR fullname LIKE ? 
                    OR phone LIKE ?)";
        $types .= 'sss';
        array_push($params, $like, $like, $like);
    }
    
    $query .= " ORDER BY username";
} else {
    // Tabel dasar
    $query = "SELECT * FROM obat WHERE 1=1";
    
    if ($search !== '') {
        $query .= " AND (nama_obat LIKE ? OR kode_obat LIKE ? OR kandungan LIKE ?)";
        $types .= 'sss';
        array_push($params, $like, $like, $like);
    }
    
    // Filter jenis atau kategori
    if ($filter != 'all') {
        if (strpos($filter, '_') !== false) {
            list($jenis, $kategori) = explode('_', $filter, 2);
            $query .= " AND jenis = ? AND kategori = ?";
            $types .= 'ss';
            array_push($params, $jenis, $kategori);
        } else {
            // Cek filter
            $kategoriList = ['Flu & Batuk', 'Nyeri & Demam', 'Alergi', 'Jantung'];
            if (in_array($filter, $kategoriList, true)) {
                $query .= " AND kategori = ?"; // Filter kategori
            } else {
                $query .= " AND jenis = ?"; // Filter jenis
            }
            $types .= 's';
            $params[] = $filter;
        }
    }
    
    $query .= " ORDER BY kode_obat ASC";
}

$rows = db_all($kon, $query, $types, $params);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>GFF Farma - Database Obat</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>

<body>
  <header>
    <div class="header-left">
      <button class="sidebar-toggle" id="sidebarToggle">
        <div class="hamburger">
          <div class="line"></div>
          <div class="line"></div>
          <div class="line"></div>
        </div>
      </button>
      <a href="#heroSection" class="logo-link" style="display: flex; align-items: center; text-decoration: none; color: white;">
        <img src="https://web.mojokertokota.go.id/images/fasilitas/2ZN6llLIQDUKVNuMcgCm8f5AErD1MzhQiT11Usfk.png" alt="Logo Apotek" />
        <h1 style="margin-left: 20px;">GFF Farma</h1>
      </a>
    </div>
    <nav>
      <a href="#heroSection" class="nav-link">Home</a>
      <a href="#foundersSection" class="nav-link">Tentang</a>
      <a href="#medicineTableSection" class="nav-link">Tabel</a>
      <a href="#footer" class="nav-link">Kontak</a>
      <a href="logout.php" class="nav-link" id="logoutLink"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
  </header>

  <div class="main-content">
    <div class="sidebar" id="sidebar">
      <div class="sidebar-header">
        <span>Menu</span>
        <button class="theme-toggle" id="themeToggle" title="Toggle Dark Mode">
          <i class="fas fa-moon"></i>
        </button>
      </div>
      <div class="sidebar-content">
        <h3>Database Tables</h3>
        <ul>
          <li><a href="index.php?table=medicine" class="<?= $table == 'medicine' ? 'active' : '' ?>"><i class="fas fa-pills"></i> Daftar Obat</a></li>
          <?php if ($isAdmin): ?>
            <li><a href="index.php?table=transaction" class="<?= $table == 'transaction' ? 'active' : '' ?>"><i class="fas fa-receipt"></i> Transaksi</a></li>
            <li><a href="index.php?table=customer" class="<?= $table == 'customer' ? 'active' : '' ?>"><i class="fas fa-users"></i> User</a></li>
          <?php endif; ?>
        </ul>
        
        <h3>Navigasi Obat</h3>
        <ul>
          <li><a href="index.php?table=medicine&filter=all" class="filter-link <?= $filter == 'all' ? 'active' : '' ?>"><i class="fas fa-tablets"></i> Semua Obat</a></li>
          <li><a href="index.php?table=medicine&filter=kapsul" class="filter-link <?= $filter == 'kapsul' ? 'active' : '' ?>"><i class="fas fa-capsules"></i> Kapsul</a></li>
          <li><a href="index.php?table=medicine&filter=tablet" class="filter-link <?= $filter == 'tablet' ? 'active' : '' ?>"><i class="fas fa-pills"></i> Tablet</a></li>
          <li><a href="index.php?table=medicine&filter=suntik" class="filter-link <?= $filter == 'suntik' ? 'active' : '' ?>"><i class="fas fa-syringe"></i> Suntik</a></li>
          <li><a href="index.php?table=medicine&filter=cair" class="filter-link <?= $filter == 'cair' ? 'active' : '' ?>"><i class="fas fa-thermometer"></i> Obat Cair</a></li>
        </ul>
        
        <h3>Kategori</h3>
        <ul>
          <li><a href="index.php?table=medicine&filter=Flu%20%26%20Batuk" class="filter-link <?= $filter == 'Flu & Batuk' ? 'active' : '' ?>"><i class="fas fa-head-side-cough"></i> Flu & Batuk</a></li>
          <li><a href="index.php?table=medicine&filter=Nyeri%20%26%20Demam" class="filter-link <?= $filter == 'Nyeri & Demam' ? 'active' : '' ?>"><i class="fas fa-tooth"></i> Nyeri & Demam</a></li>
          <li><a href="index.php?table=medicine&filter=Alergi" class="filter-link <?= $filter == 'Alergi' ? 'active' : '' ?>"><i class="fas fa-allergies"></i> Alergi</a></li>
          <li><a href="index.php?table=medicine&filter=Jantung" class="filter-link <?= $filter == 'Jantung' ? 'active' : '' ?>"><i class="fas fa-heart"></i> Jantung</a></li>
        </ul>
      </div>
    </div>

    <div class="container" id="mainContainer">
      <!-- Hero Section -->
      <section class="hero" id="heroSection">
        <div class="hero-content">
          <h1>Hello, <?= htmlspecialchars($_SESSION['user']['fullname']) ?></h1>
          <h2>Database GFF Farma<br>est. 2025</h2>
          <p>ad veritatem per caritatem</p>
          <a href="#" class="discover-btn">Discover More</a>
        </div>
      </section>

      <!-- Founders Section -->
      <section class="founders-section" id="foundersSection">
        <h2>Our Founders</h2>
        <div class="founders-grid">
          <div class="founder-card">
              <img src="img/founder-febri.jpg" alt="Febrianggi Caesar Immanuel" loading="lazy" class="founder-image">
              <h3 class="founder-name">Febrianggi Caesar Immanuel</h3>
              <p>Your quote here.</p>
          </div>
          <div class="founder-card">
              <img src="img/founder-fadhli.jpg" alt="Muhamad Fadhli Alfarizi" loading="lazy" class="founder-image">
              <h3 class="founder-name">Muhamad Fadhli Alfarizi</h3>
              <p>Your quote here.</p>
          </div>
          <div class="founder-card">
              <img src="img/founder-grego.jpg" alt="Gregorius Adiyatma Pradana" loading="lazy" class="founder-image">
              <h3 class="founder-name">Gregorius Adiyatma Pradana</h3>
              <p>Your quote here.</p>
          </div>
        </div>
      </section>

      <!-- Search dan Tabel -->
      <div class="content-wrapper" id="medicineTableSection">
        <div class="search-container">
          <form method="GET" action="index.php" class="search-form">
            <input type="hidden" name="table" value="<?= h($table) ?>">
            <input type="text" name="search" id="searchInput" placeholder="Cari data..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" id="searchButton"><i class="fas fa-search"></i> Cari</button>
          </form>
          <?php if (!$isAdmin): ?>
            <button id="openPurchaseModal" class="btn-add">
              <i class="fas fa-shopping-cart"></i> Beli Obat
            </button>
          <?php endif; ?>
          <?php if ($isAdmin && $table == 'medicine'): ?>
            <a href="add.php" class="btn-add" title="Tambah Obat"><i class="fas fa-plus"></i> Tambah Obat</a>
          <?php endif; ?>
        </div>
        
        <?php if ($table == 'medicine'): ?>
          <table id="medicineTable">
            <thead>
              <tr>
                <th>Kode Obat</th>
                <th>Nama Obat</th>
                <th>Stock</th>
                <th>Jenis</th>
                <th>Keterangan</th>
                <th>Gambar</th>
                <th>Kandungan</th>
                <th>Kategori</th>
                <th>Harga</th>
                <?php if ($isAdmin): ?>
                  <th>Aksi</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
              <tr data-type="<?= htmlspecialchars($row['jenis']) ?>" data-category="<?= htmlspecialchars($row['kategori']) ?>">
                <td><?= htmlspecialchars($row['kode_obat']) ?></td>
                <td><?= htmlspecialchars($row['nama_obat']) ?></td>
                <td><?= htmlspecialchars($row['stok']) ?></td>
                <td><?= htmlspecialchars($row['jenis']) ?></td>
                <td><?= htmlspecialchars($row['keterangan']) ?></td>
                <td>
                  <div class="medicine-image-container">
                    <img src="<?= h($row['gambar'] ?: PLACEHOLDER_IMG) ?>" alt="<?= h($row['nama_obat']) ?>" class="medicine-image" loading="lazy" onerror="this.onerror=null;this.src=<?= h(json_encode(PLACEHOLDER_IMG)) ?>;">
                    <div class="medicine-image-overlay"><?= htmlspecialchars($row['nama_obat']) ?></div>
                  </div>
                </td>
                <td><?= htmlspecialchars($row['kandungan']) ?></td>
                <td><?= htmlspecialchars($row['kategori']) ?></td>
                <td><?= isset($row['harga']) ? 'Rp ' . number_format($row['harga'], 0, ',', '.') : '-' ?></td>
                <?php if ($isAdmin): ?>
                  <td>
                    <a href='edit.php?id=<?= h(urlencode($row['kode_obat'])) ?>' class='btn-edit'>Edit</a>
                    <a href='delete.php?id=<?= h(urlencode($row['kode_obat'])) ?>&token=<?= h(csrf_token()) ?>' class='btn-delete' onclick='return confirm("Yakin ingin menghapus?")'>Hapus</a>
                  </td>
                <?php endif; ?>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php elseif ($table == 'transaction'): ?>
          <table id="transactionTable">
              <thead>
                  <tr>
                      <th>ID</th>
                      <th>Username</th>
                      <th>Nama Pelanggan</th>
                      <th>Kode Obat</th>
                      <th>Nama Obat</th>
                      <th>Tanggal</th>
                      <th>Jumlah</th>
                      <th>Harga Satuan</th>
                      <th>Total</th>
                      <?php if ($isAdmin): ?>
                          <th>Aksi</th>
                      <?php endif; ?>
                  </tr>
              </thead>
              <tbody>
                  <?php foreach ($rows as $row): ?>
                  <tr>
                      <td><?= htmlspecialchars($row['id']) ?></td>
                      <td><?= htmlspecialchars($row['username']) ?></td>
                      <td><?= htmlspecialchars($row['fullname']) ?></td>
                      <td><?= htmlspecialchars($row['kode_obat']) ?></td>
                      <td><?= htmlspecialchars($row['nama_obat']) ?></td>
                      <td><?= htmlspecialchars($row['tanggal']) ?></td>
                      <td><?= htmlspecialchars($row['jumlah']) ?></td>
                      <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                      <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                      <?php if ($isAdmin): ?>
                          <td>
                              <a href='edit_transaction.php?id=<?= (int)$row['id'] ?>' class='btn-edit'>Edit</a>
                              <a href='delete_transaction.php?id=<?= (int)$row['id'] ?>&token=<?= h(csrf_token()) ?>' class='btn-delete' onclick='return confirm("Yakin ingin menghapus?")'>Hapus</a>
                          </td>
                      <?php endif; ?>
                  </tr>
                  <?php endforeach; ?>
              </tbody>
          </table>
        <?php elseif ($table == 'customer'): ?>
          <table id="customerTable">
            <thead>
              <tr>
                <th>Username</th>
                <th>Nama Lengkap</th>
                <th>Telepon</th>
                <th>Peran</th>
                <?php if ($isAdmin): ?>
                  <th>Aksi</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['fullname']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td><?= $row['is_admin'] ? 'Admin' : 'Pelanggan' ?></td>
                <?php if ($isAdmin): ?>
                  <td>
                    <a href="edit_customer.php?id=<?= h(urlencode($row['username'])) ?>" class="btn-edit">Edit</a>
                    <?php if ($row['username'] != 'admingff'): ?>
                      <a href="delete_customer.php?id=<?= h(urlencode($row['username'])) ?>&token=<?= h(csrf_token()) ?>" class="btn-delete" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                    <?php endif; ?>
                  </td>
                <?php endif; ?>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Modal Pembelian Obat -->
  <div id="purchaseModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Form Pembelian Obat</h3>
        <span class="close-modal">&times;</span>
      </div>
      <div class="modal-body">
        <form id="purchaseForm">
          <div class="form-group">
            <label for="purchase_kode_obat">Kode Obat</label>
            <input type="text" id="purchase_kode_obat" name="kode_obat" required>
          </div>
          <div class="form-group">
            <label for="purchase_nama_obat">Nama Obat</label>
            <input type="text" id="purchase_nama_obat" name="nama_obat" readonly>
          </div>
          <div class="form-group">
            <label for="purchase_harga">Harga Satuan</label>
            <input type="text" id="purchase_harga" name="harga" readonly>
          </div>
          <div class="form-group">
            <label for="purchase_jumlah">Jumlah</label>
            <input type="number" id="purchase_jumlah" name="jumlah" min="1" required>
          </div>
          <div class="form-group">
            <label for="purchase_total">Total Harga</label>
            <input type="text" id="purchase_total" name="total" readonly>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-cancel">Batal</button>
            <button type="submit" class="btn-submit">Proses Pembelian</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Konfirmasi Pembayaran -->
  <div id="paymentModal" class="modal confirmation-modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Instruksi Pembayaran</h3>
      </div>
      <div class="modal-body">
        <p id="paymentInstruction">Silahkan lakukan pembayaran ke Nomor: 081234567890 sebesar Rp 0. Apabila sudah melakukan pembayaran, silahkan tunggu konfirmasi dari kami ya^^</p>
      </div>
      <div class="confirmation-buttons">
        <button type="button" class="btn-submit" id="confirmPayment">Saya Sudah Bayar</button>
      </div>
    </div>
  </div>

  <!-- Modal Konfirmasi Pembelian Lagi -->
  <div id="repurchaseModal" class="modal confirmation-modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Pembelian Berhasil</h3>
      </div>
      <div class="modal-body">
        <p>Pesanan Anda sedang diproses. Apakah ingin membeli lagi?</p>
      </div>
      <div class="confirmation-buttons">
        <button type="button" class="btn-submit" id="yesRepurchase">Ya</button>
        <button type="button" class="btn-cancel" id="noRepurchase">Tidak</button>
      </div>
    </div>
  </div>

  <footer id="footer">
    <div class="footer-section">
      <h3>Kontak Kami</h3>
      <div class="contact-info">
        <i class="fas fa-map-marker-alt"></i>
        <span>Jalanin aja dulu</span>
      </div>
      <div class="contact-info">
        <i class="fas fa-phone"></i>
        <span>(081) 234567890</span>
      </div>
      <div class="contact-info">
        <i class="fas fa-envelope"></i>
        <span>info@gfffarma.com</span>
      </div>
    </div>
    
    <div class="footer-section">
      <h3>Jam Operasional</h3>
      <p>Senin - Jumat: 08.00 - 21.00</p>
      <p>Sabtu: 08.00 - 18.00</p>
      <p>Minggu: 09.00 - 15.00</p>
    </div>
    
    <div class="footer-section">
      <h3>Media Sosial</h3>
      <div class="social-links">
        <a href="#"><i class="fab fa-facebook"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-whatsapp"></i></a>
      </div>
      <p>Ikuti kami untuk info terbaru dan promo</p>
    </div>
  </footer>

  <script>
    // Toggle Sidebar
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainContainer = document.getElementById('mainContainer');
    const footer = document.getElementById('footer');
    
    function toggleSidebar() {
      sidebar.classList.toggle('collapsed');
      mainContainer.classList.toggle('expanded');
      footer.classList.toggle('expanded');
      
      const isCollapsed = sidebar.classList.contains('collapsed');
      localStorage.setItem('sidebarCollapsed', isCollapsed);
    }
    
    sidebarToggle.addEventListener('click', toggleSidebar);
    
    document.addEventListener('DOMContentLoaded', () => {
      const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
      if (isCollapsed) {
        sidebar.classList.add('collapsed');
        mainContainer.classList.add('expanded');
        footer.classList.add('expanded');
      }
    });
    
    // Background
    const heroSection = document.getElementById('heroSection');
    heroSection.style.backgroundImage = "url('https://ihpl.llu.edu/sites/ihpl.llu.edu/files/field/image/GettyImages-1217730390.jpg')";
    heroSection.style.backgroundSize = "cover";
    heroSection.style.backgroundPosition = "center";
    heroSection.style.backgroundAttachment = "fixed";
    
    // Dark/Light Mode Toggle
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;
    
    themeToggle.addEventListener('click', () => {
      body.classList.toggle('dark-mode');
      const icon = themeToggle.querySelector('i');
      if (body.classList.contains('dark-mode')) {
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
        localStorage.setItem('darkMode', 'enabled');
      } else {
        icon.classList.remove('fa-sun');
        icon.classList.add('fa-moon');
        localStorage.setItem('darkMode', 'disabled');
      }
    });
    
    document.addEventListener('DOMContentLoaded', () => {
      if (localStorage.getItem('darkMode') === 'enabled') {
        body.classList.add('dark-mode');
        const icon = themeToggle.querySelector('i');
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
      }
    });
    
    // Scroll konten
    const discoverBtn = document.querySelector('.discover-btn');
    discoverBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const foundersSection = document.querySelector('.founders-section');
      foundersSection.scrollIntoView({ behavior: 'smooth' });
    });
    
    // Pergeseran sidebar
    function checkScreenSize() {
      if (window.innerWidth <= 768) {
        sidebar.classList.add('collapsed');
        mainContainer.classList.add('expanded');
        footer.classList.add('expanded');
      } else {
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (!isCollapsed) {
          sidebar.classList.remove('collapsed');
          mainContainer.classList.remove('expanded');
          footer.classList.remove('expanded');
        }
      }
    }
    
    // Smooth scroll untuk navigasi header
    document.querySelectorAll('.nav-link').forEach(link => {
      if (!link.id.includes('logoutLink')) { // Skip logout link
        link.addEventListener('click', function(e) {
          e.preventDefault();
          const targetId = this.getAttribute('href');
          const targetElement = document.querySelector(targetId);
          
          if (targetElement) {
            // Hitung offset dengan memperhitungkan tinggi header
            const headerHeight = document.querySelector('header').offsetHeight;
            const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight;
            
            window.scrollTo({
              top: targetPosition,
              behavior: 'smooth'
            });
            
            // Jika sidebar terbuka di mobile, tutup setelah memilih menu
            if (window.innerWidth <= 768 && sidebar.classList.contains('open')) {
              sidebar.classList.remove('open');
              mainContainer.classList.remove('shifted');
            }
          }
        });
      }
    });
    
    // Highlight nav link saat scroll
    const sections = document.querySelectorAll('section, footer, .content-wrapper');
    const navLinks = document.querySelectorAll('.nav-link');
    
    window.addEventListener('scroll', () => {
      let current = '';
      
      sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.clientHeight;
        
        if (pageYOffset >= (sectionTop - 100)) {
          current = section.getAttribute('id');
        }
      });
      
      navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === `#${current}`) {
          link.classList.add('active');
        }
      });
    });
    
    // Fungsi untuk modal pembelian
    const purchaseModal = document.getElementById('purchaseModal');
    const paymentModal = document.getElementById('paymentModal');
    const repurchaseModal = document.getElementById('repurchaseModal');
    const openPurchaseModalBtn = document.getElementById('openPurchaseModal');
    const closeModalBtns = document.querySelectorAll('.close-modal, .btn-cancel');
    const purchaseForm = document.getElementById('purchaseForm');
    const confirmPaymentBtn = document.getElementById('confirmPayment');
    const yesRepurchaseBtn = document.getElementById('yesRepurchase');
    const noRepurchaseBtn = document.getElementById('noRepurchase');

    // Buka modal pembelian
    if (openPurchaseModalBtn) {
      openPurchaseModalBtn.addEventListener('click', () => {
        purchaseModal.style.display = 'block';
      });
    }

    // Tutup modal
    closeModalBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        purchaseModal.style.display = 'none';
        paymentModal.style.display = 'none';
        repurchaseModal.style.display = 'none';
      });
    });

    // Auto-fill data obat saat kode obat diisi
    document.getElementById('purchase_kode_obat').addEventListener('change', function() {
      const kodeObat = this.value;
      if (kodeObat) {
        fetch(`get_medicine.php?kode_obat=${encodeURIComponent(kodeObat)}`)
          .then(response => response.json())
          .then(data => {
            if (data) {
              document.getElementById('purchase_nama_obat').value = data.nama_obat;
              document.getElementById('purchase_harga').value = 'Rp ' + data.harga.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
              calculateTotal();
            } else {
              alert('Obat tidak ditemukan');
            }
          });
      }
    });

    // Hitung total harga
    document.getElementById('purchase_jumlah').addEventListener('input', calculateTotal);

    function calculateTotal() {
      const harga = document.getElementById('purchase_harga').value.replace(/\D/g, '');
      const jumlah = document.getElementById('purchase_jumlah').value;
      if (harga && jumlah) {
        const total = parseInt(harga) * parseInt(jumlah);
        document.getElementById('purchase_total').value = 'Rp ' + total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
      }
    }

    // Proses form pembelian
    purchaseForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      formData.append('csrf', <?= json_encode(csrf_token()) ?>);
      
      fetch('process_purchase.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          purchaseModal.style.display = 'none';
          document.getElementById('paymentInstruction').textContent = 
            `Silahkan lakukan pembayaran ke Nomor: 081234567890 sebesar ${document.getElementById('purchase_total').value}. Apabila sudah melakukan pembayaran, silahkan tunggu konfirmasi dari kami ya.`;
          paymentModal.style.display = 'block';
        } else {
          alert(data.message || 'Terjadi kesalahan');
        }
      });
    });

    // Konfirmasi pembayaran
    confirmPaymentBtn.addEventListener('click', () => {
      paymentModal.style.display = 'none';
      repurchaseModal.style.display = 'block';
    });

    // Tombol Ya untuk beli lagi
    yesRepurchaseBtn.addEventListener('click', () => {
      repurchaseModal.style.display = 'none';
      purchaseModal.style.display = 'block';
      // Reset form
      purchaseForm.reset();
      document.getElementById('purchase_total').value = '';
    });

    // Tombol Tidak untuk kembali ke home
    noRepurchaseBtn.addEventListener('click', () => {
      repurchaseModal.style.display = 'none';
      
      // Tutup sidebar jika terbuka
      sidebar.classList.add('collapsed');
      mainContainer.classList.add('expanded');
      footer.classList.add('expanded');
      
      // Scroll ke hero section
      const heroSection = document.getElementById('heroSection');
      heroSection.scrollIntoView({ behavior: 'smooth' });
      
      // Simpan state sidebar tertutup di localStorage
      localStorage.setItem('sidebarCollapsed', 'true');
    });

    // Handle logout link
    document.getElementById('logoutLink').addEventListener('click', function(e) {
      e.preventDefault();
      if (confirm('Yakin ingin logout?')) {
        window.location.href = 'logout.php';
      }
    });

    window.addEventListener('resize', checkScreenSize);
    checkScreenSize();
  </script>
</body>
</html>