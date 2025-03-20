# Araç Bakım & İşlem & Takip Uygulaması PRD (Product Requirements Document)

## 1. Giriş
Bu doküman, Laravel 12 ve Vue.js kullanılarak geliştirilecek olan araç bakım, işlem ve takip uygulamasının gereksinimlerini içermektedir. Uygulama, yetkililerin araç bilgilerini kaydetmesini ve araç servise geldikçe yapılan işlemleri takip etmesini sağlar.

## 2. Amaç
Uygulamanın amacı, servis süreçlerini dijitalleştirerek araç bakım işlemlerini kolayca takip edebilmek ve raporlayabilmektir.

## 3. Kullanıcı Rolleri
- **Sistem Yetkilisi**: Kullanıcı yönetimi, araç kaydı, işlem kaydı ve raporlama gibi işlemleri yapabilir.
- **Servis Personeli**: Araç bakım işlemlerini takip edebilir ve yapılan işlemleri işaretleyerek kayıt altına alabilir.

## 4. Ana Özellikler

### 4.1 Kullanıcı Yönetimi
- Yetkililerin giriş yapabilmesi (Breeze)
- Kullanıcı rollerinin belirlenmesi (Yetkili, Servis Personeli)

### 4.2 Araç Yönetimi
- Plaka numarası
- Marka, model, yıl, şasi numarası
- Araç sahibi bilgileri (Ad Soyad, Telefon, Eposta)
- Araç geçmişi (önceki bakım kayıtları, yapılan işlemler)

### 4.3 Bakım & İşlem Takibi
- Araç servise geldikçe kilometre bilgisinin kaydedilmesi
- Yapılan işlemlerin tiklenerek kaydedilmesi
- Yapılan işlemlerin raporlanabilmesi
- İşlem kategorileri yönetilebilir olmalı
- Örnek İşlem kategorileri:
  - **Bilgisayarlı arıza tespit**
  - **Periyodik bakım**
    - Yağ
    - Yağ filtresi
  - **Ateşleme sistemi**
    - Buji
  - **Süspansiyon sistemi**
  - **Direksiyon sistemi**
  - **Fren sistemi**
  - **Elektrik sistemi**
  - **Elektronik sistemi**
  - **LPG sistemi**
  - **Genel bakım**
  - **Muayene öncesi hazırlık**

### 4.4 Kategori Yönetimi
- İşlem kategorilerinin eklenmesi, düzenlenmesi ve silinmesi
- Alt işlemlerin belirlenmesi
- Yetkili kullanıcıların kategori yönetimi yapabilmesi

### 4.5 Raporlama
- Araç bazlı bakım geçmişi raporları
- Yapılan ve yapılmayan işlemlerin detaylı dökümü
- Bakım zaman çizelgesi ve eksik işlemler

## 5. Teknoloji Seçimi
- **Backend:** Laravel 12 (PHP)
- **Frontend:** Vue
- **Veritabanı:** MySQL
- **UI Framework:** Tailwind CSS

## 6. Veritabanı Tasarımı (Özet)
- **users** (id, name, email, password, role)
- **vehicles** (id, plate_no, brand, model, year, chassis_no, owner_name, owner_phone, owner_email)
- **services** (id, vehicle_id, mileage, service_date)
- **service_items** (id, service_id, category_id, completed)
- **categories** (id, name, parent_id)

## 7. Kullanıcı Arayüzü
- **Giriş Sayfası**: Kullanıcı adı ve şifre ile giriş. Giriş yapmadan sayfaları göremez
- **Araç Listesi**: Kayıtlı araçları listeleme ve yeni araç ekleme
- **Araç Detayı**: Araç bilgileri ve servis geçmişi
- **Servis Kaydı**: Araç servise geldiğinde yapılan işlemleri seçme, tamamlanan işlemleri tikleyerek kaydetme
- **Kategori Yönetimi**: İşlem kategorilerinin listelenmesi, düzenlenmesi ve silinmesi
- **Raporlama**: Araç bazlı bakım geçmişi, yapılan işlemler

## 9. Sonuç
Bu PRD, araç bakım & işlem & takip uygulamasının gereksinimlerini ve özelliklerini tanımlar. Laravel 12 kullanılarak geliştirilecek sistem, servis süreçlerini dijitalleştirerek daha verimli bir takip mekanizması sunacaktır.

