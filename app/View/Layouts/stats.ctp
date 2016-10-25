<!doctype html>
<html ng-app="golan">

<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<title>תצפיטבע</title>
	<base href="<?php echo $this->webroot; ?>">
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width">
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBu8lMaiAjoC93NHldXJtkb4g3DltW4HxM&v=3" type="text/javascript"></script>
	<link href='http://fonts.googleapis.com/earlyaccess/opensanshebrew.css' rel='stylesheet' type='text/css'>
       
	<?php 
            echo $this->Html->css(array('/angular/styles/vendor','/angular/styles/app', '/css/stats' ,'/css/bootstrap.min'));
            echo $this->Html->script(array('/angular/scripts/vendor','/angular/scripts/app')); 
            echo $this->Html->script(array('/js/bootstrap.min','/js/jquery-1.11.0','/js/jquery-2.2.3.min','/js/highcharts','/js/highcharts-3d','/js/exporting','/js/drilldown','/js/no-data-to-display'));
        ?>
     <!--    <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
         <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css"> -->
</head>

<body>

<!--[if lt IE 10]>
<p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->

<div ng-include="'app/components/header/header.html'"></div>
<div ui-view="main"></div>
<div ui-view="stats-view">
	<div class="main container-fluid">
		<?php 
                echo $this->fetch('content'); 
                
                ?>
	</div>
</div>
<?php

//$this->Js->set('golanBackEnd', $globalData);
echo $this->Js->writeBuffer(array('onDomReady' => false));


$debug_flag = 0;
$debug_flag = Configure::read('debug');
if( !$debug_flag) {
	// GA only if debug is off
	?>
	<!-- Google Analytics: change UA-XXXXX-X to be your site's ID. -->
	<script>
		(function (i, s, o, g, r, a, m) {
			i['GoogleAnalyticsObject'] = r;
			i[r] = i[r] || function () {
					(i[r].q = i[r].q || []).push(arguments)
				}, i[r].l = 1 * new Date();
			a = s.createElement(o),
				m = s.getElementsByTagName(o)[0];
			a.async = 1;
			a.src = g;
			m.parentNode.insertBefore(a, m)
		})(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

		ga('create', 'UA-69970224-1', 'auto');

	</script>

	<?php

}   
echo $this->Html->script('/js/stats');

?>

</body>
</html>