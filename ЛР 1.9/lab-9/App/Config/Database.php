<?php

namespace App\Config;

class Database {
    public $raw_connection;
    public $dbase_name = 'veer_vr_blog';

    private $dbase_host = 'pma.has.bik';
    private $dbase_login = 'student';
    private $dbase_password = 'student';
    

    public function __construct()
    {
        $this -> raw_connection = new \mysqli(
            $this -> dbase_host,
            $this -> dbase_login,
            $this -> dbase_password,
            $this -> dbase_name,
        );
        $this -> raw_connection -> set_charset('utf8mb4');
    }
    
    
    private function get_type(mixed $key): string
    {
        return match (gettype($key)) {
            'boolean', 'integer' => 'i',
            'string' => 's',
            'double' => 'd',
            'null' => 'i',
        };
    }
    
    private function prepared_query(string $query, string $types = '', array $values = []): array
    {
        $stmt = $this
            -> raw_connection
            -> prepare($query);
        if ($types) {
            $values = array_map(fn($item) => htmlspecialchars($item), $values);
            $stmt -> bind_param($types, ... $values);
        }
        $status = $stmt -> execute();
        
        return [$stmt -> get_result(), $status];
    }
    
    
    private function where_condition(array $where): array
    {
        $where_merged = '';
        $types = '';
        $vals = [];
        $end = end($where);
        
        foreach ($where as $cond) {
            if ($cond[2] !== 'NULL') {
                $where_merged .= "{$cond[0]} {$cond[1]} ?" . ($end != $cond ? (isset($cond[3]) ? " {$cond[3]} " : ' AND ') : '');
                $types .= $this -> get_type($cond[2]);
                $vals[] = $cond[2];
            } else {
                $where_merged .= "{$cond[0]} {$cond[1]} NULL";
            }
            
        }

        return [
            " WHERE {$where_merged}",
            $types,
            $vals,
        ];
    }
    
   
    public function create(string $table, array $data): bool
    {
        $sql = "INSERT INTO {$table} (";
        $keys = array_keys($data);
        $vals = array_values($data);
        $end = end($keys);
        $types = '';
        foreach($keys as $key) {
            $sql .= $end != $key ? "{$key}, " : "{$key}) VALUES (" . substr(str_repeat('?, ', count($keys)), 0, -2) . ');';
            $types .= $this -> get_type($data[$key]);
        }

        unset($keys, $end);

        $result = $this -> prepared_query($sql, $types, $vals);
        return $result[1];
    }
    
   
    public function read(string $table, ?array $cols = null, ?array $where = null, ?int $limit = null, ?int $offset = null): array
    {
        $sql = 'SELECT ' . ($cols === null ? '*' : implode(', ', $cols)) . " FROM {$table}";
        $types = '';
        $vals = [];

        if ($where) {
            $cond = $this -> where_condition($where);
            
            $sql .= $cond[0];
            $types .= $cond[1];
            $vals = $cond[2];

            unset($cond);
        }

        if ($limit) {
            $sql .= " LIMIT {$limit}";

            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }

        unset($table, $cols, $where, $limit, $offset);

        $query = $this -> prepared_query($sql, $types, $vals);
        if ($query[1]) {
            $query = $query[0];
            $returned = [];
            while ($row = $query -> fetch_assoc()) {
                $returned[] = $row;
            }

            return $returned;
           
        } else return [];
    }
    
   
    public function update(string $table, array $data, ?array $where = null): bool
    {
        $sql = "UPDATE {$table} SET ";
        $keys = array_keys($data);
        $vals = array_values($data);
        $end = end($keys);
        $types = '';
        foreach($keys as $key) {
            $sql .= "{$key} = ?" . ($end != $key ? ', ' : ' ');
            $types .= $this -> get_type($data[$key]);
        }

        unset($keys, $end);

        if ($where) {
            $where_result = $this -> where_condition($where);
            $sql .= $where_result[0];
            $types .= $where_result[1];
            $vals = array_merge($vals, $where_result[2]);

            unset($where_result);
        }

        $result = $this -> prepared_query($sql, $types, $vals);

        return $result[1];
    }
    
    
    public function delete(string $table, array $where = null): bool
    {
        $sql = "DELETE FROM {$table} ";
        $where_cond = [];

        if ($where) {
            $where_cond = $this -> where_condition($where);
            $sql .= $where_cond[0];
        }

        return $this -> prepared_query($sql, $where_cond[1] ?? '', $where_cond[2] ?? [])[1];
    }
}