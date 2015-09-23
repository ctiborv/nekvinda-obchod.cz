<?php

require_once ('./admin/_mysql.php');
require_once ('_functions.php');

if(!empty($_GET['solo_kp']) && $_GET['solo_kp']==1) {

define(VDIR,'./UserFiles/video/');
define(SCDIR,'./UserFiles/video/screen/');

$video_file[1] = '3in1CornerPunch_ANG.flv';
$video_screen[1] = ''; //není potřeba vyplńovat, stačí spustit solo video.php udělat screen ze spuštěného vydea a uložit pod názvem jako je video
$video_file_nazev[1] = 'Děrovačky rohové';
$mime[1]='application/x-shockwave-flash';

$video_file[2] = 'BorderPunch_ANG.flv';
$video_file_nazev[2] = 'Děrovačky okrajů';
$mime[2]='application/x-shockwave-flash';

$video_file[3] = 'EyeletSetter_ANG.flv';
$video_file_nazev[3] = 'Ozdobné nýtování';
$mime[3]='application/x-shockwave-flash';

$video_file[4] = 'ScrapbossShapeboss_ANG.flv';
$video_file_nazev[4] = 'Scrapboss Shapeboss';
$mime[4]='application/x-shockwave-flash';

$video_file[5] = 'SewingMultiTool_ANG.flv';
$video_file_nazev[5] = 'Multifunkční nástroj';
$mime[5]='application/x-shockwave-flash';

$video_file[6] = 'ShapeCutterPlus_ANG.flv';
$video_file_nazev[6] = 'Vykrajovač ShapeCutter';
$mime[6]='application/x-shockwave-flash';

$video_file[7] = 'SqueezePunch_ANG.flv';
$video_file_nazev[7] = 'Děrovačky velkých tvarů';
$mime[7]='application/x-shockwave-flash';

$video_file[8] = 'StampPress_ANG.flv';
$video_file_nazev[8] = 'Razítkování Fiskars';
$mime[8]='application/x-shockwave-flash';

$video_file[9] = 'TexturesPlates_ANG.flv';
$video_file_nazev[9] = 'Strukturové desky';
$mime[9]='application/x-shockwave-flash';

$video_file[10] = 'ToolTaxi_ANG.flv';
$video_file_nazev[10] = 'Multifunkční nástroj';
$mime[10]='application/x-shockwave-flash';

// $video_file[11] = 'WireMandrel_ANG.flv';
// $video_file_nazev[11] = 'Drátové vřeteno';
// $mime[11]='application/x-shockwave-flash';
// 
// $video_file[12] = 'WireWinderSystem_ANG.flv';
// $video_file_nazev[12] = 'Drátování Fiskars';
// $mime[12]='application/x-shockwave-flash';
// 
// $video_file[13] = 'Razitko_dvourucni.flv';
// $video_file_nazev[13] = 'Razítko dvouruční';
// $mime[13]='application/x-shockwave-flash';
// 
// $video_file[14] = 'Derovacky_okraju_2v1.flv';
// $video_file_nazev[14] = 'Děrovačky okrajů 2v1';
// $mime[14]='application/x-shockwave-flash';

$video_file[11] = 'WireWinderSystem_ANG.flv';
$video_file_nazev[11] = 'Drátování Fiskars';
$mime[11]='application/x-shockwave-flash';

$video_file[12] = 'Razitko_dvourucni.flv';
$video_file_nazev[12] = 'Razítko dvouruční';
$mime[12]='application/x-shockwave-flash';

$video_file[13] = 'Derovacky_okraju_2v1.flv';
$video_file_nazev[13] = 'Děrovačky okrajů 2v1';
$mime[13]='application/x-shockwave-flash';

$odkaz='video/?video=';
}

//------------------------------
$pocet=0;

if(isset($video_file))$pocet = count($video_file);

if($pocet>0) {
reset($video_file);
reset($mime);
reset($video_file_nazev);

$SEZNAM='';
$sirka='114'; //větší je rozhozeno v IE
$VIDEO='';
$data='';

// zjištění a nastavení aktuální pozice požadovaného videa

$pozice=-1;
if(isset($_GET['video'])) $pozice = $_GET['video'];


// pokud je vybrané video vytvoříme přehrávač podle typu vide ******************

if ($pozice>=0) {
  $file = VDIR.$video_file[$pozice];
  $vlozka='.';
  if($_GET['produkt']>0) $vlozka = '../.';
  
	$size = file_size($file);
  $player='http://'.$_SERVER['SERVER_NAME'].'/flowplayer-3.1.5.swf';
  $skin='http://'.$_SERVER['SERVER_NAME'].'/'.'stijl.swf';
  $souburek_flv=$file;
  $souburek_flv2=$file2;
  $udelal_flv=0;
  /*$size_flv=GetRealSize($souburek_flv);*/
  if(file_exists($souburek_flv)) $size_flv=GetRealSize($souburek_flv);
  
  if(file_exists($file) && $size > 0) {
      if($mime[$pozice]=='video/x-ms-wmv') {
		      $souburek_wmp=$file;
          /*$size_flv=GetRealSize($souburek_flv);*/
           if(file_exists($souburek_wmp)) $size_wmp=GetRealSize($souburek_wmp);
          $uloz_wmv='<a class="uloz_wmp" href="'.$file.'" title="">'.$video_file_nazev[$pozice].' <br /><span class="velikost">'.$size_wmp.' MB <br /> Stáhnout video</span> </a>'; 
          $MEDIA_WMP='
              <div class="multim_wmp">
                <div class="player">
                  <object width="320" height="285"  type="application/x-mplayer2" data="http://'.$_SERVER['SERVER_NAME'].'/'.$souburek_wmp.'" >
                  <param name="filename" value="http://'.$_SERVER['SERVER_NAME'].'/'.$souburek_wmp.'" />
                  <param name="autosize" value="0" />
                  <param name="loop" value="true" />
                  <param name="autostart" value="1" />
                  <param name="animationatstart" value="1" />
                  <param name="displaysize" value="0" />
                  <param name="showcontrols" value="1" />
                  <param name="showaudiocontrols" value="1" />
                  <param name="showdisplay" value="0" />
                  <param name="showgotobar" value="0" />
                  <param name="showpositioncontrols" value="1" />
                  <param name="showstatusbar" value="0" />
                  <param name="showtracker" value="1" />
                  <p>Pro korektn&iacute; zobrazen&iacute; str&aacute;nky, si pros&iacute;m nainstalujte MediaPlayer přehravač.</p>
                  </object>
                </div>
                <div class="popis">
                  <span class="nazev"><br /><strong> '.$video_file_nazev[$pozice].'</strong></span><br /><br />
                  <!--<p><br />Pokud vám váš prohlížeč neumožňuje přehrát soubor přímo, využijte odkazu „Stáhnout video“ k jeho stažení do vašeho PC.<br /><br /></p>
                  '.$uloz_wmv.'-->

                </div>
                <p class="mezera">&nbsp;</p>
              </div>';
       }
       if($mime[$pozice]=='application/octet-stream' OR $mime[$pozice]=='application/x-shockwave-flash') {
		          
            $MEDIA_FLV='
              <script type="text/javascript" src="http://'.$_SERVER['SERVER_NAME'].'/flowplayer-3.1.4.min.js"></script>
              <div class="multim_flash">
                <div class="player">
                  <div id="player" style="float:left;width:320px;height:286px">
                  </div>
                </div>
                <script type="text/javascript"> 
                    // Flowplayer installation with Flashembed configuration 
                       flowplayer("player",
                        { 
                         // our Flash component 
                            src: "http://'.$_SERVER['SERVER_NAME'].'/flowplayer-3.1.5.swf", 
                         // we need at least this Flash version 
                            version: [9, 115], 
                         // older versions will see a custom message 
                            onFail: function()  { 
                              document.getElementById("info").innerHTML = 
                              "You need the latest Flash version to see MP4 movies. " + 
                              "Your version is " + this.getVersion() 
                              ; 
                              } 
                         // here is our third argument which is the Flowplayer configuration 
                        },{ clip: "'.$vlozka.$souburek_flv.'"
                        }); 
                  </script>
                
                <div class="popis">
                  <span class="nazev"><br /><strong> '.$video_file_nazev[$pozice].'</strong></span><br /><br />
                  <!--<br />Pokud vám váš prohlížeč neumožňuje přehrát soubor přímo, využijte odkazu „Stáhnout video“ k jeho stažení do vašeho PC.<br /><br />
                  <a class="uloz_flash" href="'.$file.'" title="">'.$video_file_nazev[$pozice].' <br /><span class="velikost">'.$size_flv.' MB <br /> Stáhnout video</span></a>
                  '.$uloz_wmv.'
                </div>-->
                <p class="mezera">&nbsp;</p>
              </div>';
              $udelal_flv=1;
      }
    }
}

$VIDEO=$MEDIA_FLV.$MEDIA_WMP;



//* vytvoření seznamu videí ****************************************************

for ($i=1;$pocet>=$i;$i++) {
  $x1 = explode (".", $video_file[$i]); // roztrhame - delicem je tecka
	$x2 = count($x1) - 1; // index posledniho prvku pole
	$x0 = $x2-1;
	$xname=$x1[0]; //prvni pole prvku - melo by být jméno bez přípony
	//$ext = strtoupper($x1[$x2]); // mame priponu
	
	$obr=SCDIR.$xname.'.jpg';
	if(!file_exists($obr)) $obr='/img/novideo.jpg';
  
  if($pozice != $i) {
     $img='<img src="'.$obr.'" alt="" border="0" width="'.$sirka.'"/>';
     $img='<a href="'.$odkaz.$i.'" title="'.$video_file_nazev[$i].'" >'.$img.'<br /><span style="width:'.$sirka.'">'.$video_file_nazev[$i].'</span></a>';
      }
      else $img='';
    $seznam_img.=$img;
   }
   
$SEZNAM='<p class="fotogalerie">'.$seznam_img.'</p>';


// výpis dat *******************************************************************

if($_GET['solo_kp']==1) {
$H1='Instruktážní videa';
$data='<div class="clanek">
'.$VIDEO.$SEZNAM.'
</div>';
}
//echo $data;
}
?>
