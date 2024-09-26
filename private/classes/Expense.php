<?php

namespace classes;

use http\Exception\RuntimeException;

class Expense extends Database
{
    static protected array $columns = ['id', 'uid', 'bid', 'name', 'amount', 'description', 'type', 'category', 'created_at'];
    static protected string $table_name = "transactions";
    const array TYPE = [
        'EXPENSE' => 'Expense',
        'INCOME' => 'Income',
    ];
    const array CATEGORY = [
        'DINING' => 'Dining',
        'ENTERTAINMENT' => 'Entertainment',
        'GROCERIES' => 'Groceries',
        'BILLS' => 'Bills',
        'SHOPPING' => 'Shopping',
        'TRANSPORTATION' => 'Transportation',
        'WORK' => 'Work',
        'TRAVEL' => 'Travel',
    ];
    public int $id;
    public int $uid;
    public int $bid;
    public string $name;
    public float $amount;
    public string $description;
    public string $type;
    public string $category;
    public string $created_at;

    public function __construct(array $args = []) {
        if (isset($args['id'])) {
            $this->id = $args['id'];
        }
        if (isset($args['uid'])) {
            $this->uid = $args['uid'];
        }
        if (isset($args['bid'])) {
            $this->set_bank_id($args['bid']);
        }
        if (isset($args['name'])) {
            $this->set_name($args['name']);
        }
        if (isset($args['amount'])) {
            $this->set_amount($args['amount']);
        }
        if (isset($args['description'])) {
            $this->description = $args['description'];
        }
        if (isset($args['type'])) {
            $this->validate_const($args['type'], self::TYPE, "type");
        }
        if (isset($args['category'])) {
            $this->validate_const($args['category'], self::CATEGORY, "category");
        }
        if (isset($args['created_at'])) {
            $this->created_at = $args['created_at'];
        }
    }

    public function save(array $requires=["uid", "bid", "name", "amount", "type", "category"]) : bool {
        $this->created_at = date("Y-m-d");
        return parent::save($requires);
    }

    public function set_bank_id(int|string $bid) : bool
    {
        var_dump($bid);
        if ($bid !== "CASH") {
            $this->bid = $bid;
        }

        return true;
    }

    public function set_name(string $name) {
        $name = trim($name);
        $temp_errors = array();

        if (empty($name)) {
            $temp_errors[] = "Expense name cannot be empty";
        } else if (!preg_match("/^[a-zA-Z ]{2,40}$/", $name)) {
            $temp_errors[] = "Invalid expense name. Please be a non-numerical name with a size of 2-40 characters";
        } else {
            $this->name = $name;
        }

        $this->add_errors($temp_errors);
        return count($temp_errors) == 0;
    }

    public function set_amount(int|float $amount) {
        $amount = trim($amount);
        $temp_errors = array();

        if (empty($amount)) {
            $temp_errors[] = "The amount cannot be empty.";
        } else if ($amount < 0) {
            $temp_errors[] = "Must be a positive value. Did you mean income if using negative?";
        } else {
            $this->amount = (float) $amount;
        }

        $this->add_errors($temp_errors);
        return count($temp_errors) == 0;
    }

    public static function count_all_from_user_and_bank(int $user_id, int $bank_id) : int {
        $sql = "SELECT COUNT(*) FROM " . static::$table_name . " WHERE uid=" . self::$database->escape_string($user_id) . " AND bid=" . self::$database->escape_string($bank_id);
        $result = self::$database->query($sql);
        $row = $result->fetch_assoc();
        $result->free();
        return array_shift($row);
    }

    public static function select_from_date(int $bank_id, int $month, int $year, array $args=[]) : array {
        $sql = "SELECT * FROM transactions WHERE bid = " . self::$database->escape_string($bank_id) .
            " AND MONTH(created_at) = " . self::$database->escape_string($month) . " AND YEAR(created_at) = " .
            self::$database->escape_string($year) . " ORDER BY created_at DESC";

        if (isset($args['limit'])) {
            $sql .= " LIMIT " . self::$database->escape_string($args['limit']);
        }
        if (isset($args['offset'])) {
            $sql .= " OFFSET " . self::$database->escape_string($args['offset']);
        }
        return self::find_by_sql($sql);
    }

    public static function select_summation(int $user_id, string $type, array $args=[]) : string {
        $sql = "SELECT SUM(amount) FROM transactions WHERE uid=" . $user_id . " AND type='" . self::$database->escape_string($type) . "'";
        if (isset($args['bank_id'])) {
            $sql .= " AND bid=" . self::$database->escape_string($args['bank_id']);
        }
        if (isset($args['year'])) {
            $sql .= " AND YEAR(created_at) = " . self::$database->escape_string($args['year']);
        }
        if (isset($args['month'])) {
            $sql .= " AND MONTH(created_at) = " . self::$database->escape_string($args['month']);
        }

        $result = self::$database->query($sql);
        $row = $result->fetch_assoc();
        $result->free();
        return array_shift($row) ?? 0;
    }

    public static function select_all_type_summation(int $user_id, array $cats=[], array $args=[]) : array {
        $type_summations = array();

        // defaults to CATEGORY
        if (empty($cats)) {
            $cats = static::CATEGORY;
        }

        foreach ($cats as $cat) {
            $sql = "SELECT SUM(amount) FROM transactions WHERE uid=" . $user_id . " AND category='" . self::$database->escape_string($cat) . "'";

            if (isset($args['bank_id'])) {
                $sql .= " AND bid=" . self::$database->escape_string($args['bank_id']);
            }
            if (isset($args['year'])) {
                $sql .= " AND YEAR(created_at) = " . self::$database->escape_string($args['year']);
            }
            if (isset($args['month'])) {
                $sql .= " AND MONTH(created_at) = " . self::$database->escape_string($args['month']);
            }

            $result = self::$database->query($sql);
            $row = $result->fetch_assoc();
            $result->free();
            $curr_sum = array_shift($row);

            // Only add it if exists
            if (isset($curr_sum)) {
                $type_summations[$cat] = $curr_sum;
            }
        }

        return $type_summations;
    }

}
