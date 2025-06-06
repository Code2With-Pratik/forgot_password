<?php
// Set session cookie parameters
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/smartstore/',
    'domain' => 'localhost',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
require_once 'db.php';

// Enable error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    file_put_contents('C:\xampp\htdocs\smartstore\register_debug.txt', "User already logged in. Session ID: " . session_id() . "\nSession Data: " . print_r($_SESSION, true) . "\n", FILE_APPEND);
    header("Location: http://localhost/smartstore/");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Phone number must be 10 digits.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
        if ($stmt === false) {
            $error = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("ss", $email, $phone);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Email or phone already registered.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $full_name, $email, $phone, $hashed_password);

                if ($stmt->execute()) {
                    $success = "Registration successful! Redirecting to login...";
                    file_put_contents('C:\xampp\htdocs\smartstore\register_debug.txt', "User registered successfully. Email: $email\n", FILE_APPEND);
                    header("refresh:2;url=index.php");
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Register - SmartStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#eef6fc] min-h-screen flex items-center justify-center w-screen overflow-hidden">
    <div class="w-full max-w-6xl h-[90vh] bg-white rounded-2xl shadow-2xl flex overflow-hidden">
        <!-- Left Side: Registration Form -->
        <div class="w-full md:w-1/2 p-10 flex flex-col justify-center relative bg-white">
            <!-- Logo -->
            <div class="absolute top-2 left-2 flex items-center gap-2">
                <img src="../assets/images/logo.png" alt="Logo" class="w-32 h-8" />
            </div>
            <a href="/smartstore" class="absolute top-3 right-3 text-sm text-gray-500 hover:underline">← Back to Site</a>

            <h2 class="text-2xl font-semibold text-gray-800 mt-12 mb-2 text-center">Create your SmartStore account 🚀</h2>
            <p class="text-sm text-center text-gray-500 mb-6">Register now and explore the SmartStore features!</p>

            <?php if ($error): ?>
                <p class="text-red-500 text-center mb-4"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="text-green-500 text-center mb-4"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-4">
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-600 mb-1">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : ''; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400" required>
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-600 mb-1">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400" required>
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-600 mb-1">Phone</label>
                    <input type="text" id="phone" name="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400" required>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-600 mb-1">Password</label>
                    <input type="password" id="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400" required>
                </div>
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-600 mb-1">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400" required>
                </div>
                <button type="submit" class="w-full bg-[#ff6600] text-white py-2 rounded-md hover:bg-[#e05500] transition">Create Account</button>
            </form>

            <div class="text-center mt-4 pb-2">
                <span class="text-sm text-gray-600">Already have an account?</span>
                <a href="index.php" class="text-sm text-white bg-orange-500 ml-2 px-3 py-1 rounded hover:bg-orange-600">Sign in</a>
            </div>
        </div>

        <!-- Right Side: Promo / Image -->
        <div class="w-full md:w-1/2 relative bg-gradient-to-tr from-purple-500 to-orange-400 flex items-center justify-center">
            <div class="absolute inset-0 z-0">
                <img src="https://images.unsplash.com/photo-1607746882042-944635dfe10e" alt="Confetti" class="w-full h-full object-cover opacity-70" />
            </div>
            <div class="z-10 text-center px-8 text-white">
                <h2 class="text-3xl font-extrabold mb-2">Join the SmartStore family!</h2>
                <p class="text-sm font-semibold leading-relaxed max-w-md mx-auto">Create your account and start boosting your social media presence today.</p>
            </div>
        </div>
    </div>
</body>
</html>