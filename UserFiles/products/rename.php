<?php


for($i=0;$i<1400;$i++){
	$filename=$i;
	while(strlen($filename)<6){
		$filename='0'.$filename;
	}
// 	echo $filename;
	if(file_exists($_SERVER['DOCUMENT_ROOT'].'/UserFiles/products/m-'.$filename.'.jpg')){
		echo "sem<br />";
		rename('m-'.$filename.'.jpg',''.$filename.'.jpg');
	}
	
// 	echo "<br />";
}

?>
