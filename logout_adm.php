<?php
session_start();
session_unset();
session_destroy();
header("Location: pag_login_adm.html");
exit();
