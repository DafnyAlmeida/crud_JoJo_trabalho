<?php 
session_start(); # Pega a sessão atual

$_SESSION = []; # Esvazia a sessão atual

session_destroy(); # Destroi a sessão

header("Location: login.php?status=logout_ok"); # Envia de volta para o logion

exit;