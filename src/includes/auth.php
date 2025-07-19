<?php
// Include config file
require_once 'config.php';

// Function to register a new user
function registerUser($firstName, $location, $email, $phone, $password) {
    global $conn;
    
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO users (first_name, location, email, phone, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $firstName, $location, $email, $phone, $hashedPassword);
    
    if ($stmt->execute()) {
        // Set session variables and return success
        $userId = $conn->insert_id;
        
        
        // Update last login time
        $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $updateStmt->bind_param("i", $userId);
        $updateStmt->execute();
        
        return true;
    } else {
        return false;
    }
}

// Function to login a user
function loginUser($email, $password) {
    global $conn;
    
    // Prepare statement to get user with the given email
    $stmt = $conn->prepare("SELECT id, first_name, location, email, phone, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['location'] = $user['location'];
            $_SESSION['email'] = $user['email'];
			$_SESSION['phone'] = $user['phone'];
            
            // Update last login time
            $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $user['id']);
            $updateStmt->execute();
            
            return true;
        }
    }
    
    return false;
}

// Function to logout a user
function logoutUser() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    return true;
}

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    if (loginUser($email, $password)) {
        header("Location: ../index.php");
        exit;
    } else {
        // Store error in session instead of local variable
        $_SESSION['loginError'] = "Invalid email or password";
        header("Location: ../login.php");
        exit;
    }
}

// Process signup form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $firstName = $_POST['firstName'];
    $location = $_POST['location'];
    $email = $_POST['email'];
	$phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    
    // Validate password match
    if ($password !== $confirmPassword) {
        $_SESSION['signupError'] = "Passwords do not match";
        header("Location: ../signup.php");
        exit;
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $_SESSION['signupError'] = "Email already exists";
            header("Location: ../signup.php");
            exit;
        } else {
            if (registerUser($firstName, $location, $email, $phone, $password)) {
				 $_SESSION['signupSuccess'] = "Successfully Added User";
                header("Location: ../signup.php");
                exit;
            } else {
                $_SESSION['signupError'] = "Registration failed. Please try again.";
                header("Location: ../signup.php");
                exit;
            }
        }
    }
}

// Process logout request
if (isset($_GET['logout'])) {
    logoutUser();
    header("Location: ../index.php");
    exit;
}
?> 