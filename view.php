<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of ereflect
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package   mod_ereflect
 * @copyright 2013 G.Roberts Cardiff Met
 * @license   
 */
 
/// (Replace ereflect with the name of your module and remove this line)
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

$grdebug = false;
if($grdebug)
{
    echo 'In view.php - showing posted values<br />';
    echo '<pre>';
    print_r($_REQUEST);
    echo '</pre>';	
}
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot . '/mod/ereflect/class/locallib.php');

//$id = required_param('id', PARAM_INT);
//$n  = optional_param('n', 0, PARAM_INT);

// GR to check at some stage if n is required !?

$urlparams = array( 'id' => required_param('id', PARAM_INT),
		    'n' => optional_param('n', 0, PARAM_INT),
		    'action' => optional_param('action', '', PARAM_TEXT),
		    'eq_id' => optional_param('eq_id', null, PARAM_INT),
		    'questionbank_eq_id' => optional_param('questionbank_eq_id', null, PARAM_INT),
		    /*'user_id' => optional_param('user_id', null, PARAM_INT),*/
		    'view' => optional_param('view', null, PARAM_TEXT),
                    'pageno' => optional_param('pageno', null, PARAM_INT),
    		    'ejournal_id' => optional_param('ejournal_id', null, PARAM_INT),
		    'student_id' => optional_param('student_id', null, PARAM_INT));
				  
/*'ereflect_id' => optional_param('ereflect_id', null, PARAM_INT),*/				  

if($grdebug)
{
    echo 'In view.php - showing posted values<br />';
    echo '<pre>';
    print_r($urlparams);
    echo '</pre>';	
}


//if ($id) 
if ($urlparams['id'])
{
    //$cm     = get_coursemodule_from_id('ereflect', $id, 0, false, MUST_EXIST); // gets coursemodule description based on the id of the coursemodule
    $cm     = get_coursemodule_from_id('ereflect', $urlparams['id'], 0, false, MUST_EXIST); // gets coursemodule description based on the id of the coursemodule
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $gr  	= $DB->get_record('ereflect', array('id' => $cm->instance), '*', MUST_EXIST);	
} elseif ($n) {
    //$gr	= $DB->get_record('ereflect', array('id' => $n), '*', MUST_EXIST);
    $gr		= $DB->get_record('ereflect', array('id' => $urlparams['n']), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $ereflect->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('ereflect', $ereflect->id, $course->id, false, MUST_EXIST);
	
} else {
    error('You must specify a course_module ID or an instance ID');
}

$url = new moodle_url('/mod/assign/view.php', $urlparams);
require_login($course, true, $cm);
$PAGE->set_url($url);

if($grdebug)
{
    echo 'after require_login <br />';
}
//$context = get_context_instance(CONTEXT_MODULE, $cm->id);
$context = context_module::instance($cm->id);

require_capability('mod/ereflect:view', $context);

add_to_log($course->id, 'ereflect', 'view', "view.php?id={$cm->id}", $gr->name, $cm->id);

if($grdebug)
{
    echo 'About to print context <br />';
    echo '<pre>';
    print_r($context);
    echo '</pre>';
    echo 'after add_to_log <br />';
}

$ereflect = new ereflect($context, $cm, $course);

if($grdebug)
{
    echo 'new instance of ereflect <br />';
    echo '<pre>';
    print_r($ereflect);
    echo '</pre>';
}


$params = new stdClass();		
foreach($urlparams as $key => $value)
{
	$params->$key = $value;
}

if($grdebug)
{
	echo 'params to pass into view are <br />';
	echo '<pre>';
	print_r($params);
	echo '</pre>';
}



$completion=new completion_info($course);
$completion->set_module_viewed($cm);

if($grdebug)
{
	echo 'new instance of completion_info <br />';
	echo '<pre>';
	print_r($completion);
	echo '</pre>';
}


echo $ereflect->view($params);
