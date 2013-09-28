<?php

    /***********************************************************************
     * score-report.php
     *
     * Joy Zheng, Allen Yuan, In Young Cho
     *
     * Computer Science 50
     * Final Project
     *
     * Score reports for previous competitions
     **********************************************************************/
     
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
	    include 'mysql_init.php';
	
	    $link = mysql_connect($sql_addr, $sql_user, $sql_pswd)
		    or die('Could not connect: ' . mysql_error());
	    mysql_select_db($sql_db) or die("Could not select database");


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
				for ($i = 1; $i <= 30; $i++)
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

				$tm_query .= ") AND t1.team_id < 200 "; //HACK FOR HIDDENTEAMS TO NOT SHOW UP
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

				$in_query .= ") AND t1.team_id < 200 "; //HACK FOR HIDDENTEAMS TO NOT SHOW UP
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
		//// END INDIVIDUAL QUERY
		//////////////////////////////////////////////////////////////
		
		//////////////////////////////////////////////////////////////
		//// ONLNE TEAM QUERY
		if($_POST["online_tm_teamSelect"])
		{
			$online_tm_query = "SELECT t1.team_id";

			// Add in columns
			if ($_POST["online_tm_Name"])
			{
				$online_tm_query .= ", t2.name";
			}
			if ($_POST["online_tm_School"])
			{
				$online_tm_query .= ", t3.schoolname";
			}
			if ($_POST["online_tm_Students"])
			{
				$online_tm_query .= ", t2.p1, t2.p2, t2.p3, p2,p4";
			}
			if ($_POST["online_tm_Tscores"])
			{
				for ($i = 1; $i <= 15; $i++)
				{
					$online_tm_query .= ", t1.T$i";
				}
			}
			if ($_POST["online_tm_Tsum"])
			{
				$online_tm_query .= ", t1.Tsum";
			}
			if ($_POST["online_tm_Gscores"])
			{
				for ($i = 1; $i <= 30; $i++)
				{
					$online_tm_query .= ", t1.G$i";
				}
			}
			if ($_POST["online_tm_Gsum"])
			{
				$online_tm_query .= ", t1.Gsum";
			}
			if ($_POST["online_tm_Isum"])
			{
				$online_tm_query .= ", t1.Indivsum";
			}
			if ($_POST["online_tm_total"])
			{
				$online_tm_query .= ", t1.total";
			}

			$online_tm_query .= " FROM emc2_online_tscores AS t1 INNER JOIN (emc2_online_teams " .
		  		     "AS t2, emc2_accounts AS t3) " .
				     "ON (t1.team_id = t2.team_id AND t2.email = t3.email)";

			//Parse which teams to include
			if ($_POST["online_tm_teamSelect"] != "*")
			{
				$teams = explode(" ", $_POST["online_tm_teamSelect"]);

				$online_tm_query .= " WHERE t1.team_id IN ('$teams[0]'";

				for ($i = 1; $i < count($teams); $i++)
				{
					$online_tm_query .= ", '$teams[$i]'";
				}
                
                
                ///////////////////////////////////////// Not necessary for online. check
				$online_tm_query .= ") AND t1.team_id < 200 "; //HACK FOR HIDDENTEAMS TO NOT SHOW UP
			}


			//Select the order of listing
			if ($_POST["online_tm_ordering"] == "name")
			{
				$orderby = "t2.name";
			}
			else if ($_POST["online_tm_ordering"] == "school")
			{
				$orderby = "t3.schoolname";
			}
			else if ($_POST["online_tm_ordering"] == "Tsum")
			{
				$orderby = "t1.Tsum";
			}
			else if ($_POST["online_tm_ordering"] == "Gsum")
			{
				$orderby = "t1.Gsum";
			}
			else if ($_POST["online_tm_ordering"] == "Indivsum")
			{
				$orderby = "t1.Indivsum";
			}
			else if ($_POST["online_tm_ordering"] == "subtotal")
			{
				$orderby = "t1.subtotal";
			}
			else if ($_POST["online_tm_ordering"] == "total")
			{
				$orderby = "t1.total";
			}
			else
			{
				$orderby = "t1.team_id";
			}
	

			$online_tm_query .= " ORDER BY $orderby;";
		}
		//// END ONLINE TEAM QUERY
		//////////////////////////////////////////////////////////////


		//////////////////////////////////////////////////////////////
		//// ONLINE INDIVIDUAL QUERY
		if($_POST["online_in_teamSelect"])
		{
			$online_in_query = "SELECT t1.team_id, t1.indiv_id";

			// Add in columns
			if ($_POST["online_in_Name"])
			{
				$online_in_query .= ", t4.studentName AS Name";
			}
			if ($_POST["online_in_Team"])
			{
				$online_in_query .= ", t2.name As 'Team Name'";
			}
			if ($_POST["online_in_School"])
			{
				$online_in_query .= ", t3.schoolname AS 'School Name'";
			}
			if ($_POST["online_in_Ascores"])
			{
				for ($i = 1; $i <= 20; $i++)
				{
					$online_in_query .= ", t1.A$i";
				}
			}
			if ($_POST["online_in_Asum"])
			{
				$online_in_query .= ", t1.Asum";
			}
			if ($_POST["online_in_Bscores"])
			{
				for ($i = 1; $i <= 10; $i++)
				{
					$online_in_query .= ", t1.B$i";
				}
			}
			if ($_POST["online_in_Bsum"])
			{
				$online_in_query .= ", t1.Bsum";
			}
			if ($_POST["online_in_total"])
			{
				$online_in_query .= ", t1.total";
			}

			$online_in_query .= " FROM emc2_online_iscores AS t1 INNER JOIN (emc2_online_teams " .
		  		     "AS t2, emc2_accounts AS t3, emc2_online_students AS t4) " .
				     "ON (t1.team_id = t2.team_id AND " .
				     "t2.email = t3.email AND t1.team_id = t4.team_id" .
				     " AND t1.indiv_id = t4.indiv_id) ";

			//Parse which teams to include
			if ($_POST["online_in_teamSelect"] != "*")
			{
				$teams = explode(" ", $_POST["online_in_teamSelect"]);

				$online_in_query .= "WHERE t1.team_id IN ('$teams[0]'";

				for ($i = 1; $i < count($teams); $i++)
				{
					$online_in_query .= ", '$teams[$i]'";
				}

				$online_in_query .= ") AND t1.team_id < 200 "; //HACK FOR HIDDENTEAMS TO NOT SHOW UP
			}


			//Select the order of listing
			if ($_POST["online_in_ordering"] == "name")
			{
				$orderby = "t4.studentName";
			}
			else if ($_POST["online_in_ordering"] == "team")
			{
				$orderby = "t2.name";
			}
			else if ($_POST["online_in_ordering"] == "school")
			{
				$orderby = "t3.schoolname";
			}
			else if ($_POST["online_in_ordering"] == "Asum")
			{
				$orderby = "t1.Asum";
			}
			else if ($_POST["online_in_ordering"] == "Bsum")
			{
				$orderby = "t1.Bsum";
			}
			else if ($_POST["online_in_ordering"] == "total")
			{
				$orderby = "t1.total";
			}
			else
			{
				$orderby = "t1.team_id";
			}
	

			$online_in_query .= " ORDER BY $orderby;";
		}
		//// END ONLINE INDIVIDUAL QUERY
		//////////////////////////////////////////////////////////////
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

<h2>Online Teams</h2>

<?php
	var_dump($online_tm_query);

	$online_tm_result = mysql_query($online_tm_query)
			or die ("Query failed: " . mysql_error());

	echo "<table>\n";
	$line = mysql_fetch_array($online_tm_result, MYSQL_ASSOC);
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
	}while ($line = mysql_fetch_array($online_tm_result, MYSQL_ASSOC));
	echo "</table>\n";
 
?>

<h2>Online Individuals</h2>

<?php
	var_dump($onine_in_query);

	$online_in_result = mysql_query($online_in_query)
			or die ("Query failed: " . mysql_error());

	echo "<table>\n";
	$line = mysql_fetch_array($online_in_result, MYSQL_ASSOC);
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
	}while ($line = mysql_fetch_array($online_in_result, MYSQL_ASSOC));
	echo "</table>\n";
 
?>


<?php

		include 'frame_bottom.php';
		mysql_close();
	}
?>
