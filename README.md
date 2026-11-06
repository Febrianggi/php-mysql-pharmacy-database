# GFF Farma: Pharmacy Database Website

Language: English | [Bahasa Indonesia](README.id.md)

> Note: the website interface, the Excel data and the user guideline are written in Indonesian. This README is available in English and Indonesian.

*Ad Veritatem per Caritatem*

GFF Farma is a PHP and MySQL web application for managing a small pharmacy's medicine inventory, customer accounts and purchase transactions. It was built as the final project of the Database (Basis Data) course in 2025.

## Background

Small pharmacies (UMKM) have the same tax-reporting obligations as large companies, but many keep handwritten books or no records at all, which makes accounting and tax reporting hard later on. GFF Farma records medicine stock and transaction history in a single database instead.

## Features

All users (login or sign up required):
- Sign up with a username, full name, phone number and password. Passwords are hashed with `password_hash`.
- Browse the medicine list, search it, and filter by form (Capsule, Tablet, Injection, Liquid) or category (Flu & Cough, Pain & Fever, Allergy, Heart and others).
- Buy medicine. Stock is validated and the transaction is recorded.

Admin only:
- Add, edit and delete medicines (code, name, price, stock, form, prescription requirement, image, active ingredient, category).
- View, edit and delete transactions. Deleting a transaction returns the stock.
- View, edit and delete users, including changing a user's role.
- Search across medicines, transactions and users.

## Tech stack

| Layer | Technology |
|---|---|
| Backend | PHP (`mysqli`) |
| Database | MySQL / MariaDB |
| Frontend | HTML, CSS, JavaScript, Font Awesome |
| Local server | XAMPP (Apache + MySQL) and phpMyAdmin |

## Database design

Three related tables, normalized to 3NF (the steps from UNF through 1NF and 2NF to 3NF are worked out in the Excel data).

```
users (1) ────< transactions >──── (1) obat
```

| Table | Columns |
|---|---|
| `users` | `username` (PK), `fullname`, `phone`, `password`, `is_admin`, `created_at` |
| `obat` | `kode_obat` (PK), `nama_obat`, `stok`, `harga`, `jenis`, `keterangan`, `gambar`, `kandungan`, `kategori` |
| `transactions` | `id` (PK, auto increment), `username` (FK to `users`), `kode_obat` (FK to `obat`), `tanggal_pembelian`, `jumlah` |

`db_connect.php` creates the tables and a default admin account automatically on first run.

## Project structure

```
.
├── db_connect.php          # DB connection, auto-creates tables and default admin
├── login.php / logout.php / signup.php
├── index.php               # Main page: medicine, transaction and user tables
├── add.php / insert.php    # Add medicine
├── edit.php / update.php   # Edit medicine
├── delete.php              # Delete medicine
├── process_purchase.php    # Record a purchase and reduce stock
├── edit_transaction.php / delete_transaction.php
├── edit_customer.php / delete_customer.php
├── check_medicine_code.php / get_medicine.php   # AJAX helpers
├── style.css
├── database.sql            # Optional schema and sample medicines (manual import)
└── .gitignore
```

## Getting started

1. Install [XAMPP](https://www.apachefriends.org/) and start Apache and MySQL.
2. Copy this folder to `C:\xampp\htdocs\apotek` (or `/opt/lampp/htdocs/apotek`).
3. Open phpMyAdmin (`http://localhost/phpmyadmin`) and create an empty database named `gff_farma`.
4. If your MySQL credentials differ from the default (`root`, no password), edit `db_connect.php`.
5. Open `http://localhost/apotek/login.php`. The tables are created automatically.
6. Log in with the default admin account defined in `db_connect.php`, or sign up as a customer.

To load 12 sample medicines instead of adding them by hand, import `database.sql` from the Import tab in phpMyAdmin.

## Technical notes

- Folder layout. All `.php` files and `style.css` must stay in the same folder. Pages include each other by relative path (for example `include 'db_connect.php'`), so do not move them into subfolders.
- Images. Medicine images are not uploaded to the server. In the add and edit forms the admin pastes an image URL (`URL Gambar`), which is stored in the `gambar` column. If the field is empty or the link is broken, a grey placeholder is shown. Some sample image links come from third-party sites and may expire, so replace them with your own URLs if needed. No image folder is required. The founder cards use a built-in placeholder; to use your own photo, put the file next to `index.php` and set `src="yourphoto.jpg"`.
- Internet connection. Font Awesome icons load from a CDN and the medicine images are external URLs, so icons and pictures do not appear offline.
- Sample data. The database starts empty except for the default admin. Import `database.sql` for 12 sample medicines (with image URLs) or add them from the admin page. Customers are created through sign up.
- Automatic setup. `db_connect.php` runs `CREATE TABLE IF NOT EXISTS` on every request, so importing a `.sql` file is never required. The database `gff_farma` itself must exist first.
- Requirements. PHP with `mysqli` (mysqlnd) and `mbstring`, plus MySQL or MariaDB. XAMPP includes all of these. Tested on PHP 8.3 with MariaDB 10.11.
- Sessions and roles. Login stores the user row in `$_SESSION['user']`, and admin-only pages check `is_admin`. `process_purchase.php` reduces stock when a customer buys, and deleting a transaction restores it.
- Troubleshooting. `Koneksi gagal` means MySQL is not running or the credentials in `db_connect.php` are wrong. A blank page means PHP errors are hidden, so enable error display or check Apache's `error.log`. "Unknown database" means `gff_farma` has not been created yet.

## Security

Implemented:
- All SQL uses prepared statements, and output is HTML-escaped, which protects against SQL injection and XSS.
- Passwords are hashed with `password_hash`. The session ID is regenerated on login and sign up, and the hash is never kept in the session.
- Every form, the purchase request and the delete links are protected by a CSRF token.
- Input is validated on the server (numbers, allowed values, http(s) image URLs, username, phone and password rules). Login shows one generic error, so usernames cannot be guessed.
- Purchases and edits that change stock run in database transactions with row locks, so stock cannot go negative or be sold twice.
- Admin-only pages and AJAX helpers check the role on the server. The main admin account and your own account cannot be deleted.

Still up to you if you deploy publicly:
- Change the default admin password in `db_connect.php` and set a MySQL password instead of using `root` with an empty one.
- Serve the site over HTTPS and add login rate limiting.
- The payment number in `index.php` and the footer contact details are placeholders.
