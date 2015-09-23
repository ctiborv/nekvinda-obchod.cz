<?php



$title = "Novinky";

$H1 = $title;







// *****************************************************************************
// novinky
// *****************************************************************************
// T_NEWS - id  vlozeno  txt  hidden  poradi

if(!empty($_GET['Nid'])) {
  $query = "SELECT txt,vlozeno FROM ".T_NEWS." 
  WHERE id = ".$_GET['Nid']." AND hidden = 0 AND ".SQL_C_LANG." 
  LIMIT 0,1";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  while ($z = mysql_fetch_array($v)) {
  	$datum = $dat = timestamp_to_date($z['vlozeno']);
		$TEXT = '<div class="clanek">
			<strong>'.$datum.'</strong> - '.$z['txt'].'</div>';
  }
}
else {
  $query = "SELECT txt,vlozeno FROM ".T_NEWS." 
  WHERE hidden = 0 AND ".SQL_C_LANG." ORDER BY poradi";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  $count_news=mysql_num_rows($v);
  if($count_news>0) {
    // pocet zaznamu pro strankovani                                                                      
	  $limit = records_limit();
	  $query = "SELECT txt,vlozeno FROM ".T_NEWS." 
    WHERE hidden = 0 AND ".SQL_C_LANG." ORDER BY poradi ".$limit."";
    //echo $query;
    //exit;
    $v = my_DB_QUERY($query,__LINE__,__FILE__);
    $polozky_news='';
    while ($z = mysql_fetch_array($v)) {
      $datum = $dat = timestamp_to_date($z['vlozeno']);
		  $polozky_news .= '<div class="news">
			<strong>'.$datum.'</strong>'.$z['txt'].'</div>';
    }
    $TEXT = $polozky_news;
    $pages_news = strankovani($count_news,$link=HTTP_ROOT."/novinky/");
    $TEXT=$TEXT.$pages_news;
  }
}

// *****************************************************************************
// novinky
// *****************************************************************************



if(empty($TEXT)) $TEXT = 'Nenalezeno.';





?>