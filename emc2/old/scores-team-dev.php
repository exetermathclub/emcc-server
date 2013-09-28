<?php
	session_start();

	$pg_name	= "Team Scoring";

	//If not authenticated, redirect
	if (!$_SESSION["admin"])
	{
		header("Location: admin.php");
		exit;
	}

	include 'frame_top.php';
	
	//MySQL connect
	$link = mysql_connect('localhost:3306', 'mathclub_viewer', 'porblems')
		or die('Could not connect: ' . mysql_error());
	mysql_select_db('mathclub_emc2') or die("Could not select database");


	$team_id = mysql_real_escape_string($_POST["team_id"]);

	//Submitted a "Check Name" request
	if ($_POST["action"] == "Check Name")
	{
		$query = "SELECT name FROM emc2_teams " .
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
			$checkname_result = "Team Name: $resultrow[0]";
		}			
	}
	//Submitted a Scoring
	else if ($_POST["action"] == "Submit Scores")
	{
		$form_valid = true;
		for ($i = 1; $i <= 10; $i++)
		{
			if ($_POST["T$i"] == NULL)
			{
				$form_valid = false;
				$prob_error[$i] = "Score is blank.";
			}
		}		
		
		if ($form_valid)
		{
			//Check for existing team record.
			$query = "SELECT * FROM emc2_tscores WHERE " .
				 "team_id=$team_id;";
			$result = mysql_query ($query)
					or die ("Query failed: " . mysql_error());

			$oldscores = mysql_fetch_assoc($result);

			
			//If no record of team, make new record.
			if (!$oldscores)
			{
				$query = "INSERT INTO emc2_tscores VALUES " .
					 "($team_id";
				$Tsum = 0;
				for ($i = 1; $i <= 10; $i++)
				{
					$query .= "," . $_POST["T$i"];
					$Tsum += $_POST["T$i"];
				}
				for ($i = 1; $i <= 24; $i++)
				{
					$query .= ",NULL";
				}
				$query .= ",$Tsum,NULL,NULL,NULL,NULL);";
				$result = mysql_query($query)
						or die ("Query failed: " . mysql_error());

				$submitscores_result = "Score submission for team {$team_id} successful!";
			}
			//If record exists, but this is first round of scoring
			//  or if a conflict has been detected between two grader's
			//  gradings, and this submission is the mediated, correct version.
			else if ($oldscores["Tsum"] == NULL || $_POST["override"])
			{
				$Asum = 0;
				$query = "UPDATE emc2_tscores SET ";
				for ($i = 1; $i <= 10; $i++)
				{
					$query .= "T$i={$_POST["T$i"]}, ";
					$Tsum += $_POST["T$i"];
				}
				$query .= "Tsum=$Tsum " . 
					  "WHERE team_id=$team_id;";

				$result = mysql_query($query)
						or die ("Query failed: " . mysql_error());

				$submitscores_result = "Successfully added Team scores for team $team_id";

			}
			//If this is the second round of scoring
			else
			{
				$record_agreement = true;
				for ($i = 1; $i <= 10; $i++)
				{
					if ($oldscores["T$i"] != $_POST["T$i"])
					{
						$record_agreement = false;
						$prob_error[$i] = "Original grader said {$oldscores["T$i"]} points";
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

<h1>Team Scoring</h1>

<form action="scores-team.php" method="post">

<h2>Student Identification</h2>

<p>Please check the ID number to make sure they match up with the team name.</p>

<p>
	Team ID: <input type="text" size=4 name="team_id" value="<?php echo htmlspecialchars($team_id);?>" >
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
	for($i = 1; $i <= 8; $i++)
	{
		$_POST["T$i"] == "20" ? $checkbox_C = 'checked' : $checkbox_C = '';
		$_POST["T$i"] == "0" ? $checkbox_W = 'checked' : $checkbox_W = '';
		
		echo <<<HTML
	  </tr> <tr>
	    <td>
		$i
	    </td> <td>
		<input type="radio" name="T$i" value="20" $checkbox_C >
	    </td> <td>
		<input type="radio" name="T$i" value="0" $checkbox_W >
	    </td> <td class="errordesc">
		{$prob_error[$i]}
HTML;
	}
?>
	    </td>
	  </tr>
	</table>
    </td> <td>
	<table>
	  <tr>
	    <th>
		#
	    </th> <th>
		C
	    </th> <th>
		W
<?php
	for($i = 9; $i <= 10; $i++)
	{
		$_POST["T$i"] == "20" ? $checkbox_C = 'checked' : $checkbox_C = '';
		$_POST["T$i"] == "0" ? $checkbox_W = 'checked' : $checkbox_W = '';
		
		echo <<<HTML
	  </tr> <tr>
	    <td>
		$i
	    </td> <td>
		<input type="radio" name="T$i" value="20" $checkbox_C >
	    </td> <td>
		<input type="radio" name="T$i" value="0" $checkbox_W >
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
