<?php

    /***********************************************************************
     * onlinegutsSubmit.php
     *
     * Joy Zheng, Allen Yuan, In Young Cho
     *
     * Computer Science 50
     * Final Project
     *
     * Submission page for online guts round, redirects to the correct page
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
	
	//if round has not started yet, redirect to the portal page
	if($online_guts_started == false)
	{
	    header("Location: onlinetportal.php");
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

    $pg_name = "Online Guts Submission";	    
	include 'frame_top.php';

    //fetches the current set of guts problems
    $query = "SELECT currentSet FROM emc2_online_tanswers WHERE team_id=" . $team_id . ";";
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    $resultarray = mysql_fetch_array($result);
    $currentSet = $resultarray["currentSet"];
    //determines if guts round is over
    $done = false;
    if($currentSet == 10)
    {
        $done = true;
    }
            
    //goes to the next set of guts problems
    $query = "UPDATE emc2_online_tanswers SET currentSet = currentSet + 1 WHERE team_id=" . $team_id . ";";
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());

    //redirects to the proper page
    if($done)
    {
        header("Location: onlinetportal.php");
    }
    if(!$done)
    {
        header("Location: onlineguts.php");
    }
?>
  
      

<?php
	include 'frame_bottom.php';
?>
