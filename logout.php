<?php
function logout_and_redirect($redirect_url = '/')
{
    session_start();
    session_unset();
    session_destroy();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    header("Location: $redirect_url");
    exit();
}

logout_and_redirect('http://localhost/smartstore/');
?>
