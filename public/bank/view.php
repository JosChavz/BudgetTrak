<?php
global $session, $db;

use classes\Bank;
use classes\Expense;
use classes\Pagination;

require_once '../../private/initialize.php';

global $db;

// Variables
$user_id = $_SESSION['user_id'] ?? '';
$bank_id = $_GET['id'] ?? '';
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

// Check to see if there are any params in URL
if (!isset($_GET['id']) || !is_numeric($bank_id)) {
    h(HTTP . '/bank');
    die();
}

$summation = array();
$dataPoints = array();
$current_URL = $_SERVER["REQUEST_URI"];
$arrow_next_URL = parse_url($current_URL, PHP_URL_PATH) . '?id=' . $bank_id . '&month=';
$arrow_prev_URL =  parse_url($current_URL, PHP_URL_PATH) . '?id=' . $bank_id . '&month=';

// For the next month
if (($month + 1) > 12) {
    $arrow_next_URL .= '1&year=' . $year + 1;
} else {
    $arrow_next_URL .= ($month + 1) . '&year=' . $year;
}

// For the prev month
if (($month - 1) <= 0) {
    $arrow_prev_URL .= '12&year=' . $year - 1;
} else {
    $arrow_prev_URL .= ($month - 1) . '&year=' . $year;
}

// Gets all data based off the query
$bank = Bank::find_by_id((int)$bank_id, $user_id);

if (empty($bank)) {
    h(HTTP . '/expense');
    exit();
}

$extraTitle = 'Expenses';
$user_id = $session->get_user_id();

$user_banks = Bank::find_by_user_id($user_id);

$current_page = $_GET['page'] ?? 1;
$per_page = 10;
$total_count = Expense::count_all_from_user_and_bank($session->get_user_id(), $bank_id);
$pagination = new Pagination($current_page, $per_page, $total_count);

// Gets the expenses for that bank based off the bank_id and selected month
$transactions = Expense::select_from_date((int)$bank_id, $month, $year, ['limit' => $per_page, 'offset' => $pagination->offset()]);

$expense_summation = Expense::select_summation($user_id, Expense::TYPE['EXPENSE'], ['bank_id' => $bank_id, 'month' => $month, 'year' => $year]);
$income_summation = Expense::select_summation($user_id, Expense::TYPE['INCOME'], ['bank_id' => $bank_id, 'month' => $month, 'year' => $year]);

$type_sum = Expense::select_all_type_summation($user_id, [], ['bank_id' => $bank_id, 'month' => $month, 'year' => $year]);

require_once ROOT . '/private/shared/header.php';

?>

    <div class="outer-wrapper">
        <?php require_once ROOT. '/private/shared/side-nav.php'; ?>
        <main>
            <div class="top-table">
                <h1>View Bank</h1>
                <a class="button" href="edit.php?id=<?= $bank_id ?>">Edit</a>
            </div>

            <section id="bank_bank-info">
                <h2><?= htmlspecialchars($bank->name) ?></h2>
                <p><i><?= $bank->type ?></i></p>
            </section>

            <section id="bank_extra-info">
                <div id="chartContainer" style="height: 370px; width: 90%;"></div>
                <div id="graphContainer" style="height: 370px; width: 90%;"></div>
            </section>

            <section id="bank_expenses" class="table-wrapper">
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 25px">
                    <h2>Expense Table</h2>
                    <a class="button" href="<?php echo HTTP ?>/public/expense/create.php">New Expense</a>
                </div>

                <div id="bank_month-nav">
                    <a href="<?= $arrow_prev_URL ?>">&#8656;</a>
                    <span><?= $month . '/' . $year ?></span>
                    <a href="<?= $arrow_next_URL ?>">&#8658;</a>
                </div>
                <table>
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Amount</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?= htmlspecialchars($transaction->name) ?></td>
                            <td><?= '$' . number_format($transaction->amount, 2) ?></td>
                            <td><?= $transaction->category ?></td>
                            <td><?= $transaction->type ?></td>
                            <td>
                                <a class="edit" href="/expense/edit.php?id=<?= $transaction->id ?>">
                                    <i title="edit expense" class="fa-solid fa-pen"></i>
                                </a>
                                <a class="delete" href="/expense/delete.php?id=<?= $transaction->id ?>">
                                    <i title="delete expense" class="fa-solid fa-trash"></i>
                                </a>
                                <a class="view" href="/expense/view.php?id=<?= $transaction->id ?>">
                                    <i title="view expense" class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php
                        endforeach;
                        // Iterate through summation to initialize data points
                        foreach ($type_sum as $category => $amount) {
                            $dataPoints[] = array('label' => $category, 'y' => ($amount / $expense_summation) * 100);
                        }
                        ?>
                    </tbody>
                </table>
                <?= $pagination->display() ?>
            </section>
        </main>
    </div>

<script>
    // https://canvasjs.com/php-charts/pie-chart/
    window.onload = function() {
        const chart = new CanvasJS.Chart("chartContainer", {
            animationEnabled: true,
            title: {
                text: "<?= $month ?>/<?= $year ?> Expenses"
            },
            data: [{
                type: "pie",
                yValueFormatString: "#,##0.00\"%\"",
                indexLabel: "{label} ({y})",
                dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>
            }]
        });
        chart.render();

        <?php

        $chart_data_points = array(
                array("y" => $income_summation, "label" => "Income"),
                array("y" => $expense_summation, "label" => "Expense")
        );

        ?>

        const diff = new CanvasJS.Chart("graphContainer", {
            animationEnabled: true,
            theme: "light2",
            title:{
                text: "Income vs. Expense"
            },
            axisY: {
                title: "$$$"
            },
            data: [{
                type: "column",
                yValueFormatString: "$#,##0.##",
                dataPoints: <?php echo json_encode($chart_data_points, JSON_NUMERIC_CHECK); ?>
            }]
        });
        diff.render();

    }
</script>
<?php
require_once ROOT . '/private/shared/footer.php';