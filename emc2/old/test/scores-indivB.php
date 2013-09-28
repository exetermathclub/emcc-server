<?php

    /***********************************************************************
     * scores-indivB.php
     *
     * Joy Zheng, Allen Yuan, In Young Cho
     *
     * Computer Science 50
     * Final Project
     *
     * Accuracy round scoring page
     **********************************************************************/
     
	session_start();

	$pg_name	= "Individual B Scoring";

	//If not authenticated, redirect
	if (!$_SESSION["admin"])
	{
		header("Location: admin.php");
		exit;
	}

	include 'frame_top.php';
	
	//MySQL connect
	include 'mysql_init.php';
	
	$link = mysql_connect($sql_addr, $sql_user, $sql_pswd)
		or die('Could not connect: ' . mysql_error());
	mysql_select_db($sql_db) or die("Could not select database");


	$team_id = mysql_real_escape_string($_POST["team_id"]);
	$indiv_id = mysql_real_escape_string($_POST["indiv_id"]);

	//Submitted a "Check Name" request
	if ($_POST["action"] == "Check Name")
	{
		$query = "SELECT name, p$indiv_id FROM emc2_teams " .
			 "WHERE team_id=$team_id;";

		$result = mysql_query($query) 
			or die('Query failed: ' . mysql_error());
		$resultrow = mysql_fetch_row($result);

		if ($resultrow == false)
		{
			$checkname_error = "ID number not recognized";
		}
		else
		{
			$checkname_result = "Team Name: $resultrow[0] <br> " .
				"Student Name: $resultrow[1]";
		}			
	}
	//Submitted a Scoring
	else if ($_POST["action"] == "Submit Scores")
	{
		$form_valid = true;
		for ($i = 1; $i <= 10; $i++)
		{
			if ($_POST["B$i"] == NULL)
			{
				$form_valid = false;
				$prob_error[$i] = "Score is blank.";
			}
		}		
		
		if ($form_valid)
		{
			//Check for existing student record.
			$query = "SELECT * FROM emc2_iscores WHERE " .
				 "team_id=$team_id AND indiv_id=$indiv_id;";
			$result = mysql_query ($query)
					or die ("Query failed: " . mysql_error());

			$oldscores = mysql_fetch_assoc($result);

			
			//If no record of student, make new record.
			if (!$oldscores)
			{
				$query = "INSERT INTO emc2_iscores VALUES " .
					 "($team_id,$indiv_id";
				for ($i = 1; $i <= 20; $i++)
				{
					$query .= ",NULL";
				}
				$Bsum = 0;
				for ($i = 1; $i <= 10; $i++)
				{
					$query .= "," . $_POST["B$i"];
					$Asum += $_POST["B$i"];
				}
				$query .= ",NULL,$Bsum,NULL);";
				$result = mysql_query($query)
						or die ("Query failed: " . mysql_error());

				$submitscores_result = "Score submission for student {$team_id}-{$indiv_id} successful!";
			}
			//If record exists, but this is first round of scoring
			//  or if a conflict has been detected between two grader's
			//  gradings, and this submission is the mediated, correct version.
			else if ($oldscores["Bsum"] == NULL || $_POST["override"])
			{
				$Bsum = 0;
				$query = "UPDATE emc2_iscores SET ";
				for ($i = 1; $i <= 10; $i++)
				{
					$query .= "B$i={$_POST["B$i"]}, ";
					$Bsum += $_POST["B$i"];
				}
				$query .= "Bsum=$Bsum " . 
					  "WHERE team_id=$team_id AND indiv_id=$indiv_id;";
				
				$result = mysql_query($query)
						or die ("Query failed: " . mysql_error());

				$submitscores_result = "Successfully added IndivB scores for student $team_id-$indiv_id";

			}
			//If this is the second round of scoring
			else
			{
				$record_agreement = true;
				for ($i = 0; $i <= 10; $i++)
				{
					if ($oldscores["B$i"] != $_POST["B$i"])
					{
						$record_agreement = false;
						$prob_error[$i] = "Original grader said {$oldscores["B$i"]} points";
					}
				}

				if (!$record_agreement)
				{
					$submitscores_error .= "Scores do not match with first grader.";
				}
				else
				{
					$submitscores_result .= "Scores match up with first grader!";
				}
			}

		}
		else
		{
			$submitscores_error .= "You have blank scores";
		}
	}

?>
<p style="text-align:right"><a href="admin.php">Back to Control Panel</a></p>

<h1>Individual B Scoring</h1>

<form action="scores-indivB.php" method="post">

<h2>Student Identification</h2>

<p>Please check the ID number to make sure they match up with the student and team name.</p>

<p>
	Team ID: <input type="text" size=4 name="team_id" value="<?php echo htmlspecialchars($team_id);?>" >
	Student ID: <input type="text" size=4 name="indiv_id" value="<?php echo htmlspecialchars($indiv_id);?>" >
	<input type="submit" name="action" value="Check Name">
</p>

<?php
	if ($checkname_error)
	{
?>

<p class="errorbar"><?php echo $checkname_error; ?></p>

<?php
	}
	if ($checkname_result)
	{
?>

<p class="confirmbar"><?php echo $checkname_result; ?></p>

<?php
	}
?>

<h2>Scores</h2>

<?php
	if ($submitscores_result)
	{
?>

<p class="confirmbar"><?php echo $submitscores_result; ?></p>

<?php
	}
	if ($submitscores_error)
	{
?>

<p class="errorbar"><?php echo $submitscores_error; ?></p>

<?php
	}
?>

<p>NOTE: The form will NOT submit unless every problem has been marked as 
Correct, or Wrong.</p>

<table>
  <tr>
    <td>
	<table>
	  <tr>
	    <th>
		#
	    </th> <th>
		C
	    </th> <th>
		W
<?php
	for($i = 1; $i <= 10; $i++)
	{
		$_POST["B$i"] == "9" ? $checkbox_C = 'checked' : $checkbox_C = '';
		$_POST["B$i"] == "0" ? $checkbox_W = 'checked' : $checkbox_W = '';
		
		echo <<<HTML
	  </tr> <tr>
	    <td>
		$i
	    </td> <td>
		<input type="radio" name="B$i" value="9" $checkbox_C >
	    </td> <td>
		<input type="radio" name="B$i" value="0" $checkbox_W >
	    </td> <td class="errordesc">
		{$prob_error[$i]}
HTML;
	}
?>
	    </td>
	  </tr>
	</table>
    </td>
  </tr>
</table>
<p>
	<input type="submit" name="action" value="Submit Scores">
	<input type="checkbox" name="override" value="scores">
	This is the corrected score. Override any conflicts(!)
</p>
</form>

<?php
	mysql_close($link);

	include 'frame_bottom.php';
?>
