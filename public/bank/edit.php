<?php

use classes\Bank;

require_once '../../private/initialize.php';

global $db, $errors, $status_message;

// VARIABLES
$user_id = $_SESSION['user_id'];
$bank_id = $_GET['id'] ?? '';
$bank = new Bank([]);
$bank = Bank::find_by_id((int)$bank_id, (int)$user_id);
$bank_types = Bank::BANK_TYPE;

if (!$bank || !is_numeric($bank_id)) {
    h('/bank');
    exit();
}

// POST REQUEST
if(is_post_request()) {
    // Required
    $args = $_POST['bank'] ?? [];
    $args['uid'] = $user_id;
    $args['id'] = (int)$bank_id;

    $bank = new Bank($args);

    if (empty($errors)) {
        $update_success = $bank->save();

        if($update_success) {
            $status_message = "Bank updated successfully";
            h(HTTP . '/bank/view.php?id=' . $bank_id);
            exit();
        } else $errors[] = "Bank could not be updated.";
    }
}

require_once ROOT . '/private/shared/header.php';
?>

    <div class="outer-wrapper">
        <?php require_once ROOT . '/private/shared/side-nav.php'; ?>
        <main>
            <h1>Edit Bank</h1>
            <?php if (!empty($errors)) : ?>
                <ul class="errors">
                    <?php for ($i = 0; $i < count($errors); $i++): ?>
                        <li><?= $errors[$i] ?></li>
                    <?php endfor; ?>
                </ul>
            <?php endif; ?>
            <form class="edit-form" action="edit.php?id=<?= $bank_id ?>" method="post">
                <label for="name">Name
                    <input required type="text" name="bank[name]" id="name" value="<?= $bank->name ?? '' ?>">
                </label>
                <label for="type">Type
                    <select name="bank[type]" id="type">
                        <?php foreach ($bank_types as $bank_type=>$bank_type): ?>
                            <option value="<?= $bank_type ?>" <?= $bank_type === ($bank->type ?? '') ? 'selected' : '' ?>>
                                <?= $bank_type ?>
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
