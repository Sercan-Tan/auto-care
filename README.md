# Araç Bakım & Takip Sistemi

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)

Araç Bakım & Takip Sistemi, servis süreçlerini dijitalleştirerek araç bakım işlemlerini kolayca takip edebilmek ve raporlamak için geliştirilmiş bir web uygulamasıdır.

## 📋 Özellikler

### 🚗 Araç Yönetimi
- Araç ekleme, düzenleme ve silme
- Plaka, marka, model, yıl, şasi no gibi temel araç bilgilerinin yönetimi
- Araç sahiplerine ait iletişim bilgilerinin kaydı
- Hızlı araç arama fonksiyonu

### 🔧 Bakım & İşlem Takibi
- Araçların bakım geçmişinin detaylı kaydı
- Kilometre bilgisi ile birlikte servis kayıtları
- İşlemlerin kategorilere ayrılarak takibi
- Tamamlanan işlemlerin işaretlenmesi ve raporlanması

### 📊 Kategori Yönetimi
- İşlem kategorilerini hiyerarşik olarak yönetme
- Ana kategoriler ve alt işlemler oluşturma
- Kategori bazlı raporlama imkanı

### 👥 Kullanıcı Yönetimi
- Farklı yetki seviyelerine sahip kullanıcılar (Admin, Servis Personeli)
- Güvenli oturum yönetimi

### 📈 Maliyet Takibi
- İşçilik ve parça maliyetlerinin ayrı ayrı takibi
- Toplam servis maliyeti hesaplaması
- Araç bazlı maliyet raporlaması

### 💾 Yedekleme
- Sistem verilerinin güvenli yedeklenmesi
- Yedek alma ve geri yükleme fonksiyonları

## 🛠️ Teknik Yapı

- **Backend:** Laravel 12
- **Frontend:** Vue.js 3 ve Inertia.js
- **Veritabanı:** MySQL
- **UI Framework:** Tailwind CSS
- **Authentication:** Laravel Breeze

## 💻 Kurulum

### Gereksinimler
- PHP 8.2+
- Node.js ve NPM
- Composer
- MySQL

### Adımlar

1. Repoyu klonlayın
```bash
git clone https://github.com/kullanici-adi/vehicle-tracking.git
cd vehicle-tracking
```

2. Bağımlılıkları yükleyin
```bash
composer install
npm install
```

3. Çevre değişkenlerini yapılandırın
```bash
cp .env.example .env
php artisan key:generate
```

4. Veritabanını ayarlayın
```bash
# .env dosyasını düzenleyerek veritabanı bağlantı bilgilerini girin
php artisan migrate --seed
```

5. Frontend varlıklarını derleyin
```bash
npm run build
```

6. Uygulamayı başlatın
```bash
php artisan serve
```

7. Tarayıcınızda [http://localhost:8000](http://localhost:8000) adresine gidin

## 👤 Kullanıcı Rolleri

### Admin
- Tüm sistem özelliklerine tam erişim
- Kullanıcı yönetimi
- Kategori yönetimi
- Raporlama erişimi
- Yedekleme işlemleri

### Servis Personeli
- Araç ekleme ve düzenleme
- Servis kaydı oluşturma
- İşlem tamamlama

## 🔒 Güvenlik

- CSRF koruması
- Kullanıcı bazlı yetkilendirme
- Şifreli veritabanı bağlantısı

## 📱 Duyarlı Tasarım

Uygulama, Tailwind CSS kullanılarak geliştirilmiş olup masaüstü, tablet ve mobil cihazlarda sorunsuz çalışacak şekilde tasarlanmıştır.

## 📄 Lisans

MIT lisansı altında dağıtılmaktadır. Daha fazla bilgi için LICENSE dosyasına bakın.
