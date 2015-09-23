<?php


include_once("./admin/_mysql.php");


$DATA = NULL;
$TITLE = NULL;


if(!isset($_GET["id"]) OR empty($_GET["id"]))
{ // neni zadane id newsletteru (chyba)
  $DATA = "Špatná url adresa";
}
else
{
  // obsah newsletteru
  $query = "
  SELECT text , subject
  FROM ".T_INFO_NEWS."
  WHERE id = '".$_GET["id"]."'
  ";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  $z = mysql_fetch_array($v);

  if(!isset($z["text"]))
  { // obsah nenalezen (chyba)
    $DATA = "Špatná url adresa";
  }
  else
  { // obsah newsletteru
    // ukoncovani neparovych tagu odstranime z duvodu validity
    $trans = array("/>" => ">");
    $DATA = strtr($z["text"], $trans);

    $TITLE = $z["subject"];
  }
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <title><?php echo $TITLE; ?></title>
  </head>
  <body>
    <?php
      echo $DATA;
    ?>
  </body>
</html>
