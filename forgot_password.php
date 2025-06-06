<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid request method']);
    exit();
}

$step = $_POST['step'] ?? '';

if ($step === 'request_otp') {
    $input = trim($_POST['input'] ?? '');
    
    if (empty($input)) {
        echo json_encode(['status' => 'error', 'msg' => 'Email or phone is required']);
        exit();
    }

    $stmt = $conn->prepare("SELECT id, email, phone FROM users WHERE email = ? OR phone = ?");
    if ($stmt === false) {
        echo json_encode(['status' => 'error', 'msg' => 'Database error: ' . $conn->error]);
        exit();
    }
    
    $stmt->bind_param("ss", $input, $input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'msg' => 'User not found']);
        $stmt->close();
        exit();
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    $otp = rand(100000, 999999); // Generate 6-digit OTP
    $expires = date("Y-m-d H:i:s", time() + 300); // 5-minute expiry

    // Store OTP in database
    $insert_stmt = $conn->prepare("INSERT INTO otps (user_id, otp, expires_at) VALUES (?, ?, ?)");
    if ($insert_stmt === false) {
        echo json_encode(['status' => 'error', 'msg' => 'Database error: ' . $conn->error]);
        exit();
    }
    
    $insert_stmt->bind_param("iss", $user['id'], $otp, $expires);
    if (!$insert_stmt->execute()) {
        echo json_encode(['status' => 'error', 'msg' => 'Failed to store OTP: ' . $insert_stmt->error]);
        $insert_stmt->close();
        exit();
    }
    $insert_stmt->close();

    // Fetch the latest OTP from the database to confirm it’s stored
    $fetch_stmt = $conn->prepare("SELECT otp FROM otps WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    if ($fetch_stmt === false) {
        echo json_encode(['status' => 'error', 'msg' => 'Database error: ' . $conn->error]);
        exit();
    }
    
    $fetch_stmt->bind_param("i", $user['id']);
    $fetch_stmt->execute();
    $fetch_result = $fetch_stmt->get_result();
    
    if ($fetch_result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'msg' => 'OTP not found in database']);
        $fetch_stmt->close();
        exit();
    }

    $otp_row = $fetch_result->fetch_assoc();
    $fetched_otp = $otp_row['otp'];
    $fetch_stmt->close();

    // For development: Return the OTP in the response
    echo json_encode([
        'status' => 'success',
        'msg' => 'OTP has been sent successfully',
        'user_id' => $user['id'],
        'otp' => $fetched_otp // Use the OTP fetched from the database
    ]);
    exit();
}

if ($step === 'verify_otp') {
    $user_id = $_POST['user_id'] ?? '';
    $otp = $_POST['otp'] ?? '';

    if (empty($user_id) || empty($otp)) {
        echo json_encode(['status' => 'error', 'msg' => 'User ID and OTP are required']);
        exit();
    }

    $stmt = $conn->prepare("SELECT otp, expires_at FROM otps WHERE user_id = ? AND otp = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("is", $user_id, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'invalid', 'msg' => 'Invalid OTP']);
        $stmt->close();
        exit();
    }

    $row = $result->fetch_assoc();
    $expires_at = strtotime($row['expires_at']);
    $current_time = time();

    if ($current_time > $expires_at) {
        echo json_encode(['status' => 'invalid', 'msg' => 'OTP has expired']);
        $stmt->close();
        exit();
    }

    $delete_stmt = $conn->prepare("DELETE FROM otps WHERE user_id = ? AND otp = ?");
    $delete_stmt->bind_param("is", $user_id, $otp);
    $delete_stmt->execute();
    $delete_stmt->close();

    echo json_encode(['status' => 'valid', 'msg' => 'OTP verified successfully']);
    $stmt->close();
    exit();
}

if ($step === 'reset_password') {
    $user_id = $_POST['user_id'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    if (empty($user_id) || empty($new_password)) {
        echo json_encode(['status' => 'error', 'msg' => 'User ID and new password are required']);
        exit();
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'updated', 'msg' => 'Password updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Failed to update password']);
    }
    $stmt->close();
    exit();
}

echo json_encode(['status' => 'error', 'msg' => 'Invalid step']);
?>