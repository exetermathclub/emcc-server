<?php

    /***********************************************************************
     * onlinelogin.php
     *
     * Joy Zheng, Allen Yuan, In Young Cho
     *
     * Computer Science 50
     * Final Project
     *
     * Login page for online teams and individuals
     **********************************************************************/
     
	// load the Email validation checker, among other functions
	include 'functions.php';

	session_start();
    
	// if you've logged in already, redirect to the account management page
	if(isset($_SESSION["online_student"]) && $_SESSION["online_student"] != 0)
	{
		header("Location: onlinesportal.php");
	}
    if(isset($_SESSION["online_team"]) && $_SESSION["online_team"] != 0)
    {
        header("Location: onlinetportal.php");
    }

	$pg_name = "Online Tournament Login";

    
	// Set the page type (what sections are displayed)
	// false - display regular
	// true - login already attempted
	
	$pg_type = false;
	if (isset($_POST["email"]) || isset($_POST["team_id"]) || isset($_POST["login_type"]) || isset($_POST["password"]))
	    {$pg_type	= true;}

    $login_err = array();
    $fields_full = true;

	//LOG IN 
	if ($pg_type)
	{
		if ($_POST["email"] == "")
		{
			$login_err[] = "Email cannot be empty";
			$fields_full = false;
		}
		//validEmail is from the functions.php file
		else if (!validEmail($_POST["email"]))
		{
			$login_err[] = "Email must be a valid email address";
			$fields_full = false;
		}
		// if team_id is not empty or not a non-negative integer
		if ($_POST["team_id"] == "")
		{
		    $login_err[] = "Team ID cannot be empty";
		    $fields_full = false;    
		}
		else if (!preg_match("/^\d+$/", $_POST["team_id"]))
		{
		    $login_err[] = "Team ID should be non-negative integer.";
		    $fields_full = false;
		}
		if ($fields_full)
		{
		    // connect to database
        	include 'mysql_init.php';
        	
        	$link = mysql_connect($sql_addr, $sql_user, $sql_pswd)
        		or die('Could not connect: ' . mysql_error());
        	mysql_select_db($sql_db) or die("Could not select database");
            
            // get fields
			$email = mysql_real_escape_string($_POST["email"]);
			$team_id = mysql_real_escape_string($_POST["team_id"]);
			$login_type = mysql_real_escape_string($_POST["login_type"]);
			$password = mysql_real_escape_string($_POST["password"]);
			
			// get password with that email and id
			$query = "SELECT team_password FROM emc2_online_teams WHERE email = '$email' AND team_id = '$team_id';";
			$result = mysql_query($query) or die('Query failed: ' . mysql_error());

			$resultarray = mysql_fetch_row($result);
            
            // if team_id did not match email
			if ($resultarray == false)
			{
				$login_err[] = "Email and Team ID did not match.";
			}
			else if($password == "")
			{
			    $login_err[] = "Password field cannot be empty.";
			}
			// for team login
			else if ($login_type == "team" && $resultarray[0] == $password)
			{
		
				header("Location: onlinetportal.php");
                
				mysql_free_result($result);
				mysql_close($link);

                $_SESSION = array();
				$_SESSION["online_team"] = $team_id;
				$_SESSION["online_student"] = 0;
				exit();
			}
			// for student login
			else if ($login_type != "team")
			{
			    //gets the password and key of that student
			    $student_id = intval($login_type);
		        $query = "SELECT student_key, student_password FROM emc2_online_students WHERE team_id = '$team_id' AND indiv_id = '$student_id';";
		        $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    			$resultsarray = mysql_fetch_array($result);
		        
		        //if passwords match
		        if (!$resultsarray)
		        {
		            $login_err[] = "This team does not contain said student.";
		        }
		        else 
		        {
		            if ($password == $resultsarray["student_password"])
				    {
				        header("Location: onlinesportal.php");
                        
				        mysql_free_result($result);
				        mysql_close($link);
                        
                        $_SESSION = array();
				        $_SESSION["online_student"] = $resultsarray["student_key"];
				        $_SESSION["online_team"] = 0;
				        exit();
				    }
			        $login_err[] = "Incorrect password for account.";
			    }
			}
			else
			{
			    $login_err[] = "Incorrect password for account.";
			}
			
			mysql_free_result($result);
			mysql_close($link);
		}
	}
	include 'online_constants.php';
	
	include 'frame_top.php';

?>

<h1>Online Tournament Login</h1>

	<?php
		if (!$pg_type):
	?>

        <p>Welcome to the EMC^2 online tournament!</p>
        
        <?php
            //instructions will vary depending on the day
            if ($online_started == false)
            {
        ?>
            <p>The online tournament for this year has not yet started. It will launch on <?php echo $online_date;?>.
                However, feel free to log in to the portal pages to check your password. </p>
        <?php
            }
            else
            {
        ?>
            <p>The online tournament has started! Good luck!
        
        <?php
            }
        ?>
    <?php
        endif;
?>
<b>Instructions for the online test are available <a href="EMC2012Instructions.docx">here</a></b>.

<h3>Schedule for <b>2013</b> Online Competition (January 26, 2013)</h3>

<table>
  <tr>
    <th>
	Time
    </th> <th>
	Event
    </th> 
  </tr> <tr>
    <td>
	11:00am - 11:20am
    </td> <td>
	Speed Round
    </td>
  </tr> <tr>
    <td>
	11:20am - 11:55am
    </td> <td>
	Accuracy Round
    </td> 
  </tr> <tr>
    <td>
	12:00am - 12:40am
    </td> <td>
	Team Round
    </td> 
  </tr> <tr>
    <td>
	5:00 pm after (self-paced practice)
    </td> <td>
	Guts and Puzzle Rounds Release
    </td> 
  </tr> 
</table>
Solutions and results will be available on January 27, 2013.

<p><b>Coaches will be able to access tests and answer sheets after logging in on the <a href="registration.php">online registration</a> page.</b></p>

<p>Login instructions:</p>

<ul>
  <li>
	Coaches can view and/or set passwords for both individuals and teams by logging in 
	<a href ="registration.php">here</a>.
   </li> <li>
    Enter in the email of your coach's account and the ID of your team. Your coach can
    access your team ID by logging in to their account under Registration.
   </li> <li>
	If you are an individual and want to access either the speed or accuracy rounds, 
	select your individual ID (1, 2, 3, or 4) from the drop-down menu. If you are a team looking
	to access the team and/or guts rounds, select "team" from the drop-down menu.
   </li> 
</ul>

<i>Prior to the competition's start, we strongly suggest that you familiarize yourself with the sample submission form <a href="onlinesampleblank.php">here</a>.</i>

<h2>Login</h2>

<?php
    foreach($login_err as $error_mes)
    {
?>
<p class="errorbar"><?php if (isset($_POST["submitted"])) {echo $error_mes;} ?></p>
<?php
	}
	$selected = "selected = \"selected\"";
?>

<form action="onlinelogin.php" method="post">
	<input type="hidden" name="is_login" value="true">
	Email:
	  <input type="text" name="email" value="<?php echo htmlspecialchars($_POST["email"]);?>">
	Team ID:
	  <input type="text" name="team_id" value="<?php echo htmlspecialchars($_POST["team_id"]);?>">
	Type:
	  <select name="login_type">
	    <option <?php if ($_POST["login_type"]=="team") {echo $selected; }?> value="team">Team</option>
	    <option <?php if ($_POST["login_type"]==1) {echo $selected; }?> value="1">Student 1</option>
	    <option <?php if ($_POST["login_type"]==2) {echo $selected; }?> value="2">Student 2</option>
	    <option <?php if ($_POST["login_type"]==3) {echo $selected; }?> value="3">Student 3</option>
	    <option <?php if ($_POST["login_type"]==4) {echo $selected; }?> value="4">Student 4</option>
	  </select>
	Password:
	  <input type="password" name="password">
	<input type="submit" name="submitted" value="Log In">
</form>

<?php
	include 'frame_bottom.php';
?>
