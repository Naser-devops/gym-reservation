<?php
require_once __DIR__ . '/../Models/Reservation.php';
class ReservationController
{
    private Reservation $reservation;
    public function __construct(PDO $pdo) { $this->reservation=new Reservation($pdo); }
    public function index(array $user): void
    {
        renderView('reservations/index',['reservations'=>$this->reservation->forUser((int)$user['id'])]);
    }
    public function store(array $user): void
    {
        checkCsrf();
        $slotId=positiveId(postString('time_slot_id',12));
        $note=array_key_exists('note',$_POST) ? postString('note',1500) : ''; // Optional note
        if (!$slotId || $note===null || strlen(trim($note))>1000) abortRequest(400,'Invalid booking information.');
        try {
            $result=$this->reservation->book((int)$user['id'],$slotId,trim($note));
        } catch (PDOException $e) {
            if (($e->errorInfo[1]??null)===1062) $result='duplicate';
            else throw $e;
        }
        $messages=['success'=>'Reservation submitted with Pending status.','duplicate'=>'You have already reserved this slot (including past cancelled/rejected bookings).','full'=>'No remaining capacity.','unavailable'=>'Slot no longer available.'];
        flash($result==='success'?'success':'error',$messages[$result]??'Could not reserve this slot.');
        redirectTo('classes');
    }
    public function cancel(array $user): void
    {
        checkCsrf();
        $id=positiveId(postString('id',12));
        if (!$id) abortRequest(400,'Invalid reservation ID.');
        $result=$this->reservation->cancelMine((int)$user['id'],$id);
        $messages=['success'=>'Reservation cancelled.','not_found'=>'Reservation not found.','not_allowed'=>'This reservation cannot be cancelled.'];
        flash($result==='success'?'success':'error',$messages[$result]);
        redirectTo('my-reservations');
    }
}
