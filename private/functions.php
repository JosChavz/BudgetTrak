<?php

function h(string $url): void
{
    header("Location: $url");
}

function is_post_request(): bool
{
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

function log_in_user($user) {
    session_regenerate_id();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['last_login'] = time();
    return true;
}

function log_out_user() {
//    session_destroy();
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_role']);
    unset($_SESSION['last_login']);
    return true;
}