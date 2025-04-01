<?php
include("db.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT id, password FROM users WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashed_password);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
        $_SESSION["user_id"] = $id;
        $_SESSION["username"] = $username;
        $_SESSION['loggedin'] = true;
        header("Location: home.php");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Invalid username or password!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Login</h2>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form method="post" class="border p-4 rounded shadow-sm">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" id="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <p class="mt-3 text-center">
                    Don't have an account?
                    <a href="#" onclick="redirectToSignup()">Sign Up</a>
                </p>
            </div>
        </div>
    </div>
    <script>
        function redirectToSignup() {
            var password = prompt("Enter the password to access the signup page:");

            if (password === null) return; // User canceled

            var correctPassword = "12345"; // Set the required password

            if (password === correctPassword) {
                sessionStorage.setItem("passwordVerified", "true");
                window.location.href = "signUp.php";
            } else {
                alert("Incorrect password! Access denied.");
            }
        }
    </script>
</body>

</html>