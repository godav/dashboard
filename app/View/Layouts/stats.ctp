<!doctype html>
<html>

<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<title>תצפיטבע</title>
	<base href="<?php echo $this->webroot; ?>">
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width">
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBu8lMaiAjoC93NHldXJtkb4g3DltW4HxM&v=3" type="text/javascript"></script>
	<link href='http://fonts.googleapis.com/earlyaccess/opensanshebrew.css' rel='stylesheet' type='text/css'>
       
	<?php 
            echo $this->Html->css(array('/css/vendor', '/css/app','/css/stats' ,'/css/bootstrap.min'));         
            echo $this->Html->script(array('/js/jquery-1.11.0','/js/jquery-2.2.3.min','/js/bootstrap.min','/js/highcharts','/js/highcharts-3d','/js/exporting','/js/drilldown','/js/no-data-to-display'));
        ?>

</head>

<body>

<!--[if lt IE 10]>
<p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->

<div ui-view="main"></div>
<div ui-view="stats-view">
	<div class="main container-fluid">
		<?php 
                echo $this->fetch('content'); 
                echo $this->Html->script('/js/stats');
                ?>
	</div>
</div>


</body>
</html>