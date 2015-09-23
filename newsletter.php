<?php

$H1='Odhlášení z newsletteru';

if(!empty($_GET['id']) && !empty($_GET['control'])){

 	$query="select email from ".T_INFO_NEWS_X_EMAIL." where id_message=".$_GET['id']." and control='".$_GET['control']."'";

 	
 	$v = my_DB_QUERY($query,__LINE__,__FILE__);
 	$z = mysql_fetch_array($v);
 	
 	if(mysql_num_rows($v)==0){
		$TEXT='<div class="info">
		Neplatný odkaz pro odhlášení z odběru novinek.
		</div>';
	}else{
		//kontrola, zda uz neni v blacklistu, kdyz ne, vlozit, kdyz jo, oznamit
		$query="select * from ".T_INFO_BLACKLISTED." where email='".$z['email']."'";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		
		if(mysql_num_rows($v)==0){
			//pridat do bl
			$query="insert into ".T_INFO_BLACKLISTED."(email,id_message) values('".$z['email']."',".$_GET['id'].")";
			$v = my_DB_QUERY($query,__LINE__,__FILE__);	
			
			$TEXT='<div class="info">
			Vaše adresa byla odhlášena z odběru novinek.
			</div>';	
		}else{
			$TEXT='<div class="info">
			Vaše adresa byla odhlášena z odběru novinek.
			</div>';
		}
		
	}
 	
	
}else{
	$TEXT='<div class="info">
	Neplatný odkaz pro odhlášení z odběru novinek.
	</div>';
}

?>