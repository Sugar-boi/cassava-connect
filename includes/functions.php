<?php
// includes/functions.php
//session_start();

function is_logged_in(){
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function current_user_id(){
    return $_SESSION['user_id'] ?? null;
}

function current_user_role(){
    return $_SESSION['user_role'] ?? null;
}

function flash_set($msg){
    $_SESSION['flash'] = $msg;
}
function flash_get(){
    $msg = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $msg;
}
