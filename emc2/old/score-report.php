<?php
	session_start();

	$pg_name	= "Score Report Results";


	if (!$_SESSION["admin"])
	{
		header("Location: admin.php");
	}
	else
	{
		include 'frame_top.php';


		//MySQL connect
		$link = mysql_connect('localhost:3306', 'mathclub_viewer', 'porblems')
			or die('Could not connect: ' . mysql_error());
		mysql_select_db('mathclub_emc2') or die("Could not select database");


		//////////////////////////////////////////////////////////////
		//// TEAM QUERY
		if($_POST["tm_teamSelect"])
		{
			$tm_query = "SELECT t1.team_id";

			// Add in columns
			if ($_POST["tm_Name"])
			{
				$tm_query .= ", t2.name";
			}
			if ($_POST["tm_School"])
			{
				$tm_query .= ", t3.schoolname";
			}
			if ($_POST["tm_Students"])
			{
				$tm_query .= ", t2.p1, t2.p2, t2.p3, p2,p4";
			}
			if ($_POST["tm_Tscores"])
			{
				for ($i = 1; $i <= 15; $i++)
				{
					$tm_query .= ", t1.T$i";
				}
			}
			if ($_POST["tm_Tsum"])
			{
				$tm_query .= ", t1.Tsum";
			}
			if ($_POST["tm_Gscores"])
			{
				for ($i = 1; $i <= 24; $i++)
				{
					$tm_query .= ", t1.G$i";
				}
			}
			if ($_POST["tm_Gsum"])
			{
				$tm_query .= ", t1.Gsum";
			}
			if ($_POST["tm_Isum"])
			{
				$tm_query .= ", t1.Indivsum";
			}
			if ($_POST["tm_total"])
			{
				$tm_query .= ", t1.total";
			}

			$tm_query .= " FROM emc2_tscores AS t1 INNER JOIN (emc2_teams " .
		  		     "AS t2, emc2_accounts AS t3) " .
				     "ON (t1.team_id = t2.team_id AND t2.email = t3.email)";

			//Parse which teams to include
			if ($_POST["tm_teamSelect"] != "*")
			{
				$teams = explode(" ", $_POST["tm_teamSelect"]);

				$tm_query .= " WHERE t1.team_id IN ('$teams[0]'";

				for ($i = 1; $i < count($teams); $i++)
				{
					$tm_query .= ", '$teams[$i]'";
				}

				$tm_query .= ") AND t1.team_id < 1400 "; //HACK FOR HIDDENTEAMS TO NOT SHOW UP
			}


			//Select the order of listing
			if ($_POST["tm_ordering"] == "name")
			{
				$orderby = "t2.name";
			}
			else if ($_POST["tm_ordering"] == "school")
			{
				$orderby = "t3.schoolname";
			}
			else if ($_POST["tm_ordering"] == "Tsum")
			{
				$orderby = "t1.Tsum";
			}
			else if ($_POST["tm_ordering"] == "Gsum")
			{
				$orderby = "t1.Gsum";
			}
			else if ($_POST["tm_ordering"] == "Indivsum")
			{
				$orderby = "t1.Indivsum";
			}
			else if ($_POST["tm_ordering"] == "subtotal")
			{
				$orderby = "t1.subtotal";
			}
			else if ($_POST["tm_ordering"] == "total")
			{
				$orderby = "t1.total";
			}
			else
			{
				$orderby = "t1.team_id";
			}
	

			$tm_query .= " ORDER BY $orderby;";
		}
		//// END TEAM QUERY
		//////////////////////////////////////////////////////////////


		//////////////////////////////////////////////////////////////
		//// INDIVIDUAL QUERY
		if($_POST["in_teamSelect"])
		{
			$in_query = "SELECT t1.team_id, t1.indiv_id";

			// Add in columns
			if ($_POST["in_Name"])
			{
				$in_query .= ", t4.studentName AS Name";
			}
			if ($_POST["in_Team"])
			{
				$in_query .= ", t2.name As 'Team Name'";
			}
			if ($_POST["in_School"])
			{
				$in_query .= ", t3.schoolname AS 'School Name'";
			}
			if ($_POST["in_Ascores"])
			{
				for ($i = 1; $i <= 20; $i++)
				{
					$in_query .= ", t1.A$i";
				}
			}
			if ($_POST["in_Asum"])
			{
				$in_query .= ", t1.Asum";
			}
			if ($_POST["in_Bscores"])
			{
				for ($i = 1; $i <= 10; $i++)
				{
					$in_query .= ", t1.B$i";
				}
			}
			if ($_POST["in_Bsum"])
			{
				$in_query .= ", t1.Bsum";
			}
			if ($_POST["in_total"])
			{
				$in_query .= ", t1.total";
			}

			$in_query .= " FROM emc2_iscores AS t1 INNER JOIN (emc2_teams " .
		  		     "AS t2, emc2_accounts AS t3, emc2_students AS t4) " .
				     "ON (t1.team_id = t2.team_id AND " .
				     "t2.email = t3.email AND t1.team_id = t4.team_id" .
				     " AND t1.indiv_id = t4.indiv_id) ";

			//Parse which teams to include
			if ($_POST["in_teamSelect"] != "*")
			{
				$teams = explode(" ", $_POST["in_teamSelect"]);

				$in_query .= "WHERE t1.team_id IN ('$teams[0]'";

				for ($i = 1; $i < count($teams); $i++)
				{
					$in_query .= ", '$teams[$i]'";
				}

				$in_query .= ") AND t1.team_id < 1400 "; //HACK FOR HIDDENTEAMS TO NOT SHOW UP
			}


			//Select the order of listing
			if ($_POST["in_ordering"] == "name")
			{
				$orderby = "t4.studentName";
			}
			else if ($_POST["in_ordering"] == "team")
			{
				$orderby = "t2.name";
			}
			else if ($_POST["in_ordering"] == "school")
			{
				$orderby = "t3.schoolname";
			}
			else if ($_POST["in_ordering"] == "Asum")
			{
				$orderby = "t1.Asum";
			}
			else if ($_POST["in_ordering"] == "Bsum")
			{
				$orderby = "t1.Bsum";
			}
			else if ($_POST["in_ordering"] == "total")
			{
				$orderby = "t1.total";
			}
			else
			{
				$orderby = "t1.team_id";
			}
	

			$in_query .= " ORDER BY $orderby;";
		}

?>
<p style="text-align:right"><a href="admin.php">Back to Control Panel</a></p>
<h1>Score Report Results</h1>

<h2>Teams</h2>

<?php
	var_dump($tm_query);

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
	var_dump($in_query);

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

<h2>Top  Individual in a team</h2>
<?php
	$tin_query = "select t1.team_id, t1.indiv_id, t3.SchoolName, t2.Name as TeamName, N.StudentName, ".
	" t1.A1, t1.A2, t1.A3, t1.A4, t1.A5, t1.A6, t1.A7, t1.A8, t1.A9, t1.A10, t1.A11, t1.A12, t1.A13, t1.A14, t1.A15, t1.A16, t1.A17, t1.A18, t1.A19, t1.A20, t1.Asum, t1.B1, t1.B2, t1.B3, t1.B4, t1.B5, t1.B6, t1.B7, t1.B8, t1.B9, t1.B10, t1.Bsum, t1.total ".
		"from emc2_iscores t1 ".	
		"join (select team_id,  MAX(total) as Max ". 
 		"from emc2_iscores group by team_id) as M  ".
 		" on t1.team_id = M.team_id and t1.total = M.Max ".
		"JOIN emc2_students  N ".
		" on N.team_id = t1.team_id and N.indiv_id = t1.indiv_id " .
		" JOIN emc2_teams AS t2 ON t2.team_id = t1.team_id ".
		" JOIN emc2_accounts AS t3 ON t2.email = t3.email ".	  
		" where t1.team_id < 1400"
	; 
	var_dump($tin_query);

	$tin_result = mysql_query($tin_query)
			or die ("Query failed: " . mysql_error());

	echo "<table>\n";
	$line = mysql_fetch_array($tin_result, MYSQL_ASSOC);
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
	}while ($line = mysql_fetch_array($tin_result, MYSQL_ASSOC));
	echo "</table>\n";
 
?>

<h2>Top  25 Individuals (list more for tie-breakers) </h2>
<?php
	$ttin_query = " select t1.team_id, t1.indiv_id, S.StudentName, t3.SchoolName, t2.Name as TeamName, ".
	" t1.A1, t1.A2, t1.A3, t1.A4, t1.A5, t1.A6, t1.A7, t1.A8, t1.A9, t1.A10, t1.A11, t1.A12, t1.A13, t1.A14, t1.A15, t1.A16, t1.A17, t1.A18, t1.A19, t1.A20, t1.Asum, t1.B1, t1.B2, t1.B3, t1.B4, t1.B5, t1.B6, t1.B7, t1.B8, t1.B9, t1.B10, t1.Bsum, t1.total ".
	" From (SELECT team_id, indiv_id,     ".
	" A1, A2, A3, A4, A5, A6, A7, A8, A9, A10, A11, A12, A13, A14, A15, A16, A17, A18, A19, A20, Asum, B1, B2, B3, B4, B5, B6, B7, B8, B9, B10, Bsum, total ".
	" FROM emc2_iscores where team_id < 1400 ORDER BY total DESC 
            ".
	" LIMIT 0 , 25) as t1 ".
    "Join emc2_students S ".
	" on t1.team_id = S.team_id and t1.indiv_id = S.indiv_id ".
	" JOIN emc2_teams AS t2 ON t2.team_id = t1.team_id ".
	" JOIN emc2_accounts AS t3 ON t2.email = t3.email "
	;
		 
	var_dump($ttin_query);

	$ttin_result = mysql_query($ttin_query)
			or die ("Query failed: " . mysql_error());

	echo "<table>\n";
	$line = mysql_fetch_array($ttin_result, MYSQL_ASSOC);
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
	}while ($line = mysql_fetch_array($ttin_result, MYSQL_ASSOC));
	echo "</table>\n";
 
?>



<?php

		include 'frame_bottom.php';
		mysql_close();
	}
?>
