# Seeder untuk Panel Assessor

## Quick Start

Untuk mengisi database dengan data demo lengkap untuk panel assessor, jalankan:

```bash
php artisan db:seed --class=AssessorPanelSeeder
```

## Data yang Dibuat

Seeder akan membuat data lengkap:

### 1. **Users & Roles**
- 1 Admin
- 3 Assessors (Tim Penilai)
- 10 Teachers (Guru)

### 2. **Assessment Setup**
- 2 Assessment Periods (1 aktif, 1 selesai)
- Criteria & Sub-criteria (Pedagogik, Profesional, Sosial, Kepribadian)
- KPI Form Template dengan items/indicators
- Form Assignments (penugasan assessor ke guru)

### 3. **Assessment Data**
- Assessments dengan status lengkap (draft, submitted, finalized)
- Assessment scores/nilai untuk setiap indicator
- Evidence uploads (beberapa item memiliki bukti upload)
- Status logs

### 4. **AHP & Results**
- AHP Models dengan pairwise comparisons
- AHP Weights (bobot kriteria)
- Teacher Results & Rankings per periode

## Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@demo.local | password |
| Assessor 1 | assessor1@demo.local | password |
| Assessor 2 | assessor2@demo.local | password |
| Assessor 3 | assessor3@demo.local | password |
| Guru 1-10 | guru1@demo.local ... guru10@demo.local | password |

## Akses Panel

- **Admin**: `/admin/dashboard`
- **Assessor**: `/assessor/dashboard` ← Login dengan assessor1@demo.local
- **Teacher**: `/teacher/dashboard`

## Fitur yang Bisa Ditest di Panel Assessor

1. **Dashboard** (`/assessor/dashboard`)
   - Lihat statistik: total assigned, pending, submitted, finalized
   - Lihat periode aktif
   - Lihat recent assessments

2. **Assessments** (`/assessor/assessments`)
   - Pilih periode penilaian
   - Lihat daftar guru yang ditugaskan
   - Buka form scoring untuk setiap guru
   - Input/edit scores
   - Submit assessment

3. **Results** (`/assessor/results`)
   - Lihat hasil penilaian guru yang sudah dinilai
   - Lihat breakdown per kriteria
   - Lihat notes/catatan yang diberikan

4. **Profile** (`/assessor/profile`)
   - Edit profil assessor
   - Ubah password
   - Lihat informasi akun

## Reset & Re-seed

Jika ingin mereset database dan seed ulang:

```bash
# Fresh migration + seed
php artisan migrate:fresh --seed

# Atau hanya seed ulang (tidak menghapus data lain)
php artisan db:seed --class=AssessorPanelSeeder
```

## Catatan

- Seeder ini aman dijalankan berkali-kali (ada check untuk menghindari duplikasi)
- Data ditandai dengan `meta->demo = true` untuk identifikasi
- Password semua user adalah `password` (plain text, akan di-hash otomatis oleh model)
- File evidence adalah dummy (path/filename saja, tidak ada file fisik)

## Troubleshooting

**Jika muncul error saat seeding:**

1. Pastikan migration sudah dijalankan:
   ```bash
   php artisan migrate
   ```

2. Jika ingin fresh start:
   ```bash
   php artisan migrate:fresh
   php artisan db:seed --class=AssessorPanelSeeder
   ```

3. Check koneksi database di `.env`

**Jika tidak bisa login:**

- Pastikan password adalah `password` (lowercase)
- Check apakah email sudah benar
- Pastikan status user adalah `active`
