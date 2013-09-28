<?php

    /***********************************************************************
     * registration.php
     *
     * Joy Zheng, Allen Yuan, In Young Cho
     *
     * 
     *
     * Allows users to register for accounts
     **********************************************************************/
     
error_reporting(E_ALL & ~E_NOTICE); 

	// load the email validation checker, among other functions
	include 'functions.php';

	session_start();

	// if you've logged-in already, redirect to the account management page
	if($_SESSION["email"])
	{
		header("Location: manage.php");
	}
	
	$pg_name = "Online Tournament Registration";


	// Set the page type (what sections are displayed)
	// false - display everything
	$pg_type	= false;
	
	// "login" - display only the login section
	if ($_POST["is_login"])
	{
		$pg_type = "login";
	}
	
	// "register" - display only the registration section
	else if ($_POST["is_registration"])
	{
		$pg_type = "register";
	}


	// LOG IN
	if ($pg_type == "login")
	{
		if (!$_POST["email"])
		{
			$login_err = "Email cannot be empty";
		}
		
		// validEmail is from the functions.php file
		else if (!validEmail($_POST["email"]))
		{
			$login_err = "Email must be a valid email address";
		}
		
		else
		{
		    // connect to sql database
	        include 'mysql_init.php';
	$link = mysql_connect('localhost:3306', 'mathclub_viewer', 'porblems')
		or die('Could not connect: ' . mysql_error());
	    //    $link = mysql_connect($sql_addr, $sql_user, $sql_pswd)
		  //      or die('Could not connect: ' . mysql_error());
      
	        mysql_select_db($sql_db) or die("Could not select database");

			$email = mysql_real_escape_string($_POST["email"]);
			$query = "SELECT password FROM emc2_accounts WHERE email = '$email';";
			$result = mysql_query($query) or die('Query failed: ' . mysql_error());

			$resultarray = mysql_fetch_row($result);

            // error if no such email registered
			if ($resultarray == false)
			{
				$login_err = "Email not recognized";
			}
			
			// if password matches, redirect to manage page
			else if ($resultarray[0] == sha1(mysql_real_escape_string($_POST['password'])))
			{
		
				header("Location: manage.php");

				mysql_free_result($result);
				mysql_close($link);

				$_SESSION["email"] = $email;
				exit();
			}
			
			// error message if the password is incorrect
			$login_err = "Incorrect password for supplied email";
			
			// end sql query
			mysql_free_result($result);
			mysql_close($link);
		}
	}
	// REGISTERING A NEW ACCOUNT
	if ($pg_type == "register")
	{
		// INPUT VALIDATION
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
			// NO DUPLICATE ACCOUNTS
	        include 'mysql_init.php';
	        
	        // connect to sql database
	        //$link = mysql_connect($sql_addr, $sql_user, $sql_pswd)
		       //  or die('Could not connect: ' . mysql_error());
          $link = mysql_connect('localhost:3306', 'mathclub_viewer', 'porblems')
		or die('Could not connect: ' . mysql_error());  
            
            
	        mysql_select_db($sql_db) or die("Could not select database");

			$email = mysql_real_escape_string($_POST["email"]);
			$query = "SELECT * FROM emc2_accounts WHERE email = '$email';";
			$result = mysql_query($query) or die('Query failed: ' . mysql_error());

			$resultarray = mysql_fetch_row($result);
            
            // error if account exists for email
			if ($resultarray)
			{
				$reg_error["email"] = "An account already exists for this email";
			}

			mysql_free_result($result);

			$schoolname = mysql_real_escape_string($_POST["schoolname"]);
			$query = "SELECT * FROM emc2_accounts WHERE schoolname = '$schoolname';";
			$result = mysql_query($query) or die('Query failed: ' . mysql_error());

			$resultarray = mysql_fetch_row($result);
            
            // error if account exists for school
			if ($resultarray)
			{
				$reg_error["schoolname"] = "An account already exists for this school/group";
			}

			mysql_free_result($result);

			// if no error, create the account, redirect to the manage page
			if (!$reg_error)
			{
			    // prepare sql query
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
                
                // set session variable
				$_SESSION["email"] = $email;
				$_SESSION["first_time"] = true;
				exit();
			}
			mysql_close($link);
		}
	}


	include 'frame_top.php';

?>

<h1>Online Tournament Registration</h1>

	<?php
		if (!$pg_type)
		{
	?>

<p>Registration for the 2013 online competition is now closed. Participating teams can log in to view score reports. Solutions will be available on home page next week.</p>
<p>Please send us feedback by email to 
<a href="mailto:exetermathclub+EMC2@gmail.com">exetermathclub+EMC2@gmail.com</a>. Thanks for participating.</p>

<p><b>Rules and instructions are available <a href="EMC2013Instructions.docx">here</a>.</b></p>

<p><b>Coaches will be able to access tests and answer sheets under Account Management after logging in on this page.</b></p>

<p>Rules for registering:</p>

<ul>
  <li>
	  The teams consist of up to four students.
    </li> <li>
	  We accept registrations as a school, as a group team (e.g. a math
	  circle), or as individuals by entering information of one student in one team (we will form teams for
	  you before the competition). You can modify your team roster before the registration closes.
    </li> <li class="italic">
	  Schools and clubs can now register as many teams as they can field.
    </li> <li>
    	We strongly recommend that students and coaches familiarize themselves with the answer form beforehand <a href="onlinesampleblank.php">here</a>.
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
<input type="hidden" name="is_registration" value="true" >
<table>
  <tr>
    <th colspan=2>
	Account Information
    </th>
  </tr> <tr>
    <td>
	Full Name
    </td> <td>
	<input type="text" name="fullname" value="<?php echo htmlspecialchars($_POST["fullname"]); ?>" > 
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
	<input type="text" name="email" value="<?php echo htmlspecialchars($_POST["email"]); ?>" > 
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
	<input type="password" name="password" >
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
	<input type="password" name="password2" >
    </td>
  </tr> <tr>
    <th colspan=2>
	School/Club Information
    </th>
  </tr> <tr>
    <td>
	School/Club Name
    </td> <td>
	<input type="text" name="schoolname" value="<?php echo htmlspecialchars($_POST["schoolname"]); ?>" > 
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
	<textarea cols=20 rows=4 name="address" ><?php echo htmlspecialchars($_POST["address"]); ?></textarea>
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
	<select name="schooltype" >
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
    <td colspan=2 class="right">
	<input type="submit" value="Register" >
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
