<?php
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
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
	$query = "SELECT team_id FROM emc2_teams_2012 WHERE email='$email';";
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
	$tm_query = "SELECT t1.team_id, t2.name, t1.total, r1.rank as rank, t1.Tsum, r2.rankTSum as rankTeam, t1.Gsum as guts, r3.rankGSum as rankGuts, t1.indivsum, r4.rankIndivSum as rankIndiv, " .
		 " t1.T1, t1.T2, t1.T3, " . 
		 "t1.T4, t1.T5, t1.T6, t1.T7, t1.T8, t1.T9, t1.T10, t1.T11," .
		 " t1.T12, t1.T13, t1.T14, t1.T15,  t1.G1, t1.G2, " .
		 "t1.G3, t1.G4, t1.G5, t1.G6, t1.G7, t1.G8, t1.G9, t1.G10, " .
		 "t1.G11, t1.G12, t1.G13, t1.G14, t1.G15, t1.G16, t1.G17, " .
		 "t1.G18, t1.G19, t1.G20, t1.G21, t1.G22, t1.G23, t1.G24 " . 
	
		 " FROM emc2_tscores_2012 AS t1 " .
		 "INNER JOIN (emc2_teams_2012 AS t2, emc2_accounts AS t3) ON " .
		 "(t1.team_id = t2.team_id AND t2.email = t3.email) " .
	" INNER JOIN emc2_tscores_ranktotal_2012 as r1 ON t1.team_id = r1.team_id ".
	" INNER JOIN emc2_tscores_rankTSum_2012 as r2 ON t1.team_id = r2.team_id ".
	" INNER JOIN emc2_tscores_rankGSum_2012 as r3 ON t1.team_id = r3.team_id ".
	" INNER JOIN emc2_tscores_rankIndivSum_2012 as r4 ON t1.team_id = r4.team_id ".
		 "WHERE t1.team_id IN ($wherestring)  " .
		 "ORDER BY t1.team_id;";
	$tm_result = mysql_query($tm_query) 
		or die('Query failed: ' . mysql_error());

	//Individual Scores
	$in_query = "SELECT t1.team_id, t1.indiv_id, t4.studentName AS Name, ".
		    "t2.name As 'Team Name', t1.total, t5.rank, t1.ASum as SSum, t6.RankA as SRank, t1.BSum as ASum, t7.RankB as ARank, t1.A1 as S1, t1.A2 as S2, t1.A3 as S3, t1.A4 as S4, " .
		    "t1.A5 as S5, t1.A6 as S6, t1.A7 as S7, t1.A8 as S8, t1.A9 as S9, t1.A10 as S10, t1.A11 as S11, " .
		    "t1.A12 as S12, t1.A13 as S13, t1.A14 as S14 , t1.A15 as S15, t1.A16 as S16, t1.A17 as S17, t1.A18 as S18, ".
		    "t1.A19 as S19, t1.A20 as S20, t1.Asum as SSum, t1.B1 as A1, t1.B2 as A2, t1.B3 as A3, t1.B4 as A4, " .
		    "t1.B5 as A5, t1.B6 as A6, t1.B7 as A7, t1.B8 as A8, t1.B9 as A9, t1.B10 as A10, t1.Bsum as ASum, " .
		    "t1.total FROM emc2_iscores_2012 AS t1 INNER JOIN " .
		    "(emc2_teams_2012 AS t2, emc2_accounts AS t3, emc2_students_2012 " .
		    "AS t4) ON (t1.team_id = t2.team_id AND t2.email = " .
		    "t3.email AND t1.team_id = t4.team_id AND t1.indiv_id " .
		    "= t4.indiv_id) inner join " .
			" (emc2_iscores_rank_2012 as t5) ON (t5.team_id = t1.team_id AND t5.indiv_id = t1.indiv_id)  inner join " . 
			"  (emc2_iscores_Arank_2012 as t6) ON (t6.team_id = t1.team_id AND t6.indiv_id = t1.indiv_id)  inner join  " .
	"  (emc2_iscores_Brank_2012 as t7) ON (t7.team_id = t1.team_id AND t7.indiv_id = t1.indiv_id)    " .
	        " WHERE t1.team_id IN ($wherestring) " .
		    " ORDER BY t1.team_id, t1.indiv_id;";
	$in_result = mysql_query($in_query)
		or die('Query failed: ' . mysql_error());
	
	$pg_name = "Score Report for $email";
	
	include 'frame_top.php';
?>

<p style="text-align:right"><a href="manage.php">Back to Account Management</a></p>

<h1>Score Report</h1>

<h2>Teams</h2>

<?php
// echo "query = $tm_query" ;
	$tm_result = mysql_query($tm_query)
			or die ("Query failed: " . mysql_error());
    if ( mysql_num_rows($tm_result) > 0) {
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
    }
 
?>

<h2>Individuals</h2>

<?php
// echo "query = $in_query" ;
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

