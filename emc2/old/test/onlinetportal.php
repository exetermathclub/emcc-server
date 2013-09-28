<?php

    /***********************************************************************
     * onlinetportal.php
     *
     * Joy Zheng, Allen Yuan, In Young Cho
     *
     * Computer Science 50
     * Final Project
     *
     * Allows online teams access to the guts and team rounds
     **********************************************************************/
     

	// load the Email validation checker, among other functions
	include 'functions.php';
	
	// load the constants
	include 'online_constants.php';

	session_start();
    
	// if not logged in, redirect to the login page
	if($_SESSION["online_team"] == false)
	{
		header("Location: onlinelogin.php");
	}
	    
	//Logout script and redirect
	if(isset($_GET["logout"]) && $_GET["logout"] == "now")
	{
		$_SESSION = array();
		header("Location: onlinelogin.php");
	}
	
    // connect to database
    include 'mysql_init.php';
    	
    $link = mysql_connect($sql_addr, $sql_user, $sql_pswd)
       or die('Could not connect: ' . mysql_error());
    mysql_select_db($sql_db) or die("Could not select database");
    
    $team_id = $_SESSION["online_team"];
    
    // get team name
    $query = "SELECT name, email FROM emc2_online_teams WHERE team_id = '$team_id';";
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    $resultarray = mysql_fetch_array($result);
    $team_name = $resultarray["name"];
    $email = $resultarray["email"];
    
    // get school name
    $query = "SELECT schoolname FROM emc2_accounts WHERE email = '$email';";
    $result =  mysql_query($query) or die('Query failed: ' . mysql_error()); 
    $resultarray = mysql_fetch_array($result);
    $school_name = $resultarray["schoolname"];
	
    $pg_name = "Online Team Portal";	
	include 'frame_top.php';

?>

<h1>Online Tournament Team Portal</h1>

        <p>Welcome, Team <b><?php echo $team_name; ?></b> from <b><?php echo $school_name; ?></b>!</p>
        
        <p>If this is not your team, or if you are done with this page, 
            please <a href="onlinetportal.php?logout=now">log out</a>.</p>
        
        <p>Before the contest begins, 
            please get comfortable with our answer submission form <a href="onlinesampleblank.php" >here</a>.</p>
        
        <?php
            //instructions will vary depending on the day
            if (!$online_started):
        ?>
            <p>The online tournament for this year has not yet started. 
            It will launch on <?php echo $online_date;?>. </p>
        <?php
            else:
        ?>
            <p><b>The online tournament has started! Good luck! Links to rounds will become active as they go online. 
                Note that timing will start as soon as you click on the link.</b></p>
                           
            <p><b>You may submit your answers as many times as you wish until time is up; only the last set of answers
                will be stored.</b><p>
        
        <?php
            endif;
        ?>

<h2>Team Round</h2>

    <?php if ($online_team_started): ?>
        <a href="onlineteam.php">Team Round Answer Submissions</a>
    <?php else: ?>
        Team Round Answer Submissions
    <?php endif ?>

<h2>Guts Round</h2>

    <?php if ($online_guts_started): ?>
        <a href="onlineguts.php">Guts Round Answer Submissions</a>
    <?php else: ?>
        Guts Round Answer Submissions
    <?php endif ?>
<?php if($_GET["gutsdone"] == 1)
    {
        echo " (completed)";
    }
    ?>
<?php
	include 'frame_bottom.php';
?>
