<?php

namespace   App\Models;

class Comment extends Model {
    protected string $table = 'comments';

    protected array $fillable = [
        'body',
        'post_id', 
        'user_id', 
        'parent_id',
    ];
    protected array $hidden = [
        'created_at', 
        'updated_at',
    ];
    public function __construct(?int $id = null) {
        parent::__construct();
        if ($id) {
            $this -> getById($id);
        }
    }
    public function author(): ?User {
        return new User($this -> getAttribute('user_id'));   
    }
    public function post(): ?Post {
        return new Post($this -> getAttribute('post_id'));
    }
    public function parentComment(): ?Comment {
        return new Comment($this -> getAttribute('parent_id'));
    }
    public function haveChildren(): bool {
        if ($this -> current_record !== null) {
            return !empty($this -> database -> read($this -> table, ['id'], [['parent_id', '=', $this -> getAttribute('id')]], 1));
        } else return false;
    }
}