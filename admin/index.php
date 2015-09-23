<?php
// TODO pridat fotogalerie pro produkt i samostane reseni


// cas zpracovani
function getmicrotime() {

	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);

}

$time_start = getmicrotime();





include_once "./_mysql.php";

include_once './_functions.php';

include_once './_security.php';





// seznam s odkazy na aplikace
$apps_count = count($apps);
$app_menu='';
$i = 1;
reset($apps);
while ($p = each($apps)) {

	$k = $p['key'];
	$v = $p['value'];
	
	
	if($k == APP) $akt_app = "
	<div class=\"app_aktivni\"><a href=\"".C_LOGIN."\">$v</a></div>";
	else $app_menu .= "
	<div class=\"app\"><a href=\"".C_LOGIN."&app=$k\">$v</a></div>";
	
	$i++;

}





if(empty($dct['first']))$dct['first']='';

// includujeme skripty
if(isset($_GET['f'])) $file = APP."/".$_GET['f'].'.php';

if(!isset($_GET['f'])) $data = $dct['first'];
else if(file_exists($file)) include_once $file;
else $data = $dct['not_exists'];

// potrebujeme editovat prihlaseneho uzivatele (admina) - viz _security.php
if(!empty($admin_form)) $data = $admin_form;









if(is_dir(APP)) {

	// ***************************************************************************
	// menu aplikaci
	// ***************************************************************************
	
	
	// natahneme menu aplikace
	if(file_exists(APP.'/_menu.php')) include_once APP.'/_menu.php';
	
	
	
	// generujeme menu
	unset($_SESSION['menu_tree']); // reset menu
	
	
	$m_poradi = "-1"; // pocitadlo polozek
	$np = "-1"; // nadrazena polozka
	$level_tree = "0"; // uroven ve stromu jen kvuli odsazeni
	
	get_menu($level_tree,$mn0,$m_poradi,$mn,$np);
	
	
	
	if(!empty($_SESSION['menu_tree'])) {
	
		$menu = "
		
		
		<div class=\"dtree\">
		
			<p>
				<a href=\"javascript: d.openAll();\" class=\"f9\">".$dct['mn_otevrit_vse']."</a> | 
				<a href=\"javascript: d.closeAll();\" class=\"f9\">".$dct['mn_zavrit_vse']."</a>
			</p>
			
			
			<script type=\"text/javascript\">
				<!--
				d = new dTree('d');
				".$_SESSION['menu_tree']."
				document.write(d);
				//-->
			</script>
		
		</div>";
	
	}
	
	
	unset($_SESSION['menu_tree']); // reset menu
	// ***************************************************************************
	// menu aplikaci
	// ***************************************************************************

}






header("Pragma: No-cache");
header("Cache-Control: no-cache");
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Expires: ".gmdate("D, d M Y H:i:s")." GMT");
header ("Cache-Control: no-cache, must-revalidate");



  if(empty($onload))$onload='';

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<title>Administrace - <?php echo $_SERVER['SERVER_NAME'];?></title>
	<meta http-equiv="pragma" content="no-cache">
	<meta http-equiv="cache-control" content="no-cache, must-revalidate">
	<meta http-equiv="expires" content="0">
	<meta http-equiv="last-modified" content="">
	
	<meta name="robots" content="noindex,nofollow">
	<meta name="robots" content="noarchive">
	
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
	<meta name="author" content="blazevsky, info@netaction.cz">
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
	
	<link href="style.css" rel="stylesheet" type="text/css">
	<link href="dtree.css" rel="stylesheet" type="text/css">
	<script src="fce.js" type=text/javascript></script>
	<script type="text/javascript" src="dtree.js"></script>
	<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
	
	
  <?php if(isset($_GET['f']) AND $_GET['f'] == "statistics") { //js pro statistiku ?>
  <script src="datapicker/jquery-1.9.1.js"></script>

  <!-- Kalendář -->
  <link rel="stylesheet" href="datapicker/jquery-ui.css">
  <script src="datapicker/jquery-ui.js"></script>

  <!-- Grafy -->
  <script type="text/javascript" src="js/jquery.jqplot.1.0.8r1250/jquery.jqplot.min.js"></script>
  <script type="text/javascript" src="js/jquery.jqplot.1.0.8r1250/plugins/jqplot.canvasTextRenderer.min.js"></script>
  <script type="text/javascript" src="js/jquery.jqplot.1.0.8r1250/plugins/jqplot.canvasAxisTickRenderer.min.js"></script>
  <script type="text/javascript" src="js/jquery.jqplot.1.0.8r1250/plugins/jqplot.dateAxisRenderer.min.js"></script>
  <script type="text/javascript" src="js/jquery.jqplot.1.0.8r1250/plugins/jqplot.barRenderer.min.js"></script>
  <script type="text/javascript" src="js/jquery.jqplot.1.0.8r1250/plugins/jqplot.categoryAxisRenderer.min.js"></script>
  <script type="text/javascript" src="js/jquery.jqplot.1.0.8r1250/plugins/jqplot.pointLabels.min.js"></script>
  <link rel="stylesheet" type="text/css" href="js/jquery.jqplot.1.0.8r1250/jquery.jqplot.min.css" />
  <?php } ?> 
  
  <?php if(isset($_GET['f']) AND $_GET['f'] == "products") { //js pro produkty ?>
  <script type="text/javascript" src="../js/jquery/jquery-1.8.2.min.js"></script>
  <link href="../js/fancybox/jquery.fancybox.css" type="text/css" rel="stylesheet">
  
  <script type="text/javascript" src="../js/fancybox/jquery.fancybox.js"></script>
	
	<script type="text/javascript">
		$(document).ready(
    function()
    {
			$('.fancybox_foto').fancybox(
      {
		    'width'				: '720',
		    'height'			: '60%',
        'autoScale'  	: false,
		    'type'				: 'iframe'
      });
		});

	</script>
  <?php } ?>

<?php
	// hlaseni do js alertu
	/**/
	if(!empty($_SESSION['alert_js'])) {
	
		echo "
		<script language=\"javascript\">
		function show_alert() {
			alert('".$_SESSION['alert_js']."');
		}
		</script>";
		
		$onload = "onload=\"show_alert()\"";
		
		unset($_SESSION['alert_js']);
	
	}
?>

</head>




		
<body <?php echo $onload; ?>>



<div id="left">

	
<?php
	
		
// 		echo $_SERVER['SERVER_NAME']."<br /><br />";
// 		echo $_SESSION['UserFilesPath']."<br /><br />";
    if(empty($akt_app))$akt_app='';
    if(empty($menu))$menu='';
    
		echo $akt_app;
		
		echo $menu;
		
?>
	
	
	
	<div id="app_main">
	
		<?php echo $app_menu;?>
	
	</div>


</div>





<div id="top">
	
	
	<?php echo $C_lang_selector;?>
	
	
	<div id="langs">
	
		<?php	echo S_LANG_PANEL;	?>
	
	</div>

	<div id="user">
	
		<?php	echo $S_user_data;	?>
	
	</div>

</div>




<div id="content">


<?php
	
	// nadpis stranky:
	// n1 predava key $dct ze slovniku
	// zatimco n2 primo vyraz (pouzito hlavne u polozek shopu)
	// - viz polozky menu
	// if(isset($_GET['n1'])) $nadpis = $dct[$_GET['n1']];
	// if(isset($_GET['n2'])) $nadpis = $_GET['n2'];
	
	if(empty($nadpis)) $nadpis = "&nbsp;";
	
	echo "<h1>$nadpis</h1>";
	
	echo BACK_BUTTON;
	
	
	
	// hlaseni do stranky
	if(!empty($_SESSION['alert'])) {
	
		echo "<div id=\"alert\">".nl2br($_SESSION['alert'])."</div>";
		//echo "<script language=\"Javascript\">alert('".$_SESSION['alert']."');</script>";
		unset($_SESSION['alert']);
	
	}
?>
	
	
	<br />
	
<?php

	echo $data;
	
	$time_end = getmicrotime();
	$time = $time_end - $time_start;
	
	echo "<br /><br />".round($time,3)." sec <br />Session time: ".ini_get("session.gc_maxlifetime");
?>
	
	<br /><br />


</div>


	
<?php

/*
	echo "<div style=\"position: absolute; right: 5px; top: 100px; 
	border: 1px; padding: 15px; width: 460px; font-size: 9px;\">";
			
			
			
// 			reset ($GLOBALS);
// 			while ($pole = each($GLOBALS)) {
// 				$nazev = $pole['key']; //nazev promenne ($nazev)
// 				$hodnota = $pole['value'];
// 				$hodnota = trim($hodnota); //vycisteni od prazdnych znaku na zacatku a na konci
// 				echo "<br>GLOBALS $nazev ".${$nazev} = $hodnota; //prirazeni hodnoty k nazvu, prevedeny na lokalni $
// 			}
// 			
	
			echo "<a href=\"todo.txt\" target=\"_blank\">TODO</a>";

			echo "<br /><br />";
			
			
			
			
			echo "ř." . __LINE__ . " - <b>SQL</b>:<br />
						".nl2br($_SESSION['queries'])."<br /><br />";
			
			unset($_SESSION['queries']);
			
			
			
			echo "ř." . __LINE__ . " - <b>APP</b>:  ".APP."<br />";
			echo "ř." . __LINE__ . " - <b>C_LANG</b>:  ".C_LANG."<br />";
			echo "ř." . __LINE__ . " - <b>SQL_C_LANG</b>:  ".SQL_C_LANG."<br />";
			echo "ř." . __LINE__ . " - <b>S_LOGIN</b>:  ".S_LOGIN."<br />";
			echo "ř." . __LINE__ . " - <b>C_LOGIN</b>:  ".C_LOGIN."<br />";
			echo "ř." . __LINE__ . " - <b>MAIN_LINK</b>:  ".MAIN_LINK."<br />";
			
			
			
			
			
			reset ($_SESSION);
			while ($pole = each($_SESSION))	{
				$nazev = $pole['key']; //nazev promenne ($nazev)
				$hodnota = $pole['value'];
				$hodnota = trim($hodnota); //vycisteni od prazdnych znaku na zacatku a na konci
				echo "<br>SESSION $nazev = ".${$nazev} = $hodnota; //prirazeni hodnoty k nazvu, prevedeny na lokalni $
			}
			
			
			echo "<br />";
			
			
			reset ($_GET);
			while ($pole = each($_GET))	{
				$nazev = $pole['key']; //nazev promenne ($nazev)
				$hodnota = $pole['value'];
				$hodnota = trim($hodnota); //vycisteni od prazdnych znaku na zacatku a na konci
				echo "<br>GET $nazev = ".${$nazev} = $hodnota; //prirazeni hodnoty k nazvu, prevedeny na lokalni $
			}
			
		
		
		echo "</span>";
			
			
			
			echo "<br /><br />";
			
			
			
			echo "ř." . __LINE__ . " - <b>__FILE__</b>:  " . __FILE__ . "<br />";
			echo "ř." . __LINE__ . " - <b>basename(__FILE__)</b>:  " . basename(__FILE__) . "<br />";
			echo "ř." . __LINE__ . " - <b>dirname(__FILE__)</b>:  " . dirname(__FILE__) . "<br />";
			
			echo "ř." . __LINE__ . " - <b>PHP_SELF</b>:  " . $_SERVER['PHP_SELF'] . "<br />";
			echo "ř." . __LINE__ . " - <b>basename(PHP_SELF)</b>:  " . basename($_SERVER['PHP_SELF']) . "<br />";
			echo "ř." . __LINE__ . " - <b>dirname(PHP_SELF)</b>:  " . dirname($_SERVER['PHP_SELF']) . "<br />";
			
			echo "ř." . __LINE__ . " - <b>SCRIPT_NAME</b>:  " . $_SERVER['SCRIPT_NAME'] . "<br />";
			echo "ř." . __LINE__ . " - <b>SCRIPT_FILENAME</b>:  " . $_SERVER['SCRIPT_FILENAME'] . "<br />";
			echo "ř." . __LINE__ . " - <b>PATH_TRANSLATED</b>:  " . $_SERVER['PATH_TRANSLATED'] . "<br />";
			echo "ř." . __LINE__ . " - <b>HTTP_REFERER</b>:  " . $_SERVER['HTTP_REFERER'] . "<br />";
			
			echo "ř." . __LINE__ . " - SCRIPT_URL:  " . $_SERVER['SCRIPT_URL'] . "<br />";
			echo "ř." . __LINE__ . " - SCRIPT_URI:  " . $_SERVER['SCRIPT_URI'] . "<br />";
			
			echo "ř." . __LINE__ . " - PATH:  " . $_SERVER['PATH'] . "<br />";
			echo "ř." . __LINE__ . " - DOCUMENT_ROOT:  " . $_SERVER['DOCUMENT_ROOT'] . "<br />";
			
			echo "ř." . __LINE__ . " - PHP_OS:  " . PHP_OS . "<br />";
			
			echo "ř." . __LINE__ . " - HTTP_HOST:  " . $_SERVER['HTTP_HOST'] . "<br />";
			echo "ř." . __LINE__ . " - SERVER_NAME:  " . $_SERVER['SERVER_NAME'] . "<br />";
			echo "ř." . __LINE__ . " - SERVER_ADDR:  " . $_SERVER['SERVER_ADDR'] . "<br />";
			echo "ř." . __LINE__ . " - gethostbyaddr(SERVER_NAME):  " . gethostbyaddr($_SERVER['SERVER_ADDR']) . "<br />";
			echo "ř." . __LINE__ . " - SERVER_PORT:  " . $_SERVER['SERVER_PORT'] . "<br />";
			echo "ř." . __LINE__ . " - REMOTE_ADDR:  " . $_SERVER['REMOTE_ADDR'] . "<br />";
			echo "ř." . __LINE__ . " - SERVER_ADMIN:  " . $_SERVER['SERVER_ADMIN'] . "<br />";
			
			echo "ř." . __LINE__ . " - SERVER_PROTOCOL:  " . $_SERVER['SERVER_PROTOCOL'] . "<br />";
			echo "ř." . __LINE__ . " - REQUEST_METHOD:  " . $_SERVER['REQUEST_METHOD'] . "<br />";
			echo "ř." . __LINE__ . " - QUERY_STRING:  " . $_SERVER['QUERY_STRING'] . "<br />";
			echo "ř." . __LINE__ . " - REQUEST_URI:  " . $_SERVER['REQUEST_URI'] . "<br />";
			
			echo "ř." . __LINE__ . " - argv:  " . $_SERVER['argv'] . "<br />";
			echo "ř." . __LINE__ . " - argc:  " . $_SERVER['argc'] . "<br />";
			
			echo "ř." . __LINE__ . " - HTTP_USER_AGENT:  " . $_SERVER['HTTP_USER_AGENT'] . "<br />";
			echo "ř." . __LINE__ . " - HTTP_CONNECTION:  " . $_SERVER['HTTP_CONNECTION'] . "<br />";
			echo "ř." . __LINE__ . " - SERVER_SIGNATURE:  " . $_SERVER['SERVER_SIGNATURE'] . "<br />";
			echo "ř." . __LINE__ . " - SERVER_SOFTWARE:  " . $_SERVER['SERVER_SOFTWARE'] . "<br />";
			echo "ř." . __LINE__ . " - REMOTE_PORT:  " . $_SERVER['REMOTE_PORT'] . "<br />";
			echo "ř." . __LINE__ . " - GATEWAY_INTERFACE:  " . $_SERVER['GATEWAY_INTERFACE'] . "<br />";
			
			echo "ř." . __LINE__ . " - HTTP_ACCEPT:  " . $_SERVER['HTTP_ACCEPT'] . "<br />";
			echo "ř." . __LINE__ . " - HTTP_ACCEPT_LANGUAGE:  " . $_SERVER['HTTP_ACCEPT_LANGUAGE'] . "<br />";
			echo "ř." . __LINE__ . " - HTTP_ACCEPT_ENCODING:  " . $_SERVER['HTTP_ACCEPT_ENCODING'] . "<br />";
			
			
			echo "<br />";
			
			
			
			
			
			reset ($_POST);
			while ($pole = each($_POST)) {
				$nazev = $pole['key']; //nazev promenne ($nazev)
				$hodnota = $pole['value'];
				$hodnota = trim($hodnota); //vycisteni od prazdnych znaku na zacatku a na konci
				echo "<br>POST $nazev ".${$nazev} = $hodnota; //prirazeni hodnoty k nazvu, prevedeny na lokalni $
			}
			
			echo "<br />";
			
			
			
			reset ($_REQUEST);
			while ($pole = each($_REQUEST))	{
				$nazev = $pole['key']; //nazev promenne ($nazev)
				$hodnota = $pole['value'];
				$hodnota = trim($hodnota); //vycisteni od prazdnych znaku na zacatku a na konci
				echo "<br>REQUEST $nazev = ".${$nazev} = $hodnota; //prirazeni hodnoty k nazvu, prevedeny na lokalni $
			}
	

	
	
	
	echo "</div>";*/
?>



</body>
</html>
