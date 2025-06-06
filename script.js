document.addEventListener('DOMContentLoaded', function () {
    const forgotPasswordButton = document.getElementById('forgotPasswordButton');
    const forgotPasswordPopup = document.getElementById('forgotPasswordPopup');
    const closeForgotPopupBtn = document.getElementById('closeForgotPopup');
    const forgotPasswordForm = document.getElementById('forgotPasswordForm'); // Add reference to the form
    const otpSection = document.getElementById('otpSection');
    const resetSection = document.getElementById('resetSection');
    const otpDisplay = document.getElementById('otpDisplay');

    const changePasswordBtn = document.getElementById('changePasswordButton');
    const changePasswordPopupBtn = document.getElementById('changePasswordPopup');
    const closeChangePasswordPopupBtn = document.getElementById('closeChangePasswordPopup');

    if (forgotPasswordButton && forgotPasswordPopup) {
        forgotPasswordButton.addEventListener('click', function () {
            forgotPasswordPopup.classList.remove('hidden');
        });
    }

    if (closeForgotPopupBtn) {
        closeForgotPopupBtn.addEventListener('click', function () {
            forgotPasswordPopup.classList.add('hidden');
            forgotPasswordForm.classList.remove('hidden'); // Show the form again on close
            otpSection.classList.add('hidden');
            resetSection.classList.add('hidden');
            otpDisplay.textContent = ''; // Clear OTP display on close
        });
    }

    if (changePasswordBtn && changePasswordPopupBtn) {
        changePasswordBtn.addEventListener('click', function () {
            changePasswordPopupBtn.classList.remove('hidden');
        });
    }

    if (closeChangePasswordPopupBtn) {
        closeChangePasswordPopupBtn.addEventListener('click', function () {
            changePasswordPopupBtn.classList.add('hidden');
        });
    }

    window.sendOTP = function () {
        let input = document.getElementById('forgotUsername').value;
        const currentTime = "11:06 PM IST, June 06, 2025"; // Updated to current system time, though not used in display

        fetch("forgot_password.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `step=request_otp&input=${encodeURIComponent(input)}`
        })
        .then(res => res.json())
        .then(data => {
            console.log("Fetch Response:", data); // Debug: Log the response to console
            if (data.status === 'success') {
                // For development: Display only the 6-digit OTP on the interface
                if (data.otp) {
                    otpDisplay.textContent = data.otp; // Only show the 6-digit OTP (e.g., 123456)
                } else {
                    otpDisplay.textContent = 'OTP not received';
                    console.error("OTP not found in response:", data);
                }
                sessionStorage.setItem("user_id", data.user_id);
                document.getElementById('otpUserId').value = data.user_id;
                forgotPasswordForm.classList.add('hidden'); // Hide the Send OTP form
                otpSection.classList.remove('hidden'); // Show the OTP section
            } else {
                otpDisplay.textContent = ''; // Clear OTP on error
                otpSection.classList.add('hidden'); // Hide OTP section on error
                alert("Error: " + data.msg); // Show error message
            }
        })
        .catch(error => {
            console.error("Error occurred while sending OTP:", error);
            otpDisplay.textContent = 'Error fetching OTP';
            otpSection.classList.add('hidden'); // Hide OTP section on error
            alert("An error occurred while sending OTP.");
        });
    };

    window.verifyOTP = function () {
        let otp = document.getElementById("otpInput").value;
        let user_id = sessionStorage.getItem("user_id");

        fetch("forgot_password.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `step=verify_otp&user_id=${encodeURIComponent(user_id)}&otp=${encodeURIComponent(otp)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === "valid") {
                document.getElementById("resetUserId").value = user_id;
                otpSection.classList.add('hidden'); // Hide OTP section (already done, confirming)
                resetSection.classList.remove('hidden'); // Show reset password section
            } else {
                alert("Invalid OTP. Please try again.");
            }
        })
        .catch(error => {
            console.error("Error verifying OTP:", error);
            alert("An error occurred while verifying OTP.");
        });
    };

    window.resetPassword = function () {
        let newPassword = document.getElementById("newPassword").value;
        let confirmNewPassword = document.getElementById("confirmNewPassword").value;
        let user_id = sessionStorage.getItem("user_id");

        if (newPassword !== confirmNewPassword) {
            alert("Passwords do not match!");
            return;
        }

        fetch("forgot_password.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `step=reset_password&user_id=${encodeURIComponent(user_id)}&new_password=${encodeURIComponent(newPassword)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === "updated") {
                alert("Password updated successfully! Please login with your new password.");
                window.location.href = "index.php";
            } else {
                alert("Error: " + (data.msg || "Failed to update password."));
            }
        })
        .catch(error => {
            console.error("Error resetting password:", error);
            alert("An error occurred while resetting password.");
        });
    };
});