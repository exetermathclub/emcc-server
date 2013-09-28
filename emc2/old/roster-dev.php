<?php
	session_start();

	$pg_name	= "Roster";
	$rowcount = 0;

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
?>

<p style="text-align:right"><a href="admin.php">Back to Control Panel</a></p>

<h1>Roster</h1>


<h2>Teams registered or paid their fees (Official Teams)</h2>
<table>
  <tr>
    <th>
    	Team ID
    </th> <th>
    	Name
    </th> <th>
    	Email
    </th> <th>
    	Paid
    </th><th>
    	Memo
    </th> <th>
    	p1
    </th> <th>
    	p2
    </th> <th>
    	p3
    </th> <th>
    	p4
    </th>
<?php
		$query = "SELECT team_id, name, email, fee_paid, memo, p1, p2, p3, p4 " . 
			 "FROM emc2_teams where email <> 'a@a.com' and (( P1<>'' and p2<>'' and p3<>'' and p4 <>'') or (team_id in (1301, 1282)))  order by team_id asc, fee_paid desc,  email asc;";
		$result = mysql_query($query) 
			or die('Query failed: ' . mysql_error());
		
		while( $row = mysql_fetch_assoc($result) )
		{
			echo "  </tr> <tr>\n";
			echo "    <td>\n";
			echo "\t" . htmlspecialchars($row["team_id"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["name"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["email"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["fee_paid"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["memo"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["p1"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["p2"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["p3"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["p4"]) . "\n";
			echo "    </td>\n";
			$rowcount++;
		}
		echo	"<font color='red' <b> number of full teams = " . ($rowcount) . " </b></font>\n\n";
		mysql_free_result($result);
?>
  </tr>
</table>

<h2>Teams registered or paid their fees (Not full teams)</h2>
<table>
  <tr>
    <th>
    	Team ID
    </th> <th>
    	Name
    </th> <th>
    	Email
    </th> <th>
    	Paid
    </th><th>
    	Memo
    </th> <th>
    	p1
    </th> <th>
    	p2
    </th> <th>
    	p3
    </th> <th>
    	p4
    </th>
<?php
		$query = "SELECT team_id, name, email, fee_paid, memo, p1, p2, p3, p4 " . 
			 "FROM emc2_teams where email <> 'a@a.com' and ( fee_paid=1 or P1<>'' or p2<>'' or p3<>'' or p4 <>'' or memo <> '') and team_id not in  ( select team_id from emc2_teams where p1<>'' and p2<> '' and p3 <> '' and p4 <> '')   order by team_id asc, fee_paid desc,  email asc;";
		$result = mysql_query($query) 
			or die('Query failed: ' . mysql_error());
		while( $row = mysql_fetch_assoc($result) )
		{
			echo "  </tr> <tr>\n";
			echo "    <td>\n";
			echo "\t" . htmlspecialchars($row["team_id"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["name"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["email"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["fee_paid"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["memo"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["p1"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["p2"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["p3"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["p4"]) . "\n";
			echo "    </td>\n";
		}
		mysql_free_result($result);
?>
  </tr>
</table>

<h2> Teams associated with an email account that has not re-registered any team this year)</h2>
<table>
  <tr>
    <th>
    	Team ID
    </th> <th>
    	Name
    </th> <th>
    	Email
    </th> <th>
    	Paid
    </th><th>
    	Memo
    </th> <th>
    	p1
    </th> <th>
    	p2
    </th> <th>
    	p3
    </th> <th>
    	p4
    </th>
<?php
		$query = "SELECT team_id, name, email, fee_paid, memo, p1, p2, p3, p4 " . 
			 "FROM emc2_teams where email not in (select email from emc2_teams where fee_paid=1 or P1<>'' or p2<>'' or p3<>'' or p4 <>'' or memo <> '' ) order by email, team_id;";
		$result = mysql_query($query) 
			or die('Query failed: ' . mysql_error());
		
		while( $row = mysql_fetch_assoc($result) )
		{
			echo "  </tr> <tr>\n";
			echo "    <td>\n";
			echo "\t" . htmlspecialchars($row["team_id"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["name"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["email"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["fee_paid"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["memo"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["p1"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["p2"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["p3"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["p4"]) . "\n";
			echo "    </td>\n";
		}
		mysql_free_result($result);
?>
  </tr>
</table>

<h2>Accounts with teams registered in 2013</h2>

<table>
  <tr>
    <th>
    	Name
    </th> <th>
    	Email
    </th> <th>
    	Schoolname
    </th> <th>
    	Address
    </th> <th>
    	Category
    </th>
  
<?php
		$query = "SELECT fullname, email, schoolname, address, category " .
			 "FROM emc2_accounts where email in (select email from emc2_teams where team_id < 1302 or team_id in (1476, 1477, 1513, 1533, 1536, 1540, 11152 ) ) and category <> 'test' order by email, SchoolName;";
		$result = mysql_query($query) 
			or die('Query failed: ' . mysql_error());
		while( $row = mysql_fetch_assoc($result) )
		{
			echo "  </tr> <tr>\n";
			echo "    <td>\n";
			echo "\t" . htmlspecialchars($row["fullname"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["email"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["schoolname"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . nl2br(htmlspecialchars($row["address"])) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["category"]) . "\n";			
			echo "    </td>\n";
		}
		mysql_free_result($result);
?>
  </tr>
</table>


<h2>All Registered Accounts</h2>

<table>
  <tr>
    <th>
    	Name
    </th> <th>
    	Email
    </th> <th>
    	Schoolname
    </th> <th>
    	Address
    </th> <th>
    	Category
    </th>
  
<?php
		$query = "SELECT fullname, email, schoolname, address, category " .
			 "FROM emc2_accounts where category <> 'test';";
		$result = mysql_query($query) 
			or die('Query failed: ' . mysql_error());
		while( $row = mysql_fetch_assoc($result) )
		{
			echo "  </tr> <tr>\n";
			echo "    <td>\n";
			echo "\t" . htmlspecialchars($row["fullname"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["email"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["schoolname"]) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . nl2br(htmlspecialchars($row["address"])) . "\n";
			echo "    </td> <td>\n";
			echo "\t" . htmlspecialchars($row["category"]) . "\n";			
			echo "    </td>\n";
		}
		mysql_free_result($result);
?>
  </tr>
</table>





<?php
	mysql_close($link);

	}

	include 'frame_bottom.php';
?>
