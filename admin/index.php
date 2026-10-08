<?php
declare(strict_types=1);
require_once __DIR__ . '/../inc/functions.php';

// /admin nebo /admin/ samo o sobě nic nezobrazuje — jen přesměruje na
// správné místo podle toho, jestli je návštěvník přihlášený.
// Cíl "login.php" (ne hezké "login") je tu záměrně přímý název souboru,
// aby přesměrování fungovalo i na hostingu bez zapnutého mod_rewrite.
header('Location: ' . (isAdmin() ? '../index.php' : 'login.php'));
exit;
