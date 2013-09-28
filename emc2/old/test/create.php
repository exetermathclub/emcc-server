<?php

    /***********************************************************************
     * create.php
     *
     * Joy Zheng, Allen Yuan, In Young Cho
     *
     * Computer Science 50
     * Final Project
     *
     * Problem submission form for the competition
     **********************************************************************/

  error_reporting (E_ALL ^ E_NOTICE); 
	session_start();
  
	$pg_name = "Create a Problem";

	if (!$_SESSION["login"])
	{
		header("Location: index.php");
		exit();
	}

	include 'mysql_init.php';

	// PARSE GET MESSAGE
	if ($_GET['edit'])
	{
		$pg_type = "edit";
		$problem_id = $_GET['edit'];
		$pg_addr = "create.php?edit=$problem_id";


	}
	else
	{
		$pg_type = "create";
		$pg_addr = "create.php";
	}

	$link = mysql_connect($sql_addr, $sql_user, $sql_pswd)
		or die('Could not connect: ' . mysql_error());
	mysql_select_db($sql_db) or die("Could not select database");

	//PARSE INPUTTED DATA
	if ($_POST)
	{
		if (!$_POST['name'])
		{
			$errorbar_text = "ERROR: Problem Short Name cannot be blank";
		}
		else
		{
		
			$name = mysql_real_escape_string($_POST["name"]);
			$problem = mysql_real_escape_string($_POST["problem"]);
			$answer = mysql_real_escape_string($_POST["answer"]);
			$solution = mysql_real_escape_string($_POST["solution"]);
			
			// edit/create problem
			if ($pg_type == "create")
			{
				$query = "INSERT INTO prob_problems VALUES ('','$name','$problem','$answer','$solution');";
			}
			else
			{
				$query = "UPDATE prob_problems SET name='$name', problem='$problem', answer='$answer', solution='$solution' WHERE id=$problem_id";
			}

			$result = mysql_query($query) or die('Query failed: ' . mysql_error());

			// edit/create problem/tag mappings
			if ($pg_type == "create")
			{
				$query = "SELECT id FROM prob_problems WHERE name='$name';";
				$result = mysql_query($query) or die('Query failed: ' . mysql_error());
				$results = mysql_fetch_array($result);
				$problem_id = $results[0];
			}
			else
			{
				$query = "DELETE FROM prob_ptmap WHERE prob_id=$problem_id;";
				$result = mysql_query($query) or die( $query . 'Query failed: ' . mysql_error());
			}
			
			//
			if (!$_POST['tags'])
		    {
				$errorbar_text = "ERROR: Tags cannot be blank";
				die('Note: Your problem has been created without tags. In the future, please add tags to avoid this message.');
			}
			else 
			{
			
				$query = "INSERT INTO prob_ptmap (prob_id, tag_id) VALUES ";
				foreach ($_POST["tags"] as $tag_id)
				{
					$query .= "($problem_id,$tag_id),";
				}
				$query[strlen($query) -1] = ';';
				$result = mysql_query($query) or die('Query failed: ' . mysql_error());
			}
			//job done, exit
			header("Location: problems.php?status={$pg_type}success");
			mysql_close($link);
			exit();
		}
	}

	//GET TAGS
	$query = "SELECT id,name FROM prob_tags ORDER BY parent,name;";
	$tags = mysql_query($query) or die('Query failed: ' . mysql_error());

	//READ PROBLEM DATA
	if ($pg_type == "edit")
	{
		$query = "SELECT * FROM prob_problems WHERE id='$problem_id';";
		$result = mysql_query($query) or die('Query failed: ' . mysql_error());
		
		$results = mysql_fetch_array($result);
		
		$name = stripslashes($results["name"]);
		$problem = stripslashes($results["problem"]);
		$answer = stripslashes($results["answer"]);
		$solution = stripslashes($results["solution"]);

		$query = "SELECT tag_id FROM prob_ptmap WHERE prob_id='$problem_id';";
		$result = mysql_query($query) or die('Query failed: ' . mysql_error());
		$cur_tags = array();
		while ($line = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			$cur_tags[$line['tag_id']] = $line['tag_id'];
		}
	}
	else
	{
		$problem = "Enter the problem statement here.";
		$solution = "Enter the solution(s) here.";
	}

	mysql_close($link);

	include 'frame_top.php';
?>

<?php
	if ($pg_type == "create")
	{
?>
<h1>Create a Problem</h1>
<?php
	}
	else
	{
?>
<h1>Editing Problem #<?php echo $problem_id; ?></h1>
<?php
	}
?>

<?php
	if ($errorbar_text)
	{
?>
<p class="errorbar"><?php echo $errorbar_text; ?></p>
<?php
	}
?>

<p>Note that file attachments do not yet work. You can specify files, but
nothing will be stored.</p>

<form action="<?php echo $pg_addr; ?>" method="post">

<table style="width:100%">
  <tr>
    <td>
    
      <h2>Problem</h2>

      <p>
      	Short Name:
	<input type="text" name="name" size="60" value="<?php echo $name; ?>">
      </p>

      <textarea name="problem" rows="10" cols="70">
<?php echo $problem; ?></textarea>

      <p>
        Attachment:
	<input type="file" name="porb_attach" value="">
      </p>

      <h2>Solution</h2>

      <p>
      	Answer:
	<input type="text" name="answer" size="20" value="<?php echo $answer; ?>">
      </p>

      <textarea name="solution" rows="10" cols="70">
<?php echo $solution; ?></textarea>

      <p>
        Attachment:
	<input type="file" name="sol_attach" value="">
      </p>

    </td> <td width="200px">

      <h2>Tags</h2>

      <p>
        Use Ctrl+Click to select multiple tags
      </p>

      <select name="tags[]" multiple="multiple" size="30">
<?php
	while ($line = mysql_fetch_array($tags, MYSQL_ASSOC))
	{
		if ($cur_tags[$line['id']])
		{
			$selected = ' selected="selected"';
		}
		else
		{
			$selected = '';
		}
		echo "<option value='{$line['id']}' $selected>{$line['name']}</option>";
	}
?>
      </select>
    </td>
  </tr>
</table>

<input type="submit" value="Submit">
</form>

<?php
	include 'frame_bottom.php';
?>
