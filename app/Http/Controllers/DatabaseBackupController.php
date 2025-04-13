<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Response as DownloadResponse;

class DatabaseBackupController extends Controller
{
    // Constructor'dan middleware tanımlamasını kaldırıyoruz
    public function __construct()
    {
        // Constructor boş kalsın
    }

    /**
     * Yedekleme sayfasını görüntüler
     */
    public function index(): Response
    {
        $backupFiles = $this->getBackupFiles();
        
        return Inertia::render('Backups/Index', [
            'backups' => $backupFiles
        ]);
    }

    /**
     * Yeni bir yedek oluşturur
     */
    public function create(Request $request): RedirectResponse
    {
        try {
            // Veritabanı yedeği oluşturma komutunu çalıştır
            Artisan::call('backup:run --only-db');
            return redirect()->route('backups.index')->with('flash.message', 'Yedekleme başarıyla oluşturuldu.');
        } catch (\Exception $e) {
            Log::error('Yedekleme oluşturma hatası: ' . $e->getMessage());
            return redirect()->route('backups.index')->with('flash.error', 'Yedekleme oluşturulurken bir hata oluştu: ' . $e->getMessage());
        }
    }
    /**
     * Seçilen yedeği geri yükler
     */
    public function restore(Request $request): RedirectResponse
    {
        $request->validate([
            'backup_file' => 'required|string'
        ]);

        try {
            $backupFile = $request->input('backup_file');
            $backupPath = 'Laravel/' . $backupFile;
            
            if (!Storage::exists($backupPath)) {
                return redirect()->route('backups.index')->with('flash.error', 'Yedek dosyası bulunamadı.');
            }
            
            // Geçici dizin oluştur
            $tempDir = storage_path('app/restore-temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            } else {
                // Varolan içeriği temizle
                $this->cleanDirectory($tempDir);
            }
            
            // ZIP dosyasını geçici dizine kopyala
            $zipPath = $tempDir . '/' . $backupFile;
            copy(Storage::path($backupPath), $zipPath);
            
            // ZIP dosyasını aç
            $zip = new \ZipArchive();
            if ($zip->open($zipPath) === true) {
                $zip->extractTo($tempDir);
                $zip->close();
                
                // SQL dosyasını bul
                $sqlFile = $this->findSqlFile($tempDir);
                
                if ($sqlFile) {
                    // Veritabanı bilgilerini al
                    $dbName = config('database.connections.mysql.database');
                    $dbUser = config('database.connections.mysql.username');
                    $dbPassword = config('database.connections.mysql.password');
                    $dbPort = config('database.connections.mysql.port', 3306);
                    $dbHost = config('database.connections.mysql.host', '127.0.0.1');
                    
                    // MySQL dump yolu - config'de tanımlanan dump_binary_path'i kullan
                    $mysqlPath = config('database.connections.mysql.dump.dump_binary_path', '/Applications/MAMP/Library/bin/');
                    $mysqlImportPath = rtrim($mysqlPath, '/') . '/mysql';
                    
                    // Yedek dosyasını import etmek için komutu oluştur
                    $command = "{$mysqlImportPath} --host={$dbHost} --port={$dbPort} --user={$dbUser} --password={$dbPassword} {$dbName} < {$sqlFile} 2>&1";
                    
                    // Komutu çalıştır
                    $output = [];
                    $return_var = 0;
                    exec($command, $output, $return_var);
                    
                    // Sonucu kontrol et
                    if ($return_var !== 0) {
                        Log::error('Veritabanı geri yükleme hatası: ' . implode("\n", $output));
                        return redirect()->route('backups.index')->with('flash.error', 'Veritabanı geri yüklenirken bir hata oluştu: ' . implode("\n", $output));
                    }
                    
                    // Geçici dizini temizle
                    $this->cleanDirectory($tempDir);
                    
                    return redirect()->route('backups.index')->with('flash.message', 'Veritabanı yedeği başarıyla geri yüklendi.');
                } else {
                    return redirect()->route('backups.index')->with('flash.error', 'Yedek içerisinde SQL dosyası bulunamadı.');
                }
            } else {
                return redirect()->route('backups.index')->with('flash.error', 'ZIP dosyası açılamadı.');
            }
        } catch (\Exception $e) {
            Log::error('Yedek geri yükleme hatası: ' . $e->getMessage());
            return redirect()->route('backups.index')->with('flash.error', 'Yedek geri yüklenirken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Yedek dosyalarını alır
     */
    private function getBackupFiles(): array
    {
        $files = [];
        $backupFiles = Storage::files('Laravel');
        
        foreach ($backupFiles as $file) {
            $filename = basename($file);
            $files[] = [
                'name' => $filename,
                'size' => Storage::size($file),
                'last_modified' => Storage::lastModified($file),
            ];
        }
        
        // En son oluşturulan yedekleri başa yerleştir
        usort($files, function($a, $b) {
            return $b['last_modified'] - $a['last_modified'];
        });
        
        return $files;
    }
    
    /**
     * Dışarıdan bir yedek dosyasını sisteme yükler
     */
    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:zip|max:50000', // 50MB maksimum boyut
        ]);
        
        try {
            $uploadedFile = $request->file('backup_file');
            $fileName = 'uploaded_' . date('Y-m-d-H-i-s') . '.zip';
            
            // Dosyayı Laravel dizinine yükle
            Storage::putFileAs('Laravel', $uploadedFile, $fileName);
            
            return redirect()->route('backups.index')->with('flash.message', 'Yedek dosyası başarıyla yüklendi.');
        } catch (\Exception $e) {
            Log::error('Yedek dosyası yükleme hatası: ' . $e->getMessage());
            return redirect()->route('backups.index')->with('flash.error', 'Yedek dosyası yüklenirken bir hata oluştu: ' . $e->getMessage());
        }
    }
    
    /**
     * Belirtilen dizin içindeki SQL dosyasını bulur
     */
    private function findSqlFile(string $directory): ?string
    {
        // db-dumps dizinini kontrol et (genellikle burada olur)
        $dbDumpsDir = $directory . '/db-dumps';
        if (is_dir($dbDumpsDir)) {
            $files = scandir($dbDumpsDir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                    return $dbDumpsDir . '/' . $file;
                }
            }
        }
        
        // Eğer db-dumps dizininde bulunamazsa, ana dizini kontrol et
        $files = scandir($directory);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                return $directory . '/' . $file;
            }
            
            // Alt dizinleri kontrol et (dizinse ve . veya .. değilse)
            $path = $directory . '/' . $file;
            if (is_dir($path) && $file !== '.' && $file !== '..' && $file !== 'db-dumps') {
                $sqlFile = $this->findSqlFile($path);
                if ($sqlFile) {
                    return $sqlFile;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Belirtilen dizini temizler
     */
    private function cleanDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }
        
        $files = scandir($directory);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $path = $directory . '/' . $file;
                if (is_dir($path)) {
                    $this->cleanDirectory($path);
                    rmdir($path);
                } else {
                    unlink($path);
                }
            }
        }
    }

    /**
     * Belirtilen yedek dosyasını indirir
     */
    public function download(string $filename)
    {
        $path = 'Laravel/' . $filename;
        
        if (!Storage::exists($path)) {
            return redirect()->route('backups.index')->with('flash.error', 'Yedek dosyası bulunamadı.');
        }
        
        return DownloadResponse::download(Storage::path($path), $filename);
    }
    
    /**
     * Belirtilen yedek dosyasını siler
     */
    public function delete(Request $request): RedirectResponse
    {
        $request->validate([
            'backup_file' => 'required|string'
        ]);
        
        try {
            $backupFile = $request->input('backup_file');
            $backupPath = 'Laravel/' . $backupFile;
            
            if (!Storage::exists($backupPath)) {
                return redirect()->route('backups.index')->with('flash.error', 'Yedek dosyası bulunamadı.');
            }
            
            Storage::delete($backupPath);
            
            return redirect()->route('backups.index')->with('flash.message', 'Yedek dosyası başarıyla silindi.');
        } catch (\Exception $e) {
            Log::error('Yedek dosyası silme hatası: ' . $e->getMessage());
            return redirect()->route('backups.index')->with('flash.error', 'Yedek dosyası silinirken bir hata oluştu: ' . $e->getMessage());
        }
    }
}
