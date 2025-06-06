<?php 
namespace App\Models;

class Like extends Model {
    protected string $table = 'likes';
    protected array $fillable = [
        'user_id',
        'likeable_type',
        'likeable_id',
    ];

    protected array $hidden = [
        'created_at'
    ];



    public function author(): User {
        return new User($this -> getAttribute('user_id'));
    }

    public function getRelated(): Model|\Exception {
        $type = $this -> getAttribute('likeable_type');
        $id = $this -> getAttribute('likeable_id');

        return match ($type) {
            'post' => new Post($id),
            'comment' => new Comment($id),
            default => throw new \Exception("Unknow likeable type: {$type}"),
        };
    }
    public function countlike(string $type = 'posts') {
        $likes = $this -> database -> read('likes', ['COUNT(likeable_id) AS likes'], [['likeable_id', '=', $_GET['id']], ['likeable_type', '=', $type]]);

        return($likes[0]['likes']);
    }
}
