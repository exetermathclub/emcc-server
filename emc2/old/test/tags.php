<?php

    /***********************************************************************
     * tags.php
     *
     * Joy Zheng, Allen Yuan, In Young Cho
     *
     * Computer Science 50
     * Final Project
     *
     * Manage tags for problem submissions
     **********************************************************************/
     
	session_start();

	$pg_name	= "Tag Management";

	if (!$_SESSION["login"])
	{
		header("Location: index.php");
		exit();
	}

	include 'mysql_init.php';
	
	$link = mysql_connect($sql_addr, $sql_user, $sql_pswd)
		or die('Could not connect: ' . mysql_error());
	mysql_select_db($sql_db) or die("Could not select database");

	// CREATE/EDIT TAG
	if ($_POST)
	{
		$id = $_POST['id'];
		$name = $_POST['name'];
		$parent = $_POST['parent'];
		$detail = $_POST['detail'];

		if ($name)
		{
			if ($parent == "0")
			{
				$parent = $name;
			}

			// if editing a pre-existing tag
			if ($id)
			{
				$query = "UPDATE prob_tags SET name='$name', parent='$parent', detail='$detail' WHERE id=$id;";
			}
			// if creating a new tag
			else
			{
				$query = "INSERT INTO prob_tags VALUES ('', '$name','$parent','$detail');";
			}

			$result = mysql_query($query) or die ('Query failed:' . mysql_error());
			
			if ($id)
			{
				$confirmbar_text .= "Tag successfully edited!";
			}
			else
			{
				$confirmbar_text .= "Tag successfully created!";
			}
		}
		else
		{
			$errorbar_text = "ERROR: Submitted tag did not have a name.";
		}
	}

	// PARSE GET MESSAGE
	if ($_GET['edit'])
	{
		$problem_id = $_GET['edit'];
		
		$query = "SELECT * from prob_tags WHERE id=$problem_id";
		$result = mysql_query($query) or die ('Query failed:' . mysql_error());
		$edit_tag = mysql_fetch_array($result, MYSQL_ASSOC);
	}

	// GET POSSIBLE PARENTS
	$query = "SELECT name from prob_tags WHERE parent = name;";
	$p_result = mysql_query($query) or die ('Query failed:' . mysql_error());

	// READ TAGS
	$query = "SELECT * FROM prob_tags ORDER BY parent,name;";
	$result = mysql_query($query) or die ('Query failed: ' . mysql_error());

	include 'frame_top.php';
?>

<h1>Tag Manager</h1>

<?php
	if ($confirmbar_text)
	{
?>
<p class="confirmbar"><?php echo $confirmbar_text; ?></p>
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

<h2>Edit/Create Tag</h2>

<form action="tags.php" method="post">

<p>
  Tag ID:
  <input type="text" name="id" size="3" value="<?php echo $edit_tag['id']; ?>">
  (leave blank if creating a new tag)
<br>
  Tag Name:
  <input type="text" name="name" size="55" value="<?php echo $edit_tag['name']; ?>">
  Tag Parent:
  <select name="parent" value="<?php echo $edit_tag['parent']; ?>">
    <option value="0">--None--</option>
<?php
	while ($line = mysql_fetch_array($p_result, MYSQL_ASSOC))
	{
		$name = $line['name'];
		if ($name == $edit_tag['parent'])
		{
			$selected = ' selected="selected"';
		}
		else
		{
			$selected = '';
		}
		echo "   <option$selected>$name</option>";
	}
?>
  </select>
</p>

<textarea name="detail" cols="80" rows="5">
<?php echo $edit_tag['detail']; ?></textarea>

<p>
  <input type="submit" value="Submit">
</p>

<h2>Tag List</h2>

<table width="100%">
  <tr>
    <th>
      ID
    </th> <th>
      Tag Name/Parent/Description
    </th> <th>
      Options 
    </th>
<?php
	while ($line = mysql_fetch_array($result, MYSQL_ASSOC))
	{
		$id = $line['id'];
		$name = htmlspecialchars($line['name']);
		$parent = htmlspecialchars($line['parent']);
		if ($parent == $name)
		{
			$parent = "";
		}
		$detail = nl2br(htmlspecialchars($line['detail']));

		echo <<<HTML
  </tr> <tr>
    <td>
      $id
    </td> <td>
      <p class="list_header">
        $name
	$parent
      </p>
      <p class="list_desc">
        $detail
      </p>
    </td> <td>
      <a href="tags.php?edit=$id">Edit</a>
    </td>
HTML;
	}
?>
  </tr>
</table>
<?php
	include 'frame_bottom.php';
?>
