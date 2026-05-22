session_start();


if(isset($_SESSION['userId'])){
    header('Location: pagina.php');
    exit();
}

else{
    exit();
}