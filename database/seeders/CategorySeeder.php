<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Otomobil bakımı için kategorileri oluştur
     */
    public function run(): void
    {
        // Ana kategorileri oluştur
        $mainCategories = [
            'Ateşleme Sistemleri' => [
                'Buji',
                'Enjektör',
                'Ateşleme Bobini',
                'Distribütör',
                'Ateşleme Kabloları',
                'Krank Mili Sensörü',
                'Eksantrik Mili Sensörü',
            ],
            'Periyodik Bakım' => [
                'Motor Yağı',
                'Yağ Filtresi',
                'Hava Filtresi',
                'Polen Filtresi',
                'Yakıt Filtresi',
                'Fren Hidroliği',
                'Şanzıman Yağı',
                'Diferansiyel Yağı',
                'Antifriz/Soğutma Sıvısı',
            ],
            'Fren Sistemi' => [
                'Fren Balataları',
                'Fren Diskleri',
                'Fren Kampanaları',
                'Fren Kaliperleri',
                'ABS Sensörleri',
                'El Freni Kabloları',
            ],
            'Süspansiyon Sistemi' => [
                'Amortisörler',
                'Yaylar',
                'Salıncaklar',
                'Rotiller',
                'Viraj Denge Çubuğu',
                'Rot Başı',
                'Direksiyon Kutusu',
            ],
            'Elektrik Sistemi' => [
                'Akü',
                'Alternatör',
                'Marş Motoru',
                'Sigortalar',
                'Far Ampulleri',
                'Silecek Lastikleri',
                'Klima Kompresörü',
            ],
            'Motor Aksamı' => [
                'Triger Kayışı/Zinciri',
                'Gergi Rulmanları',
                'Devirdaim Pompası',
                'Subaplar',
                'Segmanlar',
                'Contalar',
                'Pistonlar',
            ],
            'Debriyaj Sistemi' => [
                'Debriyaj Seti',
                'Debriyaj Diski',
                'Baskı Plakası',
                'Volan',
                'Debriyaj Hidroliği',
            ],
            'Egzoz Sistemi' => [
                'Katalitik Konvertör',
                'Egzoz Manifoldu',
                'Susturucu',
                'Lambda Sensörü',
                'EGR Valfi',
            ],
            'Şasi ve Gövde' => [
                'Tamponlar',
                'Kapı Kolları',
                'Ayna Aksamları',
                'Camlar',
                'Cam Krikosu',
                'Kaput Amortisörü',
            ],
            'Konfor Sistemleri' => [
                'Klima Gazı',
                'Kalorifer Radyatörü',
                'Kabin Filtresi',
                'Merkezi Kilit Sistemi',
                'Cam Suyu',
            ]
        ];

        // Ana kategorileri ve alt kategorileri oluştur
        foreach ($mainCategories as $mainName => $subCategories) {
            $mainCategory = Category::factory()->create(['name' => $mainName]);
            
            foreach ($subCategories as $subName) {
                Category::factory()
                    ->withParent($mainCategory)
                    ->create(['name' => $subName]);
            }
        }
    }
}
