<?php

include_once("./newsletter_functions.php");

if(!empty($_POST["email"]))
{ // pridani emailu do newsletteru
  delete_from_blacklist_newsletter($_POST["email"]);
  add_newsletter($_POST["email"]);

  // zprava pro uzivatele o pridani emailu
  $_SESSION['alert_js1'] = "Email ".$_POST["email"]." přidán.";

  // zpet na stranku
  header('Location: '.$_SERVER['HTTP_REFERER']);
	exit;
}

?>