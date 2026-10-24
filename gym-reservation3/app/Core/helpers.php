<?php
function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function linkTo(string $page, array $query = []): string
{
    return 'index.php?' . http_build_query(array_merge(['page' => $page], $query));
}
function redirectTo(string $page, array $query = []): never
{
    header('Location: ' . linkTo($page, $query), true, 303);
    exit;
}
function flash(string $kind, string $text): void
{
    $_SESSION['flash'] = ['kind' => $kind, 'text' => $text];
}
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e($_SESSION['csrf_token'] ?? '') . '">';
}
function checkCsrf(): void
{
    $token = $_POST['csrf_token'] ?? null;
    if (!is_string($token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        abortRequest(403, 'Invalid form request. Please refresh and try again.');
    }
}
function postOnly(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') abortRequest(405, 'Method not allowed.');
}
function getOnly(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') abortRequest(405, 'Method not allowed.');
}
function postString(string $key, int $maxBytes = 10000): ?string
{
    $value = $_POST[$key] ?? null;
    return is_string($value) && strlen($value) <= $maxBytes ? $value : null;
}
function positiveId($value): ?int
{
    if (!is_string($value) && !is_int($value)) return null;
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
    return $id === false ? null : $id;
}
function renderView(string $name, array $data = []): void
{
    $path = __DIR__ . '/../../resources/views/' . $name . '.php';
    if (!is_file($path)) throw new RuntimeException('View not found');
    extract($data, EXTR_SKIP);
    require __DIR__ . '/../../resources/views/layouts/header.php';
    require $path;
    require __DIR__ . '/../../resources/views/layouts/footer.php';
}
function abortRequest(int $status, string $message): never
{
    http_response_code($status);
    renderView('errors/message', ['message' => $message]);
    exit;
}
function localDateTime(?string $input): ?string
{
    if (!is_string($input)) return null;
    $input = trim($input);
    $dt = DateTimeImmutable::createFromFormat('!Y-m-d\\TH:i', $input);
    if (!$dt || $dt->format('Y-m-d\\TH:i') !== $input) return null;
    return $dt->format('Y-m-d H:i:s');
}
function isFuture(string $mysqlDate): bool
{
    return $mysqlDate > date('Y-m-d H:i:s');
}
