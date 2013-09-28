<?php
/****************************************************************
 * EMC2 CONTEST REGISTRATION (C) 2010 David Xiao, All rights reserved
 *
 * you may use a modified version of this code for your own site, but
 * you are NOT allowed to distribute this work unless given express
 * permission to so by the original writer.
 ****************************************************************
 *
 *
 *NOTES ON THE DATABASE
 * 
 * This script relies on the presense of a MySQL database with the following
 * format:
 * 
 * Port: Default (3306)
 * Host: localhost (main webserver)
 * 
 * Table: emc2_accounts
 * +------------+---------------+------+-----+---------+-------+
 * | Field      | Type          | Null | Key | Default | Extra |
 * +------------+---------------+------+-----+---------+-------+
 * | fullname   | varchar(40)   | YES  |     | NULL    |       |
 * | email      | varchar(40)   | NO   | PRI |         |       |
 * | password   | varbinary(40) | YES  |     | NULL    |       |
 * | schoolname | varchar(40)   | YES  |     | NULL    |       |
 * | address    | varchar(255)  | YES  |     | NULL    |       |
 * | category   | varchar(6)    | YES  |     | NULL    |       |
 * +------------+---------------+------+-----+---------+-------+
 *
 *NOTES ON DEPENDENCIES
 *
 * This script relies on the existence of manage other files in order to work:
 * 1) functions.php - provides common functions, such as checking for valid
 *    emails.
 * 2) frame_top.php - the header of the website, common to all pages
 * 3) frame_bottom.php - the footer of the website, common to all pages
 * 4) styles.css - describes the look/feel of the site and its elements
 * 5) manage.php - the account management page.
 */



	// load the Email validation checker, among other functions
	include 'functions.php';

	// set the mysql username
	$db_host = 'localhost:3306';
	$db_user = ''; //username of mySQL account here
	$db_pswd = ''; //password
	$db_base = ''; //the name of the database with the relevant information

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

	///////////////////////////////////////
	/// Processing logic


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
			$link = mysql_connect($db_host, $db_user, $db_pswd)
				or die('Could not connect: ' . mysql_error());
			mysql_select_db($db_base) or die("Could not select database");

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
			$link = mysql_connect($db_host, $db_user, $db_pswd)
				or die('Could not connect: ' . mysql_error());
			mysql_select_db($db_base) or die("Could not select database");

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

				header('Location: manage.php');
				mysql_close($link);

				$_SESSION["email"] = $email;
				$_SESSION["first_time"] = true;
				exit();
			}

			mysql_close($link);
		}
	}


	//// end of post submission processing
	///////////////////////////////////////////

	///////////////////////////////////////////
	//// output the page


	// output the header of the page
	include 'frame_top.php';

?>

<h1>Registration</h1>

	<?php
		if (!$pg_type)
		{
	?>

<p>Registration closes January 25 for teams, and January 22 for individual
students. Rules for registering:</p>

<ul>
  <li>
	Participants must live within a two-hour's driving radius from Exeter.
   </li> <li>
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

<form action="registration.php" method="post">
<input type="hidden" name="is_registration" value="true">
<table>
  <tr>
    <th colspan=2>
	Account Information
    </th>
  </tr> <tr>
    <td>
	Full Name
    </td> <td>
	<input type="text" name="fullname" value="<?php echo htmlspecialchars($_POST["fullname"]); ?>"> 
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
	<input type="text" name="email" value="<?php echo htmlspecialchars($_POST["email"]); ?>"> 
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
	<input type="password" name="password">
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
	<input type="password" name="password2">
    </td>
  </tr> <tr>
    <th colspan=2>
	School/Club Information
    </th>
  </tr> <tr>
    <td>
	School/Club Name
    </td> <td>
	<input type="text" name="schoolname" value="<?php echo htmlspecialchars($_POST["schoolname"]); ?>"> 
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
	<textarea cols=20 rows=4 name="address"><?php echo htmlspecialchars($_POST["address"]); ?></textarea>
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
	<select name="schooltype">
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
	<input type="submit" value="Register">
    </td>
  </tr>
</table>
</form>

	<?php
		}
	?>
    

<?php
	// output the page footer
	include 'frame_bottom.php';
?>
