<?php

/**
 * @param $bank array - An associative array with the following schema:
 *      * name => string
 *      * type => ENUM(CREDIT, DEBIT)
 * @return bool
 */
function validate_bank($bank) : bool {
    global $errors;
    $isValid = true;

    if (empty($bank['name'])) {
        $isValid = false;
        $errors[] = "Bank name cannot be empty";
    } else if (!preg_match("/^[a-zA-Z ]{4,40}$/", $bank['name'])) {
        $isValid = false;
        $errors[] = "Invalid bank name. Please be a non-numerical name with a size of 4-40 characters";
    }

    if (empty($bank['type'])) {
        $isValid = false;
        $errors[] = "Bank type cannot be empty";
    }

    return $isValid;
}