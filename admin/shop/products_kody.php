<?php

// *****************************************************************************
// roleta vyrobcu
// *****************************************************************************
function vyrobci($current_id,$dct) {

	// vygeneruje select s vyrobci, oznaci jako vybranou polozku podle $current_id
	// $dct = slovnik
	
	$res='';
	
	$query = "SELECT id, name FROM ".T_PRODS." WHERE ".SQL_C_LANG." ORDER BY name ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
		
		$id = $z['id'];
		$name = $z['name'];
		
		if ($current_id == $id) $selected = "selected";
		else if ($current_id == 0) $selected = "";
		else $selected = "";
		
		
		$res .= "
		<option value=\"$id\" $selected>$name</option>";
	
	}

	$select = "
		<select name=\"id_vyrobce\" class=\"f10\" style=\"width: 100%;\">
			<option value=\"0\">Všichni výrobci</option>
			$res
		</select>";
	
	
	return $select;

}
// *****************************************************************************
// roleta vyrobcu
// *****************************************************************************



$podminka = " ";

if(isset($_POST['filtr']) && $_POST['filtr'] == '1') {
  $current_id = $_POST['id_vyrobce'];
  $_SESSION['curent_id'] = $current_id;
  if ($current_id != 0) {
      $podminka = " AND id_vyrobce= " .$_POST['id_vyrobce']. " ";
  } 

} 

if (isset( $_SESSION['curent_id'])) { 
   $current_id = $_SESSION['curent_id'];
 } 


if(isset($_POST['ulozit_kody']) && $_POST['ulozit_kody'] == '1') {

 foreach($_POST as $xy=>$clm)
      {
      if( trim($clm) == "")
        {
           $clm = 'null';
        }
        else
        {
        if( substr($xy,0,7) == "product")
        {
        $id = substr($xy,8);
       	$query = "UPDATE ".T_GOODS." SET kod = '".$clm."' 
         WHERE id = $id AND ".SQL_C_LANG."";
        	my_DB_QUERY($query,__LINE__,__FILE__);
         }     }
      }

    $_SESSION['alert_js'] = "Záznamy byly uloženy";
    
    header("location: ".$_SERVER['HTTP_REFERER']);
    exit;
  
  } 





// *****************************************************************************
// seznam produktu s nulovymi kody
// *****************************************************************************

  if($_GET['a']="kody") { 

	$nadpis = "Produkty";
	
	$query = 'SELECT * FROM  '.T_GOODS. ' 
 	WHERE kod = "" AND '.SQL_C_LANG.''
 	.$podminka. '
 	ORDER BY name';
 	
 	$seznam = "";
 	
 	
 	if(empty($current_id))$current_id='';
 	
 			// roleta vyrobcu
  $seznam .= "<form action=\"\" method=\"post\"><input type=\"hidden\" name=\"filtr\" value=\"1\">";
	$seznam .= '<table width ="50%"> ';
  $seznam .= "<tr><td>Výrobce: </td><td>".vyrobci($current_id,$dct)."</td><td> ".button('submit','Filtrovat výrobce','class="butt_ostatni"')."</td></tr>";
	$seznam .= '</table></form>';	  	
 			
  $seznam .= "<form action=\"\" method=\"post\"><input type=\"hidden\" name=\"ulozit_kody\" value=\"1\">";

	$seznam .= '<table width ="50%"> ';
	$seznam .= '<tr><td>č.</td><td>kód</td><td>zboží</td></tr>';
		
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	$poc_x = 0;
	$poc_but = 0;
	while ($z = mysql_fetch_array($v)) {
	
	$poc_x++;
	$poc_but++;
	
		if($z['hidden'] == 1) $pstyle = "class=\"gray\"";
		else $pstyle = "";

	  $seznam .= "<tr>";
	  $seznam .= "<td>".$poc_x."</td>";
		$seznam .= "<td><input type=\"text\" name=\"product_".$z['id']."\" value=\"".$z['kod']."\"></td>";
    $seznam .= "<td>".$z['name']."</td>";
	  $seznam .= "</tr>";
	  
	  
	    if ($poc_but == 30){
	    $poc_but = 0;
      $seznam .= "<tr><td></td><td></td><td> ".button('submit','Uložit vyplněné kody','class="butt_ostatni"')."</td></tr>";
	  	}

}
	if ($poc_x != 0){
	$seznam .= "<tr><td></td><td></td><td> ".button('submit','Uložit vyplněné kody','class="butt_ostatni"')."</td></tr>";
  }
  $data = SEARCH_PANEL.$seznam;//."$query<br /><br />";
 	$seznam .= '</table></form>';
	
}
// *****************************************************************************
// seznam produktu s nulovymi kody
// *****************************************************************************




?>