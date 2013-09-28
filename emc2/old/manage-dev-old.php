<?php
	session_start();

	if ($_SESSION == false)
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
	$link = mysql_connect('localhost:3306', 'emcuser', 'good2me')
		or die('Could not connect: ' . mysql_error());
	mysql_select_db('emc2db') or die("Could not select database");

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

	$query = "SELECT name, p1, p2, p3, p4 FROM emc2_teams " .
		 "WHERE email = '{$_SESSION['email']}';";

	$result = mysql_query($query) or die ('Query failed: ' . mysql_error());

	$ac_teams = array();
	while ($line = mysql_fetch_assoc($result))
	{
		array_push($ac_teams, $line);
	}



	//MySQL disconnect
	mysql_free_result($result);
	mysql_close($link);

	$pg_name = "Registration";

	include 'frame_top.php';

?>

<p style="text-align:right"><a href="manage-dev.php?logout=now">Log Out</a></p>

<h1>Registration</h1>

<p>Welcome<?php if (!$_SESSION["first_time"]) echo " back"; ?>, <?php echo $ac_info["fullname"]; ?>!</p>

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

<form action="manage-dev.php" method="post">
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
arrive shortly.</p>
<?php
	}
	else
	{
		foreach ($ac_teams as $team)
		{
			if ($team_confirm && $sub_team == $team['name'])
			{
?>
<p class="confirmbar"><?php echo $team_confirm;?></p>
<?php
			}
?>
<form action="manage-dev.php" method="post">
<input type="hidden" name="team_update" value="true">
<input type="hidden" name="oldname" value="<?php echo $team['name']; ?>">
<table>
  <tr>
    <th>
    	Team Name
    </th> <td>
    	<input type="text" name="name" value="<?php echo htmlspecialchars($team['name']); ?>">
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
    	<input type="text" name="p1" value="<?php echo htmlspecialchars($team['p1']); ?>">
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
    	<input type="text" name="p2" value="<?php echo htmlspecialchars($team['p2']); ?>">
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
    	<input type="text" name="p3" value="<?php echo htmlspecialchars($team['p3']); ?>">
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
    	<input type="text" name="p4" value="<?php echo htmlspecialchars($team['p4']); ?>">
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
<input type="submit" value="Update Team Information">
</form>
<?php
		}
	}
?>

<?php
	include 'frame_bottom.php';
?/ubmit" value="Update Team Information">
</form>

