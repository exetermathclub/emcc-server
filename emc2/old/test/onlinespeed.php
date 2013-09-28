<?php

    /***********************************************************************
     * onlinespeed.php
     *
     * Joy Zheng, Allen Yuan, In Young Cho
     *
     * Computer Science 50
     * Final Project
     *
     * Competition page for online speed round
     **********************************************************************/
     

	// load the Email validation checker, among other functions
	include 'functions.php';
	
	// load the constants
	include 'online_constants.php';

	session_start();

	// if not logged in, redirect to the login page
	if($_SESSION["online_student"] == false)
	{
		header("Location: onlinelogin.php");
	}
	
	//if round has not started yet, redirect to the portal page
	if($online_speed_started == false)
	{
	    header("Location: onlinesportal.php");
	}
	
    // connect to database
    include 'mysql_init.php';
    	
    $link = mysql_connect($sql_addr, $sql_user, $sql_pswd)
       or die('Could not connect: ' . mysql_error());
    mysql_select_db($sql_db) or die("Could not select database");
    
    $student_key = $_SESSION["online_student"];
    
    // get team ID and student name
    $query = "SELECT team_id, indiv_id, studentName FROM emc2_online_students WHERE student_key = '$student_key';";
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    $resultarray = mysql_fetch_array($result);
    
    $student_name = $resultarray["studentName"];
    $team_id = $resultarray["team_id"];
    
    // get team name
    $query = "SELECT name, email FROM emc2_online_teams WHERE team_id = '$team_id';";
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    $resultarray = mysql_fetch_array($result);
    $team_name = $resultarray["name"];
    $email = $resultarray["email"];

    $pg_name = "Online Speed Test";	    
	include 'frame_top.php';

?>
    
<script type="text/javascript">

    //which round this is
    var roundCode = "A";
    //tracks where the cursor is
    var cursorPlaceId = null;
    //sets cursorPlaceId to the box that is being focused
    function focusTracker(elementId)
    {
        cursorPlaceId = elementId; 
    }
    //takes away focus
    function loseFocus()
    {
        cursorPlaceId = null;
    }
    
    //main ajax function that handles all requests to online_answerfunctions.php
    function ajax1(probNum, actionCode, changeCode, selected)
    {
        //initialize ajax object and make sure browser supports it
        var xhr = null;
        try 
        {
            xhr = new XMLHttpRequest();
        }
        catch (e)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
        if(xhr == null)
        {
            alert("Ajax not supported by your browser!");
            return;
        }
        xhr.onreadystatechange = function()
        {
            if(xhr.readyState == 4 && xhr.status == 200)
            {
                //replaces the answer space with the responseText
                document.getElementById("answer_space" + probNum).innerHTML = xhr.responseText;
            }
        }
        
        //begin to construct the URLs for ajax requests
        //following cases attach necessary parameters to the URL
        var ajaxUrl = "online_answerfunctions.php?";
        
        //construct URL for clear
        if(actionCode == 0)
        {
            ajaxUrl = ajaxUrl + "type=clear";
            ajaxUrl = ajaxUrl + "&question=" + roundCode + probNum;
        }

        //construct URL for change
        if(actionCode == 1)
        {
            //turn cursorPlaceId from "iAj" to "i"
            var i = 0;
            while(cursorPlaceId.charAt(i) != roundCode)
            {
                i++;
            }
            
            ajaxUrl = ajaxUrl + "type=change";
            ajaxUrl = ajaxUrl + "&question=" + roundCode + probNum;
            ajaxUrl = ajaxUrl + "&changed=" + changeCode;
            ajaxUrl = ajaxUrl + "&number=" + cursorPlaceId.substr(0, i);
            ajaxUrl = ajaxUrl + "&x=" + selected;            
        }        

        //construct URL for submit
        if(actionCode == 2)
        {
            ajaxUrl = ajaxUrl + "type=submit";
            ajaxUrl = ajaxUrl + "&question=" + roundCode + probNum;
            //number of boxes is in a hidden element passed by the onlineanswerfunctions.php
            var numBoxes = document.getElementById(roundCode + probNum + "numBoxes").innerHTML;
            for(var k=0; k< numBoxes; k++)
            {
                ajaxUrl = ajaxUrl + "&" + k + "=" + document.getElementById(k + roundCode + probNum).value;
            }            
        }
        
        //construct URL for initialize
        if(actionCode == 3)
        {
            ajaxUrl = ajaxUrl + "type=change";
            ajaxUrl = ajaxUrl + "&question=" + roundCode + probNum;
            ajaxUrl = ajaxUrl + "&number=0";
            ajaxUrl = ajaxUrl + "&changed=n";
        }
        
        //execute the request
        xhr.open("GET", ajaxUrl, true);
        xhr.send();
    }
    
    
    //following functions are additional handlers which then call ajax1
    function addConst(probNum)
    {
        var select = document.getElementById("constant" + probNum);
        var selectedVal = select.options[select.selectedIndex].value;
        ajax1(probNum, 1, 'c', selectedVal);
    }
    function addInterval(probNum)
    {
        var select = document.getElementById("interval" + probNum);
        var selectedVal = select.options[select.selectedIndex].value;
        ajax1(probNum, 1, 'v', selectedVal);
    }
    function addTuple(probNum)
    {
        var select = document.getElementById("tuple" + probNum);
        var selectedVal = select.value;
        ajax1(probNum, 1, 't', selectedVal);
    }
</script>


<h1>Online Tournament Speed Round</h1>

        <p>Welcome, <b><?php echo $student_name; ?></b> from team <b><?php echo $team_name; ?></b>!</p>
        
        <p> Return to the student portal <a href="onlinesportal.php">here</a>.</p>
        
        <p>If this is not you, or you are done with this page, 
            please <a href="onlinesportal.php?logout=now">log out</a>.</p>

<h2>Problems</h2>

    <a href="Speed.pdf">Download a pdf of the problems here.</a><h2>Corrections</h2>	<p>Number 5 answer submission, please do not put a colon in.</p><h2>Answer Submission</h2>
    
    

<p>Instructions: Use the buttons on the right to toggle the format of each answer.
You should always finish formatting your answer before inputting any numbers.
Once you have inputted an answer, please press submit to save your answer to a problem.  
You may submit your answers as many times as you wish until time is up; 
    only your last set of answers will be recorded.</p>

    <form action="">
<?php         
    //A for speed
    $roundCode = "A";
    $numProbs = $numSpeedProblems;

    //create the table that displays submission forms
    echo "<table>";    
    for($k = 1; $k<= $numProbs; $k++)
    {
        echo "<tr> <td id='prob" . $k . "'><h3>Problem " . $k . "</h3></td></tr>";
        //answer space, changed by ajax1
        echo "<tr> <td id='answer_space" . $k . "' width=700  height=150>";
        echo "Loading...";
        echo "<input id='" . $roundCode . $k . "numBoxes' style='display:none'/></td>";
        //various buttons
        echo "<td><div align='center'><button type ='button' onclick =\"ajax1($k, 0, -1, -1) \" > Clear Problem " . $k . "</button>";
        echo "<button type ='button' onclick =\"ajax1($k, 2, -1, -1) \"> Submit Problem " . $k . "</button>";
        echo "_____________________________________________";
        echo "</br></br><button type ='button' onclick =\"ajax1($k, 1, 'f', -1) \" > Change to Fraction" . "</button>";
        echo "<button type ='button' onclick =\"ajax1($k, 1, 'r', -1) \" > Change to Square Root" . "</button>";
        echo "</br><button type ='button' onclick =\"ajax1($k, 1, 's', -1) \" > Add Sum Element" . "</button>";
        echo "<button type ='button' onclick =\"ajax1($k, 1, 'p', -1) \" > Add Product Element" . "</button>";
        echo "<button type ='button' onclick =\"ajax1($k, 1, 'l', -1) \" > Add List Element" . "</button>";
        echo "</br><select id='constant" . $k . "'><option value='0'> &#960; </option><option value='1'>e</option><option value='2'>i</option></select>";
        echo "<button type ='button' onclick =\"addConst(". $k . ")\">Add Constant</button>";
        echo "<select id='interval" . $k . "'><option value='w'> > </option><option value='x'> &#8805; </option>";
        echo "<option value='y'> < </option><option value='z'> &#8804; </option></select>";
        echo "<button type ='button' onclick =\"addInterval(". $k . ")\">Add Interval</button>";
        echo "<input id='tuple" . $k ."' maxlength='2' size='2'/><button type ='button' onclick =\"addTuple(". $k . ")\">-tuple</button>";
        echo "</div></td> </tr>";
    }    
    echo "</table>";
    echo "<script type='text/javascript'>";
    echo "for(var j=1; j<=" . $numProbs . ";j++)";
    echo "{";
?>
            ajax1(j, 3, 'n', -1);
        }
</script>

<?php
	include 'frame_bottom.php';
?>
