<?php
session_start();
if (isset($_SESSION['userRole'])) {
    echo json_encode($_SESSION['userRole']);
} else {
    echo json_encode(null);
}
?>