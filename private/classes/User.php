<?php

namespace classes;

class User extends Database
{
    static protected string $table_name = "users";
    static protected array $columns = ['id', 'name', 'email', 'hashed_password', 'role'];
    const array USER_ROLE = [
        'ADMIN' => 'admin',
        'USER' => 'user',
    ];
    public string $id;
    public string $name;
    public string $email;
    public string $password;
    public string $confirm_password;

    protected string $hashed_password;
    public string $role = User::USER_ROLE['USER'];
    protected bool $password_required = true;

    public function __construct(array $args=[]) {
        if (array_key_exists('id', $args)) {
            $this->id = $args['id'];
        }
        if (array_key_exists('name', $args)) {
            $this->set_name($args['name']);
        }
        if (array_key_exists('password', $args)) {
            $this->set_password($args['password']);
        }
        if (array_key_exists('email', $args)) {
            $this->set_email($args['email']);
        }
        if (array_key_exists('role', $args)) {
            $this->set_role($args['role']);
        }
        if (array_key_exists('confirm_password', $args)) {
            $this->set_confirm_password($args['confirm_password']);
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function set_name(string $name): bool
    {
        $isValid = true;
        $name = trim($name);
        $temp_errors = array();

        if (empty($name)) {
            $isValid = false;
            $temp_errors[] = "User name cannot be empty";
        } else if (!preg_match("/^[a-zA-Z ]{4,40}$/", $name)) {
            $isValid = false;
            $temp_errors[] = "Invalid user name. Please be a non-numerical name with a size of 4-40 characters";
        } else {
            $this->name = $name;
        }

        $this->add_errors($temp_errors);
        return $isValid;
    }

    public function set_password(string $password): bool {
        $password = trim($password);
        $temp_errors = array();

        if($this->password_required) {
            if(empty($password)) {
                $temp_errors[] = "Password cannot be blank.";
            } elseif (!$this->has_length($password, array('min' => 12))) {
                $temp_errors[] = "Password must contain 12 or more characters";
            } elseif (!preg_match('/[A-Z]/', $password)) {
                $temp_errors[] = "Password must contain at least 1 uppercase letter";
            } elseif (!preg_match('/[a-z]/', $password)) {
                $temp_errors[] = "Password must contain at least 1 lowercase letter";
            } elseif (!preg_match('/[0-9]/', $password)) {
                $temp_errors[] = "Password must contain at least 1 number";
            } elseif (!preg_match('/[^A-Za-z0-9\s]/', $password)) {
                $temp_errors[] = "Password must contain at least 1 symbol";
            }
        }

        if (empty($temp_errors)) {
            $this->password = $password;
        }

        $this->add_errors($temp_errors);
        return count($temp_errors) === 0;
    }

    public function set_confirm_password(string $confirm_password): bool
    {
        $confirm_password = trim($confirm_password);
        $temp_errors = array();

        if (!isset($this->password)) {
            return false;
        }

        if (empty($confirm_password)) {
            $temp_errors[] = "Confirm password cannot be empty";
        } else if (($this->password ?? '') !== $confirm_password) {
            $temp_errors[] = "Passwords do not match";
        } else {
            $this->confirm_password = $confirm_password;
        }

        $this->add_errors($temp_errors);
        return count($temp_errors) === 0;
    }

    public function set_email(string $email): bool {
        $email = trim($email);
        $temp_errors = array();

        if (empty($email)) {
            $temp_errors[] = "Email cannot be empty";
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $temp_errors[] = "Invalid email format";
        } else {
            $this->email = $email;
        }

        $this->add_errors($temp_errors);
        return count($temp_errors) === 0;
    }

    public function set_role(string $role): bool {
        $role = trim($role);
        $temp_errors = array();
        if (empty($role)) {
            $temp_errors[] = "Role cannot be empty";
        } else if (!in_array($role, self::USER_ROLE)) {
            $temp_errors[] = "Invalid role";
        } else {
            $this->role = $role;
        }

        $this->add_errors($temp_errors);
        return count($temp_errors) === 0;
    }

    protected function set_hashed_password(): void
    {
        $this->hashed_password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    public function verify_password($password): bool
    {
        return password_verify($password, $this->hashed_password);
    }

    protected function create(array $requires = ['name', 'email', 'hashed_password', 'role']): bool
    {
        $this->set_hashed_password();
        return parent::create($requires);
    }

    protected function update(array $requires = ['name', 'email', 'hashed_password', 'role']): bool
    {
        if($this->password != '') {
            $this->set_hashed_password();
            // validate password
        } else {
            // password not being updated, skip hashing and validation
            $this->password_required = false;
        }
        return parent::update($requires);
    }

    static public function find_by_username($username): Database | null
    {
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE email='" . self::$database->escape_string($username) . "'";
        $obj_array = static::find_by_sql($sql);
        if(!empty($obj_array)) {
            return array_shift($obj_array);
        } else {
            return null;
        }
    }

}
