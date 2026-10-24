<?php
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Reservation.php';
class AdminController
{
    private User $users;
    private Reservation $reservations;
    public function __construct(PDO $pdo)
    {
        $this->users=new User($pdo);
        $this->reservations=new Reservation($pdo);
    }
    public function dashboard(): void
    {
        renderView('admin/dashboard',['stats'=>$this->reservations->statistics()]);
    }
    public function users(): void
    {
        renderView('admin/users/index',['users'=>$this->users->all()]);
    }
    public function changeUserStatus(array $actor): void
    {
        checkCsrf();
        $id=positiveId(postString('id',12));$active=postString('is_active',1);
        if (!$id || !in_array($active,['0','1'],true)) abortRequest(400,'Invalid user status.');
        $changed=$this->users->setActive($id,(int)$active,(int)$actor['id']);
        flash($changed?'success':'error',$changed?'Account status updated.':'No change. Administrators cannot be disabled here.');
        redirectTo('admin-users');
    }
    public function reservations(): void
    {
        $search=$_GET['search']??'';$status=$_GET['status']??'';
        if (!is_string($search)||strlen($search)>100||!is_string($status)||!in_array($status,['','Pending','Confirmed','Rejected','Cancelled'],true)) abortRequest(400,'Invalid search.');
        renderView('admin/reservations/index',[
            'rows'=>$this->reservations->forAdmin(trim($search),$status),
            'search'=>$search,'status'=>$status
        ]);
    }
    public function changeReservationStatus(): void
    {
        checkCsrf();
        $id=positiveId(postString('id',12));$status=postString('status',20);
        if (!$id||!in_array($status,['Confirmed','Rejected','Cancelled'],true)) abortRequest(400,'Invalid status.');
        $result=$this->reservations->setStatus($id,$status);
        flash($result==='success'?'success':'error',match($result){
            'success'=>'Reservation updated.','past'=>'Past reservations cannot change status.',
            'invalid_transition'=>'This status change is not allowed.',default=>'Reservation not found.'
        });
        redirectTo('admin-reservations');
    }
}
