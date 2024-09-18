<?php

use \classes\Expense;
use \classes\Bank;

include '../../private/initialize.php';
global $session;

$type_enum = Expense::TYPE;
$user_banks = Bank::find_by_user_id($session->get_user_id());
$category_enum = Expense::CATEGORY;
$expense = new Expense();

// Check to see if a POST request was made, if so, execute
if (is_post_request()) {
    $args = $_POST['expense'];
    $args['uid'] = $session->get_user_id();
    $expense = new Expense($args);

    if ($expense->save()) {
        h(HTTP . '/expense/view.php?id=' . $id);
    } else {
        $errors = [...$expense->errors];
    }
}

require_once ROOT . '/private/shared/header.php';

?>

<div class="outer-wrapper">
    <?php require_once ROOT . '/private/shared/side-nav.php'; ?>
    <main>
        <h1>Create Expense</h1>
        <?php if (!empty($errors)) : ?>
        <ul class="errors">
            <?php for ($i = 0; $i < count($errors); $i++): ?>
                <li><?= $errors[$i] ?></li>
            <?php endfor; ?>
        </ul>
        <?php endif; ?>
        <form class="edit-form" action="create.php" method="post">
            <label for="name">Name
                <input required type="text" name="expense[name]" id="name" value="<?= $expense->name ?? '' ?>">
            </label>
            <label for="amount">Amount
                <input required type="number" step="0.01" name="expense[amount]" id="amount" value="<?= $expense->amount ?? '' ?>">
            </label>
            <label for="description">Description
                <input type="text" name="expense[description]" id="description" value="<?= $expense->description ?? '' ?>">
            </label>
            <label for="bank">
                Bank
                <select name="expense[bid]" id="bank">
                    <option value="CASH">Cash</option>
                    <?php foreach ($user_banks as $bank): ?>
                        <option <?= (($expense->bid ?? '') == $bank->id) ? 'selected' : '' ?> value="<?= $bank->id ?>">
                            <?= $bank->name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label for="type">
            Type
                <select name="expense[type]" id="type">
                    <?php foreach ($type_enum as $key => $value): ?>
                        <option <?= (($expense->type ?? '') == $key) ? 'selected' : '' ?> value="<?= $key ?>">
                        <?= $value ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label for="category">
                Category
                <select name="expense[category]" id="category">
                    <?php foreach ($category_enum as $key => $value): ?>
                    <option <?= (($expense->category ?? '') == $key) ? 'selected' : '' ?> value="<?= $key ?>">
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
