<?php
define("ROOTH_PATH", __DIR__);
require_once 'middleware/user_exists.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public/css/bootstrap.min.css">
    <link rel="stylesheet" href="public/css/bootstrap-icons-1.11.0/bootstrap-icons.min.css">
    <link rel="stylesheet" href="public/css/style-main.css">
    <script defer src="public/js/bootstrap.bundle.min.js"></script>
    <title>Login page</title>
</head>
<body>
    <div class="container vh-100 d-flex align-items-center justify-content-center flex-direction-column">
        <div class="container w-50 shadow p-4 rounded-3 login">
            <img src="public/images/the_lagoon_logo.png" alt="the lagoon logo" style="height: 75px" class="mt-0 pt-0 d-block mx-auto">
            <form action="controllers/authController.php" method="POST">
                <?php if(isset($_SESSION["errors"])): ?>
                    <p class="text-danger alert alert-danger my-2">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <?php echo htmlspecialchars($_SESSION["errors"]); ?>
                    </p>
                    <?php unset($_SESSION["errors"]);?>
                <?php endif; ?>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="Password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="mb-3">
                    <button class="btn btn-primary w-100">Login</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>