<?php
	session_start();

	if ($_SESSION["email"] == false)
	{
		header("Location: registration.php");
	}

	//Logout script and redirect
	if($_GET["logout"] == "now")
	{
		$_SESSION = array();
		session_regenerate_id();
		session_destroy();

		header("Location: registration.php");
	}

	//MySQL connect
	$link = mysql_connect('localhost:3306', 'mathclub_viewer', 'porblems')
		or die('Could not connect: ' . mysql_error());
	mysql_select_db('mathclub_emc2') or die("Could not select database");

	//update account information, if any.
	if ($_POST["account_update"])
	{
		//INPUT VALIDATION
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

	//update team information, if any
	if($_POST["team_update"])
	{
		if (!$_POST["name"])
		{
			$team_error["name"] = "Please provide a team name";
		}

		$email = mysql_real_escape_string($_SESSION["email"]);
		$name = mysql_real_escape_string($_POST["name"]);
		$oldname = mysql_real_escape_string($_POST["oldname"]);

		$query = "SELECT name FROM emc2_teams WHERE name='$name';";
		$result = mysql_query($query)
			or die ('Query failed: ' . mysql_error());
		$results = mysql_fetch_assoc($result);
		if ($results && $name != $oldname )
		{
			$team_error["name"] = '"' . htmlspecialchars($name) . '" is already taken.';
		}

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


	//fetch account information
	$query = "SELECT fullname, schoolname, address, category FROM emc2_accounts " .
		 "WHERE email = '{$_SESSION['email']}';";
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());

	$ac_info = mysql_fetch_assoc($result);

	$query = "SELECT team_id, name, p1, p2, p3, p4 FROM emc2_teams " .
		 "WHERE email = '{$_SESSION['email']}';";

	$result = mysql_query($query) or die ('Query failed: ' . mysql_error());

	$ac_teams = array();
	while ($line = mysql_fetch_assoc($result))
	{
		array_push($ac_teams, $line);
	}





	
	// ****************
	
	// get the information to determine whether this user has participated in 2010
	
	//fetch account information
	$query =  "SELECT COUNT(*) AS COUNT FROM `emc2_tscores_2010` AS t1 " .
		"INNER JOIN emc2_teams_2010 AS t2 " .
		"ON t1.team_id = t2.team_id " .
		 "WHERE email = '{$_SESSION['email']}';";
	
	
	
	$result1 = mysql_query($query) or die('Query failed: ' . mysql_error());
    $num_rows = mysql_num_rows($result1);
	
	$team_info_2010 = mysql_fetch_assoc($result1);
	
	$num_rows = $team_info_2010["COUNT"];
	// ************************
	
	
	//	mysql_close($link);
		//MySQL disconnect
	mysql_free_result($result);
		//MySQL disconnect
	mysql_free_result($result1);
	
	
	// ****************
	
	// get the information to determine whether this user has participated in 2011
	
	//fetch account information
	$query11 =  "SELECT COUNT(*) AS COUNT FROM `emc2_tscores_2011` AS t1 " .
		"INNER JOIN emc2_teams AS t2 " .
		"ON t1.team_id = t2.team_id " .
		 "WHERE email = '{$_SESSION['email']}';";
	
	
	
	$result11 = mysql_query($query11) or die('Query failed: ' . mysql_error());
    $num_rows11 = mysql_num_rows($result11);
	
	$team_info_2011 = mysql_fetch_assoc($result11);
	
	$num_rows11 = $team_info_2011["COUNT"];
	// ************************
	
	
	mysql_close($link);
		//MySQL disconnect
	//mysql_free_result($result);
		//MySQL disconnect
	mysql_free_result($result11);
	
	
	
	
	
	
	
	
	
	
	
	$pg_name = "Registration";

	include 'frame_top.php';

?>


<p class="topright"><a href="manage.php?logout=now">Log Out</a></p>

<h1>Registration</h1>

<p>Welcome<?php if (!$_SESSION["first_time"]) echo " back"; ?>, <?php echo $ac_info["fullname"]; ?>!</p>

<?php
	if ($team_info_2010["COUNT"] > 0) {
?>
<p>Thanks for participating in the 2010 Exeter Math Club Competition! Detailed
score reports for your students from the 2010 competition are available <a href="team-scores.php">here. </a></p>



<?php 
	}
?>

<?php
	if ($team_info_2011["COUNT"] > 0) {
?>
<p>Thanks for participating in the 2011 Exeter Math Club Competition! Detailed
score reports for your students from the 2011 competition are available <a href="team-scores-2011.php">here. </a></p>



<?php 
	}
?>




<h2>Account Information</h2>

<?php
	if ($acc_error)
	{ 
?>
<p class="errorbar"><?php echo $acc_error; ?></p>
<?php
	}
	if ($acc_confirm)
	{ 
?>
<p class="confirmbar"><?php echo $acc_confirm; ?></p>
<?php
	}
?>

<form action="manage.php" method="post">
<input type="hidden" name="account_update" value=true>
<table style="align:center">
  <tr>
    <td>
	Name
    </td> <td>
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
  </tr> <tr>
    <td>
	Email
    </td> <td>
	<?php echo htmlspecialchars($_SESSION['email']); ?>
    </td>
  </tr> <tr>
    <td>
    	Password
    </td> <td>
    	<a href="pwchange.php">Change Password</a> 
    </td>
  </tr> <tr>
    <td>
	School/Group Name
    </td> <td>
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
  </tr> <tr>
    <td>
	Address
    </td> <td>
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
  </tr> <tr>
    <td>
	Category
    </td> <td>
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

<h2>Team Management</h2>

<?php
	if ($ac_teams == null)
	{
?>
<p>No teams have been registered for your account. Ability to add teams will
arrive shortly. Please email us at exetermathclub+EMC2@gmail.com.</p>

<?php
	}
	else
	{
?>
If you would like to send more than two teams, please email us at exetermathclub+EMC2@gmail.com.
<br> &nbsp </br>


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
  <tr>
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
<input type="submit" value="Update Team Information" >
</form>
<?php
		}
	}
?>

<?php	
	include 'frame_bottom.php';
?>
