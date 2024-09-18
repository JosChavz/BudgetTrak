<?php

namespace classes;

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

}
