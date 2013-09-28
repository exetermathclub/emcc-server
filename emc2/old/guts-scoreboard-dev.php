<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<?php 
	//MySQL connect
	$link = mysql_connect('localhost:3306', 'emcuser', 'good2me')
		or die('Could not connect: ' . mysql_error());
	mysql_select_db('emc2db') or die("Could not select database");

	$query = "SELECT t1.name, t2.Gsum, t2.G1, t2.G5, t2.G9, t2.G13, " .
		 "t2.G17, t2.G21 FROM emc2_teams as t1 INNER JOIN " .
		 "emc2_tscores AS t2 ON t1.team_id = t2.team_id order by t2.Gsum desc;";
	$result = mysql_query($query)
			or die('Query failed ' . mysql_error());
?>
<html>

<head>
	<title>EMC2 - Guts Round Scoreboard</title>

	<meta http-equiv="Contest-type" contest="text/html; charset=UTF-8">

	<link rel="stylesheet" type="text/css" href="styles.css">

	<script type="text/javascript">

		//Default values
		var minutes = 60;
		var seconds = 0;

		//current time
<?php
	if ($_GET["m"] !== NULL  && $_GET["s"] !== NULL)
	{
		$minutes = intval($_GET["m"]);
		$seconds = intval($_GET["s"]);

		echo "minutes = $minutes;";
		echo "seconds = $seconds;";
	}
?>

		function tickTimeleft()
		{

			seconds--;

			if (seconds < 0)
			{
				if (minutes > 0)
				{
					minutes--;
					seconds += 60;
				}
				else
				{
					seconds = 0;
				}
			}

			document.getElementById('timeleft_min').innerHTML = minutes;

			if (seconds >= 10)
			{
				document.getElementById('timeleft_sec').innerHTML = seconds;
			}
			else
			{
				document.getElementById('timeleft_sec').innerHTML = "0" + seconds;
			}
			
			if (seconds == 31 || seconds == 1)
			{
				window.location.replace("guts-board.php?m=" + minutes + "&s=" + seconds);
			}
			else if (minutes > 0 || seconds > 0)
			{
				setTimeout('tickTimeleft()', 1000);
			}
			else
			{
				document.getElementById('timeleft_sec').innerHTML += " TIME!!!";
			}
		}
	</script>
</head>

<body onload="setTimeout('tickTimeleft()', 1000)">
<table class="frame">
  <tr>
    <td class="Header">
      <!-- BEGIN TITLE BAR -->
      <p> 
	<span style="font-size:96px">EMC</span>
	<span style="vertical-align:top; font-size:64px">2</span>
	<span style="font-size:96px">&nbsp;-
		Guts Round</span>
      </p>
      <!-- END TITLE BAR -->
    </td>
  </tr> <tr>
    <td class="main"> 
      <!-- BEGIN MAIN PAGE CONTENT -->
	<h1>
		Time Remaining: 
		<span id="timeleft_min"><?php echo $minutes; ?></span> minutes 
		<span id="timeleft_sec"><?php echo $seconds; ?></span> seconds. 
	</h1>

	<div style="text-align:center;max-width:100%;">
<?php
	while ($resultrow = mysql_fetch_assoc($result))
	{
		$team_name = $resultrow["name"];
		$team_score = $resultrow["Gsum"];
		$team_set1 = $resultrow["G1"] === NULL ? "running" : "submitted";
		$team_set2 = $resultrow["G5"] === NULL ? "running" : "submitted";
		$team_set3 = $resultrow["G9"] === NULL ? "running" : "submitted";
		$team_set4 = $resultrow["G13"] === NULL ? "running" : "submitted";
		$team_set5 = $resultrow["G17"] === NULL ? "running" : "submitted";
		$team_set6 = $resultrow["G21"] === NULL ? "running" : "submitted";

		if (!$team_score)
		{
			$team_score = 0;
		}

		echo <<<HTML
		<table class="guts_teamtable" style="font-size:28px">
		  <tr>
		    <td colspan=5>
		    	$team_name
		    </td>
		    <td>
		    	$team_score
		    </td>
		  </tr> <tr>
		    <td class="guts_{$team_set1}set">
		    </td> <td class="guts_{$team_set2}set">
		    </td> <td class="guts_{$team_set3}set">
		    </td> <td class="guts_{$team_set4}set">
		    </td> <td class="guts_{$team_set5}set">
		    </td> <td class="guts_{$team_set6}set">
		    </td>
		  </tr>
		</table>
HTML;
	}
?>
	</div>
    </td>
  </tr>
</table>
</body>

</html>

