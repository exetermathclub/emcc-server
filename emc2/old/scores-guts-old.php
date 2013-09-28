<?php
	session_start();

	$pg_name	= "Guts Scoring";

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
		if (!$_POST["setnum"])
		{
			$form_valid = false;
			$setnum_error = "Please specify set";
		}
		for ($i = 1; $i <= 4; $i++)
		{
			if ($_POST["g$i"] == NULL)
			{
				$form_valid = false;
				$prob_error[$i] = "Score is blank.";
			}
		}		
		

		if ($form_valid)
		{
			$startnum = ($_POST["setnum"] - 1) * 4;
			$firstnum = $startnum + 1;

			//Check for existing student record.
			$query = "SELECT * FROM emc2_tscores WHERE " .
				 "team_id=$team_id;";
			$result = mysql_query ($query)
					or die ("Query failed: " . mysql_error());

			$oldscores = mysql_fetch_assoc($result);

			//If no record of student, make new record.
			if (!$oldscores)
			{
				$query = "INSERT INTO emc2_tscores VALUES " .
					 "($team_id";
				$Gsum = 0;
				for ($i = 1; $i <= 15+$startnum; $i++)
				{
					$query .= ",NULL";
				}
				for ($i = 1; $i <= 4; $i++)
				{
					$query .= "," . $_POST["g$i"];
					$Gsum += $_POST["g$i"];
				}
				for ($i = $startnum+5; $i <= 24; $i++)
				{
					$query .= ",NULL";
				}
				$query .= ",NULL,$Gsum,NULL,NULL,NULL);";
				$result = mysql_query($query)
						or die ("Query failed: " . mysql_error());

				$submitscores_result = "Score submission for team {$team_id} successful!";
			}
			//If record exists, but this is first round of scoring
			//  or if a conflict has been detected between two grader's
			//  gradings, and this submission is the mediated, correct version.
			else if ($oldscores["G{$firstnum}"] == NULL || $_POST["override"])
			{
				$Gsum = 0;
				$query = "UPDATE emc2_tscores SET ";
				for ($i = 1; $i <= 4; $i++)
				{
					$j = $i + $startnum;
					$query .= "G$j={$_POST["g$i"]}, ";
					$Gsum += $_POST["g$i"];
				}
				for ($i = 1; $i <= $startnum; $i++)
				{
					$Gsum += $oldscores["G$i"];
				}
				for ($i = $startnum + 5; $i <= 24; $i++)
				{
					$Gsum += $oldscores["G$i"];
				}
				$query .= "Gsum=$Gsum " . 
					  "WHERE team_id=$team_id;";

				$result = mysql_query($query)
						or die ("Query failed: " . mysql_error());

				$submitscores_result = "Successfully added Guts scores for set after problem $startnum for team $team_id";

			}
			//If this is the second round of scoring
			else
			{
				$record_agreement = true;
				for ($i = 1; $i <= 4; $i++)
				{
					$j = $i + $startnum;
					if ($oldscores["G$j"] != $_POST["g$i"])
					{
						$record_agreement = false;
						$prob_error[$i] = "Original grader said {$oldscores["G$j"]} points";
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

<h1>Guts Scoring</h1>

<form action="scores-guts.php" method="post">

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

<p>NOTE: The form will NOT submit unless every field is filled. Also, the set number field resets itself every submit</p>

<table>
  <tr>
    <td>
    	<table>
	  <tr>
	    <th>
	    	Set Number:
	    </th> <td>
	    	<input type="radio" name="setnum" value="1"> 1
	    </td> <td>
	    	<input type="radio" name="setnum" value="2"> 2
	    </td> <td>
	    	<input type="radio" name="setnum" value="3"> 3
	    </td> <td>
	    	<input type="radio" name="setnum" value="4"> 4
	    </td> <td>
	    	<input type="radio" name="setnum" value="5"> 5
	    </td> <td>
	    	<input type="radio" name="setnum" value="6"> 6
	    </td> <td class="errordesc">
	    	<?php echo $setnum_error; ?>
	    </td> 
	  </tr>
	</table>
    </td> 
  </tr> <tr>
    <td>
	<table>
	  <tr>
	    <th>
		#
	    </th> <th>
		Score
<?php
	for($i = 1; $i <= 4; $i++)
	{
		$score = $_POST["g$i"];
		
		echo <<<HTML
	  </tr> <tr>
	    <td>
		$i
	    </td> <td>
		<input type="text" name="g$i" value="$score" size=4>
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
