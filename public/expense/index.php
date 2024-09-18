<?php

global $session;

use classes\Bank;
use classes\Expense;
use classes\Pagination;

require_once '../../private/initialize.php';

$extraTitle = 'Expenses';

$user_banks = Bank::select_names($session->get_user_id());

$current_page = $_GET['page'] ?? 1;
$per_page = 5;
$total_count = Expense::count_all_from_user($session->get_user_id());
$pagination = new Pagination($current_page, $per_page, $total_count);

$sql = "SELECT * FROM transactions WHERE uid=" . $session->get_user_id() . ' ';
$sql .= "ORDER BY created_at ASC ";
$sql .= "LIMIT {$per_page} ";
$sql .= "OFFSET {$pagination->offset()}";
$expenses = Expense::find_by_sql($sql);

require_once ROOT . '/private/shared/header.php';

?>
    <div class="outer-wrapper">
        <?php require_once ROOT. '/private/shared/side-nav.php'; ?>

        <main>
            <div class="top-table">
                <h1>Expenses</h1>
                <a href="create.php">+ New expense</a>
            </div>

            <hr>

            <div class="table-wrapper">
                <table>
                    <thead>
                    <tr>
                        <th>
                            <label class="table-radio-container">
                                <input type="checkbox">
                                <span class="checkmark"></span>
                            </label>
                        </th>
                        <th>Name</th>
                        <th>Amount</th>
                        <th>Bank</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expenses as $expense) : ?>
                        <tr>
                            <td>
                                <label class="table-radio-container">
                                    <input type="checkbox">
                                    <span class="checkmark"></span>
                                </label>
                            </td>
                            <td><?= htmlspecialchars($expense->name) ?></td>
                            <td><?= '$' . number_format($expense->amount, 2) ?></td>
                            <td><?= (isset($expense->bid)) ? htmlspecialchars($user_banks[$expense->bid]) : 'Cash' ?></td>
                            <td><?= $expense->type ?></td>
                            <td>
                                <a class="edit" href="edit.php?id=<?= $expense->id ?>">
                                    <i title="edit expense" class="fa-solid fa-pen"></i>
                                </a>
                                <a class="delete" href="delete.php?id=<?= $expense->id ?>">
                                    <i title="delete expense" class="fa-solid fa-trash"></i>
                                </a>
                                <a class="view" href="view.php?id=<?= $expense->id ?>">
                                    <i title="view expense" class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php
                        endforeach;
                        ?>
                    </tbody>
                </table>
                <?= $pagination->display('./index.php') ?>
            </div>
        </main>
    </div>
<?php

require_once ROOT . '/private/shared/footer.php';
