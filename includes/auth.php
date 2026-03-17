<?php
session_start();

function requireLogin(){
    if(!isset($_SESSION["user_id"])){
        header("Location: login.php");
        exit();
    }
}

function requireAdmin(){
    if(!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin"){
        die("Accès refusé");
    }
}