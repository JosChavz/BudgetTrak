<?php

use classes\Bank;

require_once '../../private/initialize.php';

global $db, $errors, $status_message;

// DB CONNECTIONS
require_once ROOT . '/private/sql/bank.php';
require_once ROOT . '/private/sql/expense.php';

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
$expense_sum = 0;
$income_sum = 0;
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

// Gets the expenses for that bank based off the bank_id and selected month
$transactions = select_transaction_from_bank_month_year((int)$bank_id, $month, $year);

// Gets the count for expenses
$total_page_count = count_transactions($bank_id, $user_id);

if (empty($bank)) {
    h(HTTP . '/expense');
    exit();
}

require_once ROOT . '/private/shared/header.php';

?>

    <div class="outer-wrapper">
        <?php require_once ROOT. '/private/shared/side-nav.php'; ?>
        <main>
            <div class="top-table">
                <h1>View Bank</h1>
                <a href="edit.php?id=<?= $bank_id ?>">Edit</a>
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
                        <?php foreach ($transactions as $transaction):
                        if ($transaction['type'] === 'EXPENSE') {
                            if (isset($summation[$transaction['category']])) {
                                $summation[$transaction['category']] += $transaction['amount'];
                            } else {
                                $summation[$transaction['category']] = $transaction['amount'];
                            }

                            $expense_sum += $transaction['amount'];
                        } else {
                            $income_sum += $transaction['amount'];
                        }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($transaction['name']) ?></td>
                            <td><?= '$' . number_format($transaction['amount'], 2) ?></td>
                            <td><?= $transaction['category'] ?></td>
                            <td><?= $transaction['type'] ?></td>
                            <td>
                                <a class="edit" href="/expense/edit.php?id=<?= $transaction['id'] ?>">
                                    <i title="edit expense" class="fa-solid fa-pen"></i>
                                </a>
                                <a class="delete" href="/expense/delete.php?id=<?= $transaction['id'] ?>">
                                    <i title="delete expense" class="fa-solid fa-trash"></i>
                                </a>
                                <a class="view" href="/expense/view.php?id=<?= $transaction['id'] ?>">
                                    <i title="view expense" class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php
                        endforeach;
                        // Iterate through summation to initialize data points
                        foreach ($summation as $category => $amount) {
                            $dataPoints[] = array('label' => $category, 'y' => ($amount / $expense_sum) * 100);
                        }
                        ?>
                    </tbody>
                </table>

                <div id="bank_pagination">

                </div>
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
                array("y" => $income_sum, "label" => "Income"),
                array("y" => $expense_sum, "label" => "Expense")
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