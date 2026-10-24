<?php
class GymClass
{
    private PDO $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function available(?string $day = null): array
    {
        $sql = "SELECT s.id, s.class_name, s.description, s.starts_at, s.ends_at, s.capacity,
                (SELECT COUNT(*) FROM reservations r WHERE r.time_slot_id = s.id AND r.status IN ('Pending','Confirmed')) AS reserved_count
                FROM time_slots s WHERE s.is_active=1 AND s.is_deleted=0 AND s.starts_at > ?";
        $params = [date('Y-m-d H:i:s')];
        if ($day !== null) {
            $next = (new DateTimeImmutable($day))->modify('+1 day')->format('Y-m-d');
            $sql .= ' AND s.starts_at >= ? AND s.starts_at < ?';
            $params[] = $day . ' 00:00:00';
            $params[] = $next . ' 00:00:00';
        }
        $sql .= ' ORDER BY s.starts_at ASC, s.id ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    public function all(): array
    {
        return $this->pdo->query("SELECT s.*, (SELECT COUNT(*) FROM reservations r WHERE r.time_slot_id=s.id AND r.status IN ('Pending','Confirmed')) AS reserved_count FROM time_slots s WHERE s.is_deleted=0 ORDER BY s.starts_at DESC, s.id DESC")->fetchAll();
    }
    public function find(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM time_slots WHERE id=? AND is_deleted=0");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function create(string $name, string $description, string $start, string $end, int $capacity): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO time_slots (class_name, description, starts_at, ends_at, capacity, is_active, is_deleted) VALUES (?,?,?,?,?,1,0)');
        $stmt->execute([$name, $description, $start, $end, $capacity]);
    }
    public function update(int $id, string $name, string $description, string $start, string $end, int $capacity): string
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('SELECT starts_at, ends_at FROM time_slots WHERE id = ? AND is_deleted=0 FOR UPDATE');
            $stmt->execute([$id]);
            $old = $stmt->fetch();
            if (!$old) { $this->pdo->rollBack(); return 'not_found'; }
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reservations WHERE time_slot_id=? AND status IN ('Pending','Confirmed')");
            $stmt->execute([$id]);
            $active = (int)$stmt->fetchColumn();
            if ($active > $capacity) { $this->pdo->rollBack(); return 'capacity'; }
            if ($active > 0 && ($old['starts_at'] !== $start || $old['ends_at'] !== $end)) {
                $this->pdo->rollBack(); return 'has_bookings';
            }
            $stmt = $this->pdo->prepare('UPDATE time_slots SET class_name=?,description=?,starts_at=?,ends_at=?,capacity=? WHERE id=? AND is_deleted=0');
            $stmt->execute([$name,$description,$start,$end,$capacity,$id]);
            $this->pdo->commit(); return 'success';
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }
    public function toggle(int $id, int $active): bool
    {
        $stmt = $this->pdo->prepare('UPDATE time_slots SET is_active=? WHERE id=? AND is_deleted=0');
        $stmt->execute([$active,$id]);
        return $stmt->rowCount() > 0;
    }
    public function deleteEligible(int $id): string
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('SELECT id FROM time_slots WHERE id=? AND is_deleted=0 FOR UPDATE');
            $stmt->execute([$id]);
            if (!$stmt->fetch()) { $this->pdo->rollBack(); return 'not_found'; }
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reservations WHERE time_slot_id=? AND status IN ('Pending','Confirmed')");
            $stmt->execute([$id]);
            if ((int)$stmt->fetchColumn() > 0) { $this->pdo->rollBack(); return 'has_bookings'; }
            // Soft delete keeps historical rejected/cancelled reservations intact.
            $stmt = $this->pdo->prepare('UPDATE time_slots SET is_deleted=1, is_active=0 WHERE id=?');
            $stmt->execute([$id]);
            $this->pdo->commit(); return 'success';
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }
}
