<?php

function select_banks_by_user_id($user_id) {
    global $db;

    $sql = "SELECT * FROM banks WHERE uid = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $temp_banks = array();

    while($row = $result->fetch_assoc()) {
        $temp_banks[] = $row;
    }

    $stmt->close();
    return $temp_banks;
}

function select_bank_by_id($bank_id, $user_id) {
    global $db;
    $sql = "SELECT * FROM banks WHERE id = ? AND uid = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ii", $bank_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $temp_bank = $result->fetch_assoc();
    $stmt->close();

    return $temp_bank;
}

/**
 * @param $bank array - An associative array with the following schema:
 *      * name => string
 *      * type => ENUM(DEBIT, CREDIT)
 * @param $user_id string - User ID
 * @return int - The ID of the bank, -1 for false
 */
function create_bank($bank, $user_id) : int {
    global $db;

    $bank_name = $db->real_escape_string($bank['name']);
    $bank_type = $db->real_escape_string($bank['type']);

    $sql = "INSERT INTO banks (uid, name, type) VALUES (?, ?, ?);";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("iss", $user_id, $bank_name, $bank_type);
    try {
        $stmt->execute();
    } catch(Exception $e) {
        return -1;
    }

    $stmt->close();

    return $db->insert_id;
}

/**
 * @param $bank array - An associative array with the following schema:
 *      * id => int
 *      * name => string
 *      * type => ENUM(CREDIT, DEBIT)
 * @param $user_id
 * @return bool - Whether the bank updated successfully or not
 */
function update_bank($bank, $user_id) : bool {
    global $db;

    $bank_name = $db->real_escape_string($bank['name']);
    $bank_type = $db->real_escape_string($bank['type']);

    $sql = "UPDATE banks SET name = ?, type = ? WHERE id = ? AND uid = ?;";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ssii", $bank_name, $bank_type, $bank['id'], $user_id);
    $stmt->execute();
    $stmt->close();

    echo $db->affected_rows;
    echo $db->error;
    return ($db->affected_rows) === 1;
}

function delete_bank($bank_id, $user_id) : bool {
    global $db;
    $sql = "DELETE FROM banks WHERE id = ? AND uid = ?;";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ii", $bank_id, $user_id);
    $stmt->execute();
    $stmt->close();

    return $db->affected_rows > 0;
}