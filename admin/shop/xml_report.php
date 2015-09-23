<?php





    $nadpis = $dct['mn_xml_report'];


	
			// nalezene zaznamy
			// id c_obj f_jmeno f_adresa f_psc f_mesto f_ico f_dic f_mail f_tel p_jmeno p_adresa p_psc p_mesto pozn
			$query = "SELECT *, 
      DATE_FORMAT(datum,'%d.%m.%Y %H:%i:%s') AS datum_format
      FROM fla_shop_xml_report WHERE ".SQL_C_LANG." ORDER BY id DESC LIMIT 1000";
			$v = my_DB_QUERY($query,__LINE__,__FILE__);
			
			while ($z = mysql_fetch_array($v)) {
			

				
				$res .= "
				<tr ".TABLE_ROW.">
					<td class=\"td1\" width=\"45\" style=\"color: #999; text-align: right;\">".$z["id"]."</td>
					
					<td class=\"td1\" width=\"127\">".$z["datum_format"]."</td>
					
					<td class=\"td1\">".$z["text"]."</td>
				</tr>";//".ico_edit(MAIN_LINK."&f=orders&a=print&id=$id|$c_obj","Zobrazit objednávku")."
			
			}
			
			
			if (!empty($res)) {
			
				$data = "
				
				<table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\" bgcolor=\"#c6c6c6\">
				$res
				</table>";
				
			
			}
			else
			{
      $data = '<br /><br />'.$dct['zadny_zaznam'].'<br />';
      }





?>
