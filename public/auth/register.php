<?php

global $errors, $session;

use classes\User;

require_once '../../private/initialize.php';

$user = new User();

if (is_post_request()) {
    $user = new User($_POST['user']);
    $errors = [...$user->errors];

    if (empty($errors)) {
        if ($user->save()) {
            $new_id = $user->id;
            $session->login($user);
            h(HTTP . '/public/index.php');
        } else {
            $errors[] = 'Error creating user. If you have an account, please login.';
        }
    }
}

?>
    <?php require_once ROOT . '/private/shared/auth_header.php'; ?>

    <div id="login-wrapper">
        <h1>BudgetTrak</h1>
        <form class="login" action="./register.php" method="post">
            <label for="name">
                <span>Name</span>
                <span class="input-wrapper">
                    <i class="fa-solid fa-pencil"></i>
                    <input placeholder="Hozay" type="text" name="user[name]" id="name" value="<?= $user->name ?? '' ?>" required>
                </span>
            </label>
            <label for="email">
                <span>Email</span>
                <span class="input-wrapper">
                    <i class="fa-solid fa-user"></i>
                    <input placeholder="jchave72@ucsc.edu" type="text" name="user[email]" id="email" value="<?= $user->email ?? '' ?>" required>
                </span>
            </label>
            <label for="password">
                <span>Password</span>
                <span class="input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input placeholder="Password" type="password" name="user[password]" id="password" value="<?= $user->password ?? '' ?>" required>
                </span>
            </label>
            <label for="confirm_password">
                <span>Confirm Password</span>
                <span class="input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input placeholder="Confirm Password" type="password" name="user[confirm_password]" id="confirm_password" value="<?= $user->confirm_password ?? '' ?>" required>
                </span>
            </label>
            <input type="submit" value="submit">
        </form>

        <div id="bottom-message">
            <p>Already have an account? <a href="./login.php">Login</a></p>
        </div>
    </div>

<?php require_once ROOT . '/private/shared/auth_footer.php'; ?>
