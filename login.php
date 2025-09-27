<?php $title = "Log In"; ?>

<!DOCTYPE html>
<html lang="en">

<!-- Head & Navbar -->
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/header.php';  ?>

<!-- Main -->
<main class="page-con">
    <div class="content-box login">
        <h2 class="heading">Log in</h2>
        <form>
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required />
            </div>

            <div class="form-group">
                <label for="password">Password :</label>
                <input type="password" id="password" name="password" required />
                <a href="/password-reset.php" class="forgot">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-submit">Log in</button>

            <hr />

            <button type="button" class="btn btn-create">Create an account</button>
        </form>
    </div>
</main>

<!-- Footer -->
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/footer.php';  ?>

</html>