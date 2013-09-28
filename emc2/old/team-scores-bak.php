<?php
	
	session_start();
	
	if ($_SESSION["email"] == false)
	{
		header("Location: registration.php");
		exit(0);
	}

	//MySQL connect
	$link = mysql_connect('localhost:3306', 'mathclub_viewer', 'porblems')
		or die('Could not connect: ' . mysql_error());
	mysql_select_db('mathclub_emc2') or die("Could not select database");

	//Get teams associated with account
	$email = mysql_real_escape_string($_SESSION["email"]);
	$query = "SELECT team_id FROM emc2_teams_2010 WHERE email='$email';";
	$result = mysql_query($query) 
		or die('Query failed: ' . mysql_error());

	//Set the string of team_ids to search for
	$teams = array();
	while ($resultrow = mysql_fetch_assoc($result))
	{
		array_push($teams, "'" . $resultrow["team_id"] . "'");
	}

	$wherestring = implode(', ', $teams);

	//Team Scores
	$tm_query = "SELECT t1.team_id, t2.name, t1.T1, t1.T2, t1.T3, " . 
		 "t1.T4, t1.T5, t1.T6, t1.T7, t1.T8, t1.T9, t1.T10, t1.T11," .
		 " t1.T12, t1.T13, t1.T14, t1.T15, t1.Tsum, t1.G1, t1.G2, " .
		 "t1.G3, t1.G4, t1.G5, t1.G6, t1.G7, t1.G8, t1.G9, t1.G10, " .
		 "t1.G11, t1.G12, t1.G13, t1.G14, t1.G15, t1.G16, t1.G17, " .
		 "t1.G18, t1.G19, t1.G20, t1.G21, t1.G22, t1.G23, t1.G24, " . 
		 "t1.Gsum, t1.Indivsum, t1.total FROM emc2_tscores_2010 AS t1 " .
		 "INNER JOIN (emc2_teams_2010 AS t2, emc2_accounts AS t3) ON " .
		 "(t1.team_id = t2.team_id AND t2.email = t3.email) " .
		 "WHERE t1.team_id IN ($wherestring) AND t1.team_id < 100 " .
		 "ORDER BY t1.team_id;";
	$tm_result = mysql_query($tm_query) 
		or die('Query failed: ' . mysql_error());

	//Individual Scores
	$in_query = "SELECT t1.team_id, t1.indiv_id, t4.studentName AS Name, ".
		    "t2.name As 'Team Name', t1.A1, t1.A2, t1.A3, t1.A4, " .
		    "t1.A5, t1.A6, t1.A7, t1.A8, t1.A9, t1.A10, t1.A11, " .
		    "t1.A12, t1.A13, t1.A14, t1.A15, t1.A16, t1.A17, t1.A18, ".
		    "t1.A19, t1.A20, t1.Asum, t1.B1, t1.B2, t1.B3, t1.B4, " .
		    "t1.B5, t1.B6, t1.B7, t1.B8, t1.B9, t1.B10, t1.Bsum, " .
		    "t1.total FROM emc2_iscores_2010 AS t1 INNER JOIN " .
		    "(emc2_teams_2010 AS t2, emc2_accounts AS t3, emc2_students_2010 " .
		    "AS t4) ON (t1.team_id = t2.team_id AND t2.email = " .
		    "t3.email AND t1.team_id = t4.team_id AND t1.indiv_id " .
		    "= t4.indiv_id) WHERE t1.team_id IN ($wherestring) AND " .
		    "t1.team_id < 100 ORDER BY t1.team_id, t1.indiv_id;";
	$in_result = mysql_query($in_query)
		or die('Query failed: ' . mysql_error());
	
	$pg_name = "Score Report for $email";
	
	include 'frame_top.php';
?>

<p style="text-align:right"><a href="manage.php">Back to Account Management</a></p>

<h1>Score Report</h1>

<h2>Teams</h2>

<?php

	$tm_result = mysql_query($tm_query)
			or die ("Query failed: " . mysql_error());

	echo "<table>\n";
	$line = mysql_fetch_array($tm_result, MYSQL_ASSOC);
	    echo "\t<tr>\n";
	    foreach (array_keys($line) as $col_value) {
		echo "\t\t<th>$col_value</th>\n";
	    }
	    echo "\t</tr>\n";
	$i=0;
	do  {
	    echo "\t<tr>\n";
	    $i++;
	    foreach ($line as $col_value) {
		echo "\t\t<td>$col_value</td>\n";
	    }
	    echo "\t</tr>\n";
	}while ($line = mysql_fetch_array($tm_result, MYSQL_ASSOC));
	echo "</table>\n";
 
?>

<h2>Individuals</h2>

<?php

	$in_result = mysql_query($in_query)
			or die ("Query failed: " . mysql_error());

	echo "<table>\n";
	$line = mysql_fetch_array($in_result, MYSQL_ASSOC);
	    echo "\t<tr>\n";
	    foreach (array_keys($line) as $col_value) {
		echo "\t\t<th>$col_value</th>\n";
	    }
	    echo "\t</tr>\n";
	$i=0;
	do  {
	    echo "\t<tr>\n";
	    $i++;
	    foreach ($line as $col_value) {
		echo "\t\t<td>$col_value</td>\n";
	    }
	    echo "\t</tr>\n";
	}while ($line = mysql_fetch_array($in_result, MYSQL_ASSOC));
	echo "</table>\n";
 
?>


<?php

		include 'frame_bottom.php';
		mysql_close();
?>

