<?php
// For PHP built-in server: php -S localhost:8000 router.php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
if ($path === '/' || $path === '/index.php') {
    require __DIR__.'/index.php';
    return true;
}
if (preg_match('#^/public/assets/[a-zA-Z0-9_./-]+$#D', $path)
    && strpos($path, '..') === false
    && is_file(__DIR__.$path)) return false;
http_response_code(404);
header('Content-Type: text/plain; charset=UTF-8');
echo 'Not found.';
return true;
