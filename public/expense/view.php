<?php

require_once '../../private/initialize.php';
global $db, $session;

require_once ROOT . '/private/sql/expense.php';
require_once ROOT . '/private/sql/bank.php';

// Check to see if there are any params in URL
if (!isset($_GET['id'])) {
    h(HTTP . '/expense');
    die();
}

$id = $_GET['id'];

// Gets all data based off the query
$expense = select_transaction_where((int)$id);

if (empty($expense)) {
    h(HTTP . '/expense');
    exit();
}

// Gets the bank account for current expense
$bank = select_bank_by_id($expense['bid'], $session->get_user_id());

$extraTitle = 'Expenses';

require_once ROOT . '/private/shared/header.php';

?>
    <div class="outer-wrapper">
        <?php require_once ROOT. '/private/shared/side-nav.php'; ?>
        <main>
            <?= $session->prompt_message() ?>
            <div class="top-table">
                <h1>View Expense</h1>
                <a href="edit.php?id=<?= $id ?>">Edit</a>
            </div>
            <div>
                <p>Name: <?= htmlspecialchars($expense['name']) ?></p>
                <p>Amount: <?= '$' . number_format($expense['amount'], 2) ?></p>
                <p>Description: <?= htmlspecialchars($expense['description'] ?? '') ?></p>
                <p>Bank: <?= htmlspecialchars($bank['name'] ?? 'Cash') ?></p>
                <p>Type: <?= $expense['type'] ?></p>
                <p>Category: <?= $expense['category'] ?></p>
            </div>
        </main>
    </div>
<?php

require_once ROOT . '/private/shared/footer.php';