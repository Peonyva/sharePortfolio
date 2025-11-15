<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://kit.fontawesome.com/92f0aafca7.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="/lib/reset.css" />
    <link rel="stylesheet" href="/styles/root-default.css" />
    <link rel="stylesheet" href="/styles/standard.css" />
    <title><?php echo isset($title) ? $title : 'Documents'; ?></title>
</head>

<body>

    <!-- Navbar -->
    <header>
        <nav>
            <div class="container">
                <div class="nav-con">
                    <div class="nav-logo">
                        <a href="/index.php">
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40' fill='%23333'%3E%3Crect width='40' height='40' rx='8' fill='%23f8f9fa' stroke='%23333' stroke-width='2'/%3E%3Ctext x='20' y='26' text-anchor='middle' font-family='Arial' font-size='14' font-weight='bold'%3EL%3C/text%3E%3C/svg%3E" alt="logo" />
                            SharePortfolio
                        </a>
                    </div>

                    <div class="nav-menu">
                        <ul class="menu">
                            <li><a href="/login.php" class="btn-link">Login</a></li>
                        </ul>
                    </div>

                </div>
            </div>
        </nav>
    </header>