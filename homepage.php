<?php
session_start();
require_once 'db.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: http://localhost/smartstore/");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
        $error = "All fields are required.";
    } elseif ($new_password !== $confirm_new_password) {
        $error = "New passwords do not match.";
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (password_verify($current_password, $user['password'])) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
            if ($stmt->execute()) {
                $success = "Password changed successfully!";
            } else {
                $error = "Failed to change password. Please try again.";
            }
        } else {
            $error = "Current password is incorrect.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md text-center">
        <h2 class="text-2xl font-bold mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h2>
        <p class="text-gray-700 mb-6">Thank you for joining us. We're excited to have you here!</p>
        <button id="changePasswordButton" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 mb-4">Change Password</button>
        <a href="logout.php" class="bg-red-500 text-white py-2 px-4 rounded-lg hover:bg-red-600">Logout</a>
    </div>

    <!-- Change Password Pop-up -->
    <div id="changePasswordPopup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
            <h3 class="text-xl font-semibold mb-4">Change Password</h3>
            <?php if ($error): ?>
                <p class="text-red-500 mb-4"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="text-green-500 mb-4"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>
            <form method="POST" action="" class="space-y-4">
                <div>
                    <label for="currentPassword" class="block text-sm font-medium text-gray-600 mb-1">Current Password</label>
                    <input type="password" id="currentPassword" name="current_password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400" required />
                </div>
                <div>
                    <label for="newPassword" class="block text-sm font-medium text-gray-600 mb-1">New Password</label>
                    <input type="password" id="newPassword" name="new_password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400" required />
                </div>
                <div>
                    <label for="confirmNewPassword" class="block text-sm font-medium text-gray-600 mb-1">Confirm New Password</label>
                    <input type="password" id="confirmNewPassword" name="confirm_new_password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400" required />
                </div>
                <button type="submit" name="change_password" class="w-full bg-orange-500 text-white py-2 rounded-md hover:bg-orange-600 transition">Change Password</button>
            </form>
            <button id="closeChangePasswordPopup" class="mt-4 text-sm text-gray-600 hover:underline">Close</button>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>