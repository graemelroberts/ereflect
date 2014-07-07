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
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package   mod_ereflect
 * @copyright 2013 G.Roberts Cardiff Met
 * @license   
*/
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot . '/mod/ereflect/class/locallib.php');

// Debug parameter
$debug = false;

if($debug)
{
    echo 'In index.php <br />';
}   

$id = required_param('id', PARAM_INT);   // course

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error('invalidcourseid');
}

require_course_login($course);

$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/ereflect/index.php', array('id' => $id));
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));

$PAGE->requires->css('/mod/ereflect/styles/styles.css'); 
$PAGE->requires->css('/mod/ereflect/styles/ie9.css'); 
$PAGE->requires->js('/mod/ereflect/js/general.js',true);

//$PAGE->set_context($context);
$PAGE->get_renderer('mod_ereflect');

if($debug)
{
    
    echo 'Course details:<br /><pre>';
    print_r($course);
    echo '</pre>';
}

global $DB;  

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'ereflect'), 2);

$sql = 'SELECT e.id, cm.id coursemoduleid, e.name 
        FROM {ereflect} e
        JOIN mdl_course_modules cm ON (e.course = cm.course AND e.id = cm.instance)
        JOIN mdl_modules m ON (cm.module = m.id AND m.name = \'ereflect\')
        WHERE e.course = ?
        AND e.status = \'STUDENTENTRY\'
        ORDER BY e.timecreated';
					
$eref = $DB->get_records_sql($sql, array($id));
        
if($debug)
{
    echo 'Ereflect entries: <br />';
    echo '<pre>';
    print_r($eref);
    echo '</pre>';            
}

$erffarr2 = array();

foreach($eref as $erffarr)
{
    if($debug)
    {
        echo '<pre>';
        print_r($erffarr);
        echo '</pre>';
    }

    // Its an object within an array
    $id = $erffarr->id;
    $cm_id = $erffarr->coursemoduleid;
    $name = $erffarr->name;

    $idvalue = $id.':'.$cm_id;
    $erffarr2[$idvalue] = $name;
}
                
$field = 'ereflect_id';        
$attributes = array();
$selected = array();




echo '<div class="div_field_row"><label>'.get_string('ereflect','ereflect').'</label>';
echo html_writer::select( $erffarr2, $field, 2 );
        
//$urlparams = array('id' => $id, 'action'=>'');
$urlparams = array('action'=>null);  // Set the id in the general.js
$ereflecturl = new moodle_url('/mod/ereflect/view.php', $urlparams);
$ereflect = '<button id="id_ereflectgo" onclick="ereflectgo(\''.$ereflecturl.'\');">Go</button>';
        
echo $ereflect;
echo '</div>';        

echo $OUTPUT->footer();