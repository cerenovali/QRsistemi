<?php
require_once __DIR__ . '/src/core/Oturum.php';
Oturum::baslat();
Oturum::cikisYap();
header('Location: /giris.php');
exit();
