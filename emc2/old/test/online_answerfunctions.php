<?php

    /***********************************************************************
     * online_answerfunctions.php
     *
     * Joy Zheng, Allen Yuan, In Young Cho
     *
     * Computer Science 50
     * Final Project
     *
     * Functions to display a variety of math formats for online contest
     **********************************************************************/
     
    include 'online_constants.php';
    include 'mysql_init.php';
    
    session_start();
    
    // the maximum number of levels allowed
    define("MAXLEVELS", 9);
    
    /* to store answers */
    class Answer
    {
        /* all the major fields
           question -- round and number of the question, i.e. A1, B10, T5
           level -- level of nesting of this part of the answer
           type -- type of the answer chunk
              f -- fraction
              r -- square root
              i -- integer
              l -- list
              t -- tuple
              p -- product
              s -- sum
              w -- >, x -- >=, y -- <, z -- <=
              c -- constant (0: pi, 1: e, 2: i)
           chunks -- the array of nested pieces, each of which is of class Answer
           stringy -- the string which represents the entire answer 
           value -- the value for this integer or constant 
              (or _ if no value is needed for this type, or if the value is blank)
        */
        
        public $question; 
        private $level; 
        private $type = "i"; 
        private $chunks = array(); 
        private $stringy = "+0i_"; 
        private $value = "_"; 
    
        /* constructor: initializes the Answer from the given string
           question -- A,B,T, or G followed by integer
           level -- level of nesting for the piece to be created
           str -- the string which will represent the answer
        */
        function __construct($question, $level, $str)
        {     
            // initalizes constants
            $this -> question = $question;
            $this -> level = $level;
            $this -> stringy = $str;
            
            // initializes all the pieces the answer from the string
            $this -> fromString($str);
            $this -> toString();
        } 
        
        /* orders the pieces of the given Answer and all nested Answers 
           return: none
        */
        function order()
        {
            // first orders each of nested portions and recreates their strings
            foreach ($this -> chunks as $chunk)
            {
                $chunk -> order();
                $chunk -> toString();
            }
            
            // except for fractions and ordered tuplets, orders the nested portions    
            if ($this -> type != "t" && $this -> type != "f")
                usort($this -> chunks, array($this, "cmp"));
        }
        
        /* comparison function for sorting -- compares based on the strings used to represent Answers
           returns -1, 0, or +1 based on string comparison
        */
        function cmp($a, $b)
        {
            return strcmp($a -> stringy, $b -> stringy);
        }
        
        /* turns the Answer object into the properly ordered one represented by the given string
           s -- the string representing the Answer
           return: none
        */
        function fromString($s)
        {
            // clears out the array
            $this -> chunks = array();
            
            // the delimiter used to divide off the nested chunks
            $delim = "+" . (1 + ($this -> level));
            
            // parse out the level, type, and resulting substring
            $n = sscanf($s, "+%i%c%s", $this -> level, $this -> type, $s1);
            
            // if $s is just a value or empty value
            if (is_numeric($s1) || $s1 == "_")
            {
                $this -> value = $s1;
            }
            // if there are more nested parts to look at
            else 
            {
                if ($s1[0] == '_') // for blank spaces
                {
                    $this -> value = "_";
                    $n = sscanf($s1, "_%s", $s2);
                }
                else // for non-blank spaces
                {
                    $n = sscanf($s1, "%f%s", $this -> value, $s2);
                }
                
                // divide into consitutent parts
                $parts = explode("+".($this->level+1), $s2);
                unset($parts[0]); // get rid of the blank intial item 

                // for each nested element, build it and add it to the array
                foreach ($parts as $part)
                {
                    array_push($this -> chunks, 
                        new Answer($this -> question, 1 + $this -> level, "+". ($this->level+1) . $part));
                }
            }
            
            // order all the pieces
            $this -> order();
        }
        
        /* converts the object to a string and puts it in the object's stringy 
           return: the string;
        */
        function toString()
        {            
            // adds +(level)(type) to string
            $s = "+";
            $s = $s . ($this -> level);
            $s = $s . ($this -> type);
            
            // if an integer or charcter, puts the value; otherwise, puts a _
            if ($this -> type == "i" || $this -> type == "c")
                $s = $s . ($this -> value);
            else
                $s = $s . "_";
            
            // goes through nested parts if there are any
            if (count($this -> chunks) !=0)
            { 
                foreach ($this -> chunks as $chunk)
                {
                    $s = $s . ($chunk -> toString());
                }
            }
            
            // stores the string in stringy
            $this -> stringy = $s;
            return $s;
        }
        
        /* stores the string in a table based on the current login 
           return: none
        */
        function store()
        {
            if (isset($_GET["sample"]) && $_GET["sample"] == "y")
                {
                    $_SESSION["sampleanswer"] = $this -> stringy;
                    return;
                }
            
            // include mysql and other constants
            include 'mysql_init.php';
            include 'online_constants.php';
        
            // connect to database        	
        	$link = mysql_connect($sql_addr, $sql_user, $sql_pswd)
               or die('Could not connect: ' . mysql_error());
      	    mysql_select_db($sql_db) or die("Could not select database");
        	
        	// escapes the string to be stored    
       	    $stemp = mysql_escape_string($this -> stringy);
       	    
       	    // gets the keys for the team and student that are logged in, if any
       	    if (isset($_SESSION["online_team"]))
                $online_team = $_SESSION["online_team"];
            else
                $online_team = false;
                
            if (isset($_SESSION["online_student"]))
                $online_student = $_SESSION["online_student"];
            else
                $online_student= false;
                
            if (isset($_SESSION["admin"]))
                $admin = $_SESSION["admin"];
            else
                $admin = false;
       	    
       	    // the question
       	    $question = $this -> question;
       	    
       	    // gets the correct answer from the database and its point value
       	    $query = "SELECT $question, $question"."p FROM emc2_online_answers WHERE year='$online_year';";
       	    $result = mysql_query($query) or die('Query failed: ' . mysql_error());
       	    $goodanswer = mysql_fetch_array($result);
       	    $answer = $goodanswer["$question"];
	    $answers = explode(",", $answer);
       	    $points = $goodanswer[$question."p"];
        	
	// checks whether the answer is one of the correct ones
	$answercorrect = false;

	foreach ($answers as $choice)
	{
		if (strcmp($stemp, $choice) == 0)
			$answercorrect = true;
	}

        	// if submitting a team-based round    
       	    if ($online_team)
       	    {
       	        $query = "UPDATE emc2_online_tanswers SET $question='$stemp' WHERE team_id='$online_team';";
       	        if ($answercorrect)
       	            $query2 = "UPDATE emc2_online_tscores SET $question='$points' WHERE team_id='$online_team';";
       	        else
       	            $query2 = "UPDATE emc2_online_tscores SET $question='0' WHERE team_id='$online_team';";
       	    }
       	    // if submitting an individual-based round
       	    else if ($online_student)
       	    {
       	        $query = "UPDATE emc2_online_students SET $question='$stemp' WHERE student_key='$online_student';";
       	        if ($answercorrect)
       	            $query2 = "UPDATE emc2_online_iscores SET $question='$points' WHERE indiv_id='$online_student';";
       	        else
       	            $query2 = "UPDATE emc2_online_iscores SET $question='0' WHERE indiv_id='$online_student';";
       	    }
       	    // if submitting the correct answers via administrator login
       	    else if ($admin)
       	    {
       	        $query = "UPDATE emc2_online_answers SET $question='$stemp' WHERE year='$online_year';";
       	    }
        	
        	// makes both of the queries and closes the link    
       	    $result = mysql_query($query) or die('Query failed: ' . mysql_error()); 
       	    
       	    if ($admin != true)
       	        $result = mysql_query($query2) or die('Query failed: ' . mysql_error()); 
			mysql_close($link);		
	    }
	    
	    /* gets the string for this problem 
	       returns the string
	    */
	    function get()
	    {
	        include 'mysql_init.php';
	        include 'online_constants.php';
	        
	        if (isset($_GET["sample"]) && $_GET["sample"] == "y")
	            return $_SESSION["sampleanswer"];
	        
	        // connect to database        	
            $link = mysql_connect($sql_addr, $sql_user, $sql_pswd)
       		    or die('Could not connect: ' . mysql_error());
       	    mysql_select_db($sql_db) or die("Could not select database");
       	    
       	    // gets the keys for the team and student that are logged in, if any
       	    if (isset($_SESSION["online_team"]))
                $online_team = $_SESSION["online_team"];
            else
                $online_team = false;
                
            if (isset($_SESSION["online_student"]))
                $online_student = $_SESSION["online_student"];
            else
                $online_student= false;
                
            if (isset($_SESSION["admin"]))
                $admin = $_SESSION["admin"];
            else
                $admin = false;
       	    
       	    // gets the question
       	    $question = $this -> question;
        	
            if ($online_team != false) // if logged in as a team   	    
                $query = "SELECT $question FROM emc2_online_tanswers WHERE team_id='$online_team';";
            else if ($online_student != false) // if logged in as a student
                $query = "SELECT $question FROM emc2_online_students WHERE student_key='$online_student';";
            else if ($admin != false) // if logged in as an administrator
                $query = "SELECT $question FROM emc2_online_answers WHERE year='$online_year';";
        	    
       	    // queries the database and returns the string
       	    $result = mysql_query($query) or die('Query failed: ' . mysql_error());
       	    $value = mysql_fetch_array($result);
       	    mysql_free_result($result);
			mysql_close($link);
       	    return $value["$question"];
	    }
        
        /* builds the HTML for a textbox
           indexValue -- index of the textbox
           editable -- whether it should be editable
           viewable -- whether the text box should be visible
           return: the HTML string
        */
        function buildHTML($indexValue, $editable, $viewable)
        {
            // id and type
            $s = "<input id='" . $indexValue . $this -> question . "' type='text' ";
            
            // if the value is not a blank
            if ($this -> value != "_")
            {
                $s = $s . "value='" . $this -> value . "' ";
            }
            
            $s = $s . "onfocus='focusTracker(\"" . $indexValue . $this -> question . "\")' ";
            
            if (!$viewable)
                $s = $s . "style=\"display:none\" ";
            
            // if the text box should not be editable
            if (!$editable)
            {
                $s = $s . "readonly";
            }
            
            $s = $s."size=\"6\" onblur=\"setTimeout('loseFocus()',200)\" />";
            
            return $s;
        }
        
        /* echoes the HTML to display an answer
           editable -- whether the values can be changed
           startValue is the number to start numbering the text boxes from
           return: 1 more than the index of the last text box created
        */
        function display($editable, $startValue)
        {            
            // echoes beginning of the table only the first time around
            if ($this -> level == 0)
            {
                echo "<p>The answer is:</p>";
                echo "<table class=\"answertable\">";
                echo "<tr><td class=\"answer\">";
            }
            
            // different displays are done based on the type
            switch ($this -> type)
            {
                // integers
                case "i": 
                    echo ($this -> buildHTML($startValue, $editable, true));
                    $startValue++;
                    break;
                    
                // constant
                case "c":
                    echo ($this -> buildHTML($startValue, $editable, false));
                    switch ($this -> value)
                    {
                        case 0: //pi
                            echo "&#960;";
                            break;
                        
                        case 1: //e
                            echo "<i>e</i>";
                            break;
                        
                        case 2: //i
                            echo "<i>i</i>";
                            break;
                    }
                    $startValue++;
                    break;
                    
                // >
                case "w":
                    echo htmlspecialchars("ANSWER > ");
                    echo ($this -> buildHTML($startValue, $editable, false));
                    $startValue++;
                        
                    // iterate over everything inside
                    foreach ($this -> chunks as $chunk)
                    {
                        $startValue = ($chunk -> display($editable, $startValue));
                    }
                        
                    break;
                        
                // >=
                case "x":
                    echo htmlspecialchars("ANSWER ")."&#8805; ";
                    echo ($this ->buildHTML($startValue, $editable, false));
                    $startValue++;
                        
                    // iterate over everything inside
                    foreach ($this -> chunks as $chunk)
                    {
                        $startValue = ($chunk -> display($editable, $startValue));
                    }
                    break;
                        
                // <
                case "y":
                    echo htmlspecialchars("ANSWER < ");
                    echo ($this ->buildHTML($startValue, $editable, false));
                    $startValue++;
                        
                    // iterate over everything inside
                    foreach ($this -> chunks as $chunk)
                    {
                        $startValue = ($chunk -> display($editable, $startValue));
                    }
                    break;
                        
                // <=
                case "z":
                    echo htmlspecialchars("ANSWER "). "&#8804; ";
                    echo ($this->buildHTML($startValue, $editable, false));
                    $startValue++;
                        
                    // iterate over everything inside
                    foreach ($this -> chunks as $chunk)
                    {
                        $startValue = ($chunk -> display($editable, $startValue));
                    }
                    break;
                        
                // square root
                case "r":
                    echo "&#8730; <span class=\"answerroot\">"; 
                        
                    echo ($this ->buildHTML($startValue, $editable, false));
                    $startValue++;
                        
                    // iterate over everything inside
                    foreach ($this -> chunks as $chunk)
                    {
                        $startValue = ($chunk -> display($editable, $startValue));
                    }
                        
                    echo "</span>";
                    break;
                  
                // list    
                case "l":
                    $len = count($this -> chunks) - 2;
                    echo ($this -> buildHTML($startValue, $editable, false));
                        $startValue++;
                        
                    // iterate over everything inside, except the last element
                    for ($i = 0; $i<= $len; $i++)
                    {
                        $startValue = $this -> chunks[$i] -> display($editable, $startValue);
                        echo " AND ";
                    }
                        
                    $startValue = $this -> chunks[$len + 1] -> display($editable, $startValue);
                    break;
                    
                // sum
                case "s":
                    $len = count($this -> chunks) - 2;
                    
                    echo ($this -> buildHTML($startValue, $editable, false));
                        $startValue++;
                        
                    // iterate over everything inside, except the last element
                    for ($i = 0; $i<= $len; $i++)
                    {
                        $startValue = $this -> chunks[$i] -> display($editable, $startValue);
                        echo "+";
                    }
                        
                    $startValue = $this -> chunks[$len + 1] -> display($editable, $startValue);
                    break;
                        
                // product
                case "p":
                    $len = count($this -> chunks) - 1;
                    echo ($this -> buildHTML($startValue, $editable, false));
                            $startValue++;
                        
                    // iterate over everything inside
                    for ($i = 0; $i<= $len; $i++)
                    {
                        echo "(";
                        $startValue = $this -> chunks[$i] -> display($editable, $startValue);
                        echo ") ";
                    }
                        
                    break;
                    
                // tuple
                case "t":
                    $len = count($this -> chunks) - 2; 
                    echo ($this -> buildHTML($startValue, $editable, false));
                        $startValue++;
                            
                    echo "(";
                        
                    // iterate over everything inside, except the last element
                    for ($i = 0; $i<= $len; $i++)
                    {
                        $startValue = $this -> chunks[$i] -> display($editable, $startValue);
                        echo ", ";
                    }
                       
                    $startValue = $this -> chunks[$len + 1] -> display($editable, $startValue);
                    echo ")";
                    break;
                        
                // fraction
                case "f":
                    echo ($this -> buildHTML($startValue, $editable, false));
                        $startValue++;
                        
                    echo "</td><td><table class=\"answertable\"><tr><td class=\"fractiontop\">";
                    $startValue = $this -> chunks[0] -> display($editable, $startValue);
                    echo "</td></tr> <tr><td class=\"fractionbottom\">";
                    $startValue = $this -> chunks[1] -> display($editable, $startValue);
                    echo "</td></tr></table></td><td class=\"answer\">";
                        
                    break;
            }
            
            // ends the table only once around
            if ($this -> level == 0)
                echo "</td></tr></table>";
            
            return $startValue;
        }
        
        /* changes a string based on the values in the $_GET variable
           editable -- whether the displayed boxes should be editable
           return: false if change was valid, error message otherwise
        */
        function changeString($editable)
        {
            /* possible types:
                f -- fraction
                r -- take root of a text box
                s -- add to a sum
                p -- add to a product
                l -- add to a list
                v (then x-value) -- change to an interval
                c (then x-value) -- multiply text box by a constant
                t (then x-value) -- change text box to an n-tuple
            */
            
            // gets the currently stored string
            $s = $this -> get();
            
            //gets the number of the part we want to change
            $n = $_GET["number"];
            
            // find position of the part we want to change
            $pos = -1;
            for ($i = 0; $i <= $n; $i++)
            {
                $pos = strpos($s, "+", $pos+1);
            } //echo $pos . " hmm" . $n; 
            
            // $type = substr($s, $pos + 2, 1);
            $level = intval(substr($s, $pos + 1, 1)); // get substring with level
            $stemp = substr($s, $pos+3); // get substring starting with the value
            
            // get length of the value currently inserted
            if (sscanf($stemp, "%f%s", $num, $st) == 0)
                $len = 1;
            else
                $len = strlen($num);
            
            // get "x" -- an extra specifier for some change types
            if(isset($_GET["x"])) 
                $x = $_GET["x"];
                
            // calculate index for inserting, if needed
            $ins = $pos + 3 + $len;
            
            // casework based on the type of change
            switch ($_GET["changed"])
            {
                // a fraction
                case "f":
                    // to check whether the item to be changed was already nested in a fraction
                    $fracbad = false;
                    $lev = $s[$pos+1]; 
                    $curpos = $pos;
                    $cpos = $pos;
                    
                    // iterates downwards through the levels
                    while ($lev > 0)
                    {
                        $delim = "+".($lev-1); 
                        $temppos = strpos($s, $delim, 0);
                        
                        // finds the last item of the previous level
                        while ($temppos !== false  && $temppos < $curpos)
                        {
                            $cpos = $temppos;
                            $temppos = strpos($s, $delim, $temppos+1);
                        }
                        
                        $curpos = $cpos;
                        
                        // if it is a fraction, change fracbad
                        if ($s[$curpos+2] == "f")
                            $fracbad = 1;
                        else if ($s[$curpos+2] == "r")
                            $fracbad = 2;
                        
                        $lev --;
                    }
                    
                    // turns this item into a fraction
                    if ($level > MAXLEVELS-1 || strlen($s) > 90)
                        $l_err = "Does your answer really need to be that complicated? Probably not.";
                    else if ($fracbad == 1)
                        $l_err = "No nested fractions allowed. Reread the specifications on answer forms.";
                    else if ($fracbad == 2)
                        $l_err = "No fractions inside square roots. Reread the specifications on answer forms.";
                    else 
                    {
                        $s = substr_replace($s, "f", $pos+2, 1);
                        
                        $level++;

                        $s = substr_replace($s, "+".$level."i_", $ins, 0);
                        $s = substr_replace($s, "+".$level."i_", $ins, 0);
                    }
                    break;
                
                // a square root
                case "r":
                    // to check whether the item to be changed was already nested in a square root
                    $rootbad = false; 
                    $lev = $s[$pos+1]; 
                    $curpos = $pos; 
                    $cpos = $pos;
                    
                    // iterates downwards through the levels
                    while ($lev > 0)
                    {
                        $delim = "+".($lev-1); 
                        $temppos = strpos($s, $delim, 0); 
                        
                        // finds the last item of the previous level
                        while ($temppos !== false  && $temppos < $curpos)
                        {
                            $cpos = $temppos;
                            $temppos = strpos($s, $delim, $temppos+1);
                        }
                        
                        $curpos = $cpos;
                        
                        // if it is a square root, change rootbad
                        if ($s[$curpos+2] == "r")
                            $rootbad = true;
                        
                        $lev --;
                    }
                    
                    if ($level > MAXLEVELS-1 || strlen($s) > 90)
                        $l_err = "Does your answer really need to be that complicated? Probably not.";
                    else if ($rootbad)
                        $l_err = "No nested square roots allowed. Reread the specifications on answer forms.";
                    else
                    {
                        $s = substr_replace($s, "r", $pos + 2, 1);
                        $level++;
                    
                        $s = substr_replace($s, "+".$level."i_", $ins, 0);
                    }
                    break;
                
                // a sum
                case "s":
                    // find if element is already part of a sum
                    $delim = "+".($level-1);
                    
                    // tries to find where the sum began
                    if ($level === 0)
                        $delim = "!";
                    $p = $pos;
                    
                    while (substr($s, $p, 2) != $delim && $p >=0)
                    {
                        $p--;
                    }
                    
                    // if there was no such sum, add a sum element and increase the level
                    if ($p < 0 || ($s[$p+2] != "s"))
                    {
                        if ($level > MAXLEVELS-1 || strlen($s) > 90)
                            $l_err = "Does your answer really need to be that complicated? Probably not.";
                        else
                        {
                            $s = substr_replace($s, "s", $pos+2, 1);
                            $level++;
                            $s = substr_replace($s, "+".$level."i_", $ins, 0);
                            $s = substr_replace($s, "+".$level."i_", $ins, 0);
                        }
                    }
                    //if there was already such a sum
                    else
                        $s = substr_replace($s, "+".$level."i_", $ins, 0);
                    break;
                
                // a product
                case "p":
                    // find if element is already part of a product
                    $delim = "+".($level-1);

                    // tries to find where the product began
                    if ($level === 0)
                        $delim = "!";
                    $p = $pos;
                    
                    while (substr($s, $p, 2) != $delim && $p >=0)
                    {
                        $p--;
                    }
                    
                    // if there was no such product, add an element and increase the level
                    if ($p < 0 || ($s[$p+2] != "p"))
                    {
                        if ($level > MAXLEVELS-1 || strlen($s) > 90)
                            $l_err = "Does your answer really need to be that complicated? Probably not.";
                        else
                        {
                            $s = substr_replace($s, "p", $pos+2, 1);
                            $level++;
                            $s = substr_replace($s, "+".$level."i_", $ins, 0);
                            $s = substr_replace($s, "+".$level."i_", $ins, 0);
                        }
                    }
                    //if there was already such a product
                    else
                        $s = substr_replace($s, "+".$level."i_", $ins, 0);
                    break;
                
                // a list element          
                case "l":
                    // find if element is already part of a list
                    $delim = "+".($level-1);
                    
                    // tries to find where the list began
                    if ($level === 0)
                        $delim = "!";
                    $p = $pos;
                    
                    while (substr($s, $p, 2) != $delim && $p >=0)
                    {
                        $p--;
                    }
                    
                    // if there was no such list, add an element and increase the level
                    if ($p < 0 || ($s[$p+2] != "l"))
                    {
                        if ($level > MAXLEVELS-1 || strlen($s) > 90)
                            $l_err = "Does your answer really need to be that complicated? Probably not.";
                        else
                        {
                            $s = substr_replace($s, "l", $pos+2, 1);
                            $level++;
                            $s = substr_replace($s, "+".$level."i_", $ins, 0);
                            $s = substr_replace($s, "+".$level."i_", $ins, 0);
                        }
                    }
                    //if there was already such a list
                    else
                        $s = substr_replace($s, "+".$level."i_", $ins, 0);
                    break;
               
                // interval
                case "v":
                    if ($level > MAXLEVELS-1 || strlen($s) > 90)
                        $l_err = "Does your answer really need to be that complicated? Probably not.";
                    else
                    {
                        $s = substr_replace($s, $x, $pos + 2, 1);
                        $level++;
                        $s = substr_replace($s, "+".$level."i_", $ins, 0);
                    }
                    break;
                
                // constant
                case "c":
                    $s = substr_replace($s, "c".$x, $pos + 2, 1+$len);
                    break;
                
                // tuple
                case "t":
                    if (!isset($_GET["x"]) || !(preg_match("/^\d+$/", $x) || $x < 2))
                        $l_err = "Must enter a positive integer greater than or equal to 2 for tuple size!";
                    if ($level > MAXLEVELS-1 || strlen($s) > 90)
                        $l_err = "Does your answer really need to be that complicated? Probably not.";
                    else
                    {
                        $s = substr_replace($s, "t_", $pos + 2, 1+$len);
                        $level++;
                        
                        $add = "";
                        $x = intval($x);
                        
                        // generate string to add
                        for ($i=0; $i<$x; $i++)
                        {
                            $add = $add . "+" . $level . "i_";
                        }
                        
                        $s = substr_replace($s, $add, $ins, 0);
                    }
                    break;
            }
            
            // stores the string and guarantees ordering
            $this -> fromString($s); 
            
            // if no change
            if ($_GET["changed"] == "n")
                $this -> toString();
            else
            {
                $this -> toString();
                if (isset($_GET["sample"]) && $_GET["sample"] == "y")
                    $_SESSION["sampleanswer"] = $this -> stringy;
                else
                    $this -> store();
            } 
            
            // display the input boxes
            $n = $this -> display($editable, 0);
            
            // echoes an extra element for the JavaScript to later access
            echo "<span style=\"display:none\" id='". $this->question ."numBoxes'>" . $n ."</span>";
            
            // returns error if there was one
            if (isset($l_err))
                return $l_err;
            else
                return false;
        }
        
        /* stores the input values into the database
           return: an error if anything fails, false otherwise
        */
        function storeValues()
        {
            $i = -1;
            $s = $this -> get();
            $pos = strpos($s, "+");
            
            // iterates through all the answer fields
            do
            {
                $i++;
                
                // get length of current input
                $stemp = substr($s, $pos+3);
                if (sscanf($stemp, "%i%s", $num, $x) == 0)
                    $len = 1;
                else
                    $len = strlen($num);

                if ($s[$pos+2]== 'i')
                { 
                    //check to make sure that it's a number
                    if(!(is_numeric($_GET[$i])) || $_GET[$i]=="") 
                    {
                        $l_err = "All entries should be numeric values!";
                    }
                    else
                        $s = substr_replace($s, $_GET[$i], $pos+3, $len);
                }
            }
            while ($pos = strpos($s, "+", $pos+1));
            
            // if string is too long, complains
            if (strlen($s) > 90)
                $l_err = "Does your answer really need to be that complicated? Probably not.";
            else
            {
                $this -> fromString($s);
                $this -> toString();
                $this -> store();
            }
            
            // displays the item
            $n = $this -> display(true, 0);
            echo "<span style=\"display:none\" id='". $this->question ."numBoxes'>" . $n ."</span>";
            
            // returns error if there was one
            if (isset($l_err))
                return $l_err;
            else
                return false;
        }
        
        /* clears the answer
           return: none
        */
        function clearAnswer()
        {
            // clear out values
            $this -> type = "i";
            $this -> chunks = array(); 
            $this -> stringy = "+0i_"; 
            $this -> value="_";
            
            // store the value
            // $this -> toString();
            if (isset($_GET["sample"]) && $_GET["sample"] == "y")
                $_SESSION["sampleanswer"] = "+0i_";
            else
                $this -> store();
            
            $n = $this -> display(true, 0);
            echo "<span style=\"display:none\" id='". $this->question ."numBoxes'>" . $n ."</span>";
        }           
    } // end class
    
    if (isset($_SESSION["online_team"]))
        $online_team = $_SESSION["online_team"];
    else
        $online_team = false;
        
    if (isset($_SESSION["online_student"]))
        $online_student = $_SESSION["online_student"];
    else
        $online_student= false;
        
    if (isset($_SESSION["admin"]))
        $admin = $_SESSION["admin"];
    else
        $admin = false;
    
    if ($admin == false && $online_team == false && $online_student == false && !(isset($_GET["sample"])))
    {
        echo "You've been logged out! Please <a href='onlinelogin.php'>log in</a> again.'";
        die;
    }
    
    // creates a new answer
    if (!isset($_GET["question"]))
        $_GET["question"] = "S1";
    $answer = new Answer($_GET["question"], 0, "+0i_");
    if (isset($_GET["sample"]) && $_GET["sample"] == "y")
    {
        if (isset($_SESSION["sampleanswer"]))
            $answer -> fromString($_SESSION["sampleanswer"]);
        else
            $_SESSION["sampleanswer"] = "+0i_";
    }
    else
        $answer -> fromString($answer -> get());
    
    // makes editability work
    $editable = true;
    $round = $answer -> question;
    if ($round[0] == 'A' && $online_speed_ended)
        $editable = false;
    if ($round[0] == 'B' && $online_accuracy_ended)
        $editable = false;
    if ($round[0] == 'T' && $online_team_ended)
        $editable = false;
    if ($round[0] == 'G' && $online_guts_ended)
        $editable = false;
        
    // submit values
    if($_GET["type"] == "submit" && $editable)
    {
        $err = $answer -> storeValues();
        
        if ($err != false)
            echo "<p class=\"errorbar\">" . $err . "</p>";
        else
            echo "<p class=\"confirmbar\"> Values successfully stored! </p>";
    }
    
    // clear answer
    else if($_GET["type"] == "clear" && $editable)
    {
        $answer -> clearAnswer();
        echo "<p class=\"confirmbar\"> Answer cleared! </p>";
    }
    
    // change form
    else if($_GET["type"] == "change")
    {
        $err = $answer -> changeString($editable);
        
        if ($err != false)
            echo "<p class=\"errorbar\">" . $err . "</p>";
        else if ($_GET["changed"] != "n")
            echo "<p class=\"confirmbar\"> Form changed! </p>";
    }
?>
