<?php 
session_start();

if (!isset($_SESSION['usuario_id'])) {

    header('Location: ../public/login.php?status=acesso_negado');
    exit;
}