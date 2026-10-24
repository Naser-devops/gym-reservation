<?php
class Reservation
{
    private PDO $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function book(int $userId, int $slotId, string $note): string
    {
        $this->pdo->beginTransaction();
        try {
            // All bookings of a slot serialize on the same InnoDB row lock.
            $stmt = $this->pdo->prepare('SELECT id, capacity FROM time_slots WHERE id=? AND is_active=1 AND is_deleted=0 AND starts_at > ? FOR UPDATE');
            $stmt->execute([$slotId, date('Y-m-d H:i:s')]);
            $slot = $stmt->fetch();
            if (!$slot) { $this->pdo->rollBack(); return 'unavailable'; }

            $stmt = $this->pdo->prepare('SELECT id FROM reservations WHERE user_id=? AND time_slot_id=? LIMIT 1');
            $stmt->execute([$userId,$slotId]);
            if ($stmt->fetch()) { $this->pdo->rollBack(); return 'duplicate'; }

            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reservations WHERE time_slot_id=? AND status IN ('Pending','Confirmed')");
            $stmt->execute([$slotId]);
            if ((int)$stmt->fetchColumn() >= (int)$slot['capacity']) { $this->pdo->rollBack(); return 'full'; }

            $stmt = $this->pdo->prepare("INSERT INTO reservations (user_id,time_slot_id,note,status) VALUES (?,?,?,'Pending')");
            $stmt->execute([$userId,$slotId,$note === '' ? null : $note]);
            $this->pdo->commit(); return 'success';
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }
    public function forUser(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT r.id,r.note,r.status,r.created_at,s.class_name,s.starts_at,s.ends_at FROM reservations r JOIN time_slots s ON s.id=r.time_slot_id WHERE r.user_id=? ORDER BY s.starts_at DESC,r.id DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    public function cancelMine(int $userId, int $reservationId): string
    {
        $this->pdo->beginTransaction();
        try {
            // Lock the slot first, then the reservation, matching book() lock order.
            $stmt=$this->pdo->prepare('SELECT time_slot_id FROM reservations WHERE id=? AND user_id=?');
            $stmt->execute([$reservationId,$userId]);
            $slotId=$stmt->fetchColumn();
            if (!$slotId) { $this->pdo->rollBack(); return 'not_found'; }
            $stmt=$this->pdo->prepare('SELECT starts_at FROM time_slots WHERE id=? FOR UPDATE');
            $stmt->execute([$slotId]);
            $startsAt=$stmt->fetchColumn();
            $stmt=$this->pdo->prepare('SELECT status FROM reservations WHERE id=? AND user_id=? FOR UPDATE');
            $stmt->execute([$reservationId,$userId]);
            $status=$stmt->fetchColumn();
            if (!$startsAt || !$status) { $this->pdo->rollBack(); return 'not_found'; }
            if (!in_array($status,['Pending','Confirmed'],true) || !isFuture($startsAt)) {
                $this->pdo->rollBack(); return 'not_allowed';
            }
            $stmt=$this->pdo->prepare("UPDATE reservations SET status='Cancelled' WHERE id=? AND user_id=?");
            $stmt->execute([$reservationId,$userId]);
            $this->pdo->commit(); return 'success';
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }
    public function forAdmin(string $search='', string $status=''): array
    {
        $sql = 'SELECT r.id,r.note,r.status,r.created_at,u.full_name,u.email,s.class_name,s.starts_at,s.ends_at FROM reservations r JOIN users u ON u.id=r.user_id JOIN time_slots s ON s.id=r.time_slot_id WHERE 1=1';
        $params=[];
        if ($search !== '') {
            $sql .= " AND (u.full_name LIKE ? ESCAPE '!' OR u.email LIKE ? ESCAPE '!')";
            $escaped = str_replace(['!','%','_'],['!!','!%','!_'],$search);
            $params[]='%'.$escaped.'%'; $params[]='%'.$escaped.'%';
        }
        if ($status !== '') { $sql.=' AND r.status=?'; $params[]=$status; }
        $sql .= ' ORDER BY s.starts_at DESC,r.id DESC LIMIT 1000';
        $stmt=$this->pdo->prepare($sql);$stmt->execute($params);return $stmt->fetchAll();
    }
    public function setStatus(int $id, string $newStatus): string
    {
        if (!in_array($newStatus,['Confirmed','Rejected','Cancelled'],true)) return 'invalid';
        $this->pdo->beginTransaction();
        try {
            $stmt=$this->pdo->prepare('SELECT time_slot_id FROM reservations WHERE id=?');
            $stmt->execute([$id]);
            $slotId=$stmt->fetchColumn();
            if (!$slotId) { $this->pdo->rollBack();return 'not_found'; }
            // Same row-lock order as book() and cancelMine(): slot before reservation.
            $stmt=$this->pdo->prepare('SELECT starts_at FROM time_slots WHERE id=? FOR UPDATE');
            $stmt->execute([$slotId]);
            $startsAt=$stmt->fetchColumn();
            $stmt=$this->pdo->prepare('SELECT status FROM reservations WHERE id=? FOR UPDATE');
            $stmt->execute([$id]);
            $oldStatus=$stmt->fetchColumn();
            if (!$startsAt || !$oldStatus) { $this->pdo->rollBack();return 'not_found'; }
            if (!isFuture($startsAt)) { $this->pdo->rollBack();return 'past'; }
            $allowed = ($oldStatus==='Pending' && in_array($newStatus,['Confirmed','Rejected','Cancelled'],true))
                || ($oldStatus==='Confirmed' && $newStatus==='Cancelled');
            if (!$allowed) { $this->pdo->rollBack();return 'invalid_transition'; }
            $stmt=$this->pdo->prepare('UPDATE reservations SET status=? WHERE id=?');
            $stmt->execute([$newStatus,$id]);
            $this->pdo->commit();return 'success';
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }
    public function statistics(): array
    {
        $stats=$this->pdo->query("SELECT COUNT(*) AS total, COALESCE(SUM(status='Pending'),0) AS pending, COALESCE(SUM(status='Confirmed'),0) AS confirmed, COALESCE(SUM(status='Rejected'),0) AS rejected, COALESCE(SUM(status='Cancelled'),0) AS cancelled FROM reservations")->fetch();
        $stats['users']=(int)$this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        return $stats;
    }
}
