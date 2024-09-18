<?php

use classes\Bank;

require_once '../../private/initialize.php';

global $db, $errors, $status_message;

// Variables
$user_id = $_SESSION['user_id'];
$bank_types = Bank::BANK_TYPE;

// POST REQUEST
if (is_post_request()) {
    $args['name'] = trim($_POST['name']);
    $args['type'] = trim($_POST['type']);
    $args['uid'] = $user_id;

    $bank = new Bank($args);

    // Validates Transaction Values
    if (empty($errors)) {
        $create_success = $bank->save();

        if ($create_success) {
            $status_message = "Bank created";
            h(HTTP . '/bank/view.php?id=' . $bank->id);
            exit();
        }
    }
} else {
    $bank['name'] = $_GET['name'] ?? '';
    $bank['type'] = $_GET['type'] ?? '';
}

require_once ROOT . '/private/shared/header.php';
?>

<div class="outer-wrapper">
    <?php require_once ROOT . '/private/shared/side-nav.php'; ?>
    <main>
        <h1>Create Bank</h1>
        <?php if (!empty($errors)) : ?>
            <ul class="errors">
                <?php for ($i = 0; $i < count($errors); $i++): ?>
                    <li><?= $errors[$i] ?></li>
                <?php endfor; ?>
            </ul>
        <?php endif; ?>
        <form class="edit-form" action="create.php" method="post">
            <label for="name">Name
                <input required type="text" name="name" id="name" value="<?= $bank->name ?? '' ?>">
            </label>
            <label for="type">Type <select name="type" id="type">
                    <?php foreach ($bank_types as $bank_type => $bank_value): ?>
                        <option value="<?= $bank_type ?>" <?= $bank_type === ($bank->type ?? '') ? 'selected' : '' ?>>
                            <?= $bank_value ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <input type="submit" value="submit">
        </form>
    </main>
</div>

<?php
require_once ROOT . '/private/shared/footer.php';