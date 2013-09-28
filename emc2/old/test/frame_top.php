<?php

    /***********************************************************************
     * frame_top.php
     *
     * Joy Zheng, Allen Yuan, In Young Cho
     *
     * Computer Science 50
     * Final Project
     *
     * Common element for title bar, header, etc.
     **********************************************************************/
     
	$pg_sitename = "EMC2 - ";
	error_reporting(E_ERROR | E_WARNING | E_PARSE);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>

<head>
	<title><?php echo $pg_sitename . $pg_name; ?></title>

	<meta http-equiv="Content-type" content="text/html;charset=UTF-8">

	<link rel="stylesheet" type="text/css" href="styles.css">

	<link rel="shortcut icon" href="favicon.ico">

	<!-- Start Open Web Analytics Code -->

	<script type="text/javascript">
	//<![CDATA[
	var owa_params = new Object();
	owa_params["site_id"] = "605f8471fa2a39884d629895f1c3025e";

	//]]>
	</script>

	<script type="text/javascript" src="http://peamath.dyndns.org/owa/public/main.php?owa_view=base.jsLogLib"></script>

	<!-- End Open Web Analytics Code -->		
</head>

<body>
<table class="frame">
  <tr>
    <td colspan=2 class="Header">
      <!-- BEGIN TITLE BAR -->
      <p> 
	<span style="font-size:96px">EMC</span>
	<span style="vertical-align:top; font-size:64px">2</span>
	<span style="font-size:40px">&nbsp;&nbsp;&nbsp;
		The Exeter Math Club Competition</span>
      </p>
      <!-- END TITLE BAR -->
    </td>
  </tr> <tr>
    <td class="main">
      <!-- BEGIN MAIN PAGE CONTENT -->
