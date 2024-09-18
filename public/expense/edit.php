<?php
require_once '../../private/initialize.php';

global $db, $errors, $status_message, $session;

require_once ROOT . '/private/sql/expense.php';
require_once ROOT . '/private/sql/bank.php';

$type_enum = select_transaction_types();
$category_enum = select_transaction_categories();

// Check to see if there are any params in URL
$id = $_GET['id'] ?? '';
$expense = array();
$user_banks = select_banks_by_user_id($session->get_user_id());

if (is_post_request()) {
    $expense['name'] = trim($_POST['name']);
    $expense['amount'] = trim($_POST['amount']);
    $expense['type'] = trim($_POST['type']);
    $expense['id'] = $id;
    $expense['bank_id'] = ('cash' === $_POST['bid']) ? null : $_POST['bid'] ;
    $expense['category'] = trim($_POST['category']);

    if(isset($_POST['description'])) $expense['description'] = trim($_POST['description']);

    $result = validate_transaction_values($expense, $type_enum, $category_enum);

    if (empty($result)) {
        try {
            update_transaction($expense, $session->get_user_id());
        } catch (Exception $e) {
            $db->close();
            die($e->getMessage());
        }

        $session->message('The transaction was successfully updated.');
        h(HTTP . '/expense/view.php?id=' . $id);
    } else {
        $errors = $result;
    }
} else {
    $expense = select_transaction_where((int)$id);

    if (empty($expense)) {
        h('/expense');
        exit();
    }
}

$extraTitle = 'Expenses';

require_once ROOT . '/private/shared/header.php';

?>
    <div class="outer-wrapper">
        <?php require_once ROOT. '/private/shared/side-nav.php'; ?>
        <main>
            <?php if (!empty($errors)) : ?>
                <ul class="errors">
                    <?php for ($i = 0; $i < count($errors); $i++): ?>
                        <li><?= $errors[$i] ?></li>
                    <?php endfor; ?>
                </ul>
            <?php endif; ?>
            <div class="top-table">
                <h1>Edit Expense</h1>
            </div>
            <form class="edit-form" action="edit.php?id=<?= $id ?>" method="post">
                <label for="name">Name
                    <input required type="text" name="name" id="name" value="<?= $expense['name'] ?? '' ?>">
                </label>
                <label for="amount">Amount
                    <input required type="number" step="0.01" name="amount" id="amount" value="<?= $expense['amount'] ?? '' ?>">
                </label>
                <label for="description">Description
                    <input type="text" name="description" id="description" value="<?= $expense['description'] ?? '' ?>">
                </label>
                <label for="bank">
                    Bank
                    <select name="bid" id="bank">
                        <option value="cash">Cash</option>
                        <?php foreach ($user_banks as $bank): ?>
                            <option value="<?= $bank['id'] ?>" <?= $bank['id'] == ($expense['bid'] ?? '') ? 'selected' : '' ?>>
                                <?= $bank['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label for="type">Type <select name="type" id="type">
                        <?php foreach ($type_enum as $value): ?>
                            <option value="<?= $value ?>" <?= (($expense['type'] ?? '') === $value) ? 'selected' : '' ?>>
                                <?= $value ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label for="category">
                    Category
                    <select name="category" id="category">
                        <?php foreach ($category_enum as $value): ?>
                            <option <?= (($expense['category'] ?? '') == $value) ? 'selected' : '' ?> value="<?= $value ?>">
                                <?=  $value ?>
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