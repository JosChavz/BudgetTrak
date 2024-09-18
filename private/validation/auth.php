<?php

function validate_login($username, $password): array
{
    $temp_errors = array();

    if (empty($username) || empty($password)) {
        $temp_errors[] = "Username and Password are required";
    }

    return $temp_errors;
}