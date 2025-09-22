<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://kit.fontawesome.com/92f0aafca7.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="/lib/reset.css">
  <link rel="stylesheet" href="/styles/page.css">
  <title>Change Password</title>
</head>

<body>

    <!-- Navbar -->
    <header>
        <nav>
            <div class="container">
                <div class="nav-con">
                    <div class="nav-logo">
                        <!-- ใส่ a href รอบทั้งรูปและข้อความ -->
                        <a href="index.php"> <!-- เปลี่ยนเป็น URL ที่ต้องการ -->
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40' fill='%23333'%3E%3Crect width='40' height='40' rx='8' fill='%23f8f9fa' stroke='%23333' stroke-width='2'/%3E%3Ctext x='20' y='26' text-anchor='middle' font-family='Arial' font-size='14' font-weight='bold'%3EL%3C/text%3E%3C/svg%3E" alt="logo" />
                            SharePortfolio
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

  <!-- Main -->
    <main class="page-con">
    <div class="content-box change-password">
      <h2>Change Password</h2>

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
  <footer>
    <div class="container">
      <p>&copy; 2025 SharePortfolio. All rights reserved.</p>
    </div>
  </footer>
</body>

</html>