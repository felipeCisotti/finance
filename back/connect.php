<?php
    $server = "localhost";
    $user = "root";
    $password = "";
    $database = "financa";

    $connect = mysqli_connect($server, $user, $password, $database);
    if(!$connect){
        die("Connection failed: " . mysqli_connect_error());
    }
?>