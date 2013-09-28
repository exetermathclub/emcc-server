<?php

    /***********************************************************************
     * onlineguts.php
     *
     * Joy Zheng, Allen Yuan, In Young Cho
     *
     * Computer Science 50
     * Final Project
     *
     * Competition page for online guts round
     **********************************************************************/
     

	// load the Email validation checker, among other functions
	include 'functions.php';
	
	// load the constants
	include 'online_constants.php';

	session_start();
    
	// if not logged in, redirect to the login page
	if($_SESSION["online_team"] == false)
	{
		header("Location: onlinelogin.php");
	}
	
	//if round has not started yet, redirect to the portal page
	if($online_guts_started == false)
	{
	    header("Location: onlinetportal.php");
	}
	
    if($_GET["nextRound"])
    {
        header("Location: onlinegutsSubmit.php");
    }

    // connect to database
    include 'mysql_init.php';
    	
    $link = mysql_connect($sql_addr, $sql_user, $sql_pswd)
       or die('Could not connect: ' . mysql_error());
    mysql_select_db($sql_db) or die("Could not select database");
    
    $team_id = $_SESSION["online_team"];
    
    // get team name
    $query = "SELECT name, email FROM emc2_online_teams WHERE team_id = '$team_id';";
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    $resultarray = mysql_fetch_array($result);
    $team_name = $resultarray["name"];
    $email = $resultarray["email"];

    $query = "SELECT currentSet FROM emc2_online_tanswers WHERE team_id=" . $team_id . ";";
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    $resultarray = mysql_fetch_array($result);
    $currentSet = $resultarray["currentSet"];
    if($currentSet > 8)
    {
        header("Location: onlinetportal.php?gutsdone=1");
    }

    $pg_name = "Online Guts Round";	    
	include 'frame_top.php';

?>
    
<script type="text/javascript">

    //which round this is
    var roundCode = "G";
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
    function ajax1(probNum, actionCode, changeCode, selected, sync)
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
            //turn cursorPlaceId from "iGj" to "i"
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
        xhr.open("GET", ajaxUrl, sync);
        xhr.send();
    }
    
    
    //following functions are additional handlers which then call ajax1
    function addConst(probNum)
    {
        var select = document.getElementById("constant" + probNum);
        var selectedVal = select.options[select.selectedIndex].value;
        ajax1(probNum, 1, 'c', selectedVal, true);
    }
    function addInterval(probNum)
    {
        var select = document.getElementById("interval" + probNum);
        var selectedVal = select.options[select.selectedIndex].value;
        ajax1(probNum, 1, 'v', selectedVal, true);
    }
    function addTuple(probNum)
    {
        var select = document.getElementById("tuple" + probNum);
        var selectedVal = select.value;
        ajax1(probNum, 1, 't', selectedVal, true);
    }

    //guts submit function, submits all three sets
    function gutsSubmit(currentSet)
    {
        ajax1(currentSet*3 - 2, 2, -1, -1, false);
        ajax1(currentSet*3 - 1, 2, -1, -1, false);
        ajax1(currentSet*3, 2, -1, -1, false);
    }

</script>


<h1>Online Tournament Guts Round</h1>

        <p>Welcome, team <b><?php echo $team_name; ?></b>!</p>
        
        <p> Return to the team portal <a href="onlinetportal.php">here</a>.</p>
        
        <p>If this is not your team, or you are done with this page, 
            please <a href="onlinetportal.php?logout=now">log out</a>.</p>

<h2>Problems</h2>


    <a href="Guts.pdf">Download a pdf of the problems here.</a>
<p> Puzzle Round will be posted next week on our home page. Thanks for Participating! 
    Please email us your feedback so that we can improve our online competition. </p>
<h2>Answer Submission</h2>
    

<p>Instructions: Use the buttons on the right to toggle the format of each answer.
You should always finish formatting your answer before inputting any numbers.
Once you have inputted an answer, please press submit to save your answer to a problem.  
You may submit your answers as many times as you wish until time is up; 
    only your last set of answers will be recorded.</p>

<p>
    Guts specific instructions: Answer the questions in groups of three.  
    Please submit questions individually before submitting each triplet to ensure your answers are recorded.
    Once you submit a triplet, you will not be able to go back and work on it again.</p>

    <form action="">
<?php         
    //G for guts
    $roundCode = "G";
    $numProbs = $numGutsProblems;

    //create the table that displays submission forms
    echo "<table>";    
    for($k = 3*$currentSet - 2; $k<= 3*$currentSet; $k++)
    {
        echo "<tr> <td id='prob" . $k . "'><h3>Problem " . $k . "</h3></td></tr>";
        //answer space, changed by ajax1
        echo "<tr> <td id='answer_space" . $k . "' width=700  height=150>";
        echo "Loading...";
        echo "<input id='" . $roundCode . $k . "numBoxes' style='display:none'/></td>";
        //various buttons
        echo "<td><div align='center'><button type ='button' onclick =\"ajax1($k, 0, -1, -1, true) \" > Clear Problem " . $k . "</button>";
        echo "<button type ='button' onclick =\"ajax1($k, 2, -1, -1, true) \"> Submit Problem " . $k . "</button>";
        echo "_____________________________________________";
        echo "</br></br><button type ='button' onclick =\"ajax1($k, 1, 'f', -1, true) \" > Change to Fraction" . "</button>";
        echo "<button type ='button' onclick =\"ajax1($k, 1, 'r', -1, true) \" > Change to Square Root" . "</button>";
        echo "</br><button type ='button' onclick =\"ajax1($k, 1, 's', -1, true) \" > Add Sum Element" . "</button>";
        echo "<button type ='button' onclick =\"ajax1($k, 1, 'p', -1, true) \" > Add Product Element" . "</button>";
        echo "<button type ='button' onclick =\"ajax1($k, 1, 'l', -1, true) \" > Add List Element" . "</button>";
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
    echo "for(var j=" . (3*$currentSet - 2) . "; j<=" . (3*$currentSet) . ";j++)";
    echo "{";
?>
            ajax1(j, 3, 'n', -1, true);
        }
</script>

</br>
</br> 
</br>
<form action="onlineguts.php" method="get">
<input type="hidden" name="nextRound" value="true"/>
<?php
//submit button for triplet - clicks all 3 submit buttons and moves on to the next set
    echo "<input type =\"submit\" value=\"Submit This Triplet\" class=\"gutssubmit\" onclick =\"gutsSubmit(" . $currentSet . ")\"/>";
?>
</form>
<?php
	include 'frame_bottom.php';
?>
