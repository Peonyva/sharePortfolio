<?php $title = "Password Reset Link"; ?>

<!DOCTYPE html>
<html lang="en">

<!-- Head & Navbar -->
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/header.php';  ?>

<!-- Main -->
<main class="page-con">
    <div class="content-box password-reset-link">
        <!-- <a href="#" class="redirect">Create an account</a> -->
        <h2 class="heading">Reset Your Password</h2>
        <p>
            Check your email for a link to reset your password. <br>
            If it doesn’t appear within a few minutes, check your spam folder.
        </p>
        <a href="login.php" class="btn btn-submit">Return to sign in</a>
    </div>
</main>

<!-- Footer -->
<?php  require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/footer.php';  ?>
</html>