<?php
// Modul statisktiky.

include_once($_SERVER['DOCUMENT_ROOT'].'/admin/_mysql.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/admin//_security.php'); // Kontrola uživatele.


function microtime_float()
{
  list($usec, $sec) = explode(" ", microtime());
  return ((float)$usec + (float)$sec);
}


/**
@param (int) parent = ID rodičovské kategorie (0 = první úroveň).
"@param (int) level" = Nepovinný a je pro účel funkce rozpoznat úroveň zanoření (musí začínat o stupeň níže než je aktuální úroveň).
@return (string) tree = Kompletní strom kategorií.
*/
function get_tree($parent , $level = 0)
{
  if(!isset($_SESSION["cache"]["categories"]))
  { // Uložení výsledku dotazu do paměti.
    $query = "
    SELECT name , id , id_parent
    FROM ".T_CATEGORIES."
    ORDER BY id_parent";
    $v = my_DB_QUERY($query,__LINE__,__FILE__);

    $_SESSION["cache"]["categories"]["id_tree"] = array(); // Kompletní strom návazností mezi kategoriemi $_SESSION["cache"]["categories"]["id_tree"][0] = první úroveň
    $_SESSION["cache"]["categories"]["name"] = array(); // Kompletní seznam názvů kategorii klíč je id
    while($z = mysql_fetch_assoc($v))
    {
      $_SESSION["cache"]["categories"]["id_tree"][$z["id_parent"]][] = $z["id"];
      $_SESSION["cache"]["categories"]["name"][$z["id"]] = $z["name"];
    }
  }

  $level++;
  $tree = "";
  $tree_array = array();

  if(isset($_SESSION["cache"]["categories"]["id_tree"][$parent]))
  {
    $result = $_SESSION["cache"]["categories"]["id_tree"][$parent]; // Dotaz na vnořené kategorie.
     
    foreach($result AS $id)
    {
      //$tree .= str_repeat("&nbsp;" , $level).$_SESSION["categories"]["name"][$id]."<br />"; // Kategorie jako řetězec.
      //$tree .= get_tree($id , $level);

      // Kategorie v poli.
      $tree_tmp = get_tree($id , $level);
      if(count($tree_tmp) > 0)
      { // V kategorii jsou podkategorie.
        //$tree_array[$id] = array("name" => $_SESSION["categories"]["name"][$id] , "cat" => $tree_tmp); 
        $tree_array[$id] = $tree_tmp; // V kategorii jsou podkategorie.
      }
      else
      { // Kategorie je poslední (nemá podkategorie).
        //$tree_array[$id] = array("name" => $_SESSION["categories"]["name"][$id]); 
        $tree_array[$id] = $id;
      }
    }
  }
  
  return $tree_array;
}


/**
Pole i s vnořenými poly převede na jedno pole kde klíč bude hodnota.
@param (array) tree - Výstup z funkce get_tree().
@return (array) array[] = KEY
*/
function get_sub_array($array)
{
  $cat = array();
  while(list($key , $value) = each($array))
  {
    $cat[] = $key;
    if(is_array($value))
    {
      $cat = array_merge($cat , get_sub_array($value));
    }
  }

  return $cat;
}


/**
Všechny podkategorie v jednim poli.
@param (array) tree - Výstup z funkce get_tree()
@return (array) subcat = Všechny subkategorie v jednim poli
*/
function get_all_subcat($tree)
{
  $subcat = array();
  while(list($key , $value) = each($tree))
  {
    $subcat[$key][] = $key;
    if(is_array($value))
    {
      $subcat[$key] = array_merge($subcat[$key] , get_sub_array($value));
    }
  }

  return $subcat;
}


/**
Produkty v zadaných kategoriích.
@param (array) cat = Výstup z funkce get_all_subcat().
@return (array) cat_x_goods[id_cat] = array(id_product).
*/
function get_products_id_cat($cat)
{
  $cat_x_goods = array();
  while(list($key , $value) = each($cat))
  {
    $query = "
    SELECT id_good
    FROM ".T_GOODS_X_CATEGORIES." AS GOODS_X_CATEGORIES
    WHERE id_cat IN (".implode("," , $value).")
    ";
	  $v = my_DB_QUERY($query,__LINE__,__FILE__);
	  while($z = mysql_fetch_assoc($v))
    {
      $cat_x_goods[$key][] = $z["id_good"];
    }
  }

  return $cat_x_goods;
}



define("DAY_SECONDS" , 86399); // 1 den v sekundách (86400). Sekunda odebrána aby se nepřeskočilo do druhého dne (23:59:59).
// timestamp první objednávky.
$query = "
SELECT MIN(time) AS first_order_time
FROM ".T_ORDERS_ADDRESS."
WHERE ".SQL_C_LANG;
$v = my_DB_QUERY($query,__LINE__,__FILE__);
$z = mysql_fetch_assoc($v);
define("FIRST_ORDER_DATE" , date("d.m.Y" , $z["first_order_time"])); // Datum první objednávky.
define("BESTSELLERS_LIMIT" , 10); // Počet nejprodávanějších kusů.


/**
@param (string) date (d.m.Y)
@return (string) date (1.m+1.Y) - První den v dalším měsíci.
*/
function next_month($date)
{
  $date_parse = date_parse($date); // Dozdělení datumu.

  if($date_parse["month"] != 12) $date_parse["month"] = $date_parse["month"] + 1; // Přičtení měsíce.
  else
  { // přestup do dalšího roku
    $date_parse["year"] = $date_parse["year"] + 1;
    $date_parse["month"] = 1;
  }

  $date = '1.'.$date_parse["month"].'.'.$date_parse["year"]; // Sestavení datumu.

  return $date;
}


/**
Kontrola existence datumu.
@param (string) date
@return (string) date OR exti();
*/
function validate_date($date)
{
  $date_cache = $date; // Původní datum.
  $date = date_parse($date); // Rozdelení datumu.

  if($date["month"] == 2 AND $date["day"] > 28 AND checkdate($date["month"] , $date["day"] , $date["year"]) === FALSE)
  { // Únor v přestupném roce.
    $date["day"] = 28;
  }

  if(checkdate($date["month"] , $date["day"] , $date["year"]) === TRUE)
  { // Datum ok.
    return $date["day"].'.'.$date["month"].'.'.$date["year"];
  }
  else
  { // Chyba datumu.
    exit("Chyba datumu " . $date_cache);
  }
}


/**
Vrátí rok rozdělený na měsíce.
@param (int) year
@return (array) from_to[number of month] = ("name" => "Název měsíce", "from" => první den v měsíci , "to" => poslední den v měsíci)
*/
function year_separate_months($year)
{
  $from_to[1] = array("name" => "Leden" , "from" => "01.01.".$year, "to" => "31.01.".$year); // Leden.
  $from_to[2] = array("name" => "Únor " , "from" => "01.02.".$year, "to" => validate_date("29.02.".$year)); // Únor (kontrola přestupného roku).
  $from_to[3] = array("name" => "Březen" , "from" => "01.03.".$year, "to" => "31.03.".$year); // Březen.
  $from_to[4] = array("name" => "Duben" , "from" => "01.04.".$year, "to" => "30.04.".$year); // Duben.
  $from_to[5] = array("name" => "Květen" , "from" => "01.05.".$year, "to" => "31.05.".$year); // Květen.
  $from_to[6] = array("name" => "Červen" , "from" => "01.06.".$year, "to" => "30.06.".$year); // Červen.
  $from_to[7] = array("name" => "Červenec" , "from" => "01.07.".$year, "to" => "31.07.".$year); // Červenec.
  $from_to[8] = array("name" => "Srpen" , "from" => "01.08.".$year, "to" => "31.08.".$year); // Srpen.
  $from_to[9] = array("name" => "Září" , "from" => "01.09.".$year, "to" => "30.09.".$year); // Září.
  $from_to[10] = array("name" => "Říjen" , "from" => "01.10.".$year, "to" => "31.10.".$year); // Říjen.
  $from_to[11] = array("name" => "Listopad" , "from" => "01.11.".$year, "to" => "30.11.".$year); // Listopad.
  $from_to[12] = array("name" => "Prosinec" , "from" => "01.12.".$year, "to" => "31.12.".$year); // Prosinec.

  return $from_to;
}


/**
Obdelníkový graf pro porovnávání.
@param (string) id - ID divu kde je graf umisten (musí být unikátní).
@param (string) title - Polisek grafu.
@param (array) values - array("legend" , array(values)) - Pole s hodnotami.
@return (string) html - HTML kód gragu.
*/
function get_bar_plot_compare($id , $title , $values = array())
{
  $legends = array();
  $var_plots = array();
  $print_plots = array();
  $index = 1;
  foreach($values as $legend => $values)
  {
    $plot_name = 'plot_'.$index; // Název grafu.
    $legends[] = '{label: "'.$legend.'"}'; // Legenda pro graf.
    $var_plots[] = 'var '.$plot_name.' = ['.implode(',', $values).'];'; // JS proměnná se souřadnicemi grafu.
    $print_plots[] = $plot_name;

    $index++;
  }

  if(count($var_plots) <= 0) return ''; // Žádný graf.

  return '
  <script class="code" type="text/javascript">
  $(document).ready(function(){
        $.jqplot.config.enablePlugins = true;
        '.implode(' ', $var_plots).'

        var '.$id.' = $.jqplot("'.$id.'", ['.implode(',', $print_plots).'],
        {
          title: "'.$title.'",
          animate: !$.jqplot.use_excanvas,
          seriesDefaults:
          {
            //renderer:$.jqplot.BarRenderer,
            pointLabels: { show: true } // Vypsat hodnoty nad obdelníkem.
          },
          legend:
          {
            show: true,
            location: "e",
            placement: "outside"
          },
          series: ['.implode(',' , $legends).'],
          axesDefaults:
          {
            tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
            tickOptions: { fontFamily: "Verdana", fontSize: "8pt" }
          },
          axes:
          {
            xaxis:
            {
              renderer: $.jqplot.CategoryAxisRenderer,
              tickOptions: { angle: 30 }
            },
            yaxis:
            {
              min:0
            },
          },
          highlighter: { show: false }
        });
    });
  </script>

  <div id="'.$id.'" style="width:100%; height:400px; margin-bottom:20px;"></div>
  ';
}


/**
Obdelníkový graf pro nejprodávanější.
@param (string) id - ID divu kde je graf umisten (musí být unikátní).
@param (string) title - Polisek grafu.
@param (array) values - Pole s hodnotami.
@return (string) html - HTML kód gragu.
*/
function get_bar_plot_bestseller($id , $title , $values = array())
{
  return '
  <script class="code" type="text/javascript">
  $(document).ready(function(){
        $.jqplot.config.enablePlugins = true;
        var plot = ['.implode(',', $values).'];

        var '.$id.' = $.jqplot("'.$id.'", [plot],
        {
          title: "'.$title.'",
          animate: !$.jqplot.use_excanvas,
          seriesDefaults:
          {
            renderer:$.jqplot.BarRenderer,
            pointLabels: { show: true } // Vypsat hodnoty nad obdelníkem.
          },
          axesDefaults:
          {
            tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
            tickOptions: { fontFamily: "Verdana", fontSize: "8pt" }
          },
          axes:
          {
            xaxis:
            {
              renderer: $.jqplot.CategoryAxisRenderer,
              tickOptions: { angle: 15 }
            }
          },
          highlighter: { show: false }
        });
    });
  </script>

  <div id="'.$id.'" style="width:100%; height:400px; margin-bottom:20px;"></div>
  ';
}

/**
Nejprodávanější podle kusů
@param (date) from
@param (date) to
@param (int) limit (nepovinný)
@return (array) ("id_product" => id , "name" => (string) , "count" => (int))
*/
function bestsellers_count($from , $to , $limit = BESTSELLERS_LIMIT)
{
  $from = intval(strtotime($from));
  $to = intval(strtotime($to) + DAY_SECONDS);

  $query = "
  SELECT id_produkt , nazev_produkt , sum(ks) AS count
  FROM ".T_ORDERS_ADDRESS." AS ORDERS_ADDRESS
  JOIN ".T_ORDERS_PRODUCTS." AS ORDERS_PRODUCTS ON ORDERS_ADDRESS.id = ORDERS_PRODUCTS.id_obj
  WHERE id_produkt != 0 /* Vyloučíme dopravu */
  AND ORDERS_ADDRESS.".SQL_C_LANG."
  AND time BETWEEN ".$from." AND ".$to."
  GROUP BY id_produkt
  ORDER BY count DESC
  LIMIT ".intval($limit);
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
  $bestsellers = array();
	while($z = mysql_fetch_assoc($v))
  {
    $bestsellers[$z["id_produkt"]] = array("id_product" => $z["id_produkt"] , "name" => $z["nazev_produkt"] , "count" => $z["count"]);
  }

  return $bestsellers;
}


/**
Nejprodávanější položky
@param (date) from
@param (date) to
@param (int) limit (nepovinný)
@return (array) ("id_product" => id , "name" => (string) , "count" => (int))
*/
function bestsellers_items($from , $to , $limit = BESTSELLERS_LIMIT)
{
  $from = intval(strtotime($from));
  $to = intval(strtotime($to) + DAY_SECONDS);

  $query = "
  SELECT id_produkt , nazev_produkt , count(id_produkt) AS count
  FROM ".T_ORDERS_ADDRESS." AS ORDERS_ADDRESS
  JOIN ".T_ORDERS_PRODUCTS." AS ORDERS_PRODUCTS ON ORDERS_ADDRESS.id = ORDERS_PRODUCTS.id_obj
  WHERE id_produkt != 0 /* Vyloučíme dopravu */
  AND ORDERS_ADDRESS.".SQL_C_LANG."
  AND time BETWEEN ".$from." AND ".$to."
  GROUP BY id_produkt
  ORDER BY count DESC
  LIMIT ".intval($limit);
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
  $bestsellers = array();
	while($z = mysql_fetch_assoc($v))
  {
    $bestsellers[$z["id_produkt"]] = array("id_product" => $z["id_produkt"] , "name" => $z["nazev_produkt"] , "count" => $z["count"]);
  }

  return $bestsellers;
}


/**
Počet položek v objednávkách.
@param (date) from
@param (date) to
@param (array) (id_products)
@return (int) number_of_items_count_sold 
*/
function number_of_items_count_sold($from , $to , $id_products = array())
{
  $from = intval(strtotime($from));
  $to = intval(strtotime($to) + DAY_SECONDS);

  $AND_id_product_IN = "";
  if(is_array($id_products) AND count($id_products) > 0)
  {
    $AND_id_product_IN = "AND id_produkt IN (".implode("," , $id_products).")";
  }

  $query = "
  SELECT id_produkt
  FROM
  ".T_ORDERS_ADDRESS." AS ORDERS_ADDRESS
  JOIN ".T_ORDERS_PRODUCTS." AS ORDERS_PRODUCTS ON ORDERS_ADDRESS.id = ORDERS_PRODUCTS.id_obj
  WHERE id_produkt != 0 /* Vyloučíme dopravu */
  ".$AND_id_product_IN."
  AND ORDERS_ADDRESS.".SQL_C_LANG."
  AND time BETWEEN ".$from." AND ".$to."
  GROUP BY id_produkt
  ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);

  return mysql_num_rows($v);
}


/**
Počet prodaných kusů od - do.
@param (date) from
@param (date) to
@param (array) (id_products)
@return (int) number_of_items_sold
*/
function number_of_items_sold($from , $to , $id_products = array())
{
  $from = intval(strtotime($from));
  $to = intval(strtotime($to) + DAY_SECONDS);

  $AND_id_product_IN = "";
  if(is_array($id_products) AND count($id_products) > 0)
  {
    $AND_id_product_IN = "AND id_produkt IN (".implode("," , $id_products).")";
  }

  $query = "
  SELECT sum(ks) AS number_of_items_sold
  FROM
  ".T_ORDERS_ADDRESS." AS ORDERS_ADDRESS
  JOIN ".T_ORDERS_PRODUCTS." AS ORDERS_PRODUCTS ON ORDERS_ADDRESS.id = ORDERS_PRODUCTS.id_obj
  WHERE id_produkt != 0 /* Vyloučíme dopravu */
  ".$AND_id_product_IN."
  AND ORDERS_ADDRESS.".SQL_C_LANG."
  AND time BETWEEN ".$from." AND ".$to;
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	$z = mysql_fetch_assoc($v);

  if(isset($z["number_of_items_sold"])) return $z["number_of_items_sold"];
  else return 0;
}


/**
Počet objednávek v zadaném období
@param (date) from
@param (date) to
@param (array) (id_products)
@return (int) count
*/
function count_order($from , $to , $id_products = array())
{
  $from = intval(strtotime($from));
  $to = intval(strtotime($to) + DAY_SECONDS);

  $AND_id_product_IN = "";
  if(is_array($id_products) AND count($id_products) > 0)
  {
    $AND_id_product_IN = "AND id_produkt IN (".implode("," , $id_products).")";
  }

  $query = "SELECT id_obj
  FROM
  ".T_ORDERS_ADDRESS." AS ORDERS_ADDRESS
  JOIN ".T_ORDERS_PRODUCTS." AS ORDERS_PRODUCTS ON ORDERS_ADDRESS.id = ORDERS_PRODUCTS.id_obj
  WHERE id_produkt != 0 /* Vyloučíme dopravu */
  ".$AND_id_product_IN."
  AND ORDERS_ADDRESS.".SQL_C_LANG."
  AND time between ".$from."  AND ".$to."
  GROUP BY id_obj
  "; 
	$v = my_DB_QUERY($query,__LINE__,__FILE__);

  return mysql_num_rows($v);
}


/**
Obrat peněz v zadaném období
@param (date) from
@param (date) to
@param (array) (id_products)
@return (flout) turnover_of_money
*/
function turnover_of_money($from , $to , $id_products = array())
{
  $from = intval(strtotime($from));
  $to = intval(strtotime($to) + DAY_SECONDS);

  $AND_id_product_IN = "";
  if(is_array($id_products) AND count($id_products) > 0)
  {
    $AND_id_product_IN = "AND id_produkt IN (".implode("," , $id_products).")";
  }

  $query = "
  SELECT
  sum(cena * ks) AS turnover_of_money_without_vat,
  sum(cena * ((100 + dph) / 100) * ks) AS turnover_of_money_with_vat
  FROM ".T_ORDERS_ADDRESS." AS ORDERS_ADDRESS
  JOIN ".T_ORDERS_PRODUCTS." AS ORDERS_PRODUCTS ON ORDERS_ADDRESS.id = ORDERS_PRODUCTS.id_obj
  WHERE id_produkt != 0 /* Vyloučíme dopravu */
  ".$AND_id_product_IN."
  AND ORDERS_ADDRESS.".SQL_C_LANG."
  AND time BETWEEN ".$from." AND ".$to;
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	$z = mysql_fetch_assoc($v);

  if(isset($z["turnover_of_money_without_vat"])) return $z;
  else return array("turnover_of_money_without_vat" => 0 , "turnover_of_money_with_vat" => 0);
}


// Inicializace globálních proměnných.
$data = '';
$nadpis = '';


// Statistiky od do.
if(isset($_GET["a"]) AND $_GET["a"] == "from-to")
{ 
if(isset($_POST["ok"]) AND !empty($_POST["ok"]))
{
  // Formulář si pamatuje datum.
  $from = $_POST["from"];
  if(!isset($_POST["to"]) OR empty($_POST["to"])) $to = date("d.m.Y");
  else $to = $_POST["to"];
  if(isset($to) AND empty($from)) $from = FIRST_ORDER_DATE;
}

// výchozí nastavení od do je aktuální den.
if(!isset($from)) $from = date("d.m.Y"); // Pokud není zadáno dáme dnešní datum.
if(!isset($to)) $to = date("d.m.Y"); // Pokud není zadáno dáme dnešní datum.


$nadpis = 'Statistiky Od - Do';

$data .= '
<script>
$(function() {
$( "#from" ).datepicker();
$( "#to" ).datepicker();
$( "#compare_from" ).datepicker();
$( "#compare_to" ).datepicker();
});
</script>

<form name="" action="" method="post">
  <div>
    Datum od: <input id="from" type="text" name="from" value="'.$from.'"> do: <input id="to" type="text" name="to" value="'.$to.'">
    <input type="submit" name="ok" value="OK">
  </div>
</form>
';


$turnover_of_money = turnover_of_money($from, $to);

$table = '
<table class="statistic" style="margin-bottom:20px;">
  <tr>
    <th style="">Od - Do</th>
    <th style="width:60px;">Objednávek</th>
    <th style="width:60px;">Kusů</th>
    <th style="width:60px;">Položek</th>
    <th class="cena">Obrat bez DPH</th>
    <!--<th class="cena">Obrat s DPH</th>-->
  </tr>
  <tr>
    <td>'.$from.' - '.$to.'</td>
    <td>'.number_format(count_order($from, $to),0,","," ").'</td>
    <td>'.number_format(number_of_items_sold($from, $to),0,","," ").'</td>
    <td>'.number_format(number_of_items_count_sold($from, $to),0,","," ").'</td>
    <td class="cena">'.number_format($turnover_of_money["turnover_of_money_without_vat"],2,","," ").' Kč</td>
    <!--<td class="cena">'.number_format($turnover_of_money["turnover_of_money_with_vat"],2,","," ").' Kč</td>-->
  </tr>
</table>';

$data .= $table;

// Nejprodávanější
$bestsellers_item = bestsellers_items($from , $to);
$bestsellers_item_values = array();
foreach($bestsellers_item AS $product)
{
  $bestsellers_item_values[] = '["'.$product["name"].'" , '.$product["count"].']';
}

$bestsellers_count = bestsellers_count($from , $to);
$bestsellers_count_values = array();
foreach($bestsellers_count AS $product)
{
  $bestsellers_count_values[] = '["'.$product["name"].'" , '.$product["count"].']';
}
// END Nejprodávanější

  // Statistiky v kategorii
  $cat = get_all_subcat(get_tree(0)); // Všechny podkategorie v hlavních kategoriích.
  $id_products = get_products_id_cat($cat); // Všechny produkty v hlavních kategoriích.
  $table = array();
  while(list($id_cat , $tmp_id_products) = each($id_products))
  {
    $turnover_of_money = turnover_of_money($from , $to , $tmp_id_products); // obsahuje obrat s DPH a bez DPH.

    $count_order_val = count_order($from , $to , $tmp_id_products);
    $number_of_items_sold_val = number_of_items_sold($from , $to , $tmp_id_products);
    $number_of_items_count_sold_val = number_of_items_count_sold($from , $to , $tmp_id_products);

    if($turnover_of_money["turnover_of_money_without_vat"] == 0) continue; // Nulové hodnoty neuvádět.

    // Klíč pro seřazení je obrat.
    $table[$turnover_of_money["turnover_of_money_without_vat"]] = '
    <tr>
      <td>'.$_SESSION["cache"]["categories"]["name"][$id_cat].'</td>
      <td>'.$count_order_val.'</td>
      <td>'.$number_of_items_sold_val.'</td>
      <td>'.$number_of_items_count_sold_val.'</td>
      <td class="cena">'.number_format($turnover_of_money["turnover_of_money_without_vat"],2,","," ").' Kč</td>
      <!--<td class="cena">'.number_format($turnover_of_money["turnover_of_money_with_vat"],2,","," ").' Kč</td>-->
    </tr>';
  }
  krsort($table); // Seřazení podle klíče.

  $table = '
  <table class="statistic" style="margin-bottom:20px;">
    <tr>
      <th style="">Kategorie</th>
      <th style="width:60px;">Objednávek</th>
      <th style="width:60px;">Kusů</th>
      <th style="width:60px;">Položek</th>
      <th class="cena">Obrat bez DPH</th>
      <!--<th class="cena">Obrat s DPH</th>-->
    </tr>
    '.implode('' , $table).'
  </table>
  ';
  $data .= $table;
  // END Statistiky v kategorii

  // Inicializace grafů.
  $turnover_of_money_without_vat = array();
  $turnover_of_money_with_vat = array();
  $index = 0;
  $step = $from;
  while(strtotime($step) <= strtotime($to))
  {
    if($index == 100) break; // Stopka po 100 cyklech
    $month_last_day = date("t.m.Y", strtotime($step)); // Poslední den v měsíci.
    if(strtotime($month_last_day) >= strtotime($to)) $month_last_day = $to; // Nepřekročím rozsah do.
    // Popisek osy x pro graf.
    $date = date_parse($step);
    $date = $date["month"].".".$date["year"]; // měsíc.rok

    // Hodnoty pro grafy.
    $count_order[] = '["'.$date.'" , '.count_order($step , $month_last_day).']'; // Počet objednávek.
    $number_of_items_count_sold[] = '["'.$date.'" , '.number_of_items_count_sold($step , $month_last_day).']'; // Počet prodaných kusů.
    $number_of_items_sold[] = '["'.$date.'" , '.number_of_items_sold($step , $month_last_day).']'; // Počet prodaných položek.

    $turnover_of_money_pre = turnover_of_money($step , $month_last_day); // obsahuje obrat s DPH a bez DPH.
    $turnover_of_money_without_vat[] = '["'.$date.'" , '.$turnover_of_money_pre["turnover_of_money_without_vat"].']'; // Obrat bez DPH.
    $turnover_of_money_with_vat[] = '["'.$date.'" , '.$turnover_of_money_pre["turnover_of_money_with_vat"].']'; // Obrat s DPH.

    // Zvýšení kroku.
    $step = next_month($step);
    $index++;
  }

  // Pole pro generování grafů.
  $count_order_plot[$from."-".$to] = $count_order;
  $number_of_items_count_sold_plot[$from."-".$to] = $number_of_items_count_sold;
  $number_of_items_sold_plot[$from."-".$to] = $number_of_items_sold;
  $turnover_of_money_without_vat_plot[$from."-".$to] = $turnover_of_money_without_vat;
  $turnover_of_money_with_vat_plot[$from."-".$to] = $turnover_of_money_with_vat;

  // Generování grafů.
  //$data .= get_bar_plot_compare('turnover_of_money_with_vat_plot' , 'Obrat v Kč s DPH' , $turnover_of_money_with_vat_plot);
  $data .= get_bar_plot_compare('turnover_of_money_without_vat_plot' , 'Obrat v Kč bez DPH' , $turnover_of_money_without_vat_plot);
  $data .= get_bar_plot_compare('count_order' , 'Počet Objednávek' , $count_order_plot);
  $data .= get_bar_plot_compare('number_of_items_count_sold_plot' , 'Počet prodaných položek' , $number_of_items_count_sold_plot);
  $data .= get_bar_plot_compare('number_of_items_sold_plot' , 'Počet prodaných kusů' , $number_of_items_sold_plot);
  $data .= get_bar_plot_bestseller('bestsellers_items' , $from.' - '.$to.' Nejprodávanější položky podle objednávek (počet objednávek, kde se položka vyskytuje)' , $bestsellers_item_values);
  $data .= get_bar_plot_bestseller('bestsellers_count' , $from.' - '.$to.' Nejprodávanější položky podle počtu prodaných kusů' , $bestsellers_count_values);
}
// END statistiky od - do.


// Roční statistiky
if(isset($_GET["a"]) AND $_GET["a"] == "year")
{
  $nadpis = 'Roční statistiky';

  if(isset($_POST["ok"]) AND !empty($_POST["ok"]))
  { // Stusknuto tlařízko "OK".
    if(isset($_POST["year"]) AND !empty($_POST["year"]))
    { // Porovnáváme jiným rokem.
      foreach($_POST["year"] as $year)
      {
        $year_separate_months[$year] = year_separate_months($year);
      }
    }
  }

  if(!isset($year_separate_months) OR empty($year_separate_months) OR count($year_separate_months) <= 0)
  { // První inicializace (aktuální rok).
    $year_separate_months[date("Y")] = year_separate_months(date("Y")); // Letošní rok rozdělený po měsících.
  }

  // Select s roky od první objednávky do letošního roku.
  $first_year = date_parse(FIRST_ORDER_DATE);
  $first_year = $first_year["year"]; // Rok kdy byla první objednávka.

  // Roky (option) pro select od data první objednávky.
  $select_year = '';
  for($year = $first_year; $year != date("Y") + 1; $year++)
  {
    // Označíme vybrané roky.
    $checked = '';
    if(isset($year_separate_months[$year])) $checked = 'checked="checked"';

    $select_year .= '<input type="checkbox" name="year[]" value="'.$year.'" '.$checked.'>'.$year.' ';
  }
  // END

  $data .= '
  <form method="post" name="" action="">
    <div>
      '.$select_year.'
      <input type="submit" name="ok" value="OK">
    </div>
  </form>
  ';

  foreach($year_separate_months as $year => $month)
  {
    // Každým průchodem vynulujeme.
    $count_order = array();
    $number_of_items_count_sold = array();
    $number_of_items_sold = array();
    $turnover_of_money_without_vat = array();
    $turnover_of_money_with_vat = array();

    foreach($month as $date)
    { // Průchod po měsících.
      $count_order[] = '["'.$date["name"].'" , '.count_order($date["from"] , $date["to"]).']'; // Počet objednávek.
      $number_of_items_count_sold[] = '["'.$date["name"].'" , '.number_of_items_count_sold($date["from"] , $date["to"]).']'; // Počet prodaných kusů.
      $number_of_items_sold[] = '["'.$date["name"].'" , '.number_of_items_sold($date["from"] , $date["to"]).']'; // Počet prodaných položek.

      $turnover_of_money_pre = turnover_of_money($date["from"] , $date["to"]); // obsahuje obrat s DPH a bez DPH.
      $turnover_of_money_without_vat[] = '["'.$date["name"].'" , '.$turnover_of_money_pre["turnover_of_money_without_vat"].']'; // Obrat bez DPH.
      $turnover_of_money_with_vat[] = '["'.$date["name"].'" , '.$turnover_of_money_pre["turnover_of_money_with_vat"].']'; // Obrat s DPH.
    }

    // Pole pro generování grafů.
    $count_order_plot[$year] = $count_order;
    $number_of_items_count_sold_plot[$year] = $number_of_items_count_sold;
    $number_of_items_sold_plot[$year] = $number_of_items_sold;
    $turnover_of_money_without_vat_plot[$year] = $turnover_of_money_without_vat;
    $turnover_of_money_with_vat_plot[$year] = $turnover_of_money_with_vat;
  }

  // Generování grafů.
  $data .= get_bar_plot_compare('turnover_of_money_without_vat_plot' , 'Obrat v Kč bez DPH' , $turnover_of_money_without_vat_plot);
  //$data .= get_bar_plot_compare('turnover_of_money_with_vat_plot' , 'Obrat v Kč s DPH' , $turnover_of_money_with_vat_plot);
  $data .= get_bar_plot_compare('count_order' , 'Počet Objednávek' , $count_order_plot);
  $data .= get_bar_plot_compare('number_of_items_count_sold_plot' , 'Počet prodaných položek' , $number_of_items_count_sold_plot);
  $data .= get_bar_plot_compare('number_of_items_sold_plot' , 'Počet prodaných kusů' , $number_of_items_sold_plot);
}
// END Roční statistiky.

?>