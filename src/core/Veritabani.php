<?php
/**
 * src/core/Veritabani.php
 * PDO bağlantısını Singleton desenle yönetir.
 */
class Veritabani {
    private static ?PDO $baglanti = null;

    private static string $host = 'sql305.infinityfree.com';
    private static string $db   = 'if0_41821231_qrdb';
    private static string $user = 'if0_41821231';
    private static string $pass = 'Ceren9120sdf';          // XAMPP varsayılanı

    public static function baglan(): PDO {
        if (self::$baglanti === null) {
            try {
                $dsn = 'mysql:host=' . self::$host
                     . ';dbname=' . self::$db
                     . ';charset=utf8mb4';
                self::$baglanti = new PDO($dsn, self::$user, self::$pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                ]);
            } catch (PDOException $e) {
                // Kullanıcıya teknik detay gösterme
                error_log('DB Bağlantı Hatası: ' . $e->getMessage());
                die('<div style="padding:40px;text-align:center;font-family:sans-serif;">
                    <h2>⚠️ Veritabanı Bağlantısı Kurulamadı</h2>
                    <p>XAMPP\'te MySQL servisinin çalıştığından emin olun.</p>
                    <p>Veritabanı adı: <code>qrbarkod_db</code></p>
                </div>');
            }
        }
        return self::$baglanti;
    }

    /** Clone engellenmiş */
    private function __clone() {}
}
