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
      <h3>Create Account</h3>
      <p class="sub-text">Enter your details to create your account</p>

     <form action="register.php" method="POST">

        <label>Email</label>
        <div class="input-group">
          <i class="ri-mail-line"></i>
          <input type="email" name="email" placeholder="Enter your email" required />
        </div>

        <label>Username</label>
        <div class="input-group">
          <i class="ri-user-line"></i>
          <input type="username"  name= "username"placeholder="Enter your username" required />
        </div>

        <label>Password</label>
        <div class="input-group">
          <i class="ri-lock-line"></i>
          <input type="password" name="password" placeholder="Enter your password" required />
        </div>

        <button type="submit" class="btn login">Sign In</button>

        <p class="switch-text">
          Already have an account?
          <a href="login.php">Sign in</a>
        </p>
      </form>
    </div>
  </div>


<?php
session_start();
include('config.php');

// Enable MySQLi exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$message = "";
$type = ""; // success or error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = trim($_POST["username"]);
  $email = trim($_POST["email"]);
  $password = trim($_POST["password"]);

  // Check if username already exists
  $check = $conn->prepare("SELECT * FROM users WHERE username = ?");
  $check->bind_param("s", $username);
  $check->execute();
  $result = $check->get_result();

  if ($result->num_rows > 0) {
    $message = "Username already taken!";
    $type = "error";
  } else {
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert into database
    $insert = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $insert->bind_param("sss", $username, $email, $hashedPassword);

    try {
      if ($insert->execute()) {
        $_SESSION["user_id"] = $conn->insert_id;
        $_SESSION["username"] = $username;

        $message = "Account created successfully! Redirecting...";
        $type = "success";

        echo "
        <script>
          setTimeout(() => {
            window.location='dashboard.php';
          }, 2000);
        </script>";
      }
    } catch (mysqli_sql_exception $e) {
      if ($e->getCode() === 1062) {
        $message = "This email is already registered. Try logging in instead.";
        $type = "error";
      } else {
        $message = "An unexpected error occurred. Please try again.";
        $type = "error";
      }
    }
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