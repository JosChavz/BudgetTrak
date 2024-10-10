<?php
global $session;

use classes\Expense;

require_once '../private/initialize.php';
$extraTitle = 'Home';
$user_id = $session->get_user_id();

$month = date('m');
$year = date('Y');
$dataPoints = array();

// Gets the expenses for that bank based off the bank_id and selected month
$expense_summation = Expense::select_summation($user_id, Expense::TYPE['EXPENSE'], ['month' => $month, 'year' => $year]);
$income_summation = Expense::select_summation($user_id, Expense::TYPE['INCOME'], ['month' => $month, 'year' => $year]);

$type_sum = Expense::select_all_type_summation($user_id, [], ['month' => $month, 'year' => $year]);

foreach ($type_sum as $category => $amount) {
    $dataPoints[] = array('label' => $category, 'y' => ($amount / $expense_summation) * 100);
}

    require_once '../private/shared/header.php';
?>
    <div class="outer-wrapper">
        <?php require_once '../private/shared/side-nav.php'; ?>
        <main>
            <h2>Welcome, <?php echo $session->get_user_name() ?></h2>
            <h3>Monthly Report</h3>
            <section id="bank_extra-info">
                <div id="chartContainer" style="height: 370px; width: 90%;"></div>
                <div id="graphContainer" style="height: 370px; width: 90%;"></div>
            </section>
        </main>
    </div>

    <script>
        // https://canvasjs.com/php-charts/pie-chart/
        window.onload = function() {
            const chart = new CanvasJS.Chart("chartContainer", {
                animationEnabled: true,
                title: {
                    text: "Expenses"
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
require_once '../private/shared/footer.php';

