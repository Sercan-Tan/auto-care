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

    protected $backup_directory = 'app/private/Laravel/';

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
                try {
                    // PHP ile MySQL yedekleme işlemi
                    $dbName = config('database.connections.mysql.database');
                    $dbUser = config('database.connections.mysql.username');
                    $dbPassword = config('database.connections.mysql.password');
                    $dbHost = config('database.connections.mysql.host', '127.0.0.1');
                    $dbPort = config('database.connections.mysql.port', 3306);
                    
                    $backupFileName = 'mysql-backup-' . date('Y-m-d-H-i-s') . '.sql';
                    $backupFilePath = storage_path($this->backup_directory . $backupFileName);
                    
                    // Dizin yoksa oluştur
                    if (!file_exists(dirname($backupFilePath))) {
                        mkdir(dirname($backupFilePath), 0755, true);
                    }
                    
                    // PHP PDO kullanarak tablo yapılarını ve verileri dışa aktarma
                    $pdo = new \PDO(
                        "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8", 
                        $dbUser, 
                        $dbPassword, 
                        [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
                    );
                    
                    $tables = [];
                    $result = $pdo->query("SHOW TABLES");
                    while ($row = $result->fetch(\PDO::FETCH_NUM)) {
                        $tables[] = $row[0];
                    }
                    
                    $backupContent = "-- Veritabanı yedeği: {$dbName}\n";
                    $backupContent .= "-- Oluşturulma tarihi: " . date('Y-m-d H:i:s') . "\n\n";
                    $backupContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
                    
                    foreach ($tables as $table) {
                        // Tablo yapısını al
                        $stmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
                        $row = $stmt->fetch(\PDO::FETCH_NUM);
                        $backupContent .= "DROP TABLE IF EXISTS `{$table}`;\n";
                        $backupContent .= $row[1] . ";\n\n";
                        
                        // Tablo verilerini al
                        $stmt = $pdo->query("SELECT * FROM `{$table}`");
                        $rowCount = $stmt->rowCount();
                        
                        if ($rowCount > 0) {
                            $backupContent .= "INSERT INTO `{$table}` VALUES\n";
                            $rows = $stmt->fetchAll(\PDO::FETCH_NUM);
                            $rowsCount = count($rows);
                            
                            foreach ($rows as $i => $row) {
                                $backupContent .= "(";
                                foreach ($row as $j => $value) {
                                    if ($value === null) {
                                        $backupContent .= "NULL";
                                    } else {
                                        $value = str_replace(['\\', "'"], ['\\\\', "\'"], $value);
                                        $backupContent .= "'{$value}'";
                                    }
                                    if ($j < count($row) - 1) {
                                        $backupContent .= ",";
                                    }
                                }
                                $backupContent .= ")";
                                if ($i < $rowsCount - 1) {
                                    $backupContent .= ",\n";
                                } else {
                                    $backupContent .= ";\n\n";
                                }
                            }
                        }
                    }
                    
                    $backupContent .= "SET FOREIGN_KEY_CHECKS=1;\n";
                    
                    // Dosyaya yaz
                    if (file_put_contents($backupFilePath, $backupContent) === false) {
                        throw new \Exception('Yedekleme dosyası oluşturulamadı.');
                    }
                    
                    // Dosyayı zip'le
                    $zipFileName = 'mysql-backup-' . date('Y-m-d-H-i-s') . '.zip';
                    $zipFilePath = storage_path($this->backup_directory . $zipFileName);
                    
                    $zip = new \ZipArchive();
                    if ($zip->open($zipFilePath, \ZipArchive::CREATE) !== true) {
                        throw new \Exception('Zip dosyası oluşturulamadı.');
                    }
                    
                    $zip->addFile($backupFilePath, basename($backupFilePath));
                    $zip->close();
                    
                    // SQL dosyasını sil, sadece zip'i tut
                    unlink($backupFilePath);
                    
                } catch (\Exception $e) {
                    Log::error('PHP MySQL yedekleme hatası: ' . $e->getMessage());
                    throw new \Exception('Veritabanı yedekleme işlemi başarısız oldu: ' . $e->getMessage());
                }
                
            } elseif ($connection === 'sqlite') {
                $dbPath = config('database.connections.sqlite.database');
                $backupPath = storage_path($this->backup_directory . 'sqlite-backup-' . date('Y-m-d-H-i-s') . '.sqlite');        
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
                // MySQL için PHP tabanlı geri yükleme işlemi (mysql komutu gerektirmez)
                $this->restoreMySQLWithPDO($backupPath);
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
                $dbHost = config('database.connections.mysql.host', '127.0.0.1');
                $dbPort = config('database.connections.mysql.port', 3306);
                
                $command = "mysql --host={$dbHost} --port={$dbPort} --user={$dbUser} --password={$dbPassword} {$dbName} < {$sqlFile} 2>&1";

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
     * MySQL için PHP tabanlı geri yükleme işlemi
     */
    private function restoreMySQLWithPDO(string $backupPath): void
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
                $dbHost = config('database.connections.mysql.host', '127.0.0.1');
                $dbPort = config('database.connections.mysql.port', 3306);
                
                // Veritabanı bağlantısı
                $pdo = new \PDO(
                    "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8", 
                    $dbUser, 
                    $dbPassword, 
                    [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
                );
                
                // Foreign key kontrolleri devre dışı bırak
                $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
                
                // SQL dosyasının içeriğini oku
                $sql = file_get_contents($sqlFile);
                
                // SQL komutlarını ayrıştır ve çalıştır
                $queries = $this->splitSqlQueries($sql);
                
                try {
                    foreach ($queries as $query) {
                        if (trim($query) !== '') {
                            $pdo->exec($query);
                        }
                    }
                    
                    // Foreign key kontrolleri tekrar etkinleştir
                    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
                } catch (\PDOException $e) {
                    // Hata durumunda foreign key kontrollerini tekrar etkinleştir
                    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
                    throw new \Exception('MySQL geri yükleme hatası: ' . $e->getMessage());
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

    /**
     * SQL dosyasını farklı sorgulara ayırır
     */
    private function splitSqlQueries(string $sql): array
    {
        $queries = [];
        $currentQuery = '';
        $lines = explode("\n", $sql);
        
        foreach ($lines as $line) {
            // Yorum satırlarını ve boş satırları atla
            if (substr(trim($line), 0, 2) === '--' || trim($line) === '') {
                continue;
            }
            
            $currentQuery .= $line . "\n";
            
            // Eğer satır ";" ile bitiyorsa, yeni bir sorgu başlatılmalı
            if (substr(trim($line), -1) === ';') {
                $queries[] = $currentQuery;
                $currentQuery = '';
            }
        }
        
        // Son sorgu ";" ile bitmiyorsa, onu da ekle
        if (trim($currentQuery) !== '') {
            $queries[] = $currentQuery;
        }
        
        return $queries;
    }
}
