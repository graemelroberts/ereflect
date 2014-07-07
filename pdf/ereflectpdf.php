<?php
//if (!defined('MOODLE_INTERNAL')) {
  //  die('Direct access to this script is forbidden.'); // It must be included from view.php
//}
require_once("../../../config.php");
//require_once($CFG->dirroot . '/mod/ereflect/pdf/pdfconf.php') // unique plugin config file

//require_once("$CFG->libdir/pdflib.php");
//require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot . '/mod/ereflect/class/locallib.php');
require_once($CFG->dirroot . '/mod/ereflect/class/chart.php');

$debug = false;

$urlparams = array(	'id' => required_param('id', PARAM_INT),
					'user_id' => optional_param('user_id', null, PARAM_INT));

if($debug)
{
	echo 'In ereflectpdf.php - showing posted values<br />';
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
$instance = $ereflect->get_instance();

$d = new stdClass();
if(isset($urlparams['user_id']) && strlen($urlparams['user_id']))
{
	$user_id = $urlparams['user_id'];
}
else
{
	echo 'User not available';
}
$ereflect->print_pdf( $user_id , 'I', 'grtest.pdf');

/***********************
make_cache_directory('tcpdf');

define ('PDF_HEADER_STRING', "\n".$cm->name); // header description string

//$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);
$pdf = new PDF(PAGE_ORIENTATION, PDF_UNIT, PAGE_FORMAT, true, 'UTF-8', false, true); // Portrait

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor(PDF_AUTHOR);
$pdf->SetTitle(PDF_HEADER_TITLE);
//$pdf->setPrintHeader(true);
//$pdf->setPrintFooter(true);

// set default header data
//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING, array(1,44,86), array(1,44,86));
//$pdf->setFooterData(array(0,64,0), array(0,64,128));

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
//if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
//    require_once(dirname(__FILE__).'/lang/eng.php');
//    $pdf->setLanguageArray($l);
//}

$pdf->AddPage();

$pdf->SetTextColor(1,44,86); // Cardiff Met Blue

$pdf->SetFont(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN);

// Set some content to print
$html = <<<EOD
<div style="margin-bottom: 100px;">Please find the questions and your answers included along with some feedback.<br />
After reviewing this document, can you please enter any further comments within the E-Journal.</div>
EOD;


// Print text using writeHTMLCell()
$pdf->writeHTMLCell(0, 20, '', '', $html, 0, 1, 0, true, '', true);
//$pdf->Write(0, $html, '', 0, 'C', true, 0, false, false, 0);

$tbl = '<table border="0" cellpadding="2">
 <tr>
  <td width="20%" style="height:50px;"><b>Name</b></td>
  <td width="30%" style="height:50px;">'.$user->firstname.' '.$user->lastname.'</td>
  <td width="20%" style="height:50px;"><b>School</b></td>  
  <td width="30%" style="height:50px;">&nbsp;</td>    
 </tr>
 <tr>
  <td width="20%" style="height:50px;"><b>Course</b></td>
  <td width="30%" style="height:50px;">'.$course->fullname.'</td>
  <td width="20%" style="height:50px;"><b>Module</b></td>  
  <td width="30%" style="height:50px;">Topic 1?etc</td>    
 </tr> 
 <tr>
  <td width="20%" style="height:50px;"><b>Assessment</b></td>
  <td width="30%" style="height:50px;">'.$ereflect->coursemodule->name.'</td>
  <td width="20%" style="height:50px;"><b>Due Date</b></td>  
  <td width="30%" style="height:50px;">20th March 2011</td>    
 </tr> 
</table>';

$pdf->writeHTML($tbl, true, false, false, false, '');

//2nd Page

$pdf->AddPage();

$pdf->SetFont(PDF_FONT_NAME_MAIN, PDF_FONT_STYLE_BOLD, PDF_FONT_SIZE_MAIN+6);
$pdf->Write(0, 'Student Feedback', '', 0, 'C', true, 0, false, false, 0);


//$custx = 20;
//$custy = 30;

$pdf->SetFont(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN);

$tbl = '<table border="1" cellpadding="2" >
<thead>
 <tr>
  <td width="4%" align="right">&nbsp;</td>
  <td width="32%"><b>Question</b></td>
  <td width="32%"><b>Answer</b></td>
  <td width="32%"><b>Feedback</b></td>
 </tr>
</thead>';

//question_text, meq.open_question, meo.option_answer, meo.option_feedback

foreach($d as $key => $value)
{
	$tbl .= '
 <tr>
  <td width="4%" align="center">'.$key.'</td>
  <td width="32%">'.$value->question_text.'</td>
  <td width="32%">'.$value->option_answer.'</td>
  <td width="32%">'.$value->option_feedback.'</td>
 </tr>
 ';
}
 $tbl .= '
</table>';

$pdf->writeHTML($tbl, true, false, false, false, '');


// Only Include Student Study Prep time Bar Graph if its set 
if($instance->include_preptime_in_report==1)
{
	$preptime = $instance->preparationtime/60/60;
	$actualtime = $response->assignment_time/60/60;
	//echo 'Prep Time: '.$preptime.'<br />';
	
	if(isset($preptime) && strlen($preptime))
	{
		ob_clean();
		ob_start();
		
		// Set Variables for Bar Graph
		$keyvalues = array( 'Suggested Hours' => $preptime, 'Actual Hours' => $actualtime	); 		
		// these are default values and so don't need to be mentioned
		
		$graph = new chart($keyvalues);
		$studygraph = $graph->create_bar_graph('graemesgraph.png');						
		ob_end_clean();

		if(!empty($studygraph))
		{
			$pdf->AddPage();
			$pdf->SetFont(PDF_FONT_NAME_MAIN, PDF_FONT_STYLE_BOLD, PDF_FONT_SIZE_MAIN+6);
			$pdf->Write(0, 'Student Study Graph', '', 0, 'C', true, 0, false, false, 0);
			
			
			//$pdf->Image($studygraph);
			//Image ( $file, $x='', $y='', $w=0, $h=0, 
			//        $type='', $link='', $align='', $resize=false, $dpi=300, 
			//        $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, 
			//        $hidden=false, $fitonpage=false, $alt=false, $altimgs=array())
			
			$pdf->Image($studygraph, 15, 45, '', '', 'PNG', '', '', true, 150, 'C', false, false, 1, false, false, true);
		}
	}
}

if($instance->include_mark_in_linegraph==1)
{
	//$preptime = $instance->preparationtime/60/60;
	//$actualtime = $response->assignment_time/60/60;
	//echo 'Prep Time: '.$preptime.'<br />';
	
	if($debug){echo 'User id: '.$USER->id.', Ereflect Id: '.$instance->id.' <br />';}
	
	$sql = "SELECT er.id, er.name, eur.grade 
				FROM mdl_ereflect_user_response eur
				JOIN mdl_ereflect er ON  eur.ereflect_id = er.id
				WHERE eur.user_id = 10
				AND eur.status = 'COMPLETED'
				ORDER BY eur.timecreated";
		
	if($eq = $DB->get_records_sql($sql, array($USER->id, $instance->id)))
	{
		$n = 0;
		$eqarr = array();
		foreach($eq as $key => $value)
		{
			$n++;
			if($debug)
			{
				echo 'In loop '.$n.'<br />';
				echo '<pre>';
				print_r($value);
				echo '</pre>';
			}
			$eqarr["Mark $n"] = $value->grade;
		}
	}
	
	if($debug)
	{
		echo '<hr />';
		echo 'Getting User Response marks';
		echo '<pre>';
		print_r($eqarr);
		echo '</pre>';
	}
	
	//if(count($eqarr)>1) only if more than one mark !?
	{
		ob_clean();
		ob_start();
				
		// Set Variables for Line Graph
		//$keyvalues = array( 'Mark 1' => '61', 'Mark 2' => '46', 'Mark 3' => '71'	); 		
		//$graph = new chart($keyvalues);
		$graph = new chart($eqarr);
		$graph->maxvalue = 100;		
		$markgraph = $graph->create_line_graph('graemeslinegraph.png', $horizontallines = 10);
		ob_end_clean();

		if(!empty($markgraph))
		{
			$pdf->AddPage();
			$pdf->SetFont(PDF_FONT_NAME_MAIN, PDF_FONT_STYLE_BOLD, PDF_FONT_SIZE_MAIN+6);
			$pdf->Write(0, 'Assignment Graph', '', 0, 'C', true, 0, false, false, 0);
			
			
			//$pdf->Image($studygraph);
			//Image ( $file, $x='', $y='', $w=0, $h=0, 
			//        $type='', $link='', $align='', $resize=false, $dpi=300, 
			//        $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, 
			//        $hidden=false, $fitonpage=false, $alt=false, $altimgs=array())
			
			$pdf->Image($markgraph, 15, 45, '', '', 'PNG', '', '', true, 150, 'C', false, false, 1, false, false, true);
		}
	}
}

$filename = 'ereflect_'.$instance->id.'_user_'.$user->id.'.pdf';
$copyfolder = $CFG->cachedir.'/tcpdf/'.$filename;		
//$pdf->Output($copyfolder, 'F'); // output to file
$pdf->Output($copyfolder, 'I'); // open in browser
*/

