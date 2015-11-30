<?php 
require 'config.php';

/* login process*/
session_start();
if(isset($_POST['k0'])&& $_POST['key']==date('Ymd')) {
    //try login
    $u=$_POST['k0'];
    $p=$_POST['k7'];
    $mysqli = new mysqli($DB['host'], $DB['user'], $DB['pass'], $DB['name'], $DB['port'], $DB['sock']);
    $mysqli->set_charset("utf8");
    $result = $mysqli->query("SELECT * from  users  where login='$u' and password=SHA1('$p')");        
    while($row=$result->fetch_assoc()) {
        $_SESSION['a']=$row['login'];
        $_SESSION['n']=$row['name'];       
        $_SESSION['l']=$row['level'];         
    }
    header('Location: index.php ');
}
else {
    unset($_SESSION['a']);
    unset($_SESSION['n']);
    header('Location: index.php ');    
}



?>
