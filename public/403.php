<?php

require_once '../private/initialize.php';

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>BudgetTrak | 403</title>
    <link rel="stylesheet" href="<?php echo HTTP; ?>/public/style.css">
</head>
<body>
<div id="not-found">
    <img src="images/403.png" alt="snake">
    <h1>403 Permission Denied</h1>
    <a id="logout-403" href="<?php echo HTTP; ?>/public/auth/logout.php">Logout!</a>
</div>
</body>
</html>