<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<?php 
   	error_reporting(E_ERROR | E_WARNING | E_PARSE);

	//MySQL connect
	$link = mysql_connect('localhost:3306', 'mathclub_viewer', 'porblems')
		or die('Could not connect: ' . mysql_error());
	mysql_select_db('mathclub_emc2') or die("Could not select database");

	$query = "SELECT t1.name, t2.Gsum, t2.G1, t2.G4, t2.G7, t2.G10, " .
		 "t2.G13, t2.G16, t2.G19, t2.G22, t2.G25, t2.G28 FROM emc2_teams as t1 INNER JOIN " .
		 "emc2_tscores AS t2 ON t1.team_id = t2.team_id order by t1.name;";
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
		var minutes = 75;
		var seconds = 0;
		var timer;

		//current time
<?php
	if ($_GET["m"] !== NULL  && $_GET["s"] !== NULL)
	{
		$minutes = intval($_GET["m"]);
		$seconds = intval($_GET["s"]);

		echo "minutes = $minutes;";
		echo "seconds = $seconds;";

		//Hack to keep the sec's double digits throughout
		if ($seconds < 10)
		{
			$seconds = "0" . $seconds;
		}
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
				timer=setTimeout('tickTimeleft()', 1000);
			}
			else
			{
				document.getElementById('timeleft_sec').innerHTML += " TIME!!!";
			}
		}
	</script>
</head>

<body <?php if ($_GET["b"] != "p") echo('onload="timer=setTimeout(\'tickTimeleft()\', 1000)"'); ?>>
<table class="frame">
  <!--<tr>
    <td colspan=2 class="guts_header">
      <!-- BEGIN TITLE BAR -->
      <p> 
	<span style="font-size:60px">EMC</span>
	<span style="vertical-align:top; font-size:48px">2</span>
	<span style="font-size:60px">&nbsp;-
		Guts Round</span>
      </p>
      <!-- END TITLE BAR -->
    </td>
  </tr>--> <tr>
    <td class="main" colspan=2> 
      <!-- BEGIN MAIN PAGE CONTENT -->
	<h1 class="guts_h1">
		Time Remaining: 
		<span id="timeleft_min"><?php echo $minutes; ?></span> min 
		<span id="timeleft_sec"><?php echo $seconds; ?></span> sec
	</h1>

	<div style="text-align:center;max-width:100%;">
<?php
	while ($resultrow = mysql_fetch_assoc($result))
	{
		$team_name = $resultrow["name"];
		$team_score = $resultrow["Gsum"];
		$team_set1 = $resultrow["G1"] === NULL ? "running" : "submitted";
		$team_set2 = $resultrow["G4"] === NULL ? "running" : "submitted";
		$team_set3 = $resultrow["G7"] === NULL ? "running" : "submitted";
		$team_set4 = $resultrow["G10"] === NULL ? "running" : "submitted";
		$team_set5 = $resultrow["G13"] === NULL ? "running" : "submitted";
		$team_set6 = $resultrow["G16"] === NULL ? "running" : "submitted";
		$team_set7 = $resultrow["G19"] === NULL ? "running" : "submitted";
		$team_set8 = $resultrow["G22"] === NULL ? "running" : "submitted";
		$team_set9 = $resultrow["G25"] === NULL ? "running" : "submitted";
		$team_set10 = $resultrow["G28"] === NULL ? "running" : "submitted";

		if (!$team_score)
		{
			$team_score = 0;
		}

		echo <<<HTML
		<table class="guts_teamtable">
		  <tr>
		    <td colspan=9>
		    	$team_name
		    </td>
		    <td>
		    	$team_score
		    </td>
		  </tr> <tr class="guts_scorebar">
		    <td class="guts_{$team_set1}set">
		    </td> <td class="guts_{$team_set2}set">
		    </td> <td class="guts_{$team_set3}set">
		    </td> <td class="guts_{$team_set4}set">
		    </td> <td class="guts_{$team_set5}set">
		    </td> <td class="guts_{$team_set6}set">
		    </td> <td class="guts_{$team_set7}set">
		    </td> <td class="guts_{$team_set8}set">
		    </td> <td class="guts_{$team_set9}set">
		    </td> <td class="guts_{$team_set10}set">
		    </td>
		  </tr>
		</table>
HTML;
	}
?>
	</div>
    </td>
  </tr> <tr>
    <td class="Footer" id="controlbar" style="visibility:hidden; width:100%">
    	<input type="button" value="Pause" onclick="clearTimeout(timer);">
	<input type="button" value="Resume" onclick="timer=setTimeout('tickTimeleft()',1000);">
	<form action="guts-board.php" method="get" style="display:inline">
		&nbsp;&nbsp;||&nbsp;&nbsp;Set time:
		<input type="text" size=3 name="m"> min 
		<input type="text" size=3 name="s"> sec
		<input type="checkbox" name="b" value="p"> start timer paused
		<input type="submit" value="Go">
	</form>
	&nbsp;&nbsp;||&nbsp;&nbsp;<a href="admin.php">Back to Control Panel</a>
    </td> <td class="Footer" style="width:20px" onmouseover="getElementById('controlbar').style.visibility='visible'" onclick="getElementById('controlbar').style.visibility='hidden'">#
    </td>	
</table>
</body>

</html>

