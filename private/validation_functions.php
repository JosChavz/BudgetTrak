<?php
function validate_transaction_values(array $values, array $types, array $valid_categories) : bool | array {
    $temp_err = array();

    // Empty Array
    if (empty($values)) {
        return array('Please fill out all required fields');
    }

    // Text input are empty strings
    if (empty($values['name'])) {
        $temp_err[] = 'Name is required';
    }

    // Name has to be between 3 <= x <= 30
    if (!(3 <= strlen($values['name']) && strlen($values['name']) <= 30)) {
        $temp_err[] = 'Name has to be between 3 and 30 characters';
    }

    // Amount is empty or not a number
    if (!empty($values['amount'])) {
        if (!is_float($values['amount'] + 0) && !is_numeric($values['amount'])) {
            $temp_err[] = 'Amount is required';
        } elseif ($values['amount'] < 0) {
            $temp_err[] = 'Amount cannot be negative. That\'s what `type` is for!';
        }
    } else {
        $temp_err[] = 'Amount is required';
    }

    // Type is empty or not part of the ENUM
    if (empty($values['type']) || !in_array($values['type'], $types)) {
        $temp_err[] = 'Type is required';
    }

    // Category is empty or not part of the ENUM
    if (empty($values['category']) || !in_array($values['category'], $valid_categories)) {
        $temp_err[] = 'Category is required';
    }

    return $temp_err;
}