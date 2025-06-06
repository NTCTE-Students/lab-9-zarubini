<?php 
namespace  App\Models;

class Post extends Model {
    protected string $table = 'posts';

    protected array $fillable = [
        'heading',
        'body',
        'user_id',
    ];

    public function __construct(?int $id = null) {
        parent::__construct();
        if ($id) {
            $this -> getById($id);
        }
    }

    public function  author(): User {
        return new User($this -> getAttribute('user_id'));
    }
}