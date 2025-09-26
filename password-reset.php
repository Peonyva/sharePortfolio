<?php $title = "Password Reset"; ?>

<!DOCTYPE html>
<html lang="en">

<!-- Head & Navbar -->
<?php require_once 'layout/header.php'; ?>

<!-- Main -->
<main class="page-con">
    <div class="content-box password-reset">
        <h2>Reset Password</h2>
        <p>Enter your email and we will send you a link to reset your password.</p>
        <form>
            <div class="form-group">
                <label for="email">Email Address :</label>
                <input type="email" id="email" placeholder="Enter your email" required>
            </div>
            <button type="submit" class="btn btn-submit">Send Reset Link</button>
        </form>
    </div>
</main>

<!-- Footer -->
<?php require_once 'layout/footer.php'; ?>

</html>