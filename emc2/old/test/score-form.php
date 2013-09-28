<?php

    /***********************************************************************
     * score-form.php
     *
     * Joy Zheng, Allen Yuan, In Young Cho
     *
     * Computer Science 50
     * Final Project
     *
     * Form to generate score reports
     **********************************************************************/
     
	session_start();

	$pg_name	= "Score Report Form";


	if (!$_SESSION["admin"])
	{
		header("Location: admin.php");
	}
	else
	{
		include 'frame_top.php';
?>
<p style="text-align:right"><a href="admin.php">Back to Control Panel</a></p>
<h1>Score Report Form</h1>

<form action="score-report.php" method="post">

<h2>Team Scores</h2>

<table>
  <tr>
    <td>
    	Team ID's
    </td> <td colspan=3>
    	<input type="text" name="tm_teamSelect">
    	'*' selects all teams
    </td>
  </tr> <tr>
    <td>
    	Field Selection
    </td> <td>
    	<input type="checkbox" name="tm_Name" value="true"> Name of Team
      <br>
    	<input type="checkbox" name="tm_School" value="true"> Name of School
      <br>
    	<input type="checkbox" name="tm_Coach" value="true"> Name of Coach
      <br>
    	<input type="checkbox" name="tm_Students" value="true"> Names of Students
      <br>
    	<input type="checkbox" name="tm_Tscores" value="true"> Team Round Scores
      <br>
    	<input type="checkbox" name="tm_Tsum" value="true"> Team Round Point Total
      <br>
    	<input type="checkbox" name="tm_Gscores" value="true"> Guts Round Scores
      <br>
    	<input type="checkbox" name="tm_Gsum" value="true"> Guts Round Point Total
      <br>
    	<input type="checkbox" name="tm_Isum" value="true"> Individual Round Point Total
      <br>
    	<input type="checkbox" name="tm_total" value="true"> Total earned points
    </td> <td>
    	Order By
    </td> <td>
    	<input type="radio" name="tm_ordering" value="team_id"> Team ID
      <br>
    	<input type="radio" name="tm_ordering" value="name"> Team Name
      <br>
    	<input type="radio" name="tm_ordering" value="school"> School Name
      <br>
    	<input type="radio" name="tm_ordering" value="Tsum"> Team Round Scores
      <br>
    	<input type="radio" name="tm_ordering" value="Gsum"> Guts Round Scores
      <br>
    	<input type="radio" name="tm_ordering" value="subtotal"> Guts Round Scores
      <br>
    	<input type="radio" name="tm_ordering" value="Indivsum"> Individual Round Scores
      <br>
    	<input type="radio" name="tm_ordering" value="total"> Total Team Points.
    </td>
  </tr>        
</table>

<h2>Individual Scores</h2>

<table>
  <tr>
    <td>
    	Team ID's
    </td> <td colspan=3>
    	<input type="text" name="in_teamSelect">
    	'*' selects all teams
    </td>
  </tr> <tr>
    <td>
    	Field Selection
    </td> <td>
    	<input type="checkbox" name="in_Name" value="true"> Name of Student
      <br>
    	<input type="checkbox" name="in_Team" value="true"> Name of Student's Team
      <br>
    	<input type="checkbox" name="in_School" value="true"> Name of Student's School
      <br>
    	<input type="checkbox" name="in_Ascores" value="true"> Speed Round Scores
      <br>
    	<input type="checkbox" name="in_Asum" value="true"> Speed Round Point Total
      <br>
    	<input type="checkbox" name="in_Bscores" value="true"> Accuracy Round Scores
      <br>
    	<input type="checkbox" name="in_Bsum" value="true"> Accuracy Round Point Total
      <br>
    	<input type="checkbox" name="in_total" value="true"> Score Total 
    </td> <td>
    	Order By
    </td> <td>
    	<input type="radio" name="in_ordering" value="id"> Student ID
      <br>
    	<input type="radio" name="in_ordering" value="name"> Students' Name
      <br>
    	<input type="radio" name="in_ordering" value="team"> Students' Team
      <br>
    	<input type="radio" name="in_ordering" value="school"> Students' School
      <br>
    	<input type="radio" name="in_ordering" value="Asum"> Speed Round Score
      <br>
    	<input type="radio" name="in_ordering" value="Bsun"> Accuracy Round Score
      <br>
    	<input type="radio" name="in_ordering" value="total"> Individual Total
    </td>
  </tr>        
</table>

<h2>Online Team Scores</h2>

<table>
  <tr>
    <td>
    	Team ID's
    </td> <td colspan=3>
    	<input type="text" name="online_tm_teamSelect">
    	'*' selects all teams
    </td>
  </tr> <tr>
    <td>
    	Field Selection
    </td> <td>
    	<input type="checkbox" name="online_tm_Name" value="true"> Name of Team
      <br>
    	<input type="checkbox" name="online_tm_School" value="true"> Name of School
      <br>
    	<input type="checkbox" name="online_tm_Coach" value="true"> Name of Coach
      <br>
    	<input type="checkbox" name="online_tm_Students" value="true"> Names of Students
      <br>
    	<input type="checkbox" name="online_tm_Tscores" value="true"> Team Round Scores
      <br>
    	<input type="checkbox" name="online_tm_Tsum" value="true"> Team Round Point Total
      <br>
    	<input type="checkbox" name="online_tm_Gscores" value="true"> Guts Round Scores
      <br>
    	<input type="checkbox" name="online_tm_Gsum" value="true"> Guts Round Point Total
      <br>
    	<input type="checkbox" name="online_tm_Isum" value="true"> Individual Round Point Total
      <br>
    	<input type="checkbox" name="online_tm_total" value="true"> Total earned points
    </td> <td>
    	Order By
    </td> <td>
    	<input type="radio" name="online_tm_ordering" value="team_id"> Team ID
      <br>
    	<input type="radio" name="online_tm_ordering" value="name"> Team Name
      <br>
    	<input type="radio" name="online_tm_ordering" value="school"> School Name
      <br>
    	<input type="radio" name="online_tm_ordering" value="Tsum"> Team Round Scores
      <br>
    	<input type="radio" name="online_tm_ordering" value="Gsum"> Guts Round Scores
      <br>
    	<input type="radio" name="online_tm_ordering" value="subtotal"> Guts Round Scores
      <br>
    	<input type="radio" name="online_tm_ordering" value="Indivsum"> Individual Round Scores
      <br>
    	<input type="radio" name="online_tm_ordering" value="total"> Total Team Points.
    </td>
  </tr>        
</table>

<h2>Online Individual Scores</h2>

<table>
  <tr>
    <td>
    	Team ID's
    </td> <td colspan=3>
    	<input type="text" name="online_in_teamSelect">
    	'*' selects all teams
    </td>
  </tr> <tr>
    <td>
    	Field Selection
    </td> <td>
    	<input type="checkbox" name="online_in_Name" value="true"> Name of Student
      <br>
    	<input type="checkbox" name="online_in_Team" value="true"> Name of Student's Team
      <br>
    	<input type="checkbox" name="online_in_School" value="true"> Name of Student's School
      <br>
    	<input type="checkbox" name="online_in_Ascores" value="true"> Speed Round Scores
      <br>
    	<input type="checkbox" name="online_in_Asum" value="true"> Speed Round Point Total
      <br>
    	<input type="checkbox" name="online_in_Bscores" value="true"> Accuracy Round Scores
      <br>
    	<input type="checkbox" name="online_in_Bsum" value="true"> Accuracy Round Point Total
      <br>
    	<input type="checkbox" name="online_in_total" value="true"> Score Total 
    </td> <td>
    	Order By
    </td> <td>
    	<input type="radio" name="online_in_ordering" value="id"> Student ID
      <br>
    	<input type="radio" name="online_in_ordering" value="name"> Students' Name
      <br>
    	<input type="radio" name="online_in_ordering" value="team"> Students' Team
      <br>
    	<input type="radio" name="online_in_ordering" value="school"> Students' School
      <br>
    	<input type="radio" name="online_in_ordering" value="Asum"> Speed Round Score
      <br>
    	<input type="radio" name="online_in_ordering" value="Bsun"> Accuracy Round Score
      <br>
    	<input type="radio" name="online_in_ordering" value="total"> Individual Total
    </td>
  </tr>        
</table>

<p><input type="submit" value="Run Report!"></p>
</form>
<?php

		include 'frame_bottom.php';
	}
?>
