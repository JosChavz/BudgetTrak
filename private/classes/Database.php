<?php

namespace classes;

use AllowDynamicProperties;
use mysqli;
use mysqli_sql_exception;

#[AllowDynamicProperties] class Database
{
    static protected mysqli $database;
    static protected string $table_name;
    static protected array $columns = array();
    public array $errors = array();

    static public function set_database($database): void
    {
        self::$database = $database;
    }

    /**
     * @param $sql
     * @return Database[]
     */
    static function find_by_sql($sql): array
    {
        $result = self::$database->query($sql);

        if(!$result) {
            exit("Database query failed.");
        }

        // results into objects
        $object_array = [];
        while($record = $result->fetch_assoc()) {
            $object_array[] = self::instantiate($record);
        }

        $result->free();

        return $object_array;
    }

    /**
     * @return Database[]
     */
    static public function find_all() {
        $sql = "SELECT * FROM " . static::$table_name;
        return self::find_by_sql($sql);
    }

    static public function count_all() {
        $sql = "SELECT COUNT(*) FROM " . static::$table_name;
        $result = self::$database->query($sql);
        $row = $result->fetch_assoc();
        $result->free();
        return array_shift($row);
    }

    static public function count_all_from_user(int $user_id) {
        $sql = "SELECT COUNT(*) FROM " . static::$table_name . " WHERE uid=" . self::$database->escape_string($user_id);
        $result = self::$database->query($sql);
        $row = $result->fetch_assoc();
        $result->free();
        return array_shift($row);
    }

    /**
     * @param $user_id
     * @return Database[]
     */
    static function find_by_user_id($user_id) : array {
        $sql = "SELECT * FROM " . static::$table_name . " WHERE uid="
            . self::$database->escape_string($user_id) . ";";
        return self::find_by_sql($sql);
    }

    /**
     * @param $bank_id int
     * @param $user_id int
     * @return Database | null
     */
    static function find_by_id(int $bank_id, int $user_id) : Bank | null {
        $sql = "SELECT * FROM " . static::$table_name . " WHERE id="
            . self::$database->escape_string($bank_id)
            . " AND uid=" . self::$database->escape_string($user_id) . " LIMIT 1;";

        $res = self::find_by_sql($sql);
        return array_shift($res);
    }

    /**
     * @param array $requires
     * @return bool
     */
    protected function create(array $requires=["uid", "name", "type"]) : bool {
        $attributes = $this->attributes();

        $valid = $this->validate_attributes($requires, $attributes);
        if (!$valid) {
            $this->errors[] = 'Some attributes are missing';
            return false;
        }

        $sql = "INSERT INTO " . static::$table_name . " (";
        $sql .= join(', ', array_keys($attributes));
        $sql .= ") VALUES ('";
        $sql .= join("', '", array_values($attributes));
        $sql .= "')";

        try {
            $result = self::$database->query($sql);
        } catch(mysqli_sql_exception $e) {
            $err = "";
            // Detailed error
            // $err = "Error " . $e->getCode() . ":\n ";
            $err .= match ($e->getCode()) {
                1062 => "Duplicate entry",
                1452 => "No matching bank ID. Please contact support.",
                default => $e->getMessage(),
            };

            $this->errors[] = $err;
            $result = null;
        }

        if ($result) {
            $this->id = self::$database->insert_id;
            return true;
        }
        return false;
    }

    protected function update(array $requires=["uid", "name", "type"]) : bool {
        $attributes = $this->attributes();
        $valid = $this->validate_attributes($requires, $attributes);
        if (!$valid) {
            $this->errors[] = 'Some attributes are missing';
            return false;
        }

        $key_val_attr = $this->update_attributes($attributes);

        $sql = "UPDATE " . static::$table_name . " SET ";
        $sql .= implode(', ', $key_val_attr);
        $sql .= " WHERE id="
            . self::$database->escape_string($this->id)
            . " LIMIT 1;";

        try {
            self::$database->query($sql);
        } catch (mysqli_sql_exception $e) {
            $this->errors = "Unable to add transaction";
            // echo $e;
        }

        return (self::$database->affected_rows) >= 0;
    }

    protected function update_attributes(array $attr) : array {
        $temp_attributes = array();
        foreach ($attr as $key => $value) {
            $temp_attributes[] = "$key='" . self::$database->escape_string($value) ."'";
        }

        return $temp_attributes;
    }

    /**
     * @param array $requires
     * @return bool
     */
    public function save(array $requires=[]) : bool {
        if (isset($this->id)) {
            return $this->update($requires);
        } else {
            return $this->create($requires);
        }
    }

    public function delete() : bool {
        if (!isset($this->id)) {
            $this->errors[] = "No ID provided";
            return false;
        }

        $sql = "DELETE FROM " . static::$table_name . " WHERE id="
            . self::$database->escape_string($this->id)
            . " LIMIT 1;";

        self::$database->query($sql);
        return (self::$database->affected_rows) >= 0;
    }


    /**
     * All attributes must be present for CRUD to occur
     * @param array $requirements - keys that must be present
     * @param array $attr - the attributes that the object currently has
     * @return bool
     */
    protected function validate_attributes(array $requirements, array $attr) : bool {
        $attr_keys = array_keys($attr);

        foreach ($requirements as $requirement) {
            if (!in_array($requirement, $attr_keys)) {
                $this->errors[] = "Field $requirement is missing!";
                return false;
            }
        }

        return true;
    }

    static protected function instantiate($row): Database
    {
        $object = new static([]);
        foreach ($row as $key => $value) {
            if (property_exists($object, $key)) {
                $object->$key = $value;
            }
        }

        return $object;
    }

    public function attributes(): array
    {
        $attributes = [];
        foreach(static::$columns as $column) {
            if($column == 'id') { continue; }
            if (isset($this->$column)) {
                $attributes[$column] = self::$database->escape_string($this->$column);
            }
        }
        return $attributes;
    }

    /**
     * has_length('abcd', ['min' => 3, 'max' => 5])
     * validate string length
     * combines functions_greater_than, _less_than, _exactly
     * spaces count towards length
     * use trim() if spaces should not count
     * @param $value
     * @param $options
     * @return bool
     */
    protected function has_length($value, $options): bool {
        if(isset($options['min']) && !$this->has_length_greater_than($value, $options['min'] - 1)) {
          return false;
        } elseif(isset($options['max']) && !$this->has_length_less_than($value, $options['max'] + 1)) {
          return false;
        } elseif(isset($options['exact']) && !$this->has_length_exactly($value, $options['exact'])) {
          return false;
        } else {
        return true;
    }
  }

    // has_length_greater_than('abcd', 3)
    // * validate string length
    // * spaces count towards length
    // * use trim() if spaces should not count
    private function has_length_greater_than($value, $min): bool
    {
        $length = strlen($value);
        return $length > $min;
    }

    // has_length_less_than('abcd', 5)
    // * validate string length
    // * spaces count towards length
    // * use trim() if spaces should not count
    private function has_length_less_than($value, $max): bool
    {
        $length = strlen($value);
        return $length < $max;
    }

    // has_length_exactly('abcd', 4)
    // * validate string length
    // * spaces count towards length
    // * use trim() if spaces should not count
    private function has_length_exactly($value, $exact): bool
    {
        $length = strlen($value);
        return $length == $exact;
    }

    protected function add_errors(array $temp_errors) : void {
        $this->errors = array_merge($this->errors, $temp_errors);
    }

    protected function validate_const($val, array $const_arr, string $key) : bool {
        $const_keys = array_keys($const_arr);
        if (in_array($val, $const_keys)) {
            $this->$key = $val;
        } else {
            $this->errors[] = "Type $key is not in valid types";
            return false;
        }

        return true;
    }
}
