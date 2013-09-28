<?php

	// load the Email validation checker, among other functions
	include 'functions.php';

	session_start();

	// if you've loggin in already, redirect to the account management page
	if($_SESSION["email"])
	{
		header("Location: manage.php");
	}
	
	$pg_name	= "Registration";


	// Set the page type (what sections are displayed)
	// false - display everything
	// "login" - display only the login section
	// "register" - display only the registration section
	$pg_type	= false;
	if ($_POST["is_login"])
	{
		$pg_type = "login";
	}
	else if ($_POST["is_registration"])
	{
		$pg_type = "register";
	}


	//LOG IN
	if ($pg_type == "login")
	{
		if (!$_POST["email"])
		{
			$login_err = "Email cannot be empty";
		}
		//validEmail is from the functions.php file
		else if (!validEmail($_POST["email"]))
		{
			$login_err = "Email must be a valid email address";
		}
		else
		{
			$link = mysql_connect('localhost:3306', 'mathclub_viewer', 'porblems')
				or die('Could not connect: ' . mysql_error());
			mysql_select_db('mathclub_emc2') or die("Could not select database");

			$email = mysql_real_escape_string($_POST["email"]);
			$query = "SELECT password FROM emc2_accounts WHERE email = '$email';";
			$result = mysql_query($query) or die('Query failed: ' . mysql_error());

			$resultarray = mysql_fetch_row($result);
			

			if ($resultarray == false)
			{
				$login_err = "Email not recognized";
			}
			else if ($resultarray[0] == sha1(mysql_real_escape_string($_POST['password'])))
			{
		
				header("Location: manage.php");

				mysql_free_result($result);
				mysql_close($link);

				$_SESSION["email"] = $email;
				exit();
			}
			
			$login_err = "Incorrect password for supplied email";
			mysql_free_result($result);
			mysql_close($link);
		}
	}
	//REGISTERING A NEW ACCOUNT
	if ($pg_type == "register")
	{

		//REGISTRATION CLOSED - HOW DID YOU GET HERE, ANYWAYS?!
		$reg_wtf = "WAIT, WHAT?! HOW DID YOU DO THAT, ANYWAYS?!";

	/*
		//INPUT VALIDATION
		if (!$_POST["fullname"])
		{
			$reg_error["fullname"] = "Full name cannot be empty";
		}
		if (!$_POST["email"])
		{
			$reg_error["email"] = "Email cannot be empty";
		}
		if (!validEmail($_POST["email"]))
		{
			$reg_error["email"] = "Email must be a valid email";
		}
		if ($_POST["password"] != $_POST["password2"])
		{
			$reg_error["password"] = "Passwords do not match";
		}
		if (!$_POST["schoolname"])
		{
			$reg_error["schoolname"] = "School name cannot be empty";
		}
		if (!$_POST["address"])
		{
			$reg_error["address"] = "Address cannot be empty";
		}
		if (!$_POST["schooltype"])
		{
			$reg_error["schooltype"] = "Please select one";
		}
		if (!$reg_error)
		{
			//NO DUPLICATE ACCOUNTS
			$link = mysql_connect('localhost:3306', 'mathclub_viewer', 'porblems')
				or die('Could not connect: ' . mysql_error());
			mysql_select_db('mathclub_emc2') or die("Could not select database");

			$email = mysql_real_escape_string($_POST["email"]);
			$query = "SELECT * FROM emc2_accounts WHERE email = '$email';";
			$result = mysql_query($query) or die('Query failed: ' . mysql_error());

			$resultarray = mysql_fetch_row($result);

			if ($resultarray)
			{
				$reg_error["email"] = "An account already exists for this email";
			}

			mysql_free_result($result);

			$schoolname = mysql_real_escape_string($_POST["schoolname"]);
			$query = "SELECT * FROM emc2_accounts WHERE schoolname = '$schoolname';";
			$result = mysql_query($query) or die('Query failed: ' . mysql_error());

			$resultarray = mysql_fetch_row($result);

			if ($resultarray)
			{
				$reg_error["schoolname"] = "An account already exists for this school/group";
			}

			mysql_free_result($result);

			//create the account, redirect
			if (!$reg_error)
			{
				$fullname = mysql_real_escape_string($_POST["fullname"]);
				$password = sha1(mysql_real_escape_string($_POST["password"]));
				$address = mysql_real_escape_string($_POST["address"]);
				$category = mysql_real_escape_string($_POST["schooltype"]);

				$query = "INSERT INTO emc2_accounts VALUES ('$fullname','$email'" .
					 ",'$password','$schoolname','$address','$category');";
				$result = mysql_query($query) or die('Query failed: ' . mysql_error());

				$query = "INSERT INTO emc2_teams VALUES ('','$email 1','$email'," . 
					 "'','','','');";
				$result = mysql_query($query) or die('Query failed: ' . mysql_error());

				$query = "INSERT INTO emc2_teams VALUES ('','$email 2','$email'," . 
					 "'','','','');";
				$result = mysql_query($query) or die('Query failed: ' . mysql_error());

				header('Location: manage.php');
				mysql_close($link);

				$_SESSION["email"] = $email;
				$_SESSION["first_time"] = true;
				exit();
			}

			mysql_close($link);
		}
	*/
	}


	include 'frame_top.php';

?>

<h1>Registration</h1>

	<?php
		if (!$pg_type)
		{
	?>

<p>Registration for the 2011 competition is currently closed. If you
registered for the 2010 competition however, you may still access your
information through the login.</p>
<p>Rules for registering:</p>

<ul>
  <li>
	The teams consist of four students. If you can't seem to find a last
	teammate, contact us by email after registering, and we'll see what's
	possible.
   </li> <li>
	We accept registrations as a school, as a group team (e.g. a math
	circle), and as individuals without a team (we will put form teams for
	you before the competition).
   </li> <li style="font-style:italic">
	Schools and clubs can now register as many teams as they can field. If
	you would like to field more than two teams, please email us at
	exetermathclub@gmail.com
   </li> <li> 
	Registration is $60 for a team of four students, $17 for an individual.
	<br>
	The registration fee includes a pizza lunch.
   </li> <li>
	Payment by check only. Please make them out to Phillips Exeter Academy,
	and send them to:<br>
	&nbsp;&nbsp;&nbsp;Zuming Feng<br>
	&nbsp;&nbsp;&nbsp;20 Main St<br>
	&nbsp;&nbsp;&nbsp;Exeter, NH 03833
   </li>
</ul>

	<?php
		}
		if ($pg_type != "register")
		{
	?>

<h2>Login</h2>

<?php
	if ($login_err)
	{ 
?>
<p class="errorbar"><?php echo $login_err; ?></p>
<?php
	}
?>

<form action="registration.php" method="post">
	<input type="hidden" name="is_login" value="true">
	Email:
	  <input type="text" name="email" value="<?php echo htmlspecialchars($_POST["email"]);?>">
	Password:
	  <input type="password" name="password">
	<input type="submit" value="Log In">
</form>

	<?php
		}
		if ($pg_type != "login")
		{
	?>

<h2>Register</h2>

<?php
	if ($reg_wtf)
	{ 
?>
<p class="errorbar"><?php echo $reg_wtf; ?></p>
<?php
	}
?>

<form action="registration.php" method="post">
<input type="hidden" name="is_registration" value="true" disabled="disabled">
<table>
  <tr>
    <th colspan=2>
	Account Information
    </th>
  </tr> <tr>
    <td>
	Full Name
    </td> <td>
	<input type="text" name="fullname" value="<?php echo htmlspecialchars($_POST["fullname"]); ?>" disabled="disabled"> 
<?php
		if ($reg_error["fullname"])
		{
?>
	<span class="errordesc"><?php echo $reg_error["fullname"]; ?></span> 
<?php
		}
?>
    </td>
  </tr> <tr>
    <td>
	Email
    </td> <td>
	<input type="text" name="email" value="<?php echo htmlspecialchars($_POST["email"]); ?>" disabled="disabled"> 
<?php
		if ($reg_error["email"])
		{
?>
	<span class="errordesc"><?php echo $reg_error["email"]; ?></span> 
<?php
		}
?>
    </td>
  </tr> <tr>
    <td>
	Password
    </td> <td>
	<input type="password" name="password" disabled="disabled">
<?php
		if ($reg_error["password"])
		{
?>
	<span class="errordesc"><?php echo $reg_error["password"]; ?></span> 
<?php
		}
?>
    </td>
  </tr> <tr>
    <td>
	Confirm Password
    </td> <td>
	<input type="password" name="password2" disabled="disabled">
    </td>
  </tr> <tr>
    <th colspan=2>
	School/Club Information
    </th>
  </tr> <tr>
    <td>
	School/Club Name
    </td> <td>
	<input type="text" name="schoolname" value="<?php echo htmlspecialchars($_POST["schoolname"]); ?>" disabled="disabled"> 
<?php
		if ($reg_error["schoolname"])
		{
?>
	<span class="errordesc"><?php echo $reg_error["schoolname"]; ?></span> 
<?php
		}
?>
    </td>
  </tr> <tr>
    <td>
	Address
    </td> <td>
	<textarea cols=20 rows=4 name="address" disabled="disabled"><?php echo htmlspecialchars($_POST["address"]); ?></textarea>
<?php
		if ($reg_error["address"])
		{
?>
	<span class="errordesc"><?php echo $reg_error["address"]; ?></span> 
<?php
		}
?>
    </td>
  </tr> <tr>
    <td>
	Registering as a
    </td> <td>
	<select name="schooltype" disabled="disabled">
	  <option value="">Select a type</option>
	  <option value="public">Public</option>
	  <option value="privat">Private</option>
	  <option value="magnet">Magnet</option>
	  <option value="group">Other Group/Club</option>
	  <option value="indiv">Individual(s) (email us too)</option>
	  <option value="other">Other (email us too)</option>
	</select>
<?php
		if ($reg_error["schooltype"])
		{
?>
	<span class="errordesc"><?php echo $reg_error["schooltype"]; ?></span> 
<?php
		}
?>
    </td>
  </tr> <tr>
    <td colspan=2 style="text-align:right">
	<input type="submit" value="Register" disabled="disabled">
    </td>
  </tr>
</table>
</form>

	<?php
		}
	?>
    

<?php
	include 'frame_bottom.php';
?>
