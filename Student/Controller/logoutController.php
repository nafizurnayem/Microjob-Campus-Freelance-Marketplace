<?php
require "../../Model/init.php";

logoutUser();

Header("Location: " . BASE_URL . "/Student/View/login.php");
exit;
