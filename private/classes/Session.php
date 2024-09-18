<?php

namespace classes;

class Session
{
    private string $user_id;
    private string $user_name;
    private string $user_role = "";
    private int $last_login;
    const int MAX_LOGIN_AGE = 60 * 60 * 24;

    public function __construct() {
        session_start();
        $this->check_stored_login();
    }

    public function login($user): bool
    {
        if ($user) {
            session_regenerate_id();
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_name'] = $user->name;
            $_SESSION['user_role'] = $user->role;
            $_SESSION['last_login'] = time();
            $this->user_id = $user->id;
        }

        return true;
    }

    public function is_logged_in(): bool
    {
        return isset($this->user_id) && $this->last_login_is_recent();
    }

    public function logout(): bool
    {
        unset($_SESSION['user_id']);
        unset($this->user_id);
        unset($_SESSION['user_name']);
        unset($this->user_name);
        unset($_SESSION['user_role']);
        unset($this->user_role);
        unset($_SESSION['last_login']);
        unset($this->last_login);
        return true;
    }

    private function last_login_is_recent(): bool
    {
        if(!isset($this->last_login)) {
            return false;
        } elseif(($this->last_login + self::MAX_LOGIN_AGE) < time()) {
            return false;
        } else {
            return true;
        }
    }

    private function check_stored_login() : void {
        if (isset($_SESSION['user_id'])) {
            $this->user_id = $_SESSION['user_id'];
        }
        if (isset($_SESSION['user_name'])) {
            $this->user_name = $_SESSION['user_name'];
        }
        if (isset($_SESSION['user_role'])) {
            $this->user_role = $_SESSION['user_role'];
        }
        if (isset($_SESSION['last_login'])) {
            $this->last_login = $_SESSION['last_login'];
        }
    }

    public function message($msg="") {
        if(!empty($msg)) {
            // Then this is a "set" message
            $_SESSION['message'] = $msg;
            return true;
        } else {
            // Then this is a "get" message
            return $_SESSION['message'] ?? '';
        }
    }

    public function prompt_message() : string {
        if (!isset($_SESSION['message'])) {
            return '';
        }

        ob_start();
        ?>

        <div id="status-message">
            <?= $_SESSION['message'] ?>
        </div>

        <?php
        $this->clear_message();
        return ob_get_clean();
    }

    public function clear_message(): void
    {
        unset($_SESSION['message']);
    }

    /**
     * @return string
     */
    public function get_user_id(): string
    {
        return $this->user_id;
    }

    /**
     * @return string
     */
    public function get_user_name(): string
    {
        return $this->user_name;
    }

    /**
     * @return string
     */
    public function get_user_role(): string
    {
        return $this->user_role;
    }

}
