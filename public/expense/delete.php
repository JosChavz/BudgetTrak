<?php

require_once '../../private/initialize.php';
require_once ROOT . '/private/sql/expense.php';

$db = db_connect();

if (!isset($_GET['id'])) {
    h(HTTP . '/expense' );
    exit;
}

$id = $_GET['id'];

delete_transaction($id);

h(HTTP . '/expense');
exit();