<?php

    /***********************************************************************
     * admin.php
     *
     * Joy Zheng, Allen Yuan, In Young Cho
     *
     * Computer Science 50
     * Final Project
     *
     * Login site for admin password: porblems
     **********************************************************************/

/*
	//OPEN WEB ANALYTICS LOGGER CALL
	require_once('/var/www/owa/owa_php.php');
		
	$config['site_id'] = '605f8471fa2a39884d629895f1c3025e';
	$owa = new owa_php($config);
	$owa->log();
*/
	session_start();

	$pg_name	= "Contest Control";


	//Logout script and redirect
	if($_GET["logout"] == "now")
	{
		$_SESSION = array();
		session_regenerate_id();
		session_destroy();

		header("Location: admin.php");
	}

	include 'frame_top.php';

	if (!$_SESSION["admin"] && $_POST["password"] != 'porblems')
	{
		if($_POST)
		{
			$passworderr = "Incorrect password: please try again.";
		}

		echo <<<HTML

<h1>Admin Login</h1>
<h2>Password?</h2>
<form action="admin.php" method="POST">
  <p>
  	Password: 
	<input type="password" name="password">
	<input type="submit" value="Enter">
	<span class="errordesc">$passworderr</span>
  </p>
</form>
HTML;
	}
	else
	{
		$_SESSION["admin"] = true;
?>
<p style="text-align:right"><a href="admin.php?logout=now">Log Out</a></p>

<h1>Contest Control</h1>

<h2>Registration</h2>

<p><a href="roster.php">Roster</a></p>

<h2>Scoring</h2>

<p>
	<a href="scores-indivA.php">Individual Round A</a> | 
	<a href="scores-indivB.php">Individual Round B</a> | 
	<a href="scores-team.php">Team Round</a> | 
	<a href="scores-guts.php">Guts Round</a>  
</p>

<h2>Answers</h2>

<p>
	<a href="adminspeed.php">Individual Round A (Speed)</a>
	<a href="adminaccuracy.php">Individual Round B (Accuracy)</a>
	<a href="adminteam.php">Team Round</a>
	<a href="adminguts.php">Guts Round</a>
	</p>

<h2>Guts</h2>

<p>
	<a href="guts-board.php">Guts Scoreboard</a> 
</p>

<h2>Results</h2>

<p>
	<a href="score-form.php">Score Reports</a>
</p>

<?php

	}

	include 'frame_bottom.php';
?>
