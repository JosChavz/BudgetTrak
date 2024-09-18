<?php

global $session;
require_once '../../private/initialize.php';

// User was never logged in to begin with
if (!$session->is_logged_in()) {
    h('/public/auth/login.php');
}

$session->logout();
h('/public/auth/login.php');