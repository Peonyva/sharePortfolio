<?php $title = "Change Password"; ?>

<!DOCTYPE html>
<html lang="en">

<!-- Head & Navbar -->
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/header.php';  ?>

<!-- Main -->
<main class="page-con">
  <div class="content-box change-password">
    <h2 class="heading">Change Password</h2>

    <form>
      <div class="form-group">
        <label for="email">Email :</label>
        <input type="email" id="email" value="example@email.com" readonly>
      </div>

      <div class="form-group">
        <label for="password">New Password :</label>
        <input type="password" id="password" placeholder="Enter new password" required>
      </div>

      <ul class="password-rules">
        <li>8–16 characters</li>
        <li>Upper & lowercase letters</li>
        <li>Numbers & symbols</li>
      </ul>

      <div class="form-group">
        <label for="confirm">Confirm Password :</label>
        <input type="password" id="confirm" placeholder="Confirm password" required>
      </div>

      <button type="submit" class="btn btn-submit">Change Password</button>
    </form>
  </div>
</main>

<!-- Footer -->
<?php  require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/footer.php';  ?>

</html>