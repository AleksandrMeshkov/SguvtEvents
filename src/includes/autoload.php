<?php
/**
 * Autoloader для библиотек экспорта
 * 
 * Поддерживает два режима:
 * 1. Production: загрузка из libs.phar
 * 2. Development: загрузка из src/lib/
 */

// В Docker/Linux зависимости устанавливаются Composer-ом.
$composerAutoload = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
    return;
}

// Путь к PHAR-архиву (внутри папки server)
$pharPath = __DIR__ . '/../../server/libs/libs.phar';

if (file_exists($pharPath)) {
    // Production режим - загрузка из PHAR
    // Регистрируем PHAR с алиасом для корректной работы внутренних require
    Phar::loadPhar($pharPath, 'libs.phar');
    
    // TCPDF_STATIC::file_exists() блокирует все stream wrappers кроме http/https
    // Поэтому извлекаем шрифты из PHAR в реальную папку на файловой системе
    $fontsDir = realpath(__DIR__ . '/../../server/libs') . '/tcpdf_fonts/';
    
    // Проверяем, есть ли уже извлечённые шрифты (helvetica.php как маркер)
    if (!file_exists($fontsDir . 'helvetica.php')) {
        if (!is_dir($fontsDir)) {
            mkdir($fontsDir, 0777, true);
        }
        // Извлекаем все файлы шрифтов из PHAR
        $phar = new Phar($pharPath);
        foreach (new RecursiveIteratorIterator($phar) as $file) {
            $path = $file->getPathname();
            if (strpos($path, 'tcpdf/fonts/') !== false) {
                $relativePath = str_replace('\\', '/', $path);
                $relativePath = preg_replace('#.*?tcpdf/fonts/#', '', $relativePath);
                $destPath = $fontsDir . $relativePath;
                $destDir = dirname($destPath);
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0777, true);
                }
                copy($path, $destPath);
            }
        }
    }
    
    // Блокируем загрузку tcpdf_config.php (он использует realpath(), который не работает с phar://)
    if (!defined('K_TCPDF_EXTERNAL_CONFIG')) {
        define('K_TCPDF_EXTERNAL_CONFIG', true);
    }
    
    // Задаём константы путей вручную
    if (!defined('K_PATH_MAIN')) {
        define('K_PATH_MAIN', 'phar://libs.phar/tcpdf/');
    }
    if (!defined('K_PATH_FONTS')) {
        define('K_PATH_FONTS', $fontsDir);
    }
    if (!defined('K_PATH_IMAGES')) {
        define('K_PATH_IMAGES', 'phar://libs.phar/tcpdf/examples/images/');
    }
    if (!defined('K_BLANK_IMAGE')) {
        define('K_BLANK_IMAGE', '_blank.png');
    }
    if (!defined('K_PATH_CACHE')) {
        define('K_PATH_CACHE', sys_get_temp_dir() . '/');
    }
    
    // Загружаем TCPDF
    require_once 'phar://libs.phar/tcpdf/tcpdf.php';
    
    // Автозагрузчик для остальных библиотек
    spl_autoload_register(function ($class) {
        // PHPWord (PhpOffice\PhpWord\)
        if (strpos($class, 'PhpOffice\\PhpWord\\') === 0) {
            $relativeClass = substr($class, strlen('PhpOffice\\PhpWord\\'));
            $file = 'phar://libs.phar/phpword/src/PhpWord/' . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
            return;
        }
        
        // Laminas Escaper (Laminas\Escaper\)
        if (strpos($class, 'Laminas\\Escaper\\') === 0) {
            $relativeClass = substr($class, strlen('Laminas\\Escaper\\'));
            $file = 'phar://libs.phar/laminas-escaper/src/src/' . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
            return;
        }
        
        // PhpOffice Math (PhpOffice\Math\)
        if (strpos($class, 'PhpOffice\\Math\\') === 0) {
            $relativeClass = substr($class, strlen('PhpOffice\\Math\\'));
            $file = 'phar://libs.phar/phpoffice/math/src/Math/' . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
            return;
        }
    });
} else {
    // Development режим - загрузка из исходников
    $libDir = __DIR__ . '/../lib/';
    
    spl_autoload_register(function ($class) use ($libDir) {
        // TCPDF
        if (strpos($class, 'TCPDF') === 0) {
            $file = $libDir . 'tcpdf/tcpdf.php';
            if (file_exists($file)) {
                require_once $file;
            }
            return;
        }
        
        // PHPWord (PhpOffice\PhpWord\)
        if (strpos($class, 'PhpOffice\\PhpWord\\') === 0) {
            $relativeClass = substr($class, strlen('PhpOffice\\PhpWord\\'));
            $file = $libDir . 'phpword/src/PhpWord/' . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
            return;
        }
        
        // Laminas Escaper (Laminas\Escaper\)
        if (strpos($class, 'Laminas\\Escaper\\') === 0) {
            $relativeClass = substr($class, strlen('Laminas\\Escaper\\'));
            $file = $libDir . 'laminas-escaper/src/src/' . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
            return;
        }
        
        // PhpOffice Math (PhpOffice\Math\)
        if (strpos($class, 'PhpOffice\\Math\\') === 0) {
            $relativeClass = substr($class, strlen('PhpOffice\\Math\\'));
            $file = $libDir . 'phpoffice/math/src/Math/' . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
            return;
        }
    });
    
    // Загружаем TCPDF явно (так как он использует classmap)
    require_once $libDir . 'tcpdf/tcpdf.php';
}
