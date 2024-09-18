<?php

function select_expense_by_user_id($user_id): array
{
    global $db;

    $sql = "SELECT * FROM transactions WHERE uid = ? ORDER BY id DESC";

    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $arr = array();
    $res = $stmt->get_result();

    while($row = $res->fetch_assoc()) {
        $arr[] = $row;
    }

    $stmt->free_result();
    return $arr ?? array();
}

function select_all() : array {
    global $db;
    $res =  $db->query("SELECT * FROM transactions ORDER BY id DESC");
    $arr = array();
    while($row = $res->fetch_assoc()) {
        $arr[] = $row;
    }
    $res->free();
    return $arr ?? array();
}

function select_transaction_from_bank_month_year(int $bank_id, int $month, int $year) : array {
    global $db;
    $sql = "SELECT * FROM transactions WHERE bid = ? AND MONTH(created_at) = ? AND YEAR(created_at) = ? ORDER BY id DESC";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("iii", $bank_id, $month, $year);
    $stmt->execute();
    $arr = array();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()) {
        $arr[] = $row;
    }
    $res->free();
    return $arr ?? array();
}

function select_transaction_types(): array
{
    global $db;

    $query = "SELECT COLUMN_TYPE as Type FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'budget-tracker' AND TABLE_NAME = 'transactions' AND COLUMN_NAME = 'type';";

    $result = $db->query($query);

    $val = $result->fetch_all(MYSQLI_NUM)[0][0];

    preg_match('/enum\((.*)\)$/', $val, $matches);
    $vals = explode(',', $matches[1]);

    $result->free();

    $trimmed_vals = array();
    foreach($vals as $key => $value) {
        $value = trim($value, "'");
        $trimmed_vals[] = $value;
    }

    return $trimmed_vals;
}

function select_transaction_categories(): array
{
    global $db;

    $query = "SELECT COLUMN_TYPE as Categories FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'budget-tracker' AND TABLE_NAME = 'transactions' AND COLUMN_NAME = 'category';";

    $result = $db->query($query);

    $val = $result->fetch_all(MYSQLI_NUM)[0][0];

    preg_match('/enum\((.*)\)$/', $val, $matches);
    $vals = explode(',', $matches[1]);

    $result->free();

    $trimmed_vals = array();
    foreach($vals as $key => $value) {
        $value = trim($value, "'");
        $trimmed_vals[] = $value;
    }

    return $trimmed_vals;
}

/**
 * @throws Exception
 */
function select_transaction_where(int $transaction_id) : array {
    global $db;

    $stmt = $db->prepare("SELECT * FROM transactions WHERE id=?;");
    $stmt->bind_param('i', $transaction_id);

    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->fetch_assoc();
    $result->free();
    return $row ?? [];
}

function count_all_transactions(int $user_id) : int {
    global $db;
    $stmt = $db->prepare("SELECT COUNT(id) FROM transactions WHERE uid=?;");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $result->free();
    return $row['COUNT(id)'];
}

function count_transactions(int $bank_id, int $user_id) : int {
    global $db;
    $stmt = $db->prepare("SELECT COUNT(id) FROM transactions WHERE uid=? AND bid=?;");
    $stmt->bind_param("ii", $user_id, $bank_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $result->free();
    return $row['COUNT(id)'];
}

/**
 * @throws Exception
 */
function update_transaction(array $values, $user_id) : void {
    global $db;

    $query = "UPDATE transactions SET name=?, amount=?, type=?, bid=?, category=?";
    $amount = (float)$values['amount'];

    if (isset($values['description'])) $query .= ", description=?";

    $query .= " WHERE id=? AND uid=? LIMIT 1;";

    $stmt = $db->prepare($query);

    if (isset($values['description'])) {
        $stmt->bind_param('sdsissii', $values['name'], $amount, $values['type'], $values['bank_id'], $values['category'], $values['description'], $values['id'], $user_id);
    } else {
        $stmt->bind_param('sdsiisi', $values['name'], $amount, $values['type'], $values['id'], $values['bank_id'], $values['category'], $user_id);
    }

    $stmt->execute();
    $stmt->free_result();
}

function create_transaction(array $values, $user_id) : int {
    global $db;

    $amount = (float)$values['amount'];

    $query = "INSERT INTO transactions (name, amount, type, uid, bid, category";

    if (isset($values['description'])) {
        $query .= ", description";
    }

    $query .= " ) VALUES (?, ?, ?, ?, ?, ?";
    if (isset($values['description'])) {
        $query .= ", ?";
    }

    $query .= ");";

    $stmt = $db->prepare($query);

    if (isset($values['description'])) {
        $stmt->bind_param('sdsiiss', $values['name'], $amount, $values['type'], $user_id, $values['bank_id'], $values['category'], $values['description']);
    } else {
        $stmt->bind_param('sdsiis', $values['name'], $amount, $type, $uid, $values['bank_id'], $values['category']);
    }

    $stmt->execute();
    $stmt->free_result();
    return $db->insert_id;
}

function delete_transaction(int $transaction_id) : void {
    global $db;
    $query = "DELETE FROM transactions WHERE id=? LIMIT 1;";
    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $transaction_id);
    $stmt->execute();
    $stmt->free_result();
}