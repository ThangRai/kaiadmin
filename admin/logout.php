<?php
session_start();
session_destroy();
// Thêm tham số logout vào URL
header("Location: login.php?logout=1");
exit;
?>
