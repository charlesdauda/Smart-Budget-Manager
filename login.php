<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
    <link rel="stylesheet" href="auth.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" 
  integrity="sha512-XcIsjKMcuVe0Ucj/xgIXQnytNwBttJbNjltBV18IOnru2lDPe9KRRyvCXw6Y5H415vbBLRm8+q6fmLUU7DfO6Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script src="https://kit.fontawesome.com/a2d9d6b0ef.js" crossorigin="anonymous"></script>
</head>
<body>
    
<div class="auth-container">
    <div class="auth-logo">
      <i class="ri-wallet-3-line"></i>
      <h2>SmartBudget</h2>
      <p>Simple. Elegant. Productive.</p>
    </div>

    <div class="auth-box">
      <h3>Welcome Back</h3>
      <p class="sub-text">Sign in to your account to continue</p>

       <form method="POST" action="login.php">
        <label>Username</label>
        <div class="input-group">
          <i class="ri-user-line"></i>
          <input type="username" name= "username"placeholder="Enter your username" required />
        </div>

        <label>Password</label>
        <div class="input-group">
          <i class="ri-lock-line"></i>
          <input type="password" name="password" placeholder="Enter your password" required />
        </div>

        <button type="submit" class="btn login">Sign In</button>

        <p class="switch-text">
          Don’t have an account?
          <a href="register.php">Sign up</a>
        </p>
      </form>
    </div>
  </div>


  <?php
session_start();
include('config.php');

$message = "";
$type = ""; // success or error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = trim($_POST["username"]);
  $password = trim($_POST["password"]);

  $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user["password"])) {
      $_SESSION["user_id"] = $user["id"];
      $_SESSION["username"] = $user["username"];
      header("Location: dashboard.php");
      exit();
    } else {
      $message = "Invalid password!";
      $type = "error";
    }
  } else {
    $message = "No account found with that username!";
    $type = "error";
  }
}
?>


<?php if (!empty($message)): ?>
  <div class="message-box <?= $type ?>">
    <?= htmlspecialchars($message) ?>
  </div>
  <script>
    setTimeout(() => {
      document.querySelector('.message-box').classList.add('hide');
    }, 4000);
  </script>
<?php endif; ?>



  <script src="theme.js"></script>
</body>
</html>