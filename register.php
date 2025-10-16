<?php $title = "Register"; ?>

<!DOCTYPE html>
<html lang="en">

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/header.php';  ?>

<!-- Main -->
<main class="page-con register">
    <div class="content-box register">

        <h2 class="heading">Register to create your free portfolio.</h2>

        <form id="register" method="POST">
            <div class="grid grid-cols-2">

                <div class="form-group">
                    <label for="firstname" class="required-label">Firstname :</label>
                    <input type="text" id="firstname" name="firstname" required>
                </div>
                <div class="form-group">
                    <label for="lastname" class="required-label">Lastname :</label>
                    <input type="text" id="lastname" name="lastname" required>
                </div>
                <div class="form-group">
                    <label for="birthdate" class="required-label">Date of birth :</label>
                    <input type="date" id="birthdate" name="birthdate" required>
                </div>
                <div class="form-group">
                    <label for="email" class="required-label">Email :</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password" class="required-label">Password :</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" required>
                        <i class="fas fa-eye toggle-password"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password-confirm" class="required-label">Confirm Password :</label>
                    <div class="password-container">
                        <input type="password" id="password-confirm" name="password-confirm" required>
                        <i class="fas fa-eye toggle-password"></i>
                    </div>
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
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/footer.php';  ?>
<script src="/main.js"></script>

</html>