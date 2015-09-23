<?php
// *****************************************************************************
// show.php
// *****************************************************************************

if (file_exists($_GET['i']))
	$show = "
	<img src=\"".$_GET['i']."\" border=\"0\"onclick=\"javascript: window.close();\" 
	title=\"kliknutím zavřít / click for close\" style=\"cursor: pointer;\">";



echo "
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">

<html>
<head>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
	<title>:::::</title>
</head>

<body leftmargin=\"0\" topmargin=\"0\" rightmargin=\"0\" 
bottommargin=\"0\" marginwidth=\"0\" marginheight=\"0\">

$show

</body>
</html>";

// *****************************************************************************
// show.php
// *****************************************************************************
?>