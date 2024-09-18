<?php

use classes\Bank;

require_once '../../private/initialize.php';
require_once ROOT . '/private/sql/bank.php';

$extraTitle = 'Expenses';
$user_id = $_SESSION['user_id'] ?? null;

$user_banks = Bank::find_by_user_id($user_id);

require_once ROOT . '/private/shared/header.php';

?>
    <div class="outer-wrapper">
        <?php require_once ROOT. '/private/shared/side-nav.php'; ?>
        <main>
            <main>
                <div class="top-table">
                    <h1>Banks</h1>
                    <a href="create.php">+ New bank</a>
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
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($user_banks as $row) : ?>
                            <tr>
                                <td>
                                    <label class="table-radio-container">
                                        <input type="checkbox">
                                        <span class="checkmark"></span>
                                    </label>
                                </td>
                                <td><?= htmlspecialchars($row->name) ?></td>
                                <td><?= $row->type ?></td>
                                <td>
                                    <a class="view" href="view.php?id=<?= $row->id ?>">
                                        <i title="view expense" class="fa-solid fa-eye"></i>
                                    </a>
                                    <a class="edit" href="edit.php?id=<?= $row->id ?>">
                                        <i title="edit expense" class="fa-solid fa-pen"></i>
                                    </a>
                                    <a class="delete" href="delete.php?id=<?= $row->id ?>">
                                        <i title="delete expense" class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php
                        endforeach;
                        ?>
                        </tbody>
                    </table>
                </div>
        </main>
    </div>
<?php

require_once ROOT . '/private/shared/footer.php';
