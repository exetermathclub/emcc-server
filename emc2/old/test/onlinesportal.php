<?php

    /***********************************************************************
     * onlinesportal.php
     *
     * Joy Zheng, Allen Yuan, In Young Cho
     *
     * Computer Science 50
     * Final Project
     *
     * Allows individual participants to access accuracy and speed rounds
     **********************************************************************/
     
	// load the Email validation checker, among other functions
	include 'functions.php';
	
	// load the constants
	include 'online_constants.php';

	session_start();

	// if not logged in, redirect to the login page
	if($_SESSION["online_student"] == false)
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
    
    $student_key = $_SESSION["online_student"];
    
    // get team ID and student name
    $query = "SELECT team_id, indiv_id, studentName FROM emc2_online_students WHERE student_key = '$student_key';";
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    $resultarray = mysql_fetch_array($result);
    
    $student_name = $resultarray["studentName"];
    $team_id = $resultarray["team_id"];
    
    // get team name
    $query = "SELECT name, email FROM emc2_online_teams WHERE team_id = '$team_id';";
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    $resultarray = mysql_fetch_array($result);
    $team_name = $resultarray["name"];
    $email = $resultarray["email"];
    
    function logout()
    {
        $_SESSION["online_team"] = false;
        $_SESSION["online_student"] = false;
        redirect("onlinelogin.php");
    }    
    
    $pg_name = "Online Student Portal";	
	include 'frame_top.php';

?>

<h1>Online Tournament Student Portal</h1>

        <p>Welcome, <b><?php echo $student_name; ?></b> from team <b><?php echo $team_name; ?></b>!</p>
        
        <p>If this is not you, or you are done with this page, 
            please <a href="onlinesportal.php?logout=now" >log out</a>.</p>

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

<h2>Speed Round</h2>

    <?php if ($online_speed_started): ?>
        <a href="onlinespeed.php">Speed Round Answer Submissions</a>
    <?php else: ?>
        Speed Round Answer Submissions
    <?php endif ?>

<h2>Accuracy Round</h2>

    <?php if ($online_accuracy_started): ?>
        <a href="onlineaccuracy.php">Accuracy Round Answer Submissions</a>
    <?php else: ?>
        Accuracy Round Answer Submissions
    <?php endif ?>
    
<?php
	include 'frame_bottom.php';
?>
