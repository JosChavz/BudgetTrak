<?php
global $session;
    // Checks to see if a user is logged in
    if (!$session->is_logged_in()) {
        h('/auth/login.php');
        exit();
    }

    if (!$session->get_user_role()) {
        h('/403.php');
        exit();
    }
    const SEP = ' | ';
    $loggedInMenu = '';
?>
<!doctype html>
<html lang="en" style="background: black">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>BudgetTrak <?= !empty($extraTitle) ? SEP . "$loggedInMenu" : '' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= HTTP ?>/public/style.css">
    <script src="https://kit.fontawesome.com/5920ad757d.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.canvasjs.com/ga/jquery.canvasjs.min.js"></script>
</head>
<body>
