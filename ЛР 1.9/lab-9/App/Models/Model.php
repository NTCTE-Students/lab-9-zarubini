<?php 

namespace App\Models;

use App\Config\Database;

abstract class Model {
    protected Database $database;
    protected string $table;
    protected string $key = 'id';
    protected array $fillable = [];
    protected array $hidden = [];
    protected ?array $current_record = null;
    public function __construct(){
        $this->database = new Database();
    }
    public function getVisibleColums(): array {
        return array_filter(array_diff($this -> fillable, $this -> hidden));
    }
    public function getById(string|int $id): Model {
        $record = $this -> database
        -> read($this -> table, where: [[$this -> key, '=', $id]]);
        if (!empty($record)) {
            $this -> current_record = $record[0];
        }
        return $this;
    }
    public function getByWhere(array $where): Model {
        $database = $this -> database;
        $record = $this -> database
            ->read($this -> table, where: $where);
        if (!empty($record)) {
            $this -> current_record = $record[0];
        }
        return $this;
    }
    public function fillAndSave(array $data): Model {
        if (!empty($this -> current_record)) {
            $id = $this -> current_record[$this -> key];
            $data = array_intersect_key($data, array_flip( $this -> fillable));
            if ($this -> database -> update($this -> table, $data, [[$this ->key, '=', $id]])) {
                $this -> current_record = array_merge($this -> current_record, $data);
            }
        }
        return $this;
    }
    public function delete(): Model
    {
        if (!empty($this -> current_record)) {
            $id = $this -> current_record[$this -> key];
            if ($this -> database -> delete($this -> table, [[$this -> key, '=', $id]])) {
                $this -> current_record = null;
            }
        }

        return $this;
    }
    public function createAndSet(array $data): Model {
        $data = array_intersect_key($data, array_flip( $this -> fillable));
        if ($this -> database -> create($this -> table, $data)) {
            $this -> current_record = array_merge([
                $this -> key => $this -> database -> raw_connection -> insert_id,
            ], $data);
        }
        return $this;   
    }
    public function getAttribute(string $attribute): mixed {
        return $this -> current_record[$attribute] ?? null;
    }
}
