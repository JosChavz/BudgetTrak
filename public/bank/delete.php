<?php

use classes\Bank;

require '../../private/initialize.php';
global $errors;

if (!isset($_GET['id'])) {
    h(HTTP . '/bank' );
    exit;
}

$bank_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$bank = Bank::find_by_id($bank_id, $user_id);

// POST request will delete the record
// Expenses along with it should, too?
if (is_post_request()) {
    $delete_success = $bank->delete();
    if ($delete_success) {
        h(HTTP . '/bank');
    } else {
        $errors[] = [...$bank->errors];
    }
}

require_once ROOT . '/private/shared/header.php';

?>

    <div class="outer-wrapper">
    <?php require_once ROOT. '/private/shared/side-nav.php'; ?>
        <main>
            <h1>Delete Bank</h1>
            <p>Are you sure you want to delete <strong><?= $bank->name ?></strong>?</p>
            <div id="errors" style="margin-top: 45px">
                <span>DISCLAIMER</span>
                <p style="font-weight: normal">Note that <strong>all</strong> expenses connected to this bank will also be deleted.</p>
                <p>Is that okay?</p>
                <?php if($errors):
                    foreach($errors as $error): ?>
                    <span><?= $error ?></span>
                    <?php endforeach; endif; ?>
            </div>
            <form action="delete.php?id=<?= $bank->id ?>" method="post">
                <input type="submit" value="delete">
            </form>
        </main>
    </div>

<?php
require_once ROOT . '/private/shared/footer.php';
?>