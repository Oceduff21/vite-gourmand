<?php

function setFlash($message){
$_SESSION["flash"] = $message;
}

function showFlash(){

if(isset($_SESSION["flash"])){

echo '<div class="alert alert-success">'
.$_SESSION["flash"].
'</div>';

unset($_SESSION["flash"]);

}
}