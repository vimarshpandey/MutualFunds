<?php
    date_default_timezone_set("Asia/Kolkata");
    $cur_date = date('Y-m-d');
    $cur_time = date('H:i:s');
    $con = mysqli_connect("127.0.0.2:3307","root","","mutual_funds");
    if(!$con)
    {
        die("Connection Error");
    }
?>