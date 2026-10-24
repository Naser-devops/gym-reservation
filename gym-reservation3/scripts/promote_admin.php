<?php
// Promotes a REAL existing registered account, with no default/admin test credentials.
if (PHP_SAPI!=='cli') {http_response_code(403);exit('CLI only');}
require_once __DIR__.'/../app/Core/Database.php';
$email=$argv[1]??'';
if (!filter_var($email,FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR,"Usage: php scripts/promote_admin.php email@example.com\n");exit(1);
}
try {
    $pdo=(new Database())->connect();
    $stmt=$pdo->prepare("UPDATE users SET role='admin' WHERE email=? AND is_active=1");
    $stmt->execute([$email]);
    if ($stmt->rowCount()) echo "Promoted existing active account: $email\n";
    else echo "No row changed. Account may not exist, be inactive, or already be admin.\n";
} catch (Throwable $e) {
    fwrite(STDERR,"Promotion failed: ".$e->getMessage()."\n");exit(1);
}
