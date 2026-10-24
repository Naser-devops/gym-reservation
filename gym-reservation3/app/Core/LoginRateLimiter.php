<?php
/**
 * Filesystem login-attempt throttling, shared between sessions on the same server.
 * Keep storage/ private and writable by PHP. Do not expose this directory over HTTP.
 * For multi-server hosting replace this with a shared cache/DB limiter.
 */
class LoginRateLimiter
{
    private string $directory;
    private const WINDOW = 900; // 15 minutes

    public function __construct(string $directory)
    {
        $this->directory = $directory;
        if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
            error_log('Login rate limiter storage is not writable: directory unavailable');
        }
    }

    private function keyFile(string $key): string
    {
        return $this->directory . '/' . hash('sha256', $key) . '.json';
    }

    /** Access locked state and optionally update it; fail open to the existing session throttle. */
    private function operate(string $key, int $limit, bool $increment = false, bool $clear = false): bool
    {
        if (!is_dir($this->directory) || !is_writable($this->directory)) {
            error_log('Login rate limiter disabled: writable storage required');
            return true;
        }
        $path = $this->keyFile($key);
        // A first-time, successful login should not create a cache file.
        if (!$increment && !$clear && !is_file($path)) return true;
        $file = @fopen($path, 'c+');
        if (!$file) { error_log('Login rate limiter could not open storage'); return true; }
        @chmod($path, 0600);
        try {
            if (!flock($file, LOCK_EX)) return true;
            $data = json_decode(stream_get_contents($file) ?: '', true);
            $now = time();
            if (!is_array($data) || !isset($data['start'], $data['count']) || !is_int($data['start']) || !is_int($data['count']) || $data['start'] > $now || $data['start'] < $now - self::WINDOW) {
                $data = ['start' => $now, 'count' => 0];
            }
            $allowed = $data['count'] < $limit;
            if ($clear) $data = ['start' => $now, 'count' => 0];
            elseif ($increment) ++$data['count'];
            if ($increment || $clear) {
                rewind($file);
                ftruncate($file, 0);
                fwrite($file, json_encode($data));
                fflush($file);
            }
            return $allowed;
        } finally {
            flock($file, LOCK_UN);
            fclose($file);
        }
    }

    public function allowed(string $email, string $ip): bool
    {
        return $this->operate('account:' . strtolower($email), 8)
            && $this->operate('ip:' . $ip, 60);
    }

    public function failed(string $email, string $ip): void
    {
        $this->operate('account:' . strtolower($email), 8, true);
        $this->operate('ip:' . $ip, 60, true);
    }

    public function succeeded(string $email): void
    {
        $this->operate('account:' . strtolower($email), 8, false, true);
    }
}
