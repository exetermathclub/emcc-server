<?php
	session_start();

	$pg_name	= "Roster";

	include 'frame_top.php';

	$db_host = 'localhost:3306';
	$db_user = '';
	$db_pswd = '';
	$db_base = '';
	
	//MySQL connect
	$link = mysql_connect($db_host, $db_user, $db_pswd)
		or die('Could not connect: ' . mysql_error());
	mysql_select_db($db_base) or die("Could not select database");
?>

<h1>Roster</h1>

<h2>Accounts</h2>

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
			 "FROM emc2_accounts;";
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

	include 'frame_bottom.php';
?>
