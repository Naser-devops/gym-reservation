<?php
require_once __DIR__ . '/../Models/GymClass.php';
class ClassController
{
    private GymClass $gymClass;
    public function __construct(PDO $pdo) { $this->gymClass=new GymClass($pdo); }

    public function index(): void
    {
        $day=$_GET['date']??null;
        if ($day!==null && (!is_string($day) || !preg_match('/^\\d{4}-\\d{2}-\\d{2}$/D',$day) || !checkdate((int)substr($day,5,2),(int)substr($day,8,2),(int)substr($day,0,4)))) {
            abortRequest(400,'Invalid date.');
        }
        renderView('classes/index',['slots'=>$this->gymClass->available($day),'day'=>$day]);
    }
    public function adminIndex(): void
    {
        renderView('admin/classes/index',['slots'=>$this->gymClass->all()]);
    }
    public function createForm(): void
    {
        renderView('admin/classes/create');
    }
    public function editForm(): void
    {
        $id=positiveId($_GET['id']??null);
        if (!$id || !($slot=$this->gymClass->find($id))) abortRequest(404,'Class not found.');
        renderView('admin/classes/edit',['slot'=>$slot]);
    }
    private function inputs(): ?array
    {
        $name=postString('class_name',400);
        $description=postString('description',5000);
        $start=localDateTime(postString('starts_at',20));
        $end=localDateTime(postString('ends_at',20));
        $capacity=positiveId(postString('capacity',10));
        if ($name===null || $description===null || $start===null || $end===null || $capacity===null) return null;
        $name=trim($name);$description=trim($description);
        if ($name==='' || !preg_match('//u',$name) || preg_match_all('/./us',$name)>100 || strlen($description)>3000 || $capacity>1000 || !isFuture($start) || $end<=$start) return null;
        return [$name,$description,$start,$end,$capacity];
    }
    public function store(): void
    {
        checkCsrf();
        $values=$this->inputs();
        if (!$values) {flash('error','Invalid class details. Use future times, end after start, and capacity 1–1000.');redirectTo('admin-create-class');}
        $this->gymClass->create(...$values);
        flash('success','Class created.');redirectTo('admin-classes');
    }
    public function update(): void
    {
        checkCsrf();
        $id=positiveId(postString('id',12));
        $values=$this->inputs();
        if (!$id || !$values) {flash('error','Invalid class details.');redirectTo('admin-classes');}
        $result=$this->gymClass->update($id,...$values);
        $messages=['success'=>'Class updated.','capacity'=>'Capacity is below existing active bookings.','has_bookings'=>'Cannot change dates for a class with pending or confirmed bookings.','not_found'=>'Class not found.'];
        flash($result==='success'?'success':'error',$messages[$result]??'Could not update class.');
        redirectTo('admin-classes');
    }
    public function toggle(): void
    {
        checkCsrf();
        $id=positiveId(postString('id',12));$active=postString('is_active',1);
        if (!$id || !in_array($active,['0','1'],true)) abortRequest(400,'Invalid class status.');
        $ok=$this->gymClass->toggle($id,(int)$active);
        flash($ok?'success':'error',$ok?'Class status updated.':'Class not found or unchanged.');redirectTo('admin-classes');
    }
    public function delete(): void
    {
        checkCsrf();
        $id=positiveId(postString('id',12));
        if (!$id) abortRequest(400,'Invalid class ID.');
        $result=$this->gymClass->deleteEligible($id);
        flash($result==='success'?'success':'error',match($result){
            'success'=>'Class removed from the active catalog (history preserved).',
            'has_bookings'=>'Cannot delete a class with Pending or Confirmed reservations.',
            default=>'Class not found.'
        });
        redirectTo('admin-classes');
    }
}
