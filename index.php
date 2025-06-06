<?php
// Set session cookie parameters to ensure the cookie is available for all paths
session_set_cookie_params([
    'lifetime' => 0, // Session cookie lasts until the browser is closed
    'path' => '/smartstore/', // Cookie available for all paths under /smartstore/
    'domain' => 'localhost',
    'secure' => false, // Set to true if using HTTPS
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
require_once 'db.php';

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// If user already logged in, redirect to homepage
if (isset($_SESSION['user_id'])) {
    file_put_contents('C:\xampp\htdocs\smartstore\login_debug.txt', "Redirecting logged-in user. Session ID: " . session_id() . "\nSession Data: " . print_r($_SESSION, true) . "\n", FILE_APPEND);
    header("Location: http://localhost/smartstore/");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']); // Can be email or phone
    $password = trim($_POST['password']);

    // Validate input
    if (empty($username) || empty($password)) {
        $error = "Please enter both username (email or phone) and password.";
    } else {
        // Prepare statement to find user by email or phone
        $stmt = $conn->prepare("SELECT id, full_name, password FROM users WHERE email = ? OR phone = ?");
        if ($stmt === false) {
            $error = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Verify password using password_verify
                if (password_verify($password, $user['password'])) {
                    // Password correct, set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    // Debug: Log session ID and data
                    file_put_contents('C:\xampp\htdocs\smartstore\login_debug.txt', "Login successful. Session ID: " . session_id() . "\nSession Data: " . print_r($_SESSION, true) . "\n", FILE_APPEND);
                    // Ensure session is saved
                    session_write_close();
                    header("Location: http://localhost/smartstore/");
                    exit();
                } else {
                    $error = "Invalid password. Please try again.";
                }
            } else {
                $error = "Email or phone not found.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - SmartStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#eef6fc] min-h-screen flex items-center justify-center w-screen overflow-hidden">
    <div class="w-full max-w-6xl h-[90vh] bg-white rounded-2xl shadow-2xl flex overflow-hidden">
        <!-- Left Side: Login Form -->
        <div class="w-full md:w-1/2 p-10 flex flex-col justify-center relative bg-white">
            <!-- Logo -->
            <div class="absolute top-6 left-6 flex items-center gap-2">
                <img src="../assets/images/logo.png" alt="Logo" class="w-40 h-10" />
            </div>
            <a href="/smartstore" class="absolute top-6 right-6 text-sm text-gray-500 hover:underline">← Back to Site</a>

            <h2 class="text-2xl font-semibold text-gray-800 mt-12 mb-2 text-center">Sign in with your account <span>🌟</span></h2>
            <p class="text-sm text-center text-gray-500 mb-6">Log in to your account now and take advantage of the benefits.</p>

            <?php if ($error): ?>
                <p class="text-red-500 text-center mb-4"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <!-- Google Button -->
            <div class="flex justify-center mb-4">
                <button class="bg-white shadow border border-gray-300 rounded-lg px-6 py-2 flex items-center gap-2 hover:bg-gray-100 transition">
                    <img src="../assets/images/googleLogo.png" class="w-5 h-5" />
                    <span class="text-gray-700 text-sm">Sign in with Google</span>
                </button>
            </div>

            <div class="relative mb-4 text-center">
                <div class="absolute inset-0 flex items 10% of the page (or screen). */
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative text-sm bg-white px-2 text-gray-500">or</div>
            </div>

            <form method="POST" action="" class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-600 mb-1">E-Mail Address or Phone</label>
                    <input type="text" id="username" name="username" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400" required />
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-600 mb-1">Password</label>
                    <input type="password" id="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400" required />
                </div>
                <button type="submit" class="w-full bg-[#ff6600] text-white py-2 rounded-md hover:bg-[#e05500] transition">Sign in</button>
            </form>

            <div class="text-center mt-4">
                <button id="forgotPasswordButton" class="text-sm text-blue-600 hover:underline">Forgot Password?</button>
            </div>

            <div class="text-center mt-4">
                <span class="text-sm text-gray-600">Don't have a SmartStore Account?</span>
                <a href="register.php" class="text-sm text-white bg-orange-500 ml-2 px-3 py-1 rounded hover:bg-orange-600">Register Now</a>
            </div>
        </div>

        <!-- Right Side: Promo / Image -->
        <div class="w-full md:w-1/2 relative bg-gradient-to-tr from-purple-500 to-orange-400 flex items-center justify-center">
            <div class="absolute inset-0 z-0">
                <img src="https://images.unsplash.com/photo-1607746882042-944635dfe10e" alt="Confetti" class="w-full h-full object-cover opacity-70" />
            </div>
            <div class="z-10 text-center px-8 text-white">
                <h2 class="text-3xl font-extrabold mb-2">Time to Grow on Social Media!</h2>
                <p class="text-sm font-semibold leading-relaxed max-w-md mx-auto">Welcome to SmartStore | The platform that boosts your Social Media! Explore all packages now.</p>
            </div>
        </div>
    </div>

    <!-- Forgot Password Pop-up -->
    <div id="forgotPasswordPopup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
            <h3 class="text-xl font-semibold mb-4">Forgot Password</h3>
            <form id="forgotPasswordForm" method="POST" action="forgot_password.php" class="space-y-4">
                <div>
                    <label for="forgotUsername" class="block text-sm font-medium text-gray-600 mb-1">Email or Phone</label>
                    <input type="text" id="forgotUsername" name="username" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400" required />
                </div>
                <button type="submit" name="send_otp" class="w-full bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600 transition">Send OTP</button>
            </form>
            <div id="otpSection" class="hidden mt-4">
                <p class="text-green-500 text-center mb-2">Enter the OTP sent to your email/phone:</p>
                <p id="otpDisplay" class="text-gray-600 text-center mb-4 font-semibold"></p>
                <form id="verifyOtpForm" class="space-y-4">
                    <input type="hidden" name="user_id" id="otpUserId">
                    <div>
                        <label for="otpInput" class="block text-sm font-medium text-gray-600 mb-1">Enter OTP</label>
                        <input type="text" id="otpInput" name="otp" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400" required />
                    </div>
                    <button type="button" onclick="verifyOTP()" class="w-full bg-green-500 text-white py-2 rounded-md hover:bg-green-600 transition-colors">Verify OTP</button>
                </form>
            </div>
            <button id="closeForgotPopup" class="mt-4 text-sm text-gray-600 hover:underline">Close</button>
        </div>
    </div>

    <!-- Reset Password Pop-up -->
    <div id="resetPasswordPopup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
            <h3 class="text-xl font-semibold mb-4">Reset Password</h3>
            <form id="resetPasswordForm" method="POST" action="forgot_password.php" class="space-y-4">
                <input type="hidden" name="username" id="resetUsername">
                <div>
                    <label for="newPassword" class="block text-sm font-medium text-gray-600 mb-1">New Password</label>
                    <input type="password" id="newPassword" name="new_password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400" required />
                </div>
                <div>
                    <label for="confirmNewPassword" class="block text-sm font-medium text-gray-600 mb-1">Confirm New Password</label>
                    <input type="password" id="confirmNewPassword" name="confirm_new_password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400" required />
                </div>
                <button type="submit" name="reset_password" class="w-full bg-orange-500 text-white py-2 rounded-md hover:bg-orange-600 transition">Reset Password</button>
            </form>
            <button id="closeResetPopup" class="mt-4 text-sm text-gray-600 hover:underline">Close</button>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>