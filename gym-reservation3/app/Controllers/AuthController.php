<?php
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Core/LoginRateLimiter.php';
class AuthController
{
    private User $user;
    public function __construct(PDO $pdo) { $this->user = new User($pdo); }

    public function showRegister(): void { renderView('auth/register'); }
    public function showLogin(): void { renderView('auth/login'); }

    public function register(): void
    {
        checkCsrf();
        $name = postString('full_name', 400);
        $email = postString('email', 300);
        $password = postString('password', 256);
        if ($name === null || $email === null || $password === null) abortRequest(400,'Invalid form data.');
        $name=trim($name); $email=trim($email);
        if ($name==='' || !preg_match('//u',$name) || preg_match_all('/./us',$name)>100 || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>255 || strlen($password)<8 || strlen($password)>72) {
            flash('error','Check your name, email and password (8–72 bytes).');
            redirectTo('register');
        }
        try {
            if ($this->user->findByEmail($email)) {
                flash('error','An account with this email already exists.');
                redirectTo('register');
            }
            $this->user->create($name,$email,$password);
            flash('success','Account created. Please log in.');
            redirectTo('login');
        } catch (PDOException $e) {
            if (($e->errorInfo[1]??null)===1062) {
                flash('error','An account with this email already exists.');
            } else {
                error_log('Register database failure SQLSTATE: '.$e->getCode());
                flash('error','Registration unavailable; try again later.');
            }
            redirectTo('register');
        }
    }
    public function login(): void
    {
        checkCsrf();
        $email=postString('email',300);$password=postString('password',256);
        if ($email===null || $password===null) abortRequest(400,'Invalid login data.');
        $email=trim($email);
        if (!filter_var($email,FILTER_VALIDATE_EMAIL) || $password==='') {
            flash('error','Invalid email or password.');redirectTo('login');
        }
        // Shared across browser sessions on one server; session throttle remains as a fallback.
        $limiter = new LoginRateLimiter(__DIR__ . '/../../storage/login-limits');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown'; // Never trust a client-supplied forwarded header.
        if (!$limiter->allowed($email, $ip)) {
            flash('error', 'Too many attempts. Try again in 15 minutes.');
            redirectTo('login');
        }
        $window=$_SESSION['login_attempt_window']??0;
        if (!is_int($window) || time()-$window>900) {
            $_SESSION['login_attempt_window']=time();$_SESSION['login_attempts']=0;
        }
        if (($_SESSION['login_attempts']??0)>=8) {
            flash('error','Too many attempts. Try again in 15 minutes.');redirectTo('login');
        }
        try {
            $account=$this->user->findByEmail($email);
            if (!$account || (int)$account['is_active']!==1 || !password_verify($password,$account['password_hash'])) {
                $_SESSION['login_attempts']++;
                $limiter->failed($email, $ip);
                flash('error','Invalid email or password.');redirectTo('login');
            }
            session_regenerate_id(true);
            $limiter->succeeded($email);
            $_SESSION['user_id']=(int)$account['id'];
            $_SESSION['csrf_token']=bin2hex(random_bytes(32));
            unset($_SESSION['login_attempts'],$_SESSION['login_attempt_window']);
            flash('success','Welcome back!');
            redirectTo('account');
        } catch (PDOException $e) {
            error_log('Login SQLSTATE: '.$e->getCode());
            flash('error','Login temporarily unavailable.');redirectTo('login');
        }
    }
    public function logout(): never
    {
        checkCsrf();
        $_SESSION=[];
        if (ini_get('session.use_cookies')) {
            $p=session_get_cookie_params();
            setcookie(session_name(),'',[
                'expires'=>time()-3600,'path'=>$p['path'],'domain'=>$p['domain'],
                'secure'=>$p['secure'],'httponly'=>true,'samesite'=>$p['samesite']??'Lax'
            ]);
        }
        session_destroy();
        redirectTo('login');
    }
}
