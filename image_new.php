<?php


if(empty($_GET['size'])){
  $velikost='small';     //defaultni - middle, original, small
}else{
  $velikost=$_GET['size'];
}


if(!empty($_GET['file']) && !empty($_GET['e']) && $_GET['file']!='nfoto'){

	if(empty($_GET['cati'])){
	  $cesta='./UserFiles/products/'.$velikost.'/'.$_GET['file'].'';
	  
	//   echo $cesta;
	}else{
	  $cesta='./UserFiles/categories/'.$velikost.'/'.$_GET['file'].'';		
	}
  
 
  if(file_exists($cesta)){
  

        $cestaVODOZNAK='./UserFiles/products/'.$velikost.'/stamp.png';
        // echo "urcitej";exit;
        // Load the stamp and the photo to apply the watermark to
     
        $rozm = getimagesize($cestaVODOZNAK);
        
        $stamp = imagecreatefrompng($cestaVODOZNAK);

/* vodoznak do praveho dolniho rohu 
$rozmery = getimagesize($cesta);
$marge_right = ($rozmery[0] / 2 - $rozm[0] / 2) * (-1);
$marge_bottom = ($rozmery[1] / 2 - $rozm[1] / 2) * (-1); 
*/

/* vodoznak na stred */
$marge_right = 0;
$marge_bottom = 0;

        // Set the margins for the stamp and get the height/width of the stamp image
        //$marge_right = 0;
        //$marge_bottom = 0;
        $sx = imagesx($stamp);
        $sy = imagesy($stamp);
        
    switch($_GET['e']){
        case 'jpg':{
          $im = imagecreatefromjpeg($cesta);  
          break;
        }
        
        case 'gif':{
          $im = imagecreatefromgif($cesta);
          break;
        }
        
        case 'png':{
          $im = imagecreatefrompng($cesta);    
          break;
        }
    }
    
    
    $x=imagesx($im);
    $y=imagesy($im);
    
//     echo $x."<br />";
//     echo $y."<br />";
//     echo $sx."<br />";
//     echo $sy."<br />";
//     echo $marge_right."<br />";
//     echo $marge_bottom."<br />";     exit;
      
    // Copy the stamp image onto our photo using the margin offsets and the photo 
    // width to calculate positioning of the stamp. 
    
    
//     if($sx>$x || $sy>$y){
// //     echo "ano";exit;
//       $stamp=resizeStamp($stamp,$x,$y,$rozm);    
//     }
//     
//     
//     $sx = imagesx($stamp);
//     $sy = imagesy($stamp);

//     if($velikost!='small'){
      $xmargin=$x - $sx - (($x/2)-($sx/2)) - $marge_right;
      $ymargin=$y - $sy - (($y/2)-($sy/2)) - $marge_bottom;
//     }else{
//       $xmargin=$x;
//       $ymargin=$y;
//     }
//     
    imagecopy($im, $stamp, $xmargin, $ymargin, 0, 0, imagesx($stamp), imagesy($stamp));
    
    // Output and free memory
    
    header('Content-type: image/png');
    imagepng($im);
    imagedestroy($im);
    
  }else{
  
    $im = imagecreatefromjpeg('./img/nfoto.jpg');
    header('Content-type: image/jpeg');
    imagejpeg($im);
    imagedestroy($im);
  
  }
}else{
//   echo "univerzalni";exit;
  $im = imagecreatefromjpeg('./img/nfoto.jpg');
  header('Content-type: image/jpeg');
  imagejpg($im);
  imagedestroy($im);
}



// *****************************************************************************
// upload a vytvareni kopii obrazku
// *****************************************************************************
function resizeStamp($orig,$width_max,$height_max,$rozm) {

	$in = $orig; // png
  

	if($width_max > $height_max) {
		$k_width = $width_max / $height_max;
		$k_height = 1;
	} elseif($height_max > $width_max) {
		$k_width = 1;
		$k_height = $height_max / $width_max;
	} else {
		$k_width = 1;
		$k_height = 1;
	}
	
	if($rozm[0] < $width_max && $rozm[1] < $height_max) {
		$width = $rozm[0];
		$height = $rozm[1];
	} elseif($rozm[0] / $k_width > $rozm[1] / $k_height) {
		$width = $width_max;
		$k = $rozm[0] / $width_max;
		$height = ceil($rozm[1] / $k);
	} elseif($rozm[0] / $k_width < $rozm[1] / $k_height) { 
  	$height = $height_max;
		$k = $rozm[1] / $height_max;
		$width = ceil($rozm[0] / $k);
	} else {
		if($width_max > $height_max) {			
			$width = $height_max * $k_width;
			$height = $height_max * $k_height;
		} else {
			$width = $width_max * $k_width;
			$height = $width_max * $k_height;			
		}
	
	}

	
	$out = imagecreatetruecolor($width,$height);
	
	
	imagecopyresampled($out,$in,0,0,0,0,$width,$height,$rozm[0],$rozm[1]);
	
  return $out;
}


?>
