<?php
global $session;
    $curr_full_dir = $_SERVER['PHP_SELF'];
    $curr_dir = substr($curr_full_dir, 0, strrpos($curr_full_dir, '/'));
    $curr_dir = (0 === strlen($curr_dir)) ? '/' : $curr_dir;

    function appendClass($expected) : string {
        global $curr_dir;
        return ($expected === $curr_dir) ? 'class="selected"' : '';
    }
?>

<aside>
    <span class="website-header">
        BudgetTrak
    </span>
    <nav>
        <ul>
            <li <?= appendClass('/') ?>>
                <a href="<?= HTTP ?>">
                    <p><i class="fa-solid fa-house"></i>Home</p>
                </a>
            </li>
            <li <?= appendClass('/expense') ?>>
                <a href="<?= HTTP . '/expense'?>">
                    <p><i class="fa-solid fa-money-bills"></i>Expenses</p>
<!--                    <span class="bubble">2</span>-->
                </a>
            </li>
            <li <?= appendClass('/bank') ?>>
                <a href="<?= HTTP . '/bank' ?>">
                    <p><i class="fa-solid fa-building-columns"></i>Banks</p>
<!--                    <span class="bubble">9+</span>-->
                </a>
            </li>
        </ul>
    </nav>
    
    <div id="aside-user">
        <div class="img-wrapper">
            <img src="<?= HTTP ?>/public/images/Preview-8.png" alt="profile pic">
        </div>
        <span id="aside-user-name"><?= $session->get_user_name() ?></span>
        <a id="logout-user" href="<?= HTTP ?>public/auth/logout.php" aria-label="log out">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>
    </div>
</aside>
