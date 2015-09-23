<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="cs" lang="cs">
  <head> 
    <title><?php echo uvozovky($TITLE); ?></title> 
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
    <meta name="Description" content="<?php echo uvozovky($DESCRIPTION); ?>" /> 
    <meta name="Keywords" content="<?php echo uvozovky($KEYWORDS); ?>" /> 
    <meta name="robots" content="index, follow" /> 
    <meta name="author" content="www.netaction.cz" /> 
    <?php echo $OVEROVACI_KOD; ?>
    <base href="http://<?php echo $_SERVER['SERVER_NAME']; ?>" /> 
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />

    <!-- google font open sans condensed -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans+Condensed:300&subset=latin,latin-ext' rel='stylesheet' type='text/css' />

    <link rel="stylesheet" href="/css/style.css" type="text/css" /> 
    <!--[if IE 7]>
      <link rel="stylesheet" href="/css/ie7.css" type="text/css" />
    <![endif]-->
    <!--[if IE 6]>
      <link rel="stylesheet" href="/css/ie6.css" type="text/css" />
    <![endif]-->


    <script src="/admin/fce.js" type="text/javascript"></script>
    <script type="text/javascript" src="/js/_functions.js"></script>
      
    <script type="text/javascript" src="/js/jquery-1.8.2.min.js"></script>



    <!-- Add mousewheel plugin (this is optional) -->
    <script type="text/javascript" src="/js/fancybox/jquery.mousewheel-3.0.6.pack.js"></script>
    
    <!-- Add fancyBox -->
    <link rel="stylesheet" href="/js/fancybox/jquery.fancybox.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="/js/fancybox/helpers/jquery.fancybox-thumbs.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="/js/fancybox/helpers/jquery.fancybox-buttons.css" type="text/css" media="screen" />
    <script type="text/javascript" src="/js/fancybox/jquery.fancybox.pack.js"></script>
    
    <!-- Optionally add helpers - button, thumbnail and/or media -->
    <script type="text/javascript" src="/js/fancybox/helpers/jquery.fancybox-buttons.js"></script>
    <script type="text/javascript" src="/js/fancybox/helpers/jquery.fancybox-media.js"></script>
    
    <script type="text/javascript" src="/js/fancybox/helpers/jquery.fancybox-thumbs.js"></script>


    <script type="text/javascript">
    	$(document).ready(function() {
    		$('a.fancybox').fancybox({
        });
    	});
    </script>

    <!-- Cycle2  -->
    <script type="text/javascript" src="/js/cycle/jquery.cycle2.min.js"></script>
    <!-- Cycle2 -->

    <script type="text/javascript">
    //<![CDATA[
    var _hwq = _hwq || [];
      _hwq.push(['setKey', '9C0D8AF98215F649D9BB87224CEFAFB5']);_hwq.push(['setTopPos', '60']);_hwq.push(['showWidget', '22']);(function() {
      var ho = document.createElement('script'); ho.type = 'text/javascript'; ho.async = true;
      ho.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.heureka.cz/direct/i/gjs.php?n=wdgt&sak=9C0D8AF98215F649D9BB87224CEFAFB5';
      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ho, s);
    })();
    //]]>
    </script>


<?php
if(!empty($_SESSION['alert_js1'])) {
	echo '
		<script type="text/javascript">
			function show_alert() {
				alert("'.$_SESSION['alert_js1'].'");
			}
		</script>';
		
	$onload = 'onload="show_alert()"';
		
	unset($_SESSION['alert_js1']);
}

if(!empty($javascript)) {
	echo $javascript;
}

echo $ADD_STYLE;
echo $GAAS;
?>

</head>


<body <?php echo $onload;?>>

  <div id="header_border_top">
  </div>
  <div id="header_bg">
		<div id="header">
      <?php echo $USER; ?>

			<a id="logo" href="/">
				
			</a>

      <div id="header_banner">
        <div class="doprava">
          <strong>VEŠKERÉ ZBOŽÍ SKLADEM</strong><br />
          Zasíláme do 24 hodin
        </div>
        <div class="pneu">
          <strong>PNEU DORUČÍME DO 24 HODIN<br />
          A ZDARMA!</strong>
        </div>
      </div>

			<a id="basket" href="/nakupni-kosik/">
				<span class="nadpis">
					Košík
				</span>
				<span class="polozky">
					<strong><?php echo $_SESSION['basket_total'];?> Kč</strong> / <?php if(empty($_SESSION['basket_suma'])) echo '0'; else echo $_SESSION['basket_suma'];?> ks
				</span>
			</a>

      <div class="clear"> </div>

			<div id="menu_top">
				<?php echo $TOP_MENU; ?>
        <div class="clear"> </div>
			</div>

      <div class="clear"> </div>
		</div>
  </div>

	<div id="content">
		<div id="left">
			<div id="search">
				<form method="get" action="/vyhledavani/">
					<div>
					  <input type="text" name="sWord" class="sword" value="<?php echo $sWord; ?>" />
					  <input type="submit" value="" class="button" />
            <a href="http://www.nekvinda-obchod.cz/29-pneumatiky/" title="Vyhledat pneu podle parametrů">Vyhledat pneu podle parametrů</a>
					</div>
				</form>
			</div>

			<div id="menu_left">
				<?php echo $LEFT_MENU ?>
			</div>

      <?php echo $BANER; ?>

      <div id="menu_text">
        <h2>Autobaterie a nabíječky autobaterií</h2>
        U nás zakoupíte <a href="http://www.nekvinda-obchod.cz/1-autobaterie/" title="Autobaterie Sznajder a Yuasa">autobaterie</a> vysoké kvality od světových výrobců a za příznivé ceny. Může se Vám hodit i <a href="http://www.nekvinda-obchod.cz/17-nabijecky-autobaterii/" title="Nabíječky autobaterií">nabíječka autobaterií</a>.<br />
        <br />
        <h2>Informace k autobateriím</h2>
        <a href="http://www.nekvinda-obchod.cz/clanek/12-zarucni-podminky-autobaterii.html" title="Záruční podmínky autobaterií">Záruční podmínky autobaterií</a><br />
        <a href="http://www.nekvinda-obchod.cz/clanek/13-znaky-plneho-nabiti-autobaterie.html" title="Znaky plného nabití autobaterie">Znaky plného nabití autobaterie</a><br />
        <a href="http://www.nekvinda-obchod.cz/clanek/14-zasady-bezpecnosti-pri-manipulaci-s-autobaterii.html" title="Zásady bezpečnosti při manipulaci s autobaterií">Zásady bezpečnosti při manipulaci s autobaterií</a><br />
        <a href="http://www.nekvinda-obchod.cz/clanek/15-vyber-a-instalace-nove-autobaterie.html" title="Výběr a instalace nové autobaterie">Výběr a instalace nové autobaterie</a><br />
        <a href="http://www.nekvinda-obchod.cz/clanek/16-udrzba-skladovani-a-likvidace-autobaterii.html" title="Údržba, skladování a likvidace autobaterií">Údržba, skladování a likvidace autobaterií</a><br />
        <br />
        <h2>Prodej pneumatik</h2>
        <a href="http://www.nekvinda-obchod.cz/clanek/28-nejsirsi-nabidka-pneu-pro-tezkou-techniku-skladem.html" title="Pneumatiky pro těžkou techniku skladem">Pneumatiky pro těžkou techniku skladem</a><br />

        <br /><br />

        <div class="newsletter_add" style="margin-bottom:20px;">
          <script type="text/javascript">
            function is_email_address(email)
            {
              var mail=/^.+@.+\..{2,4}$/
              return (mail.test(email));
            }

            function add_newsletter_valid(formular)
            {
              if (formular.email.value == "" || !is_email_address(formular.email.value))
              {
                window.alert("Prosíme uveďte správně Váš email.");
                formular.email.focus();
                return false;
              }

              return true;
            }
          </script>

          <form method="post" action="./newsletter_add.php" onsubmit="return add_newsletter_valid(this);">
            <table class="usertable">
              <tr>
                <th>
                  Přihlásit se k odběru novinek
                </th>
              </tr>
              <tr>
                <td>
                  Email:
                </td>
              </tr>
              <tr>
                <td>
                  <input style="width:160px;" type="text" name="email" value="" />
                </td>
              </tr>
              <tr>
                <td>
                  <input style="width:185px;" type="submit" name="prihlasit" value="Přihlásit" />
                </td>
              </tr>
            </table>
          </form>
        </div>


        <!-- Certifikát heuréka -->
        <div style="text-align:center; margin-bottom:20px;">
          <div id="showHeurekaBadgeHere-3"></div><script type="text/javascript">
          //<![CDATA[
          var _hwq = _hwq || [];
          _hwq.push([\'setKey\', \'9C0D8AF98215F649D9BB87224CEFAFB5\']);_hwq.push([\'showWidget\', \'3\', \'15481\', \'Nekvinda-Obchod.cz\', \'nekvinda-obchod-cz\']);(function() {
          var ho = document.createElement(\'script\'); ho.type = \'text/javascript\'; ho.async = true;
          ho.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.heureka.cz/direct/i/gjs.php?n=wdgt&sak=9C0D8AF98215F649D9BB87224CEFAFB5\';
          var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ho, s);
          })();
          //]]>
          </script>
        </div>
      </div>
		</div>

		<div id="content_right">
			<div id="navigation">
				<?php echo $NAVIGATION; ?>
			</div>

      <h1><?php echo $H1; ?></h1>

      <?php if(!empty($TEXT)): ?>
  			<div class="text">
  				<?php echo $TEXT; ?>
  			</div>
      <?php endif; ?>

      <?php
        if(!empty($PRODUCTS))
        {
         $PRODUCTS =
         $POROVNAVAC.
         $tridit.
         $PAGES.
         '<div class="products">
           '.$PRODUCTS.'
          </div>'.
          $PAGES;
        }

        echo
			  $PRODUCTS.'
			  '.$FORM.'
			  '.$COMMENTS;
      ?>
		</div>


    <div id="footer">
    	<?php echo $FOOT; ?>
      <div class="clear"> </div>
    </div>
	</div>

  <div id="autor">
    <?php include('vyrobilo-studio-netaction-cz.php'); ?>
  </div>

  <div id="fixed_bottom_bar">
    <?php echo $POROVNANI; ?>
  </div>

  <?php echo $GATR; ?>

</body>
</html>
