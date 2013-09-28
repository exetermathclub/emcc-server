<?php

    /***********************************************************************
     * manage.php
     *
     * Joy Zheng, Allen Yuan, In Young Cho
     *
     * Computer Science 50
     * Final Project
     *
     * Coaches' portal to manage teams and accounts
     **********************************************************************/
     
	session_start();
    error_reporting(E_ALL & ~E_NOTICE); 
	
	// require login if e-mail not set
	if ($_SESSION["email"] == false)
	{
		header("Location: registration.php");
	}

	// Logout script and redirect
	if ($_GET["logout"] == "now")
	{
		$_SESSION = array();
		session_regenerate_id();
		session_destroy();

		header("Location: registration.php");
	}

	// MySQL connect
	/*$link = mysql_connect('localhost:3306', 'mathclub_viewer', 'porblems')
		or die('Could not connect: ' . mysql_error());
	 mysql_select_db('allenyuan_mathclub_wikiTest') or die("Could not select database");
*/
	include 'mysql_init.php';

	$link = mysql_connect($sql_addr, $sql_user, $sql_pswd)
		or die('Could not connect: ' . mysql_error());
	mysql_select_db('mathclub_wikiTest') or die("Could not select database");

	// update account information, if any.
	if ($_POST["account_update"])
	{
		// INPUT VALIDATION
		if (!$_POST["fullname"])
		{
			$acc_error["fullname"] = "Full name cannot be empty";
		}
		
		if (!$_POST["schoolname"])
		{
			$acc_error["schoolname"] = "School name cannot be empty";
		}
		
		if (!$_POST["address"])
		{
			$acc_error["address"] = "Address cannot be empty";
		}
		
		if (!$_POST["schooltype"])
		{
			$acc_error["schooltype"] = "Please select one";
		}
        
        // execute sql query
		if (!$acc_error)
		{
			$email = mysql_real_escape_string($_SESSION["email"]);
			$fullname = mysql_real_escape_string($_POST["fullname"]);
			$schoolname = mysql_real_escape_string($_POST["schoolname"]);
			$address = mysql_real_escape_string($_POST["address"]);
			$category = mysql_real_escape_string($_POST["schooltype"]);
			
			$query = "UPDATE emc2_accounts SET fullname='$fullname', " .
				 "schoolname='$schoolname', address='$address', " .
				 "category='$category' WHERE email='{$email}';";
			$result = mysql_query($query) 
				or die('Query failed: ' . mysql_error());
			$acc_confirm = "Account information changed successfully!";
		}
	}

	// update team information, if any
	if ($_POST["team_update"])
	{
	    // require team name
		if (!$_POST["name"])
		{
			$team_error["name"] = "Please provide a team name";
		}

		$email = mysql_real_escape_string($_SESSION["email"]);
		$name = mysql_real_escape_string($_POST["name"]);
		$oldname = mysql_real_escape_string($_POST["oldname"]);

        // check if team name is taken
		$query = "SELECT name FROM emc2_teams WHERE name='$name';";
		$result = mysql_query($query)
			or die ('Query failed: ' . mysql_error());
		$results = mysql_fetch_assoc($result);
		
		if ($results && $name != $oldname )
		{
			$team_error["name"] = '"' . htmlspecialchars($name) . '" is already taken.';
		}
        
        // execute sql query if no errors
		if (!$team_error)
		{
			$p1 = mysql_real_escape_string($_POST["p1"]);
			$p2 = mysql_real_escape_string($_POST["p2"]);
			$p3 = mysql_real_escape_string($_POST["p3"]);
			$p4 = mysql_real_escape_string($_POST["p4"]);

			$query = "UPDATE emc2_teams SET name='$name', " .
				 "p1='$p1', p2='$p2', p3='$p3', p4='$p4' " . 
				 "WHERE email='$email' AND name='$oldname';";			
			$result = mysql_query($query) 
				or die('Query failed: ' . mysql_error());

			$team_confirm = "Information for team $oldname changed" .
					" successfully!";
			$sub_team = $name;
		}
		else
		{
			$sub_team = $oldname;
		}

	}

	// update online team information, if any
	if ($_POST["online_team_update"])
	{
	    // require team name and password
		if (!trim($_POST["online_name"]) || !trim($_POST["team_pw"]))
		{
			$online_team_error["name"] = "Please provide a team name and password";
		}
		
		// require every non-blank student to have a pw
		if (trim($_POST["online_p1"]) && (trim($_POST["p1_pw"]) == "" || trim($_POST["p1_pw"]) == NULL))
		{
		    $online_team_error["p1"] = "Please provide a password";
		}
		
		if (trim($_POST["online_p2"]) && (trim($_POST["p2_pw"]) == "" || trim($_POST["p2_pw"]) == NULL))
		{
		    $online_team_error["p2"] = "Please provide a password";
		}
		
		if (trim($_POST["online_p3"]) && (trim($_POST["p3_pw"]) == "" || trim($_POST["p3_pw"]) == NULL))
		{
		    $online_team_error["p3"] = "Please provide a password";
		}

		if (trim($_POST["online_p4"]) && (trim($_POST["p4_pw"]) == "" || trim($_POST["p4_pw"]) == NULL))
		{
		    $online_team_error["p4"] = "Please provide a password";
		}
		
		$email = mysql_real_escape_string($_SESSION["email"]);
		$name = mysql_real_escape_string($_POST["online_name"]);
		$oldname = mysql_real_escape_string($_POST["online_oldname"]);

        // check if team name is taken
		$query = "SELECT name FROM emc2_online_teams WHERE name='$name';";
                // select online database
                mysql_select_db('mathclub_wikiTest') or die("Could not select database");
                
		$result = mysql_query($query)
			or die ('Query failed: ' . mysql_error());
		$results = mysql_fetch_assoc($result);
		if ($results && $name != $oldname )
		{
			$online_team_error["name"] = '"' . htmlspecialchars($name) . '" is already taken.';
		}
        
        // execute sql query if no errors
		if (!$online_team_error)
		{
			$p1 = mysql_real_escape_string(trim($_POST["online_p1"]));
			$p2 = mysql_real_escape_string(trim($_POST["online_p2"]));
			$p3 = mysql_real_escape_string(trim($_POST["online_p3"]));
			$p4 = mysql_real_escape_string(trim($_POST["online_p4"]));
            $p1_pw = mysql_real_escape_string($_POST["p1_pw"]);
            $p2_pw = mysql_real_escape_string($_POST["p2_pw"]);
            $p3_pw = mysql_real_escape_string($_POST["p3_pw"]);
            $p4_pw = mysql_real_escape_string($_POST["p4_pw"]);
            $team_pw = mysql_real_escape_string($_POST["team_pw"]);
            
            if ($p1 == "")
            {
                $p1 = NULL;    
            }
                        mysql_select_db('mathclub_wikiTest') or die("Could not select database");

			$query = "UPDATE emc2_online_teams SET name='$name', team_password = '$team_pw', " .
				 "p1='$p1', p2='$p2', p3='$p3', p4='$p4', " .
				 "p1_pw = '$p1_pw', p2_pw = '$p2_pw', p3_pw = '$p3_pw', p4_pw = '$p4_pw' " . 
				 "WHERE email='$email' AND name='$oldname';";
			
			$result = mysql_query($query) 
				or die('Query failed: ' . mysql_error());

			$online_team_confirm = "Information for team $oldname changed" .
					" successfully!";
			$online_sub_team = $name;
		}
		
		else
		{
			$online_sub_team = $oldname;
		}
	}
    
    // add fields for online team
    if ($_POST["add_online_team"])
    {
        mysql_select_db($sql_db) or die("Could not select database");
        $email = mysql_real_escape_string($_SESSION["email"]);
        $query = "INSERT INTO emc2_online_teams (name, email) " .
                 "VALUES ('joy_crazy51353', '$email');";
        $result = mysql_query($query) or die('Query failed: ' . mysql_error());
        $query = "SELECT team_id FROM emc2_online_teams " .
                 "WHERE email = '$email' AND name = 'joy_crazy51353';";
		$result = mysql_query($query)
			or die ('Query failed: ' . mysql_error());
	    $row = mysql_fetch_array($result);
	    $team_number = $row["team_id"];
	    $name = $email . " " . $team_number;
	    $query = "UPDATE emc2_online_teams SET name = '$name' " .
                 "WHERE email = '$email' AND name = 'joy_crazy51353';";
		$result = mysql_query($query) or die ('Query failed: ' .mysql_error());  
		$query = "INSERT INTO emc2_online_tanswers (team_id) " .
		         "VALUES (" . $team_number . ");";
        mysql_query($query) or die('Query failed: ' .mysql_error()); 
		$query = "INSERT INTO emc2_online_tscores (team_id) " .
         "VALUES (" . $team_number . ");";
        mysql_query($query) or die('Query failed: ' .mysql_error()); 

		header("Location: manage-redirect.php");      
    }
    
    // delete team
    if($_POST["delete_online_team"])
    {
        mysql_select_db($sql_db) or die("Could not select database");
        $team_id = $_POST["online_team_id"];
        $query = "DELETE FROM emc2_online_teams WHERE team_id = '$team_id';";
        $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    }
     
       // select main database
                mysql_select_db($sql_db) or die("Could not select database");
	// fetch account information
	$query = "SELECT fullname, schoolname, address, category FROM emc2_accounts " .
		 "WHERE email = '{$_SESSION['email']}';";
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());
	$ac_info = mysql_fetch_assoc($result);

    // fetch team information for current user
	$query = "SELECT team_id, name, p1, p2, p3, p4 FROM emc2_teams " .
		 "WHERE email = '{$_SESSION['email']}';";
	$result = mysql_query($query) or die ('Query failed: ' . mysql_error());
	$ac_teams = array();
	
	// store results in array
	while ($line = mysql_fetch_assoc($result))
	{
		array_push($ac_teams, $line);
	}
	
        // select online database
                mysql_select_db($sql_db) or die("Could not select database");
	// fetch online team information for current user
	$query = "SELECT * FROM emc2_online_teams " . 
	      "WHERE email = '{$_SESSION['email']}';";	
	$result = mysql_query($query) or die ('Query failed: ' . mysql_error());	
	$online_ac_teams = array();
	
	// store results in array
	while ($line = mysql_fetch_assoc($result))
	{
		array_push($online_ac_teams, $line);
	}
	
	// ****************
	
                mysql_select_db($sql_db) or die("Could not select database");
	// fetch online team information for current user
	// get the information to determine whether this user has participated in 2010
	
	
  
	   // ****************
	
	// get the information to determine whether this user has participated in 2013
	
	//fetch account information
	$query13 =  "SELECT COUNT(*) AS COUNT FROM `emc2_online_tscores` AS t1 " .
		"INNER JOIN emc2_online_teams AS t2 " .
		"ON t1.team_id = t2.team_id " .
		 "WHERE t2.email = '{$_SESSION['email']}';";
	
	// echo $query13; 
	
	$result13 = mysql_query($query13) or die('Query failed: ' . mysql_error());
    $num_rows13 = mysql_num_rows($result13);
	
	$team_info_2013 = mysql_fetch_assoc($result13);
	
	$num_rows13 = $team_info_2013["COUNT"];
	// ************************
  
	mysql_close($link);
		//MySQL disconnect
	//mysql_free_result($result);
		//MySQL disconnect
	
	
	$pg_name = "Registration";

	include 'frame_top.php';

?>


<p class="topright"><a href="manage.php?logout=now">Log Out</a></p>

<h1>Registration</h1>

<p>Welcome<?php if (!$_SESSION["first_time"]) echo " back"; ?>, <?php echo $ac_info["fullname"]; ?>!</p>


<?php

    // check if team partcipated in 2010 competition
	if ($team_info_2013["COUNT"] > 0) {
?>

<p>Thanks for participating in the 2013 Exeter Math Club Competition! Detailed
score reports for your students from the 2013 competition are available <a href="team-scores-2013.php">here. </a></p>



<?php 
	}
?>

<?php
    // check if team participated in 2011 competition
	if ($team_info_2011["COUNT"] > 0) {
?>
<p>Thanks for participating in the 2011 Exeter Math Club Competition! Detailed
score reports for your students from the 2011 competition are available <a href="team-scores-2011.php">here. </a></p>
<?php 
	}
?>


<?php
	if ($team_info_2012["COUNT"] > 0) {
?>
<p>
  Thanks for participating in the 2012 Exeter Math Club Competition! Detailed
  score reports for your students from the 2012 competition are available <a href="team-scores-2012.php">here. </a>
</p>

<?php 
	}
?>




<h2>Account Information</h2>

<?php
    // display error message if there is an error
	if ($acc_error)
	{ 
?>
<p class="errorbar"><?php echo $acc_error; ?></p>
<?php
	}
	
	// else display a confirmation message
	if ($acc_confirm)
	{ 
?>
<p class="confirmbar"><?php echo $acc_confirm; ?></p>
<?php
	}
?>
<form action="manage.php" method="post">
  <input type="hidden" name="account_update" value=true>

  <table>
    <tr>
      <td>
	  Name
      </td> 
      <td>
	    <input type="text" name="fullname" value="<?php echo htmlspecialchars($ac_info["fullname"]); ?>">
<?php
		  if ($acc_error["fullname"])
		  {
?>
	          <span class="errordesc"><?php echo $acc_error["fullname"]; ?></span> 
<?php
		  }
?>
      </td>
    </tr> 
    <tr>
      <td>
	  Email
      </td> 
      <td>
	  <?php echo htmlspecialchars($_SESSION['email']); ?>
      </td>
    </tr> 
    <tr>
      <td>
      Password
      </td> 
      <td>
    	  <a href="pwchange.php">Change Password</a> 
      </td>
    </tr> 
    <tr>
      <td>
	  School/Group Name
      </td> 
      <td>
	  <input type="text" name="schoolname" value="<?php echo htmlspecialchars($ac_info["schoolname"]); ?>">
<?php
		  if ($acc_error["schoolname"])
		  {
?>
              <span class="errordesc"><?php echo $acc_error["schoolname"]; ?></span> 
<?php
		  }
?>
      </td>
    </tr> 
    <tr>
      <td>
	  Address
      </td> 
      <td>
	  <textarea cols=20 rows=4 name="address"><?php echo htmlspecialchars($ac_info["address"]); ?></textarea>
<?php
		  if ($acc_error["address"])
		  {
?>
	  <span class="errordesc"><?php echo $acc_error["address"]; ?></span> 
<?php
		  }
?>
      </td>
    </tr> 
    <tr>
      <td>
	  Category
      </td> 
      <td>
	  <select name="schooltype">
	    <option value="">Select a type</option>
	    <option value="public" <?php if ($ac_info["category"] == "public") echo "selected"; ?>>Public</option>
	    <option value="privat" <?php if ($ac_info["category"] == "privat") echo "selected"; ?>>Private</option>
	    <option value="magnet" <?php if ($ac_info["category"] == "magnet") echo "selected"; ?>>Magnet</option>
	    <option value="group" <?php if ($ac_info["category"] == "group") echo "selected"; ?>>Other Group/Club</option>
	    <option value="indiv" <?php if ($ac_info["category"] == "indiv") echo "selected"; ?>>Individual(s) (email us too)</option>
	    <option value="other" <?php if ($ac_info["category"] == "other") echo "selected"; ?>>Other (email us too)</option>
	    </select>
<?php
		  if ($acc_error["schooltype"])
		  { 
?>
	          <span class="errordesc"><?php echo $acc_error["schooltype"]; ?></span> 
<?php
		  }
?>
      </td>
    </tr>
  </table>

  <input type="submit" value="Update Account Information">
</form>


<h2>Online Tournament Tests and Answer Sheets</h2>

<p><a href="EMC2012Instructions.docx">Instructions and Rules</a></p>

<p><a href="EMC2_Problems_Speed2012.pdf">Speed Round Problems</a></p>

<p><a href="EMC2_Answer_Sheets2012_Speed.pdf">Speed Round Answer Sheets</a></p>

<p><a href="EMC2_Problems_Accuracy2012.pdf">Accuracy Round Problems</a></p>

<p><a href="EMC2_Answer_Sheets2012_Accuracy.pdf">Accuracy Round Answer Sheets</a></p>

<p><a href="EMC2_Problems_Team2012.pdf">Team Round Problems</a></p>

<p><a href="EMC2_Answer_Sheets2012_Team.pdf">Team Round Answer Sheets</a></p>

<p><a href="EMC2_Problems_Guts2012.pdf">Guts Rounds Problems</a></p>

<p><a href="EMC2_Answer_Sheets2012_Guts.pdf">Guts Round Answer Sheets</a></p>

<h2>Team Management</h2>

<?php
	if ($ac_teams == null)
	{
?>
<!--<p>No teams have been registered for your account. Ability to add teams will
arrive shortly. Please email us at exetermathclub+EMC2@gmail.com.</p> -->
<p>Team management is currently disabled because this is online management. </p>

<?php
	}
	else
	{
?>
<!--If you would like to send more than two teams, please email us at exetermathclub+EMC2@gmail.com.  -->
<p>Team management is currently disabled because this is online management. </p>



<?php 
		foreach ($ac_teams as $team)
		{
			if ($team_confirm && $sub_team == $team['name'])
			{
?>
<p class="confirmbar"><?php echo $team_confirm;?></p>
<?php
			}
?>
<form action="manage.php" method="post">
<input type="hidden" name="team_update" value="true" >
<input type="hidden" name="oldname" value="<?php echo $team['name']; ?>" >

<table>
    <th>
    	Team Name
    </th> <td>
    	<input type="text" name="name" value="<?php echo htmlspecialchars($team['name']); ?>" >
<?php
		if ($team_error["name"] && $sub_team == $team['name'])
		{
?>
	<span class="errordesc"><?php echo $team_error["name"]; ?></span> 
<?php
		}
?>
    </td>
  </tr> <tr>
    <td>
    	Student 1
    </td> <td>
    	<input type="text" name="p1" value="<?php echo htmlspecialchars($team['p1']); ?>" >
<?php
		if ($team_error["p1"] && $sub_team == $team['name'])
		{
?>
	<span class="errordesc"><?php echo $team_error["p1"]; ?></span> 
<?php
		}
?>
    </td>
  </tr> <tr>
    <td>
    	Student 2
    </td> <td>
    	<input type="text" name="p2" value="<?php echo htmlspecialchars($team['p2']); ?>" >
<?php
		if ($team_error["p2"] && $sub_team == $team['name'])
		{
?>
	<span class="errordesc"><?php echo $team_error["p2"]; ?></span> 
<?php
		}
?>
    </td>
  </tr> <tr>
    <td>
    	Student 3
    </td> <td>
    	<input type="text" name="p3" value="<?php echo htmlspecialchars($team['p3']); ?>" >
<?php
		if ($team_error["p3"] && $sub_team == $team['name'])
		{
?>
	<span class="errordesc"><?php echo $team_error["p3"]; ?></span> 
<?php
		}
?>
    </td>
  </tr> <tr>
    <td>
    	Student 4
    </td> <td>
    	<input type="text" name="p4" value="<?php echo htmlspecialchars($team['p4']); ?>" >
<?php
		if ($team_error["p4"] && $sub_team == $team['name'])
		{
?>
	<span class="errordesc"><?php echo $team_error["p4"]; ?></span> 
<?php
		}
?>
    </td>
  </tr>
</table>
<input type="submit" disabled="disabled" value="Update Team Information" >
</form>
<?php
		}
	}
?>

<h2><a name="online_management"></a>Online Team Management</h2>

<?php
	if ($online_ac_teams == null)
	{
?>
<p>You have not registered any online teams</p>

<?php
	}
?>

    <form action="manage.php#online_management" method="post">
    <input type="hidden" name="add_online_team" value="true">
    <input type="submit" value="Add Online Team" >
    </form>

<?php 
		foreach ($online_ac_teams as $online_team)
		{
			if ($online_team_confirm && $online_sub_team == $online_team['name'])
			{
?>
<p class="confirmbar"><?php echo $online_team_confirm;?></p>
<?php
			}
?>
<form action="manage.php#online_management" method="post">
  <input type="hidden" name="online_team_update" value="true" >
  <input type="hidden" name="online_oldname" value="<?php echo $online_team['name']; ?>" >

  <table>  
    <tr>
      <th>
           Team ID
      </th>
      <td>
        <?php echo htmlspecialchars($online_team['team_id']); ?>
      </td>
    <tr>
      <th>
    	  Team Name
      </th> 
      <td>
    	<input type="text" name="online_name" value="<?php echo htmlspecialchars($online_team['name']); ?>" >
        Password <input type="text" name="team_pw" value="<?php echo htmlspecialchars($online_team['team_password']); ?>">
<?php
		if ($online_team_error["name"] && $online_sub_team == $online_team['name'])
		{
?>
	        <span class="errordesc"><?php echo $online_team_error["name"]; ?></span> 
<?php
		}
?>
      </td>
    </tr> 
    <tr>
      <td>
    	Student 1
      </td> 
      <td>
    	<input type="text" name="online_p1" value="<?php echo htmlspecialchars($online_team['p1']); ?>" >
        Password
        <input type="text" name="p1_pw" value="<?php echo htmlspecialchars($online_team['p1_pw']); ?>" >
<?php
		  if ($online_team_error["p1"] && $online_sub_team == $online_team['name'])
	  	  { 
?>
  	          <span class="errordesc"><?php echo $online_team_error["p1"]; ?></span> 
<?php
	  	  } 		
?> 
      </td>
    </tr> 
    <tr>
      <td>
    	  Student 2
      </td> 
      <td>
       <input type="text" name="online_p2" value="<?php echo htmlspecialchars($online_team['p2']); ?>" >
         Password
         <input type="text" name="p2_pw" value="<?php echo htmlspecialchars($online_team['p2_pw']); ?>" >
<?php
		 if ($online_team_error["p2"] && $online_sub_team == $online_team['name'])
		 {
?>
	         <span class="errordesc"><?php echo $online_team_error["p2"]; ?></span> 
<?php
 	  	 }
?>
      </td>
    </tr> 
    <tr>
      <td>
    	  Student 3
      </td> 
      <td>
    	  <input type="text" name="online_p3" value="<?php echo htmlspecialchars($online_team['p3']); ?>" >
          Password
          <input type="text" name="p3_pw" value="<?php echo htmlspecialchars($online_team['p3_pw']); ?>" >
<?php
		  if ($online_team_error["p3"] && $online_sub_team == $online_team['name'])
		  {
?>
	          <span class="errordesc"><?php echo $online_team_error["p3"]; ?></span> 
<?php
		  }
?>
      </td>
    </tr> 
    <tr>
      <td>
    	Student 4
      </td> 
      <td>
    	<input type="text" name="online_p4" value="<?php echo htmlspecialchars($online_team['p4']); ?>" >
        Password
        <input type="text" name="p4_pw" value="<?php echo htmlspecialchars($online_team['p4_pw']); ?>" >
<?php
		if ($online_team_error["p4"] && $online_sub_team == $online_team['name'])
		{
?>
	        <span class="errordesc"><?php echo $online_team_error["p4"]; ?></span> 
<?php
		}
?>    
      </td>
    </tr>
  </table>

  <input type="submit" value="Update Team Information" >
</form>

<form action="manage.php#online_management" method="post">
  <input type="hidden" name="online_team_id" value="<?php echo $online_team['team_id']; ?>" >
  <input type="hidden" name="delete_online_team" value="true" >
  <input type="submit" value="Delete Team" >
</form> 

<br>
<?php
	}
?>

<?php	
	include 'frame_bottom.php';
?>




