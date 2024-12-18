<?php
if (!function_exists('getDatabaseConnection')) {
    function getDatabaseConnection(){
        $servername = "localhost";
        $username = "root";
        $password = "";
        $database = "kpop4life";

        $connection = new mysqli($servername, $username, $password, $database);
        if ($connection->connect_error){
            die("Error failed to connect to MySQL: " . $connection->connect_error);
        }
        return $connection; 
    }
}

if (!function_exists('closeDatabaseConnection')) {
    function closeDatabaseConnection($connection){
        if ($connection) {
            $connection->close();
        }
    }
}
?>