# CMS Sederhana

CMS Sederhana adalah sebuah Content Management System (CMS) sederhana yang dibangun menggunakan PHP native dengan pendekatan MVC (Model-View-Controller). CMS ini dirancang untuk memudahkan pengelolaan konten website dengan fitur-fitur dasar yang diperlukan.

## Fitur

- Sistem autentikasi (login, register, lupa password)
- Manajemen pengguna (admin, editor, author)
- Manajemen kategori
- Manajemen artikel (CRUD)
- Manajemen file upload
- Sistem invite code untuk registrasi
- Log aktivitas pengguna
- API sederhana
- Tema AdminLTE 3
- Responsive design

## Persyaratan Sistem

- PHP >= 7.4
- MySQL >= 5.7
- Apache/Nginx
- mod_rewrite enabled
- PDO PHP Extension
- GD PHP Extension
- Fileinfo PHP Extension
- OpenSSL PHP Extension
- Mbstring PHP Extension

## Instalasi

1. Clone repository ini:
```bash
git clone https://github.com/username/cms_sederhana.git
cd cms_sederhana
```

2. Buat database MySQL baru:
```sql
CREATE DATABASE cms_sederhana;
```

3. Import file SQL:
```bash
mysql -u username -p cms_sederhana < database/cms_sederhana.sql
```

4. Salin file .env.example menjadi .env:
```bash
cp .env.example .env
```

5. Edit file .env sesuai konfigurasi server Anda:
```env
APP_URL=http://localhost/cms_sederhana
DB_DATABASE=cms_sederhana
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. Buat direktori yang diperlukan dan atur permission:
```bash
mkdir -p app/storage/logs
mkdir -p app/storage/cache
mkdir -p app/storage/sessions
mkdir -p public/uploads
chmod -R 755 app/storage
chmod -R 755 public/uploads
```

7. Akses aplikasi melalui browser:
```
http://localhost/cms_sederhana
```

## Struktur Direktori

```
cms_sederhana/
├── app/
│   ├── config/         # Konfigurasi aplikasi
│   ├── controllers/    # Controller
│   ├── core/          # Core classes
│   ├── helpers/       # Helper functions
│   ├── models/        # Model
│   ├── storage/       # Storage (logs, cache, sessions)
│   └── views/         # View templates
├── database/          # File SQL dan migrasi
├── public/            # Public files
│   ├── assets/       # CSS, JS, images
│   └── uploads/      # Uploaded files
├── .env              # Environment variables
├── .env.example      # Environment template
├── .gitignore        # Git ignore file
├── .htaccess         # Apache configuration
├── index.php         # Front controller
└── README.md         # Documentation
```

## Penggunaan

### Login

Default login:
- Username: admin
- Password: admin123

### Manajemen Pengguna

1. Login sebagai admin
2. Akses menu Users
3. Tambah, edit, atau hapus pengguna
4. Atur role dan status pengguna

### Manajemen Kategori

1. Login sebagai admin/editor
2. Akses menu Categories
3. Tambah, edit, atau hapus kategori
4. Atur status kategori

### Manajemen Artikel

1. Login sebagai admin/editor/author
2. Akses menu Posts
3. Tambah, edit, atau hapus artikel
4. Upload gambar
5. Atur status artikel (draft/published)

### Invite Code

1. Login sebagai admin
2. Akses menu Invite Codes
3. Generate kode undangan
4. Atur expiry time
5. Revoke kode jika diperlukan

## API

### Posts API

```
GET /api/posts              # Get all posts
GET /api/posts/{id}         # Get post by ID
GET /api/posts/category/{id} # Get posts by category
```

### Categories API

```
GET /api/categories         # Get all categories
GET /api/categories/{id}    # Get category by ID
```

### Users API

```
GET /api/users             # Get all users (admin only)
GET /api/users/{id}        # Get user by ID (admin only)
```

## Keamanan

- Password hashing menggunakan bcrypt
- CSRF protection
- XSS protection
- SQL injection protection
- Session security
- File upload validation
- Input validation & sanitization
- Rate limiting untuk login
- Secure headers

## Pengembangan

1. Fork repository
2. Buat branch baru (`git checkout -b fitur-baru`)
3. Commit perubahan (`git commit -am 'Tambah fitur baru'`)
4. Push ke branch (`git push origin fitur-baru`)
5. Buat Pull Request

## Lisensi

CMS Sederhana dilisensikan di bawah [MIT License](LICENSE).

## Kontribusi

Kontribusi selalu diterima! Silakan buat issue atau pull request untuk berkontribusi.

## Kontak

Jika Anda memiliki pertanyaan atau saran, silakan buat issue di repository ini. 