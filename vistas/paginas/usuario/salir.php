<?php
session_reset();
session_unset();
session_destroy();
header('location:indexp.php');

?>