<?php

$q1 = "CREATE TABLE ".T_SEO." (
  id int(10) unsigned NOT NULL auto_increment,
  idneceho int(10) unsigned NOT NULL,
  ceho smallint(1) unsigned NOT NULL,
  title text NOT NULL default '',
  keywords text NOT NULL default '',
  description text NOT NULL default '',
  foot text NOT NULL default '',
  lang smallint(5) unsigned NOT NULL default '0',
  UNIQUE KEY id (id)
) TYPE=MyISAM;";
tbl_exists(T_SEO,$q1,__LINE__,__FILE__);

function form_seo($form_data,$sc) {
  if(!isset($sc)) $sc=1;
  else 	$sc++;
  
  if(empty($form_data['seo_title']))$form_data['seo_title']='';
  if(empty($form_data['seo_description']))$form_data['seo_description']='';
  if(empty($form_data['seo_keywords']))$form_data['seo_keywords']='';
  if(empty($form_data['seo_foot']))$form_data['seo_foot']='';

$SEO = "
	
	<table border=\"0\" cellspacing=\"2\" cellpadding=\"0\">
		
	<tr>
		<td width=\"15\" valign=\"middle\">
			<img src=\"icons/ico_arr_down.gif\" alt=\"\" title=\"\" 
			border=\"0\" height=\"15\" width=\"15\" class=\"expandcontent\" 
			onclick=\"expandcontent('sc$sc')\">
		</td>
		
		<td valign=\"middle\" align=\"left\">
			<span class=\"expandcontent\" onclick=\"expandcontent('sc$sc')\">SEO</span>
		</td>
	</tr>
	
	<tr>
		<td width=\"15\" valign=\"middle\">&nbsp;</td>
		
		<td nowrap>
			<div class=\"switchcontent\" id=\"sc$sc\">
				<span class=\"f10\">(pokročilá nastavení, nenastavujte neovládate-li SEO)</span><br /><br />
        Title<br />
        <input type=\"text\" class=\"seo\" name=\"SEO_title\" value=\"".$form_data['seo_title']."\" /><br />
				Description<br />
        <textarea class=\"seo\" name=\"SEO_description\" rows=3>".$form_data['seo_description']."</textarea><br />
			  Keywords<br />
        <textarea class=\"seo\" name=\"SEO_keywords\" rows=2>".$form_data['seo_keywords']."</textarea><br />
        Foot<br />
        <textarea class=\"seo\" name=\"SEO_foot\" rows=2>".$form_data['seo_foot']."</textarea><br />
			</div>
		</td>
	</tr>
	
	</table>";
  
  return $SEO;
}
  
  function uloz_seo($SEO, $kcemu) {//kcemu ... 1-clanek,2-kategorie,3-produkt
    if ($SEO['SEO_title']>'' OR $SEO['SEO_keywords']>'' OR $SEO['SEO_description']>'' OR $SEO['SEO_foot']>'') // zjistíme jestli bude co ukládat
      $stav='zadano';
    else 
      $stav='prazdne';
    if(!empty($SEO['id'])) {// i když bude editována nějaká věta, nevíme jestli pro ni je i odpovídající záznam v SEO
      $queryseo="SELECT id FROM ".T_SEO." WHERE idneceho=".$SEO['id']." AND ceho=".$kcemu." AND lang=".C_LANG." LIMIT 1";
      $vseo = my_DB_QUERY($queryseo,__LINE__,__FILE__);
		  if( $seo_zaznam = @mysql_result($vseo, 0, 0)){ //pokud je odpovídající seo záznam, budeme větu aktualizovat nebo mazat v případě prázdných hodnot
        if ($stav=='prazdne') 
          $queryseo = "DELETE FROM ".T_SEO." WHERE id=".$seo_zaznam;
        else
          $queryseo = "UPDATE ".T_SEO." SET 
          title = '".$SEO['SEO_title']."',
          keywords = '".$SEO['SEO_keywords']."',
          description = '".$SEO['SEO_description']."',
          foot = '".$SEO['SEO_foot']."'
          WHERE  id=".$seo_zaznam;
        my_DB_QUERY($queryseo,__LINE__,__FILE__);
        if ($stav=='prazdne')  my_OPTIMIZE_TABLE(T_SEO);
      }
      else { //v opačném případě založíme záznam pro id editované věty
        $queryseo = "INSERT INTO ".T_SEO." VALUES('',".$SEO['id'].",".$kcemu.",'".$SEO['SEO_title']."','".$SEO['SEO_keywords']."','".$SEO['SEO_description']."','".$SEO['SEO_foot']."',".C_LANG.")";
        if ($stav=='zadano') //zápis budeme provádět pouze jeli něco zadáno
          my_DB_QUERY($queryseo,__LINE__,__FILE__);
      }
    }
    else {
      $queryseo = "INSERT INTO ".T_SEO." 
      VALUES('',".$SEO['novy_zaznam'].",".$kcemu.",'".$SEO['SEO_title']."','".$SEO['SEO_keywords']."','".$SEO['SEO_description']."','".$SEO['SEO_foot']."',".C_LANG.")";
      if ($stav=='zadano') //zápis budeme provádět pouze jeli něco zadáno
        my_DB_QUERY($queryseo,__LINE__,__FILE__);
    }
  }
  
  
  function nacti_seo($idneceho,$kcemu) {//kcemu ... 1-clanek,2-kategorie,3-produkt
    $queryseo="SELECT title, keywords, description, foot FROM ".T_SEO." WHERE idneceho=".$idneceho." AND ceho=".$kcemu." AND lang=".C_LANG." LIMIT 1";
    $vseo = my_DB_QUERY($queryseo,__LINE__,__FILE__);    
    
    $SEO_data=null;
    
    while ($zseo = mysql_fetch_array($vseo)) {
      $SEO_data['seo_title']=$zseo['title'];
      $SEO_data['seo_keywords']=$zseo['keywords'];
      $SEO_data['seo_description']=$zseo['description'];
      $SEO_data['seo_foot']=$zseo['foot'];
    }
    return $SEO_data;
  }
  
  
  function delete_seo($idneceho,$kcemu) {//kcemu ... 1-clanek,2-kategorie,3-produkt
    $queryseo = "DELETE FROM ".T_SEO." WHERE idneceho=".$idneceho." AND ceho=".$kcemu." AND lang=".C_LANG;
    my_DB_QUERY($queryseo,__LINE__,__FILE__);
    my_OPTIMIZE_TABLE(T_SEO);
  }
  
  
  ?>
