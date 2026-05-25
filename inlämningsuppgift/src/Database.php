<?php
 
// =============================================================
//  Database.php
//  Sköter PDO-uppkopplingen mot databasen. PDO = PHP Data Objects
//  Använder Singleton-mönstret så att bara EN koppling
//  skapas per request, oavsett hur många klasser som anropar den.
// =============================================================
 
class Database
{
    // Håller den enda PDO-instansen
    private static ?PDO $instance = null;
 
    // Privat konstruktor — ingen kan skriva "new Database()" utifrån
    private function __construct() {}
 
    // Förhindrar att klassen klonas
    private function __clone() {}
 
    //  connect()
    //  Returnerar den delade PDO-instansen.
    //  Skapar den första gången, återanvänder sedan samma.
    public static function connect(): PDO
    {
        if (self::$instance === null) {
            // Läs in .env om den inte redan är inläst
            self::loadEnv();
 
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $name = $_ENV['DB_NAME'] ?? '';
            $user = $_ENV['DB_USER'] ?? '';
            $pass = $_ENV['DB_PASS'] ?? '';
 
            $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
 
            self::$instance = new PDO($dsn, $user, $pass, [
                // Kasta ett undantag vid SQL-fel istället för tyst misslyckande
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
 
                // fetch() returnerar associativa arrayer som standard
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
 
                // Stäng av emulerade prepared statements — säkrare
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }
 
        return self::$instance;
    }
 
    //  loadEnv()
    //  Enkel .env-laddare — läser rad för rad och fyller $_ENV.
    //  Kräver inte något externt paket (t.ex. vlucas/phpdotenv).
    private static function loadEnv(): void
    {
        // Hitta .env i projektets rot (en nivå upp från public/)
        $path = dirname(__DIR__) . '/.env';
 
        if (!file_exists($path)) {
            return; // Ingen .env — förlita sig på systemets miljövariabler
        }
 
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
 
        foreach ($lines as $line) {
            // Hoppa över kommentarer
            if (str_starts_with(trim($line), '#')) {
                continue;
            }
 
            // Dela på första "=" — värdet kan innehålla "=" (t.ex. lösenord)
            [$key, $value] = explode('=', $line, 2);
 
            $key = trim($key);
            $value = trim($value);
 
            // Sätt bara om den inte redan finns som systemvariabel
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }
}
