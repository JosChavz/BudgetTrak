<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

use classes\Bank;
use classes\Database;
use classes\Expense;
use classes\Session;

$private_end = strpos(__DIR__, '/private') + 1;
define("ROOT", substr(__DIR__, 0, $private_end));

$env = parse_ini_file(ROOT . 'private/.env');

define("HTTP", ($env["PRODUCTION"] === "false")
    ? $env['DEVELOPMENT_SERVER']
    : $env['PRODUCTION_SERVER']
);

require_once 'functions.php';
require_once 'database.php';
require_once 'validation_functions.php';

$db = db_connect();
$errors = array();
$status_message = null;

require_once ROOT . '/private/classes/Database.php';

// -> All classes in directory
foreach(glob(ROOT . 'private/classes/*.php') as $file) {
    require_once($file);
}

// Autoload class definitions
function my_autoload($class) {
    if(preg_match('/\A\w+\Z/', $class)) {
        include('classes/' . $class . '.php');
    }
}
spl_autoload_register('my_autoload');
Database::set_database($db);

$session = new Session();
