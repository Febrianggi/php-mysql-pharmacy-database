# GFF Farma — Pharmacy Database Website

**🌐 Language:** 🇬🇧 **English** · [🇮🇩 Bahasa Indonesia](README.id.md)

> 📝 **Note:** the project documents (report, presentation slides and user guideline) and the in-app interface are written in **Indonesian**. This README is provided in English and Indonesian for convenience.

> *Ad Veritatem per Caritatem*

**GFF Farma** is a PHP + MySQL web application for managing a small pharmacy's medicine inventory, customer accounts and purchase transactions. It was built as the final project of the **Database (Basis Data)** course, 2025.

## Background

Small and medium pharmacies (UMKM) have the same tax-reporting obligations as large companies, but often rely on handwritten books or no records at all. That makes accounting and tax reporting difficult later on. GFF Farma gives a pharmacy a systematic way to record medicine stock and transaction history in one database.

## Features

**Everyone (login / sign up required)**
- Sign up with username, full name, phone number and password (passwords are hashed with `password_hash`)
- Browse the medicine list, search it, and filter by form (Capsule, Tablet, Injection, Liquid) or category (Flu & Cough, Pain & Fever, Allergy, Heart, …)
- Buy medicine — stock is validated and the transaction is recorded

**Admin only**
- Add, edit and delete medicines (code, name, price, stock, form, prescription requirement, image, active ingredient, category)
- View, edit and delete transactions (deleting a transaction returns the stock)
- View, edit and delete users, including changing a user's role
- Quick search across medicines, transactions and users

## Tech stack

| Layer | Technology |
|---|---|
| Backend | PHP (`mysqli`) |
| Database | MySQL / MariaDB |
| Frontend | HTML, CSS, JavaScript, Font Awesome |
| Local server | XAMPP (Apache + MySQL) and phpMyAdmin |

## Database design

Three related tables, normalized up to **3NF** (UNF → 1NF → 2NF → 3NF; the full worksheet is in [`docs/`](docs/)).

```
users (1) ────< transactions >──── (1) obat
```

| Table | Columns |
|---|---|
| `users` | `username` (PK), `fullname`, `phone`, `password`, `is_admin`, `created_at` |
| `obat` | `kode_obat` (PK), `nama_obat`, `stok`, `harga`, `jenis`, `keterangan`, `gambar`, `kandungan`, `kategori` |
| `transactions` | `id` (PK, auto increment), `username` (FK → users), `kode_obat` (FK → obat), `tanggal_pembelian`, `jumlah` |

The tables and a default admin account are created automatically by `db_connect.php` on first run.

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
├── database.sql            # Optional schema + sample medicines (manual import)
├── .gitignore
└── docs/                   # Report, presentation, user guide, normalization data
```

## Getting started

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache** and **MySQL**.
2. Copy this folder to `C:\xampp\htdocs\apotek` (or `/opt/lampp/htdocs/apotek`).
3. Open phpMyAdmin (`http://localhost/phpmyadmin`) and create an empty database named **`gff_farma`**.
4. If your MySQL credentials differ from the default (`root`, no password), edit `db_connect.php`.
5. Open `http://localhost/apotek/login.php`. The tables are created automatically.
6. Log in with the default admin account defined in `db_connect.php`, or sign up as a customer.

> Optional: import `database.sql` in phpMyAdmin (*Import* tab) to load 12 sample medicines instead of adding them by hand.

## Technical notes

- **Flat structure.** All `.php` files and `style.css` must stay in the **same folder** — pages include each other by relative path (e.g. `include 'db_connect.php'`), so do not move them into subfolders.
- **Images.** Medicine images are **not uploaded to the server**. In the add/edit form the admin pastes an image **URL** (`URL Gambar`), which is stored in the `gambar` column. If left empty (or if the link is broken), a grey placeholder image is shown. Some sample image links come from third-party sites and may expire over time; replace them with your own URLs if needed. No image folder is needed. The founder cards use a built-in placeholder; to use your own photo, put the file in the same folder as `index.php` and set `src="yourphoto.jpg"`.
- **Internet connection required.** Font Awesome icons are loaded from a CDN and the medicine images are external URLs, so icons and pictures will not appear offline.
- **Sample data.** The database starts empty (except the default admin). Import `database.sql` for 12 sample medicines (with image URLs), or add them through the admin page; customers are created through sign up.
- **Auto-setup.** `db_connect.php` runs `CREATE TABLE IF NOT EXISTS` on every request, so you never need to import a `.sql` file. The database itself (`gff_farma`) must exist first.
- **Requirements.** PHP with `mysqli` (mysqlnd) and `mbstring` (all included in XAMPP) and MySQL / MariaDB. Tested on PHP 8.3 with MariaDB 10.11.
- **Sessions & roles.** Login stores the user row in `$_SESSION['user']`; admin-only pages check `is_admin`. Stock is reduced in `process_purchase.php` when a customer buys, and restored when an admin deletes a transaction.
- **Troubleshooting.** `Koneksi gagal` = MySQL not running or wrong credentials in `db_connect.php`; blank page = enable PHP error display or check Apache `error.log`; "Unknown database" = create `gff_farma` first.

## Security

Implemented:
- All SQL uses **prepared statements** (no SQL injection); output is HTML-escaped (no XSS).
- Passwords are hashed with `password_hash`; the session ID is regenerated on login/sign up; the hash is never kept in the session.
- **CSRF protection** on every form, the purchase request and the delete links.
- Server-side validation of all input (numbers, allowed values, http(s) image URLs, username/phone/password rules) and a single generic login error (no username enumeration).
- Purchases and edits that change stock run inside **database transactions** with row locks, so stock cannot go negative or be sold twice.
- Admin-only pages and AJAX helpers check the role on the server. The main admin account and your own account cannot be deleted.

Still your responsibility if you deploy publicly:
- Change the default admin password in `db_connect.php` and set a MySQL password (do not use `root` with an empty password).
- Serve the site over HTTPS and add login rate limiting.
- The pharmacy payment number in `index.php` and the footer contact details are placeholders.

## Documentation

See the [`docs/`](docs/) folder: project report, presentation, user guideline and the normalization worksheet (UNF → 3NF).

> 🇮🇩 All explanations in the slides, the report and the user guideline are written in **Indonesian (Bahasa Indonesia)**.
