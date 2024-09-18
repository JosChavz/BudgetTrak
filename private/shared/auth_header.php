<?php
global $errors;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>BudgetTrak | Login</title>
    <script src="https://kit.fontawesome.com/5920ad757d.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.canvasjs.com/ga/jquery.canvasjs.min.js"></script>

    <link rel="stylesheet" href="<?= HTTP ?>/public/style.css">
</head>
<body>
    <div id="auth" class="wrapper">
        <?php if (!empty($status_message)): ?>
            <div id="status-message">
                <p><?= $status_message ?></p>
            </div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div id="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
