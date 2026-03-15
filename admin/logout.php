<?php
require_once __DIR__ . '/config.php';

session_name(SESSION_NAME);
session_start();
session_unset();
session_destroy();

header('Location: login.php');
exit;
