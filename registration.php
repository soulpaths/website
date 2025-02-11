<?php
// Start session
session_start();

// Redirect to index.php if the user is already logged in
if (isset($_SESSION["user"])) {
    header("Location: index.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style1.css">
</head>

<body>
    <div class="container">
        <div class="left-side">
            <h1 style="color: green; font-size: 100px;">FARM</h1>
            <h1 style="color: green; font-size: 100px;">EASY</h1>
        </div>
        <div class="right-side">
            <h2>Registration Form</h2>

            <?php
            // Initialize an array for error messages
            $errors = [];

            // Handle form submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Collect form data
                $firstName = $_POST["first_name"] ?? '';
                $lastName = $_POST["last_name"] ?? '';
                $email = $_POST["email"] ?? '';
                $password = $_POST["password"] ?? '';
                $passwordRepeat = $_POST["repeat_password"] ?? '';
                $dob = $_POST["dob"] ?? '';
                $gender = $_POST["gender"] ?? '';
                $country = $_POST["country"] ?? '';
                $phoneNumber = $_POST["phone_number"] ?? '';

                // Validate the form data
                if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($passwordRepeat) || empty($dob) || empty($gender) || empty($country) || empty($phoneNumber)) {
                    $errors[] = "All fields are required.";
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Invalid email format.";
                }

                if (strlen($password) < 8) {
                    $errors[] = "Password must be at least 8 characters long.";
                }

                if ($password !== $passwordRepeat) {
                    $errors[] = "Passwords do not match.";
                }

                // Validate phone number
                if (!preg_match('/^\d{10}$/', $phoneNumber)) {
                    $errors[] = "Invalid phone number format. Please enter a 10-digit number.";
                }

                // If there are no validation errors, proceed with registration
                if (empty($errors)) {
                    // Connect to the database
                    require_once "database_connect.php";

                    // Check if the email already exists
                    $sql = "SELECT * FROM users WHERE email = ?";
                    $stmt = mysqli_prepare($conn, $sql);

                    // Check if the statement was prepared successfully
                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, "s", $email);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);

                        if (mysqli_num_rows($result) > 0) {
                            $errors[] = "Email already exists!";
                        } else {
                            // Hash the password
                            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                            // Insert the user data into the database
                            $sql = "INSERT INTO users (first_name, last_name, email, password, dob, gender, country, phone_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                            $stmt = mysqli_prepare($conn, $sql);

                            // Check if the statement was prepared successfully
                            if ($stmt) {
                                mysqli_stmt_bind_param($stmt, "ssssssss", $firstName, $lastName, $email, $passwordHash, $dob, $gender, $country, $phoneNumber);
                                mysqli_stmt_execute($stmt);

                                // If insertion is successful, log the user in and redirect to the marketplace page
                                if (mysqli_stmt_affected_rows($stmt) > 0) {
                                    $_SESSION["user"] = $email;
                                    echo "<script>location.href='login.php'</script>";
                                    exit();
                                } else {
                                    $errors[] = "Registration failed. Please try again.";
                                }
                            } else {
                                $errors[] = "Database error: unable to prepare the statement.";
                            }
                        }
                    } else {
                        $errors[] = "Database error: unable to prepare the statement.";
                    }

                    // Close the statement and connection
                    mysqli_stmt_close($stmt);
                    mysqli_close($conn);
                }
            }

            // Display errors if there are any
            if (!empty($errors)) {
                echo "<div class='alert alert-danger'>";
                foreach ($errors as $error) {
                    echo "<p>$error</p>";
                }
                echo "</div>";
            }
            ?>

            <!-- Registration form -->
            <form action="" method="post">
                <div class="form-group">
                    <input type="text" class="form-control" name="first_name" placeholder="First Name" required>
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" name="last_name" placeholder="Last Name" required>
                </div>
                <div class="form-group">
                    <input type="email" class="form-control" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" name="repeat_password" placeholder="Repeat Password" required>
                </div>
                <div class="form-group">
                    <input type="date" class="form-control" name="dob" placeholder="Date of Birth" required>
                </div>
                <div class="form-group">
                    <select class="form-control" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" name="country" placeholder="Country" required>
                </div>
                <div class="form-group">
                    <input type="tel" class="form-control" name="phone_number" placeholder="Phone Number" required>
                </div>
                <div class="form-btn">
                    <input type="submit" class="btn btn-primary" name="register" value="Register">
                </div>
            </form>

            <!-- Link to login page -->
            <div>
                <p>Already have an account? <a href="login.php">Login Here</a></p>
            </div>
        </div>
    </div>
</body>

</html>