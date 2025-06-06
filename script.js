const signUpButton = document.getElementById('signUpButton');
const signInButton = document.getElementById('signInButton');
const signInForm = document.getElementById('signIn');
const signUpForm = document.getElementById('signup');

// Forgot Password Pop-up
const forgotPasswordButton = document.getElementById('forgotPasswordButton');
const forgotPasswordPopup = document.getElementById('forgotPasswordPopup');
const closeForgotPopup = document.getElementById('closeForgotPopup');
const otpSection = document.getElementById('otpSection');
const otpDisplay = document.getElementById('otpDisplay');
const otpUsername = document.getElementById('otpUsername');
const resetPasswordPopup = document.getElementById('resetPasswordPopup');
const closeResetPopup = document.getElementById('closeResetPopup');
const resetUsername = document.getElementById('resetUsername');

// Change Password Pop-up
const changePasswordButton = document.getElementById('changePasswordButton');
const changePasswordPopup = document.getElementById('changePasswordPopup');
const closeChangePasswordPopup = document.getElementById('closeChangePasswordPopup');

// Handle sign-up and sign-in form toggling (if used)
if (signUpButton && signInButton && signInForm && signUpForm) {
    signUpButton.addEventListener('click', function() {
        signInForm.style.display = "none";
        signUpForm.style.display = "block";
    });

    signInButton.addEventListener('click', function() {
        signInForm.style.display = "block";
        signUpForm.style.display = "none";
    });
}

// Forgot Password Pop-up
if (forgotPasswordButton) {
    forgotPasswordButton.addEventListener('click', function() {
        forgotPasswordPopup.classList.remove('hidden');
    });
}

if (closeForgotPopup) {
    closeForgotPopup.addEventListener('click', function() {
        forgotPasswordPopup.classList.add('hidden');
        otpSection.classList.add('hidden');
    });
}

// Show OTP section if redirected back after sending OTP
if (window.location.search.includes('show_otp=1')) {
    forgotPasswordPopup.classList.remove('hidden');
    otpSection.classList.remove('hidden');
    const otp = "<?php echo isset($_SESSION['otp']) ? $_SESSION['otp'] : ''; ?>";
    otpDisplay.textContent = otp;
    otpUsername.value = "<?php echo isset($_SESSION['otp_user']) ? $_SESSION['otp_user'] : ''; ?>";
}

// Show Reset Password pop-up if OTP is verified
if (window.location.search.includes('reset_password=1')) {
    resetPasswordPopup.classList.remove('hidden');
    resetUsername.value = "<?php echo isset($_SESSION['otp_verified_user']) ? $_SESSION['otp_verified_user'] : ''; ?>";
}

if (closeResetPopup) {
    closeResetPopup.addEventListener('click', function() {
        resetPasswordPopup.classList.add('hidden');
    });
}

// Change Password Pop-up
if (changePasswordButton) {
    changePasswordButton.addEventListener('click', function() {
        changePasswordPopup.classList.remove('hidden');
    });
}

if (closeChangePasswordPopup) {
    closeChangePasswordPopup.addEventListener('click', function() {
        changePasswordPopup.classList.add('hidden');
    });
}

function alertFunc() {
    alert("alert");
}

function sendOTP() {
    let input = document.getElementById("emailOrPhone").value;

    fetch("forgot_password.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `step=request_otp&input=${input}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            alert("OTP: " + data.otp); // For development only
            sessionStorage.setItem("user_id", data.user_id);
            document.getElementById("otpSection").style.display = "block";
        } else {
            alert("User not found!");
        }
    });
}

function verifyOTP() {
    let otp = document.getElementById("otpField").value;
    let user_id = sessionStorage.getItem("user_id");

    fetch("forgot_password.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `step=verify_otp&user_id=${user_id}&otp=${otp}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "valid") {
            document.getElementById("resetSection").style.display = "block";
        } else {
            alert("Invalid OTP");
        }
    });
}

function resetPassword() {
    let newPassword = document.getElementById("newPassword").value;
    let user_id = sessionStorage.getItem("user_id");

    fetch("forgot_password.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `step=reset_password&user_id=${user_id}&new_password=${newPassword}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "updated") {
            alert("Password updated successfully");
        }
    });
}
