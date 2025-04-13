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
            'backups' => $backupFiles,
            'flash' => [
                'error' => session('flash.error'),
                'message' => session('flash.message')
            ]
        ]);
    }

    /**
     * Yeni bir yedek oluşturur (MySQL ve SQLite desteği ile)
     */
    public function create(Request $request): RedirectResponse
    {
        try {
            $connection = config('database.default');

            if ($connection === 'mysql') {
                // MySQL için yedekleme komutunu çalıştır
                $exitCode = Artisan::call('backup:run --only-db');
                $output = Artisan::output();
                
                if ($exitCode !== 0) {
                    Log::error('Artisan komutu hata kodu ile sonlandı: ' . $exitCode);
                    Log::error('Artisan çıktısı: ' . $output);
                    throw new \Exception('Yedekleme işlemi başarısız oldu. Hata kodu: ' . $exitCode . '. Detaylar: ' . $output);
                }
                
                if (strpos($output, 'Command not found') !== false || strpos($output, 'command not found') !== false) {
                    Log::error('Komut bulunamadı hatası tespit edildi: ' . $output);
                    throw new \Exception('Yedekleme komutu bulunamadı. Sistem hatası: ' . $output);
                }
            } elseif ($connection === 'sqlite') {
                $dbPath = config('database.connections.sqlite.database');
                $backupPath = storage_path('app/private/Laravel/sqlite-backup-' . date('Y-m-d-H-i-s') . '.sqlite');        
                if (!copy($dbPath, $backupPath)) {
                    throw new \Exception('SQLite yedekleme işlemi başarısız oldu.');
                }
            } else {
                throw new \Exception('Desteklenmeyen veritabanı türü: ' . $connection);
            }

            return redirect()->route('backups.index')->with('flash.message', 'Yedekleme başarıyla oluşturuldu.');
        } catch (\Exception $e) {
            Log::error('Yedekleme oluşturma hatası: ' . $e->getMessage());
            return redirect()->route('backups.index')->with('flash.error', 'Yedekleme oluşturulurken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Seçilen yedeği geri yükler (MySQL ve SQLite desteği ile)
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

            $connection = config('database.default');

            if ($connection === 'mysql') {
                // MySQL için geri yükleme işlemi
                $this->restoreMySQL($backupPath);
            } elseif ($connection === 'sqlite') {
                // SQLite için geri yükleme işlemi
                $dbPath = config('database.connections.sqlite.database');
                if (!copy(Storage::path($backupPath), $dbPath)) {
                    throw new \Exception('SQLite geri yükleme işlemi başarısız oldu.');
                }
            } else {
                throw new \Exception('Desteklenmeyen veritabanı türü: ' . $connection);
            }

            return redirect()->route('backups.index')->with('flash.message', 'Veritabanı yedeği başarıyla geri yüklendi.');
        } catch (\Exception $e) {
            Log::error('Yedek geri yükleme hatası: ' . $e->getMessage());
            return redirect()->route('backups.index')->with('flash.error', 'Yedek geri yüklenirken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * MySQL için geri yükleme işlemi
     */
    private function restoreMySQL(string $backupPath): void
    {
        $tempDir = storage_path('app/restore-temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        } else {
            $this->cleanDirectory($tempDir);
        }

        $zipPath = $tempDir . '/' . basename($backupPath);
        copy(Storage::path($backupPath), $zipPath);

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($tempDir);
            $zip->close();

            $sqlFile = $this->findSqlFile($tempDir);
            if ($sqlFile) {
                $dbName = config('database.connections.mysql.database');
                $dbUser = config('database.connections.mysql.username');
                $dbPassword = config('database.connections.mysql.password');
                $dbPort = config('database.connections.mysql.port', 3306);
                $dbHost = config('database.connections.mysql.host', '127.0.0.1');

                // MySQL yolunu yapılandırmadan al veya varsayılan olarak belirle
                $mysqlPath = config('database.connections.mysql.dump.dump_binary_path', '/usr/bin/');
                $mysqlImportPath = rtrim($mysqlPath, '/') . '/mysql';

                $command = "{$mysqlImportPath} --host={$dbHost} --port={$dbPort} --user={$dbUser} --password={$dbPassword} {$dbName} < {$sqlFile} 2>&1";

                $output = [];
                $return_var = 0;
                exec($command, $output, $return_var);

                if ($return_var !== 0) {
                    throw new \Exception('MySQL geri yükleme hatası: ' . implode("\n", $output));
                }

                $this->cleanDirectory($tempDir);
            } else {
                throw new \Exception('Yedek içerisinde SQL dosyası bulunamadı.');
            }
        } else {
            throw new \Exception('ZIP dosyası açılamadı.');
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
            'backup_file' => 'required|file|mimetypes:application/zip,application/x-sqlite3|max:50000', // 50MB maksimum boyut
        ]);
        
        try {
            $uploadedFile = $request->file('backup_file');

            $connection = config('database.default');

            if ($connection === 'sqlite') {
                $fileName = 'uploaded_' . date('Y-m-d-H-i-s') . '.sqlite';
            } else {
                $fileName = 'uploaded_' . date('Y-m-d-H-i-s') . '.zip';
            }
            
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
