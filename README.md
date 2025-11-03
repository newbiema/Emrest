# 🎞️ Frame Of Us

**Frame Of Us** adalah web album foto dengan nuansa _pixel aesthetic_, dibuat untuk menyimpan dan menampilkan momen-momen berharga dalam bentuk galeri interaktif.  
Dilengkapi dengan **dashboard admin**, sistem **multi-album carousel**, serta dukungan **musik latar** yang sinkron antar halaman.

![Preview Screenshot](docs/preview.png)

---

## ✨ Fitur Utama

### 🖼️ Galeri Publik
- Tampilan grid interaktif dengan animasi AOS.
- Setiap album memiliki slider (carousel) yang bisa di-swipe seperti di TikTok.
- Mendukung **fullscreen viewer** (`post.php`) dengan tombol share dan download.
- Musik latar yang tetap **berlanjut** meski berpindah halaman.
- Penghitung total pengunjung dan _online visitors_ real-time.

### 🛠️ Dashboard Admin
- Upload banyak foto sekaligus (drag & drop reordering).
- Edit album (judul, deskripsi, tambah, hapus, atau ganti foto).
- Reorder foto dalam album via drag & drop (mobile friendly dengan SortableJS).
- Kelola musik latar aktif (`music.php`).
- Statistik album & foto secara keseluruhan.

### 🎧 Musik Latar Sinkron
- Pemutaran musik disimpan via `sessionStorage`, sehingga saat berpindah halaman musik tidak berhenti.
- Admin bisa mengganti file MP3 aktif dari panel.

---

## 📂 Struktur Direktori

