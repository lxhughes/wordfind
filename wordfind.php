<?
// Start time - for testing runtime.
$starttime = time()+microtime();

// DEFINITIONS/GLOBAL VARIABLES

// Testing, yes or no? 
if (isset($_GET['testing'])) {
	define ("TESTING", 1);
	}

// Worksafe, yes or no?
if (isset($_GET['worksafe'])) {
	define ("WORKSAFE", 1);
	}

// Generates random seed of this board
$seed = rand(10000, 99999);
if (isset($_GET['seed'])) {
	$seed = $_GET['seed'];
	if (!is_numeric($seed)) die("Seed must be a number");
	}
srand($seed);


// Which tile distribution to use?
// OPTIONS: 1 = 4x4 1980s; 2 = 4x4 1990s; 3 = 5x5
if (isset($_GET['distro'])) {
	$distro = $_GET['distro'];
	$okvalues = array(1, 2, 3);
	if (!in_array($distro, $okvalues)) die("Error, unknown distribution type");
	}
else { 
	$distro = 1; // Default
	} 


// Size of board (numeric value, used in various parts of the program) depending on user's distribution preference
if (($distro == 1) || ($distro == 2)) {
	$size = 4;
	}
elseif ($distro == 3) {
	$size = 5;
	}


// What letter minimum to use? 
if (isset($_GET['lettermin'])) {
	$minimum = $_GET['lettermin'];
	if (!is_numeric($minimum)) die("Minimum must be a number");
	}
else {
	$minimum = 3;	// Default
	}


// How long to make the timer? 
if (isset($_GET['timerl'])) {
	$timerl = $_GET['timerl'];
	if (!is_numeric($timerl)) die("Error, timer length must be a number");
	}
else {
	$timerl = 180;
}

// Friend's score, for comparison (ie if someone else sent you the link)
if (isset($_GET['fscore'])) {		
	$fscore = $_GET['fscore'];
	if (!is_numeric($fscore)) die("Error, friend's score must be a number");
	}


// Make the random board
$grid = Board_Maker();
	// A one-d version of the random board for use by support/crawl functions
	$flat_grid = Grid_Flattener();
	// A coordinate value version of the grid used in the board crawler. Based on size.
	$coord_grid = MakeCoordGrid();


// SEND USER TO APPROPRIATE PLACE

if (isset($_GET['prefs'])) {	// If the prefs have been submitted, post the gameplay pace
	DisplayGame();
	}
elseif ((isset($_GET['distro'])) && (!isset($_GET['prefs']))) {	// If the distro but not prefs have been submitted, post the 'your friend wants you to play' page
	WelcomeFriend();
	}
else {
	DisplayChoices();		// If nothing has been submitted, display options.
	}



// CHOICES PAGE

// DisplayChoices
// Prints out the choices you will get if you have not set them yet.
// IN: nothing; GLOBAL: all settings; OUT: nothing (performs action, setting distro and minimum letters)
function DisplayChoices() {
	global $seed, $distro, $lettermin, $minimum, $timerl;

	echo "<form method=get>";
		echo "<input type=hidden name=seed value=".$seed.">";

	Page_Format();
	
	?>
	<p>Select your tileset: 
		<select name='distro'>
			<option value=1>4x4 1970s-80s</option>
			<option value=2 selected>4x4 1990s</option>
			<option value=3>5x5</option>
		</select>
	<p>Select your minimum number of letters: 
		<select name='lettermin'>
			<option value=2>2</option>
			<option value=3 selected>3</option>
			<option value=4>4</option>
			<option value=5>5</option>
		</select>
	<p>Select your time limit: 
		<select name='timerl'>
			<option value=10>10 seconds</option>
			<option value=30>30 seconds</option>
			<option value=60>1 minute</option>
			<option value=180 selected>3 minutes</option>
			<option value=300>5 minutes</option>
		</select>
	<?

	Worksafe_Box();

	echo "\n<p><input type=submit value=Ready name=prefs>";
	echo "\n</form>";

	}


// WELCOME FRIEND PAGE

// WelcomeFriend
//
// IN: nothing; GLOBAL: all preferences; OUT: nothing (gives user chance to click ok to play))
function WelcomeFriend() {
	global $seed, $distro, $lettermin, $minimum, $timerl, $fscore;

	Page_Format();
	Fake_Board();

	echo "<form method=get>";
		echo "<input type=hidden name=seed value=".$seed.">";
		echo "<input type=hidden name=distro value=".$distro.">";
		echo "<input type=hidden name=lettermin value=".$minimum.">";
		echo "<input type=hidden name=timerl value=".$timerl.">";
		echo "<input type=hidden name=fscore value=".$fscore.">"; 

	echo "<p>Your friend wants you to play this game.";
	echo "<p>How you will play: Find words on the board by connecting letters in any direction (left, right, up, down, diagonal). You can change directions mid-word. You can't use the same instance of a letter twice. Input your words in the text box separated by any number or combination of spaces, commas, periods, or semicolons.";
	echo "<p>Your friend has already chosen the following preferences:";
	echo "<p><b>Tile Set:</b> ";
		if ($distro == 3) { echo "5x5"; }
		elseif ($distro == 2) { echo "4x4 1990s"; }
		else { echo "4x4 1980s"; }
	echo "<p><b>Minimum Word Length:</b> ".$minimum;
	echo "<p><b>Time Board Will Remain Active:</b> ";
		if ($timerl == 300) { echo "5 minutes"; }
		elseif ($timerl == 180) { echo "3 minutes"; }
		elseif ($timerl == 60) { echo "1 minute" ; }
		else { echo $timerl." seconds"; }
	echo "<p><b>Game #:</b> ".$seed;
	Worksafe_Box();
	if (isset($fscore)) {
		echo "<p><b>Friend's Score:</b> ".$fscore;
		echo "<p>Can you beat that? Remember, your scores aren't saved (I'm going to forget your friend's score after this game), so you are on the honor system, and can always say you got a million jillion points.";
	}
	
	echo "<p>Take a deep breath and get ready to type.";

	echo "<p><input type=submit value=Ready name=prefs>";
	echo "</form>";
}


// GAMEPLAY PAGE

// DisplayGame
// Displays everything you need to play the game: the board, the input box.
// IN: nothing; GLOBAL: all preferences; OUT: nothing (displays stuff on screen)
function DisplayGame() {
	global $seed, $grid, $distro, $lettermin, $minimum, $timerl;

	Page_Format();

	// Set up the timer
		?>
		
		<SCRIPT LANGUAGE="JavaScript">
		function timer(count) {
			if (count == 0) {
				document.gameform.submit();
  			   	}
			 else {
				document.getElementById("timer").innerHTML=time_format(count);
				count--;
			  	setTimeout("timer("+count+", 0)",1000);
				}
 			}

		function time_format(number) {	// Formats seconds count as m:ss, and turns it red if there's 10 seconds or less left
			var myminutes = parseInt(number/60);
			var myseconds = number%60;
				if (myseconds < 10) {
					myseconds = "0"+myseconds;
				}
			var numstring = myminutes+":"+myseconds;

			if ((myminutes < 1) && (myseconds < 11)) {
				numstring = "<font color=ff0000>"+numstring+"</font>";
			}
			return numstring;
		}

		</script>

		<?

		echo "<body onLoad='timer(".$timerl.");'>";
		echo "<p>Randomly Shaken Board: ";
		Board_Printer($grid);


	if (!isset($_POST['done'])) {
	
		echo "<form name=gameform method=post>";

		// Hidden fields, containing all the info we know
		echo "<input type=hidden name=seed value=".$seed.">";
		echo "<input type=hidden name=distro value=".$distro.">";
		echo "<input type=hidden name=lettermin value=".$minimum.">";
		echo "<input type=hidden name=timerl value=".$timerl.">";
		if (isset($_GET['worksafe'])) {	echo "<input type=hidden name=worksafe value=1>"; }


		// Input box
		?>

		<p>Find words on the board by connecting letters in any direction (left, right, up, down, diagonal). You can change directions mid-word. You can't use the same instance of a letter twice. Input your words in the text box separated by any number or combination of spaces, commas, periods, or semicolons.
		<br>Minimum word length: <? echo $minimum; ?>
		<p><textarea rows=5 cols=20 name=user_list wrap=physical></textarea>
		<p><div id="timer"></div>
		<input type=hidden name=done value=1>
		<?
		if (TESTING == 1) {
			echo "<input type=submit name=Bypass>";
		}
		echo "</form>";
	}
	else {

		// Deal with user input

		$input = trim($_POST['user_list']); // Get raw input from form (trim trims any opening or trailing whitespace)
		$pattern = '/[^a-zA-Z\\s,\.;]/';  // Matches anything that's not upper or lowercase letters, space, comma, period, semicolon
		$input = preg_replace($pattern, "", $input); // Filters input replacing illegal characters with nothing
		$delimiter = '/[,\.\\s;]+/';  // Delimiter could be any number of comma OR period OR space OR semicolon
		$input_array = preg_split($delimiter, $input); // Splits filtered input by delimiter

			Wordlist_Scorer($input_array);

		}

}

// PAGE FORMATTING
// Page Format
// Formats page with CSS
// IN: nothing; OUT: nothing
function Page_Format() {
	// HTML stuff
	echo "<html>\n<head>\n<title>Word Find</title>\n\n";

	// Pretty formatting, unless worksafe
	if (WORKSAFE != 1) {
	?>

	<!--This is the CSS for formatting, particularly the tables-->
	<link rel="stylesheet" type="text/css" href="http://www.laurahughes.com/css/style.css" />
	<style type="text/css">
	td.board {
		background: #ffffff;
		border: 1px solid;
		spacing: 8px 8px 8px 8px;
		width: 20px;
		font-size: 18px;
		font-weight: bold;
		text-align: center;
	}
	td.tinyboard {
		background: #ffffff;
		border: 1px solid;
		spacing: 4px 4px 4px 4px;
		width: 14px;
		font-size: 12px;
		text-align: center;

		}
	</style>
	</head>
	<body>

	<div id=lynx><a class=lynx href="http://www.laurahughes.com/">main</a></div>
	
	<div id=name>Word Find</div>
	
	<div id=bodytext> 

	<? 
	}
}


// Worksafe_Box
// IN: none; OUT: none
// Prints a checkbox allowing the user to change worksafe preference. Current status of box is inherited from current preference settings.
function Worksafe_Box() {
	echo "<p><input type=checkbox name=worksafe value=1";
	if (WORKSAFE == 1) { 
		echo " checked ";
		}
	echo ">Work safe? (Check this is you'd rather see the game as an unformatted HTML page, plaintext on a white background.)<br>";
	}

// MAKING THE BOARD

// Board_Printer
// Prints each letter in an HTML grid
// IN: 2-d Grid (with rows); OUT: Action (prints board)

function Board_Printer($grid){
	echo "<table>";

	foreach ($grid as $row) {
		echo "<tr>";
		foreach ($row as $value) {
			$value = Qize($value);
			echo "<td class=board>".$value."</td>";
			}	
		echo "</tr>";
		}
	
	echo "</table>";

	if (TESTING == 1) {
		print_r($grid);
		}	
}


// Qize
// Checks if a letter is Q; adds the letter u if it is. (Used anywhere I want to display Q as Qu.)
// IN: letter; OUT: letter (or Qu)
function Qize($letter){
	if ($letter == "Q") {
		$letter = "Qu";
		}
	return $letter;
}


// Board_Maker
// Prints the selected tiles from Tile_Selector in random order.
// GLOBAL: size
function Board_Maker() {
	global $size;
	$selected_tiles = Tile_Selector();
	shuffle($selected_tiles);	// Shuffles selected tiles 
	for ($i = 0; $i < $size; $i++) { // Build four rows
		for ($j = 0; $j < $size; $j++) { // Build four cubes per row
			$current_tile = array_pop($selected_tiles);
			$row[] = $current_tile;
		}
		$grid[] = $row;
		unset($row);	// Clears row for next iteration
	}
	return $grid;
}


// Tile_Selector
// Randomly selects one tile from each cube. Calls Get_Tiles.
// GLOBAL: cubepool(array of acceptable cube arrays), size; OUT: selected_tiles (array of size-squared letters, each selected from one of the cubes)
function Tile_Selector() {
	$cube_pool = Get_Cubes();
	foreach ($cube_pool as $cube) {
		$randnumber = rand(0, 5);
		$tile = $cube[$randnumber];
		$selected_tiles[] = $tile;
	}
	return $selected_tiles;
}


// Get_Cubes
// Gets tile distribution from flat file, letterdistro.txt
// IN: distro   OUT: array (grid) of arrays (cubes)
function Get_Cubes() {
	global $distro;
	global $size;
	// Identify the tileset text file to use. 
	if ($distro == 1) {
		$distro_file = "4x4_80s.txt";
		}
	elseif ($distro == 2) {
		$distro_file = "4x4_90s.txt";
		}
	elseif ($distro == 3) {
		$distro_file = "5x5.txt";
		}
	$posish = 0;			 // Initializes position in file
	$fh = fopen($distro_file, 'r');     // Opens letter distro file
	for ($i = 0; $i < ($size * $size); $i++) { // Get size-squared cubes	
		fseek($fh, $posish);		   // Starts at appropriate starting position
		$cubeline = fgets ($fh, 7);	   // Gets six characters
		$cube = str_split($cubeline);   // Add array version of those six characters to $cubes array
		$posish = $posish + 7;		  // Set new starting position

		$cube_pool[] = $cube;			// fill current cube bucket with current letters
		}
	fclose($fh);				  // Close the letters file.
	return $cube_pool;
}

// ALTERNATE VERSIONS OF THE BOARD

// Fake_Board
// A fake board for use by the friend preparation screen and the pause screen.
// IN: nothing; GLOBAL: size; OUT: nothing.
function Fake_Board() {
	global $size;
	$counter = 0;
	$fakeletters = str_split("SAMPLESAMPLESAMPLESAMPLESAMPLE");

	echo "<table>";
	for ($i = 0; $i < $size; $i++){
		echo "<tr>";
		for ($j = 0; $j < $size; $j++){
			echo "<td class=board>".$fakeletters[$counter]."</td>";
			$counter++;
		}
		echo "</tr>";
	}
	echo "</table>";

}

// Grid_Flattener
// $flat_grid is the 1-d version (ie. rowless) of the array containing all coordinate in grid
// IN: Nothing; GLOBAL: grid; OUT: 1-d array
function Grid_Flattener(){
	global $grid;
	foreach ($grid as $row) {
		foreach ($row as $item) {
			$items_in_grid[] = $item;
		}
	}
	return $items_in_grid;
}

// MakeCoordGrid
// $coord_grid is the map of where all the coordinate pairs should go 
// IN: Nothing. GLOBAL: Size. OUT: Array of coordinate pairs. 
function MakeCoordGrid() {
	global $size;
	for ($i = 0; $i < $size; $i++){
		for ($j = 0; $j < $size; $j++){
		$coord_grid[] = array($i, $j);
		}
	}
	return $coord_grid;
}


// SCORING & RESULTS

// Wordlist_Scorer
// Given a list of words, converts the strings to arrays for use in rest of program; outputs a table in which to print words and customized messages from Word_Decider call; and totals the scores of the acceptable words. 
// IN: input list; GLOBAL: all preferences; OUT: nothing
function Wordlist_Scorer($input_list){
	global $seed, $distro, $minimum, $timerl, $fscore;
	$runningtotal = 0;
	$checked_words = array();

	echo "<table>";		// Create table of answers
	foreach ($input_list as $item) {
		echo "<tr><td valign=top><p><b>";
		echo $item; 
		echo "</b></td><td valign=top>";
		$decisionarray = Word_Decider($item, $checked_words);
		$checked_words = $decisionarray[1];
		$runningtotal = $runningtotal + $decisionarray[0];
		echo "</td></tr>";
	}
	echo "</table>";
	echo "<p><font size=+1>Your Score: ".$runningtotal."</font>";
	if (isset($fscore)) {
		echo "<p><font size=+1 color=#666666>Score to Beat: ".$fscore."</font>";
		if ($runningtotal > $fscore) { 
			echo "<p>Congratulations! Go rub it in your friend's face.";
			}
		elseif ($runningtotal == $fscore) {
			echo "<p>Tie! You guys are evenly matched.";
			}
		else {
			echo "<p>Better luck next time.";
			} 
	}
	

	$thisurl = "http://".$_SERVER['HTTP_HOST']."/laura/wordfind/?seed=".$seed."&distro=".$distro."&lettermin=".$minimum."&timerl=".$timerl."&fscore=".$runningtotal;
	echo "<p>Want to play this board against a friend? Send them to: <a href=".$thisurl.">".$thisurl."</a>";
	echo "<p>Alternately, you can <a href=http://".$_SERVER['HTTP_HOST']."/laura/wordfind/";
	if (WORKSAFE == 1) { echo "?worksafe=1"; }
	echo ">play again</a>.";

}


// Word_Decider
// Given a word, outputs customized error messages and stops if any of the letters are not on the board (from an Array_Onboard call), or if the word is not in the dictionary (from a Dictionary_Checker call), or if the letters in the word are nonadjacent (from an Array_Adjacent_Huh call). Outputs a word score from a Score_Up call if the word is on the board. 
// IN: string, array of checked words; GLOBAL: minimum;  OUT: Nothing or an array of (score, checked words array)
 function Word_Decider($input_string, $checked_words){
	global $minimum;
	$num_letters = strlen($input_string);

	// Word Deciding!
	if ($num_letters < $minimum) {
		echo "The word is not long enough. Words must be at least ".$minimum." letters long.";
		}
	elseif (in_array($input_string, $checked_words)) {
		echo "The word has already been counted.";
		}
	else {			
		$input_array = str_split(preg_replace('/QU+/', "Q", strtoupper($input_string))); // A version of the input string which is (1) all upper case, (2) replaced all QU with Q, (3) split into an array.
		$wordscore = 0;	// Initialize score for word

		if (Array_Onboard($input_array) != $input_array) {
			echo "The word contains letters not on the board, so the word is illegal."; 
			 }
		else {
			$stringlower = strtolower($input_string);	// Creates a lower case version of input string for use by dictionary
			if (Dictionary_Checker($stringlower) == false) {
				echo "The word is not in the dictionary, so the word is illegal.";
				}
			else {
				if (Array_Adjacent_Huh($input_array)) {
					$checked_words[] = $input_string;
					$wordscore = Score_Up($num_letters);
					echo "<br>Your score for this word is ".$wordscore.".";
					}	
				else {
					echo "<br>The letters are not adjacent on the board, so the word is illegal."; 
					}
				}
			}
		}
		
	return array($wordscore, $checked_words);
}


// Score_Up
// Given a number of letters, returns a score. NOTE: THIS SHOULD EVENTUALLY SCALE UP WITH MINIMUM NUMBER OF LETTERS? MAYBE?
// IN: integer (number of letters in a given word); OUT: integer (score)
function Score_Up($num_letters) {
	if ($num_letters <=4) {
		$wordscore = 1;
	}
	elseif ($num_letters == 5) {
		$wordscore = 2;
	}
	elseif ($num_letters == 6) {
		$wordscore = 3;
	}
	elseif ($num_letters == 7) {
		$wordscore = 5;
	}
	elseif ($num_letters == 8) {
		$wordscore = 8;
	}
	elseif ($num_letters >= 9) {
		$wordscore = 11;
	}
	return $wordscore;
}



// CHECK IF LETTER IS ON BOARD

// Array_Onboard
// Returns array if every letter in the array is on the grid. Otherwise, returns the first letter that was not on the grid. Calls Letter_Onboard_Huh.
// IN: array;  OUT: array
function Array_Onboard($array){
	$new_old_array = null;
	foreach ($array as $letter) {
		if (Letter_Onboard_Huh($letter)) {
			$new_old_array[] = $letter;
		}
		else { 
			break;
		}
	}
	return $new_old_array;
}


// Letter_Onboard_Huh
// Tests if a given letter is on the board.
// IN: letter; GLOBAL: flat grid; OUT: boolean
function Letter_Onboard_Huh($letter){
	global $flat_grid;
	if (in_array ($letter, $flat_grid)){
		return true;
	}
	else {
		return false;
	}
}



// CHECK IF WORD IS IN DICTIONARY

// Dictionary_Checker
// Returns true if string is in dictionary file. 
// IN: string; OUT: true or false
function Dictionary_Checker($string) {

	// Open dictionary file
	$dictionary_file = "twl.txt";
	$fh = fopen($dictionary_file, 'r');

	// Starting values of high, mid, low point of dictionary file
	$filesize = filesize($dictionary_file);
	$lowpoint = 0;
	$highpoint = $filesize;
	$midpoint = intval(($lowpoint+$highpoint)/2); // Typecast avg as integer
	
	// Zero in on approximate area by halving dictionary 20 times
	for ($i = 0; $i < 20; $i++) {
		list($position, $result) = Word_Finder($midpoint, $string, $fh);	// Get results of Word_Finder and assign them to variables $position, $result
		if ($result == 3) { // Just right
		
			if (TESTING == 1) {
				echo "Check it out, I randomly flipped to the right word in the dictionary.<br>";
			}		

			return true;			
		}
		elseif ($result == 2) { // Too high
		
			if (TESTING == 1) {
				echo "Too far. Let me flip back a bit.";
				}		
	
			$highpoint = $midpoint;
		}
		else {  // Too low

			if (TESTING == 1) {
				echo "Not far enough. Let me flip ahead a bit.";
				}		
	
			$lowpoint = $midpoint;
		}
	
	$midpoint = intval(($lowpoint+$highpoint)/2); // Get new midpoint
	}

	// If you haven't found the word yet, go back far enough to catch previous word
	$lowpoint = $lowpoint - 30;
	if ($lowpoint < 0) {
		$lowpoint = 0;
		}

	fseek($fh, $lowpoint); // Go to the place to start looking
	if ($lowpoint != 0) {
		fgets($fh); // (Except at beginning of dictionary,) get a line so next fgets starts looking at beginning of a line.
	}

	while(!feof($fh)) { // While not at end of file,
		$current_word = trim(fgets($fh));
		if (TESTING == 1) {
			echo "<br>Currently checking ".$string." against ".$current_word."... ";
			}

		if ($current_word == $string) {
			if (TESTING == 1) {
				echo "I found it in the dictionary!<br>";
				}

			return true; // Return true if you hit the word
			}
		elseif ($current_word > $string) { // Return false if you pass where the word should be
			if (TESTING == 1) {
				echo "I did not find it in the dictionary.<br>";
				}

			return false;
			}
		}	
	
	// Close dictionary file
	fclose($fh);
}


// Word_Finder
// Checks a position in a file and determines whether the word is there, higher, or lower. 
// IN: position, word, file; OUT: array of position, integer representing too high (1), too low (2), or just right (3)
function Word_Finder($position, $word, $file) {

	fseek($file, $position); // Find line at position
	fgets($file);  // Get the line at the position
	$current_line = trim(fgets($file)); // Current line removes extranous whitespace
	
	if (TESTING == 1) {
		echo "<br>Checking ".$word." against ".$current_line.". ";  
		}	

	if ($current_line < $word) {
		$result = 1;
		}
	elseif ($current_line > $word) {
		$result = 2;
		}
	else {
		$result = 3;
		}
 
	return array(ftell($file), $result); // Return current position of file pointer, and result of higher-or-lower test
}



// CHECK IF ALL LETTERS ARE ADJACENT

// Array_Adjacent_Huh
// For each starting letter:
// Takes that letter as a given (adds to used_letters), and looks for a path from each instance of the next letter.
// As soon as it finds a complete path from Pathfinder, returns true.
// If Pathfinder returns a -1, calls itself with the current used_letters and a shifted array.
// In: Word array, optional used array; OUT: true or false
function Array_Adjacent_Huh($array) {

	$allfirsts = Letter_to_Coord($array[0]);
	$first = $allfirsts[0];

	$used_letters = Pathfinder($first, array_slice($array, 1), array($first));	// Set used letters as the string resulting from this first, the rest of the array, and a used-letters array containing only this first
	
	if (!in_array(-1, $used_letters)) {		// Success? Great.
		if (TESTING == 1) echo "<br>Great, found a complete string from the first letter.";
		Tiny_Gridder($used_letters, "#0000ff");
		return true;
			}
	else {
		if (TESTING == 1) {
			echo "About to run Backtracker with this dead-end path: ";
			Tiny_Gridder($used_letters, "#ff0000");
			}

		if (Backtracker($used_letters, $array) == true) {
			if (TESTING == 1) echo "Backtracker returned true.";
			return true;
			}
		else {
			if (TESTING == 1) echo "Backtracker returned false.";
			return false;
			}
	}


}



// Backstracker
// Sets right a path that once went wrong (ie, path ending in -1).
// Using the letter of the last coord in the path, and the second-last coord in the path, checks for other acceptable neighbors.
// For each it finds, replaces the last item in the path with the new item, and runs Pathfinder from there.
// If not, calls itself.
// If path is empty, returns false.
// Returns true if Pathfinder returns a complete path (no -1).
// IN: a dead-ended used_letters; the rest of the array

function Backtracker($badpath, $array, $blacklist = array()) {

	if (empty($badpath)) {
		return false;
		}
	if (count($badpath) == 1) {
		if ($badpath[0] == -1) {
			return false;
			}
		}
	else {
		$minusone = array_pop($badpath);
		$deadend = array_pop($badpath);
		$lastletter = Coord_to_Letter($deadend);
		$blacklist[] = $deadend;
		
		$retries = Acceptable_Instances($lastletter, $badpath);

		// Filter out the retries that are blacklisted

		if (empty($retries)) {
			$good_retries = array();
			}
		else {
			foreach($retries as $retry) {
				if (!in_array($retry, $blacklist)) {
						$good_retries[] = $retry;
						}
					}
			}
	
		if (empty($good_retries)) {
				$progress = count($badpath);
				$restarray = array_slice($array, $progress);
				$badpath[] = -1; // Add this for to be cut off when Backtracker runs again.

			if (TESTING == 1) {
				echo "Found no acceptable instances in filtered retry list. Stepping back again.";
				echo "Current badpath: ";
				Tiny_Gridder($badpath, "#00aa00");
				echo "Progress Index: ".$progress;
				echo "<br>Restarray: ";
				print_r($restarray);
				}
			if (Backtracker($badpath, $array, $blacklist)) {
				return true;
				}
			}
		else {

			foreach ($good_retries as $retry) {

				$badpath[] = $retry;
				$progress = count($badpath);
				$restarray = array_slice($array, $progress);
	
				if (TESTING == 1) {
					echo "Current blacklist: ";
					Tiny_Gridder($blacklist, "#ff0000");
					echo "About to pathfind with this array: ";
					Tiny_Gridder($badpath, "#009900");
					echo "... and this restarray: ";
					print_r($restarray);
					}
				
				$newpath = Pathfinder($retry, $restarray, $badpath);

				if (!in_array(-1, $newpath)) {
					if (TESTING == 1) echo "Great, found a complete string.";
					Tiny_Gridder($newpath, "#0000ff");
					return true;
					}
				else {
					if (TESTING == 1) {
						echo "Found no acceptable instances from Pathfinder. Stepping back again.";
						}
					if (Backtracker($newpath, $array, $blacklist)) {
						return true;
						}
					else {
						continue;
						}
					}
				} // end of for-loop
			}
		}
	}



// Pathfinder
// Given a starting coordinate, the rest of a word, and a used-letters array,
// uses Pair_Adjacent to test if the first of the rest of the word is next to the starting coordinate.
// If Pair Adjacent returns -1, adds -1 to the used_letters array and returns it to Array_Adjacent_Huh.
// If Pair Adjacent returns a coordinate, however, adds that coordinate to used_letters,
// then calls itself using that coordinate and the rest of the array
// until it runs out of array, in which case it returns the complete used-letters array.
// In: coordinates of starting point, array of rest of word, array of used letters; OUT: array of used letters
function Pathfinder($firstcoord, $restarray, $used_letters) {
	
	if (count($restarray) < 1) {
		if (TESTING == 1) {
			echo "<br>No more pairs to check.";
		}
		return $used_letters;
	}
	else {
		$firstletter = array_shift($restarray);
		$next_coord = Pair_Adjacent($firstcoord, $firstletter, $used_letters);
		$used_letters[] = $next_coord;

		if ($next_coord == -1) {
			if (TESTING == 1) {
				echo "<br>Found a dead end.";
			}
			return $used_letters;
		}
		else {
			if (TESTING == 1) {
				Tiny_Gridder($used_letters, "#00ff00");
				echo "<br>Found a valid pair. Continuing... ";
			}
			return Pathfinder($next_coord, $restarray, $used_letters);
		}
	}
}


// Pair_Adjacent
// Given a starting coordinate, a letter, and a used_letters array,
// find the neighbors of the starting coordinate,
// removes any neighbors which have already been used in used_letters,
// and checks the remaining array for a match with the target letter.
// If it finds a match, returns the coordinate of the matching letter.
// Else, returns false.
function Pair_Adjacent($coord, $letter, $used_letters) {
	$Neighbors = Neighbor_Lister($coord);		// Find the neighbors of the coordinate pair

	if (TESTING == 1) {
		echo "<br>PAIR ADJACENT";
		$letter_of_coord = Coord_to_Letter($coord);
		echo "<br>Starting centerletter is ".$letter_of_coord." and the acceptable neighbors of that are ";
		Tiny_Gridder($Neighbors, "#aa00aa");
		}

	foreach ($Neighbors as $neighbor) {		
		$neighborletter = Coord_to_Letter($neighbor);	
		if (TESTING == 1) {
			echo "<br>Is ".$neighborletter." the same as ".$letter."? ";
			}

		if ($neighborletter == $letter) {		
			if (TESTING == 1) {
				echo "Yes! Has it been used? ";
				}
			if (in_array($neighbor, $used_letters)) {
				if (TESTING == 1) {
					echo "Oh, has already been used. Trying next neighbor...";
					}
				continue;
				}
			else {
				if (TESTING == 1) {
					echo "Nope! Great! Returning the coordinates of this ".$neighborletter.". ";
					}
			return $neighbor;			// Return the coordinate
			}
		}
		else {
			if (TESTING == 1) {
				echo "No. Trying next neighbor... ";
				}
			continue;
			}
	}
	if (TESTING == 1) {
		echo "Found no neighbor of ".Coord_to_Letter($coord)." which was ".$letter.". ";
		}
	return -1;
}

// Acceptable_Instances
// Given a letter and a list of used coordinates, returns ALL the coordinates of that letter on board 
// that are neighbors of the last item in the coordinate list. 
// If the coordinate is a blank array, returns all the coordinates of that letter on the board.
// IN: letter, coord pair; OUT: array of coord pairs
function Acceptable_Instances($letter, $coordlist) {
	if (empty($coordlist)) {
		return Letter_to_Coord($letter);
		}
	else {
		
		// Find neighbors of last item in coordlist
		$lastitem = end($coordlist);
		$Coord_Neighbors = Neighbor_Lister($lastitem);
		
		// Make a list of the neighbors that are the right letter
		foreach ($Coord_Neighbors as $Neighbor) {
			$Neighletter = Coord_to_Letter($Neighbor);
			if ($Neighletter == $letter) {
				$acc_instances[] = $Neighbor;
				}
			}
			

		if (TESTING == 1) {
			echo "Acc Instances received this used-letters list: ";
			Tiny_Gridder($coordlist, "#00aa00");
			echo " Acc instances named this the final letter: ".Coord_to_Letter($lastitem);
			echo "<br>Acceptable instances of ".$letter;
			Tiny_Gridder($acc_instances, "#cccccc");
			}
		return $acc_instances;
			
		}
}


// Tiny_Gridder
// Makes a small version of the board with all the letters in a given array printed in a given color.
// IN: array of coordinate pairs, color; GLOBAL: gird; OUT: no value/action (prints board)
function Tiny_Gridder($used_coords, $color) {
	global $coord_grid;
	global $size;

	// Create flat array of either colored or plain html letters associated with the given coords.
	foreach ($coord_grid as $coord) {
		$assoc_letter = Coord_to_Letter($coord);
		$assoc_letter = Qize($assoc_letter);
		if (in_array($coord, $used_coords)) {
			$all_letters[] = "<font color=".$color.">".$assoc_letter."</font>";
		}
		else {
			$all_letters[] = $assoc_letter;
		}
	}

	$count = 0;		// Initiatlize loop counter

	// Create table.  
		echo "<table>";
		for ($i = 0; $i < $size; $i++) {
			echo "<tr>";
			for ($j = 0; $j < $size; $j++) {
				echo "<td class=tinyboard>".$all_letters[$count]."</font></b></td>";
				$count++;
				}
			echo "</tr>";
			}
		echo "</table>";
	}



// Neighbor_Lister
// Given a coordinate pair, creates an array of all the coordinate pairs which would surround it on a grid. (Each coordinate pair is itself an array.)
// IN: coordinate pair; GLOBAL: size; OUT: array of all the possible neighbor coord pairs

function Neighbor_Lister($coord) {
	global $size;
	$xval = $coord[0];
	$yval = $coord[1];
	$Neighbors = array(
	    array($xval - 1, $yval - 1),
	    array($xval - 1, $yval),
	    array($xval - 1, $yval + 1),
	    array($xval, $yval - 1),
	    array($xval, $yval + 1),
	    array($xval + 1, $yval - 1),
	    array($xval + 1, $yval),
	    array($xval + 1, $yval + 1)
	    );
	// This removes any instances which exceed the boundaries of the board.
	foreach ($Neighbors as $key => $neighbor) {
		if (($neighbor[0] < 0) || $neighbor[0] >= $size || ($neighbor[1] < 0) || $neighbor[1] >= $size) {
			unset($Neighbors[$key]);
		}	
	}
	return $Neighbors;
		
}

// Coord_to_Letter
// Given a coordinate pair, returns the letter associated with the coordinate pair on the board. If the xvalue exceeds the number of elements in the first row of the grid, or the yvalue exceeds the number of rows in the grid, returns null.
// IN: coordinate pair; GLOBAL: grid; OUT: letter or null
function Coord_to_Letter($coord) {
	global $grid;
	$xval = $coord[0];	// Get x value out of input pair array
	$yval = $coord[1];      // Get y value out of input pair array
if ($xval < count($grid[0]) && $yval < count($grid)) {
	return $grid[$xval][$yval];
	}
else {
	return null;
	}
}

// Letter_to_Coord
// Given a letter, returns an array containing each of the coordinate values of instances of that letter on the board.
// IN: letter; GLOBAL: coord_grid, flat_grid; OUT: array of arrays (each of the sub-arrays contains two values, x and y value)
function Letter_to_Coord($letter){
	global $coord_grid;
	global $flat_grid;
	$Loc_in_Flatgrid = array_keys($flat_grid, $letter);
	foreach ($Loc_in_Flatgrid as $value) {
		$Coords_of_Letter[] = $coord_grid[$value];
	}
	return $Coords_of_Letter;
}




// Time the program.
$endtime=time()+microtime();
$totaltime=$endtime-$starttime;
//echo "<p>Run time: ".$totaltime;
?>

</div>
</body>
</html>
