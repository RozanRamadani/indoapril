# 🏢 IndoApril - Sistem Manajemen Inventori

> Sistem manajemen inventori modern dan profesional yang dibangun dengan Laravel 11, Tailwind CSS 4, dan Alpine.js

![Laravel](https://img.shields.io/badge/Laravel-11-red?style=flat-square&logo=laravel)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.1-blue?style=flat-square&logo=tailwindcss)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-green?style=flat-square&logo=alpinedotjs)
![PHP](https://img.shields.io/badge/PHP-8.2+-purple?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange?style=flat-square&logo=mysql)

---

## 📋 Daftar Isi

- [Overview](#overview)
- [Fitur](#fitur)
- [Tech Stack](#tech-stack)
- [Instalasi](#instalasi)
- [Dokumentasi](#dokumentasi)
- [Database Schema](#database-schema)
- [UTS Requirements](#uts-requirements)

---

## 🎯 Overview

**IndoApril** adalah sistem manajemen inventori barang yang dirancang untuk memudahkan pengelolaan stok, transaksi, dan pelaporan bisnis. Sistem ini dibangun dengan fokus pada:

- ✅ **User Experience**: Interface modern dan intuitif
- ✅ **Performance**: Optimasi query dan asset bundling
- ✅ **Responsive Design**: Mobile-first approach
- ✅ **Maintainability**: Component-based architecture

---

## ✨ Fitur

### 📊 Dashboard
- Statistik real-time (Total Barang, Nilai Inventori, Vendor, Transaksi)
- Chart statistik per jenis barang dengan progress bars
- Quick actions untuk akses cepat
- Tabel barang terbaru (5 items terakhir)
- Info sistem dan tips penggunaan

### 📦 Manajemen Barang (CRUD)
- **Create**: Form tambah barang dengan validasi
- **Read**: Tabel dengan filter (Aktif/Semua)
- **Update**: Edit data barang existing
- **Delete**: Soft delete dengan status (Aktif/Nonaktif)

#### Fitur Detail:
- Stats cards untuk overview cepat
- Filter status dengan active state
- Confirmation modal sebelum nonaktifkan
- Flash messages untuk feedback
- Breadcrumb navigation
- Form dengan helper text dan icons
- Responsive table dengan hover effects

### 🏢 Manajemen Vendor (CRUD)
- **Create**: Form tambah vendor dengan validasi
- **Read**: Tabel dengan filter (Aktif/Semua)
- **Update**: Edit data vendor existing
- **Delete**: Soft delete dengan status (Y/N)

#### Fitur Detail:
- Stats cards (Total, Aktif, Badan Hukum, Perorangan)
- Filter status dengan active state
- Badge tipe vendor (Badan Hukum/Perorangan)
- Confirmation modal sebelum nonaktifkan
- Flash messages untuk feedback
- Breadcrumb navigation
- Form dengan emoji icons
- Purple/Pink gradient theme

### 🎨 UI/UX Components
- **Layout Component**: Master template dengan navbar, footer
- **Navbar**: Responsive dengan mobile menu (Alpine.js)
- **Flash Messages**: Dismissible alerts dengan Alpine.js
- **Modals**: Interactive confirmation dengan animations

---

## 🛠️ Tech Stack

### Backend
- **Framework**: Laravel 11.x
- **PHP**: 8.2+
- **Database**: MySQL 8.0.30
- **ORM**: Eloquent

### Frontend
- **CSS Framework**: Tailwind CSS 4.1.14
- **JavaScript**: Alpine.js 3.x
- **Build Tool**: Vite 7.0.7
- **Icons**: Heroicons (SVG)

---

## 📥 Instalasi

### Prerequisites
- PHP >= 8.2
- Composer
- Node.js & npm
- MySQL
- Laragon (atau XAMPP/WAMP)

### Step-by-Step

1. **Clone Repository**
```bash
git clone https://github.com/yourusername/indoapril.git
cd indoapril
```

2. **Install Dependencies**
```bash
composer install
npm install
```

3. **Environment Setup**
```bash
copy .env.example .env
php artisan key:generate
```

4. **Database Configuration**

Edit `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=indoapril
DB_USERNAME=root
DB_PASSWORD=
```

5. **Build Assets**
```bash
npm run build
```

6. **Run Server**
```bash
php artisan serve
```

7. **Access Application**
```
http://127.0.0.1:8000
```

---

## 📚 Dokumentasi

### File Documentation
- [CHANGELOG-BARANG.md](CHANGELOG-BARANG.md) - Changelog fitur barang
- [DASHBOARD-DOCUMENTATION.md](DASHBOARD-DOCUMENTATION.md) - Dokumentasi dashboard
- [VENDOR-DOCUMENTATION.md](VENDOR-DOCUMENTATION.md) - Dokumentasi vendor module
- [SCREENSHOT-GUIDE.md](SCREENSHOT-GUIDE.md) - Panduan screenshot untuk UTS

---

## 🗄️ Database Schema

### Tabel Utama

#### `barang`
| Column | Type | Description |
|--------|------|-------------|
| idbarang | INT (PK) | Primary key |
| jenis | VARCHAR(50) | Jenis/kategori barang |
| nama | VARCHAR(100) | Nama barang |
| idsatuan | INT | Foreign key ke tabel satuan |
| status | TINYINT(1) | 1=Aktif, 0=Nonaktif |
| harga | DECIMAL(15,2) | Harga satuan |

---

## 🎓 UTS Requirements

### ✅ Completed

1. **Forms** ✅
   - Input Form (Create)
   - Edit Form (Update)
   - Delete Form (Soft Delete via Status)

2. **Views** ✅
   - Dashboard
   - Index/List View
   - Create View
   - Edit View

### ⏳ Pending (Database Routines)

3. **Function** ⏳ - Minimal 1 Function
4. **Stored Procedure** ⏳ - Minimal 1 Stored Procedure
5. **Trigger** ⏳ - Minimal 1 Trigger

---

## 🚀 Performance

### Build Metrics
- **CSS**: 72.19 kB (gzip: 12.98 kB)
- **JS**: 80.59 kB (gzip: 30.19 kB)
- **Build Time**: ~1.8s

---

**Built with ❤️ using Laravel 11 + Tailwind CSS 4 + Alpine.js**

© 2025 IndoApril. All rights reserved.

