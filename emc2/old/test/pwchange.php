<?php 

    /***********************************************************************
     * pwchange.php
     *
     * Joy Zheng, Allen Yuan, In Young Cho
     *
     * Computer Science 50
     * Final Project
     *
     * Password change form for coaches
     **********************************************************************/
     
	session_start();
	
	if ($_SESSION["email"] == false)
	{
		header("Location: registration.php");
	}

	$pg_name	= "Password Change";
	

	include 'frame_top.php';

	if ($_POST && $_POST["new_password"] == $_POST["confirm_password"])
	{
/*		//MySQL connect
		$link = mysql_connect('localhost:3306', 'mathclub_viewer', 'porblems')
			or die('Could not connect: ' . mysql_error());
		mysql_select_db('allenyuan_mathclub_emc2') or die("Could not select database");
*/
    	include 'mysql_init.php';
	
    	$link = mysql_connect($sql_addr, $sql_user, $sql_pswd)
    		or die('Could not connect: ' . mysql_error());
    	mysql_select_db($sql_db) or die("Could not select database");

		$email = mysql_real_escape_string($_SESSION["email"]);
		$query = "SELECT password FROM emc2_accounts WHERE email='$email';";
		$result = mysql_query($query) or die('Query failed: ' . mysql_error());

		$resultrow = mysql_fetch_assoc($result);

		$new_password = sha1($_POST["new_password"]);
		if ($resultrow["password"] === sha1($_POST["old_password"]))
		{
			$query = "UPDATE emc2_accounts " .
				 "SET password='$new_password' WHERE " .
				 "email='$email';";
			$result = mysql_query($query) or die('Query failed: ' . mysql_error());

			$pswd_confirm = "Password successfully changed!";
		}
	}
	else if ($_POST["old_password"])
	{
		$pswd_error = "New Password and Confirm New Password do not match";
	}
?>

<p class="topright"><a href="manage.php">Back to Account</a></p>

<h1>Password Change</h1>

<p> Changing password for <?php echo $_SESSION["email"]; ?> </p>

<?php
	if ($pswd_error)
	{ 
?>
<p class="errorbar"><?php echo $pswd_error; ?></p>
<?php
	}
	if ($pswd_confirm)
	{ 
?>
<p class="confirmbar"><?php echo $pswd_confirm; ?></p>
<?php
	}
?>

<form action="pwchange.php" method="post">

<table>
  <tr>
    <td>
	Old Password 
    </td> <td>
    	<input type="password" name="old_password">
    </td>
  </tr> <tr>
    <td>
	New Password
    </td> <td>
    	 <input type="password" name='new_password'>
    </td>
  </tr> <tr>
    <td>
    	Confirm New Password
    </td> <td>
        <input type="password" name='confirm_password'>
    </td>
  </tr>
</table>

<input type="submit" value="Change Password">

</form>

<?php
	include 'frame_bottom.php';
?>
