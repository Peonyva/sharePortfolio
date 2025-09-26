<?php $title = "Register"; ?>

<!DOCTYPE html>
<html lang="en">

<!-- Head & Navbar -->
<?php require_once 'layout/header.php'; ?>

<!-- Main -->
<main class="page-con register">
    <div class="content-box register">
        <!-- <div class="account-text">
            Already have an account? <a href="#" class="redirect-col2">Login</a>
        </div> -->

        <h2>Register to create your free portfolio.</h2>

        <form>
            <div class="grid grid-cols-2">

                <div class="form-group">
                    <label for="firstname" class="required-label">Firstname :</label>
                    <input type="text" id="firstname" required>
                </div>
                <div class="form-group">
                    <label for="firstname" class="required-label">Lastname :</label>
                    <input type="text" id="lastname" required>
                </div>
                <div class="form-group">
                    <label for="datebirth" class="required-label">Date of birth :</label>
                    <input type="date" id="datebirth" required>
                </div>
                <div class="form-group">
                    <label for="email" class="required-label">Email :</label>
                    <input type="email" id="email" required>
                </div>
                <div class="form-group">
                    <label for="password" class="required-label">Password :</label>
                    <input type="password" id="password" required>
                </div>
                <div class="form-group">
                    <label for="password-confirm" class="required-label">Confirm Password :</label>
                    <input type="password" id="password-confirm" required>
                </div>

            </div>

            <ul class="password-rules">
                <li>8–16 characters</li>
                <li>Upper & lowercase letters</li>
                <li>Numbers & symbols</li>
            </ul>

            <button type="submit" class="btn btn-submit">Create account</button>
        </form>
    </div>
</main>

<!-- Footer -->
<?php require_once 'layout/footer.php'; ?>

</html>