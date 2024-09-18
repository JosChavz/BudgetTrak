<?php

function db_connect(): mysqli
{
    global $env;
    return new mysqli('localhost', $env['DB_USER'], $env['DB_PASS'], $env['DB_TABLE']);
}

function db_escape($connection, $string) {
    return mysqli_real_escape_string($connection, $string);
}

function db_disconnect($connection) {
    if(isset($connection)) {
        mysqli_close($connection);
    }
}
