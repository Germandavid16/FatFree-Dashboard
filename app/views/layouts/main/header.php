<!DOCTYPE html>
<html>
<head>
	<title><?=$title?></title>
	<script src="/js/jquery-3.2.1.min.js"></script>
	<script src="/js/main.js"></script>
	<script src="/js/bootstrap.min.js" type="text/javascript"></script>
	<script src="/js/material.min.js" type="text/javascript"></script>
	<script src="/js/perfect-scrollbar.jquery.min.js" type="text/javascript"></script>

	<link rel="stylesheet" type="text/css" href="/css/style.css">
	<link rel="stylesheet" type="text/css" href="/css/font-awesome.css">

	<link href="/css/bootstrap.min.css" rel="stylesheet" />
	<!--  Material Dashboard CSS    -->
	<link href="/css/material-dashboard23cd.css?v=1.2.1" rel="stylesheet" />
	<!--  CSS for Demo Purpose, don't include it in your project     -->
	<link href="/css/demo.css" rel="stylesheet" />
	<!--     Fonts and icons     -->
	<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons" />
	<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Montserrat%3A300%2C400%2C500%2C600%2C700%2C900%7COpen+Sans%3A300%2C400%2C500%2C600%2C700%2C800%7CPlayfair+Display%7CRoboto%7CRaleway%7CSpectral%7CRubik|Material+Icons">
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="<? if ($page) { ?>page_<?=$page?><?}?>">
<?php if (isset($h1_inner) &&  $h1_inner == "Sign in") { ?>
<? } else  { ?>
<div class="mainbar">
	<div class="logo">
		<img src="/img/logo.jpg">
	</div>
	<div class="bar">
		<? if ($menu) { ?>
		<div class="menu">
			<ul>
				<? foreach ($menu as $item) { ?>
				<li class="<? if ($item['active']) { ?>active<? } ?>">
					<a class="item" href="<?=$item['link']?>"><?=$item['title']?></a>
				</li>
				<? } ?>
			</ul>
		</div>
		<? } ?>
	</div>
</div>
<? } ?>
<? if ($submenuList) { ?>    
<div class="secondbar">
	<?php if (isset($mpt_session_name) && !empty($mpt_session_name)) :?>
		<h3 style="margin-left: 15px; color: #30a2c1;"><?php echo 'Welcome '.$mpt_session_name; ?></h3>
	<?php endif;?>

	<? foreach ($submenuList as $i => $submenu) { ?>    
	<div class="submenu submenu<?=$i?>">
		<ul>
			<? foreach ($submenu as $item) { ?>
			<li class="<? if ($item['active']) { ?>active<? } ?>">
				<a class="item" href="<?=$item['link']?>"><?=$item['title']?></a>
			</li>
			<? } ?>
		</ul>
	</div>
	<? } ?>
</div>
<? } ?>

<? if ($h1) { ?>
	<h1><?=$h1?></h1>
<? } ?>

<? if (!isset($h1_inner) || $h1_inner != "Sign in") { ?>
<div class="content">
<? } ?>

<? if ($messageCommon)  { ?>
	<div class="messageCommon"><?=$messageCommon?></div>
<? } ?>
<? if ($messageError)  { ?>
	<div class="messageCommon messageError"><?=$messageError?></div>
<? } ?>