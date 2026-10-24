<?php
// ONE-TIME UPGRADE for existing reservation_db tables. CLI only. Back up DB first!
if (PHP_SAPI !== 'cli') {http_response_code(403);exit('CLI only');}
require_once __DIR__.'/../app/Core/Database.php';
function columnExists(PDO $pdo,string $table,string $column): bool {
    $q=$pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?');
    $q->execute([$table,$column]);return (bool)$q->fetchColumn();
}
function indexExists(PDO $pdo,string $table,string $index): bool {
    $q=$pdo->prepare('SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND INDEX_NAME=?');
    $q->execute([$table,$index]);return (bool)$q->fetchColumn();
}
try {
    $pdo=(new Database())->connect();
    foreach ([
        ['users','is_active','ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1'],
        ['time_slots','is_deleted','ALTER TABLE time_slots ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0'],
        ['reservations','note','ALTER TABLE reservations ADD COLUMN note TEXT NULL'],
        ['reservations','status',"ALTER TABLE reservations ADD COLUMN status ENUM('Pending','Confirmed','Rejected','Cancelled') NOT NULL DEFAULT 'Pending'"],
        ['reservations','created_at','ALTER TABLE reservations ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP']
    ] as [$table,$column,$sql]) {
        if (!columnExists($pdo,$table,$column)) { $pdo->exec($sql);echo "Added $table.$column\n"; }
        else echo "Already present $table.$column\n";
    }
    // No automatic deletion or merge: duplicates must be reviewed manually.
    if (!indexExists($pdo,'reservations','unique_user_slot')) {
        $dupes=$pdo->query('SELECT user_id,time_slot_id,COUNT(*) n FROM reservations GROUP BY user_id,time_slot_id HAVING n > 1 LIMIT 5')->fetchAll();
        if ($dupes) {
            fwrite(STDERR,"Duplicate reservations exist. Resolve carefully before creating UNIQUE index. Nothing was deleted.\n");
            exit(2);
        }
        $pdo->exec('ALTER TABLE reservations ADD UNIQUE KEY unique_user_slot (user_id,time_slot_id)');
        echo "Added unique_user_slot\n";
    }
    if (!indexExists($pdo,'reservations','idx_res_slot_status')) {
        $pdo->exec('CREATE INDEX idx_res_slot_status ON reservations (time_slot_id,status)');
    }
    if (!indexExists($pdo,'time_slots','idx_slot_availability')) {
        $pdo->exec('CREATE INDEX idx_slot_availability ON time_slots (is_deleted,is_active,starts_at)');
    }
    echo "Migration complete. Existing users and bookings preserved.\n";
} catch (Throwable $e) {
    fwrite(STDERR,"Migration failed: ".$e->getMessage()."\n");exit(1);
}
