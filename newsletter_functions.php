<?php


include_once("./admin/_mysql.php");


/*
ulozi vsechny emaily v retezci oddelene bilimi znaky

@param (string) emaily - emaily oddelene bilimi znaky
*/
function add_newsletter($emaily)
{
 	preg_match_all('/([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})/',strtolower($emaily), $matches);
  
	if(is_array($matches[0]))
  {
		foreach($matches[0] as $mail)
    {
      // kontrola zda email uz v databazi neni
			$query="
      SELECT email
      FROM ".T_INFO_IMPORTED."
      WHERE email = '".$mail."'
      AND ".SQL_C_LANG;
			$v = my_DB_QUERY($query,__LINE__,__FILE__);

			if(mysql_num_rows($v) > 0)
      { // email uz v databazi je

			}
      else
      { // novy email
				$query="INSERT INTO ".T_INFO_IMPORTED." values ('".$mail."' , '".C_LANG."')";
				my_DB_QUERY($query,__LINE__,__FILE__);
			}
		}
	}
}


/*
prida email na blacklist

@param (string) email - email ktery prijde na blacklist
*/
function blacklist_newsletter($email)
{
  // kontrola zda email uz na blacklistu neni
  $query = "
  SELECT email
  FROM ".T_INFO_BLACKLISTED."
  WHERE email='".$email."'";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);

	if(mysql_num_rows($v) == 0)
  {
		//pridat na blacklist
		$query = "INSERT INTO ".T_INFO_BLACKLISTED." (email) values ('".$email."')";
		my_DB_QUERY($query,__LINE__,__FILE__);
	}
}


/*
odstrani email z blacklistu

@param (string) email - email t
*/
function delete_from_blacklist_newsletter($email)
{
  // kontrola zda email uz na blacklistu neni
  $query = "
  SELECT email
  FROM ".T_INFO_BLACKLISTED."
  WHERE email='".$email."'";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);

	if(mysql_num_rows($v) != 0)
  {
		//odstraneni z blacklistu
		$query = "DELETE FROM ".T_INFO_BLACKLISTED." WHERE email = '".$email."'";
		my_DB_QUERY($query,__LINE__,__FILE__);
	}
}

?>