<?php 
namespace App\Models;
class User extends Model {
    protected string $table = 'users';
    protected array $fillable = [
        'firstname',
        'lastname',
        'patronymic',
        'email',
        'password_hash',
    ];
    protected array $hidden = [
        'password_hash',
        'created_at',
        'updated_at',
    ] ;
    public function __construct(?int $id = null) {
        parent::__construct();
        if ($id) {
            $this -> getById($id);
        }
    }
    public function getByEmail(string $email): User {
        return $this -> getByWhere([['email', '=', $email]]);
    }

    private function setPassword(array &$data): void {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);
    }
    public function fillAndSave(array $data): Model {
        $this -> setPassword($data);
        return parent::fillAndSave($data);
    }
    public function createAndSet(array $data): Model {
        $this -> setPassword($data);
        return parent::createAndSet($data);
    }

    public function post(): array|null {
        if ($this -> current_record['id'] !== null){
            return $this -> database -> read('posts', where: [['user_id', '=', $this -> current_record['id']]]);
        } else {
            return null;
        }
    }
}