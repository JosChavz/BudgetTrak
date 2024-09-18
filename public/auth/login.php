<?php

global $session;

use classes\User;

require_once '../../private/initialize.php';

$user = new User();

if (is_post_request()) {
    $user = new User($_POST['user']);
    $errors = [...$user->errors];

    if (empty($errors)) {
        $existing_user = User::find_by_username($user->email);
        if ($existing_user != NULL && $existing_user->verify_password($user->password)) {
            $session->login($existing_user);
            h(HTTP . '/public/index.php');
        } else {
            $errors[] = "Login was unsuccessful!";
        }
    }
}

?>
    <?php require_once ROOT . '/private/shared/auth_header.php'; ?>

    <div id="login-wrapper">
        <h1>BudgetTrak</h1>
        <form class="login" action="./login.php" method="post">
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
                <a id="forgot-password" href="./forgot">
                    Forgot Password?
                </a>
            </label>
            <input type="submit" value="submit">
        </form>

        <div id="bottom-message">
            <p>Don't have an account? <a href="./register.php">Sign Up</a></p>
        </div>
    </div>

<?php require_once ROOT . '/private/shared/auth_footer.php'; ?>
