SmartStore Project
Setup Instructions

Create a database named login in phpMyAdmin using XAMPP Server.
Import the login.sql file to set up the database schema.
Place the project files in C:\xampp\htdocs\smartstore\.
Start the XAMPP server (Apache and MySQL).
Access the project at https://forgotpasswordmanager.ct.ws/

Features

Sign Up: Register a new user with full name, email, phone, and password.
Login: Log in using email or phone and password.
Forgot Password: 
Click "Forgot Password?" on the login page.
Enter email or phone, receive an OTP (displayed on the interface for development mode).
Verify OTP and reset the password.


Change Password:
On the homepage, click "Change Password".
Enter current password, new password, and confirm new password to update.


Logout: Log out and destroy the session.

File Structure

login.sql: Database schema with users and otps tables.
db.php: Database connection configuration.
index.php: Login page with "Forgot Password" functionality.
forgot_password.php: Handles OTP generation, verification, and password reset.
register.php: Sign-up page.
homepage.php: Homepage for logged-in users with "Change Password" functionality.
logout.php: Logs out the user.
script.js: JavaScript for handling pop-up visibility and form toggling.


  
