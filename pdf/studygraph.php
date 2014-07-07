<?php
//if (!defined('MOODLE_INTERNAL')) {
  //  die('Direct access to this script is forbidden.'); // It must be included from view.php
//}
require_once("../../../config.php");
require_once("$CFG->libdir/pdflib.php");
//require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot . '/mod/ereflect/class/locallib.php');
require_once($CFG->dirroot . '/mod/ereflect/class/chart.php');

$debug = false;

/* Test for Bar Chart*/

$keyvalues = array( 'Suggested Hours' => '15', 'Actual Hours' => '13'	); 	// Required  are an array of values for X/Y Axis
$graph = new chart($keyvalues);
//$graph->imgheight = 700; //optional
//$graph->imgwidth = 100; //optional
//$graph->margins = 50;
//$graph->barwidth = 20;
$studygraph = $graph->create_bar_graph('graemesbargraph.png', 20, 5);
//return($studygraph);


/* Test for Line Graph */
$keyvalues = array( 'Mark 1' => '61', 'Mark 2' => '46', 'Mark 3' => '71'	); 		
// these are default values and so don't need to be mentioned
		
$graph = new chart($keyvalues);
$markgraph = $graph->create_line_graph('graemeslinegraph.png');

//return($markgraph);




/*		
$urlparams = array(	'id' => required_param('id', PARAM_INT),
					'user_id' => optional_param('user_id', null, PARAM_INT));

if($debug)
{
	echo 'In studygraph.php - showing posted values<br />';
	echo '<pre>';
	print_r($urlparams);
	echo '</pre>';	
}

if ($urlparams['id'])
{
	$cm     = get_coursemodule_from_id('ereflect', $urlparams['id'], 0, false, MUST_EXIST); // gets coursemodule description based on the id of the coursemodule
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
	$gr  	= $DB->get_record('ereflect', array('id' => $cm->instance), '*', MUST_EXIST);
}

$context = context_module::instance($cm->id);
require_capability('mod/ereflect:view', $context);

//add_to_log($course->id, 'ereflect', 'view', "ereflectpdf.php?id={$cm->id}&user_id={}", $gr->name, $cm->id);

$ereflect = new ereflect($context, $cm, $course);

$d = new stdClass();
if(isset($urlparams['user_id']) && strlen($urlparams['user_id']))
{
	//$ereflect->add_addanswers_form_elements($mform, $data, $parameters, $ereflect_questions, $ereflect_user_response);

	$d = $ereflect->get_student_data($urlparams['user_id']);
	
	$table = 'user';	
	$conditions = array('id' => $urlparams['user_id']);
	$user = $DB->get_record($table, $conditions);
}
else
{
	if($debug)
	{
		echo 'No Data available';
	}
}

if($debug)
{
	echo 'Student Data returned<br />';
	echo '<pre>';
	print_r($d);
	echo '</pre>';	
	echo '<hr />';
	echo 'ereflect<br />';
	echo '<pre>';
	echo 'ereflect details <br />';
	print_r($ereflect);
	echo '</pre>';	
	echo '<hr />';
	echo 'User Details: <br />';
	echo '<pre>';
	print_r($user);
	echo '</pre>';	
	echo '<hr />';
	
}

// This works... attempt to wrap it up into a class to call

$values=array(
	"Jan" => 110,
	"Feb" => 130,
	"Mar" => 215,
	"Apr" => 81,
	"May" => 310,
	"Jun" => 110,
	"Jul" => 190,
	"Aug" => 175,
	"Sep" => 390,
	"Oct" => 286,
	"Nov" => 150,
	"Dec" => 196
); 

//Now define the size of image, i have used an image of size 600x400 for this tutorial.
$img_width=600;
$img_height=400; 

//The graph we are going to create has a border around it, i have declared a variable $margins to create that border around the four sides. 
$margins=20;

//Now find the size of graph by subtracting the size of borders.  
$graph_width=$img_width - $margins * 2;
$graph_height=$img_height - $margins * 2; 

// Create an image of the size defined above 
$img=imagecreate($img_width,$img_height);

//Define the width of bar. Gap between the bars will depend upon the width and number of bars and the gaps will be one more than the total number of bars as there is gap on the right and left of the graph. You can see in our example, we have 12 bars but 13 gaps, thats why you see ($total_bars+1) in the denominator. 

$bar_width=20;
$total_bars=count($values);
$gap= ($graph_width- $total_bars * $bar_width ) / ($total_bars +1); 
 
// Define colors to be used in the graph 
$bar_color=imagecolorallocate($img,0,64,128);
$background_color=imagecolorallocate($img,240,240,255);
$border_color=imagecolorallocate($img,200,200,200);
$line_color=imagecolorallocate($img,220,220,220); 

// Create a border around the graph by filling in rectangle 
imagefilledrectangle($img,1,1,$img_width-2,$img_height-2,$border_color);
imagefilledrectangle($img,$margins,$margins,$img_width-1-$margins,$img_height-1-$margins,$background_color); 

// Now the maximum value is required to adjust the scale. Ratio is calculated by dividing graph height by maximum graph value. Each value will be multiplied with ratio, so that no bar goes beyond the graph height. 
$max_value=max($values);
$ratio= $graph_height/$max_value; 

//Drawing horizontal lines is optional, note that the margin variable is subtracted from image height so that first line is positioned inside the graph area (Discarding the margins). 
//If you have trouble understanding this code, use a paper and pencil to manually find the values of variable at each repitition of the loop 

$horizontal_lines=20;
$horizontal_gap=$graph_height/$horizontal_lines;
for($i=1;$i<=$horizontal_lines;$i++)
{
	$y=$img_height - $margins - $horizontal_gap * $i ;
	imageline($img,$margins,$y,$img_width-$margins,$y,$line_color);
	$v=intval($horizontal_gap * $i /$ratio);
	imagestring($img,0,5,$y-5,$v,$bar_color);
}

//Here comes the most crucial part of our graph, drawing the bars. Each of the 8 lines in the for loop are individually explained below 
//1. Extract key and value pair from the current pointer position, each iteration of loop moves the internal pointer of array to the next entry
//2. The x1 value (i.e. left) of each bar gets and increment by $gap+$bar_width with each iteration of loop
//3. The x2 value (i.e. right) is calculated by adding bar width with x1 
//4. y1 is the top of each bar. ratio is multiplied with individual values to mare sure that bars remain inside the graph boundries.
//5. y2 (i.e bottom) is fix for all bars. Can also be placed outside the loop 
//6. Draw the graph with calculated left, top, right and bottom positions 
//7. The numeric value of each bar is shown at the top. Some plus or minus will be required to center align the displayed value with the bar
//8. Display the legend i.e. Month names 

for($i=0;$i< $total_bars; $i++){
	list($key,$value)=each($values);
	$x1= $margins + $gap + $i * ($gap+$bar_width) ;
	$x2= $x1 + $bar_width;
	$y1=$margins +$graph_height- intval($value * $ratio) ;
	$y2=$img_height-$margins;
	imagefilledrectangle($img,$x1,$y1,$x2,$y2,$bar_color);
	imagestring($img,0,$x1+3,$y1-10,$value,$bar_color);
	imagestring($img,0,$x1+3,$img_height-15,$key,$bar_color);
} 

//echo 'K_PATH_CACHE Path is: '.K_PATH_MAIN.'cache/ <br />';
//echo 'Moodledata cache path is: '.$_SERVER['DOCUMENT_ROOT']./'MoodleData/Cache/tcpdf <br />';
//echo '$CFG cachedir is '.$CFG->cachedir.'<br />';

//header("Content-type:image/png");
//imagepng($img, $CFG->cachedir.'/tcpdf'); 
//header("Content-type:image/jpeg");
//imagejpeg($img);  this works
//imagejpeg($img, $CFG->cachedir.'/tcpdf/simpletest.jpg');

//imagepng($img, $CFG->cachedir.'/tcpdf/simpletest.png'); 
imagepng($img);

//error_reporting(1);
//ob_clean();
//ob_start();
//imagepng($img);
//$image_data = ob_get_contents();
//ob_end_clean();
*/
 
