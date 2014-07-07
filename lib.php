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
 * Library of interface functions and constants for module ereflect
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the ereflect specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_ereflect
 * @copyright  2013 Graeme Roberts  Cardiff Met
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** example constant */
//define('ereflect_ULTIMATE_ANSWER', 42);

////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function ereflect_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:         return true;
        case FEATURE_SHOW_DESCRIPTION:  return true;
        case FEATURE_BACKUP_MOODLE2:    return true;
            
        default:                        return null;
    }
}

/**
 * Saves a new instance of the ereflect into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $ereflect An object from the form in mod_form.php
 * @param mod_ereflect_mod_form $mform
 * @return int The id of the newly inserted ereflect record
 */
function ereflect_add_instance(stdClass $ereflect, mod_ereflect_mod_form $mform = null) {
	
    global $CFG, $DB;
    require_once(dirname(__FILE__) . '/class/locallib.php');
		
    $grdebug = false;

    if($grdebug)
    {
        echo 'in ereflect_add_instance <br />';
    }

    $ereflect->timecreated = time();
    $ereflect->completion_message  = '';          // updated later    
	
    if($grdebug)
    {
        echo 'time created = '.$ereflect->timecreated.', before insert_record<br />';

        echo '<pre>';
        print_r($ereflect);
        echo '</pre>';
    }
        
    // Setting of opendate and closedate if the check boxes are empty
    if (empty($ereflect->useopendate)) {
        $ereflect->opendate = 0;
        //echo 'set opendate to zero<br />';
    }
    if (empty($ereflect->useclosedate)) {
        $ereflect->closedate = 0;
        //echo 'set closedate to zero<br />';
    }
        
    # You may have to add extra stuff in here #
    //return $DB->insert_record('ereflect', $ereflect);
    $ereflect->id = $DB->insert_record('ereflect', $ereflect);
	
    if($grdebug)
    {
        echo 'after insert_record<br />';	
    }
	
    // new stuff goes here for update of editor 	
    // we need to use context now, so we need to make sure all needed info is already in db
    $cmid = $ereflect->coursemodule;
	
    $DB->set_field('course_modules', 'instance', $ereflect->id, array('id' => $cmid));
    $context = context_module::instance($cmid);
	
    if($grdebug)
    {
        echo '<pre>';	
        print_r($context);
        echo '</pre>';
     }	

        
    if ($draftitemid = $ereflect->completion_message_editor['itemid']) {
                
        $ereflect->completion_message = file_save_draft_area_files($draftitemid, $context->id, 'mod_ereflect', 'completion_message',
                0, ereflect::instruction_editors_options($context), $ereflect->completion_message_editor['text']);        

        // re-save the record with the replaced URLs in editor fields
        $DB->update_record('ereflect', $ereflect);
        
    }


    return $ereflect->id;	
}

/**
 * Updates an instance of the ereflect in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $ereflect An object from the form in mod_form.php
 * @param mod_ereflect_mod_form $mform
 * @return boolean Success/Fail
 */
function ereflect_update_instance(stdClass $ereflect, mod_ereflect_mod_form $mform = null) {
    global $DB;

    $ereflect->timemodified = time();
    $ereflect->id = $ereflect->instance;

    // Setting of opendate and closedate if the check boxes are empty
    if (empty($ereflect->useopendate)) {
        $ereflect->opendate = 0;
    }
    if (empty($ereflect->useclosedate)) {
        $ereflect->closedate = 0;
    }    

    //return $DB->update_record('ereflect', $ereflect);
    if($DB->update_record('ereflect', $ereflect))
    {
        $context = context_module::instance($ereflect->coursemodule);    

        if ($draftitemid = $ereflect->completion_message_editor['itemid']) {    
            $ereflect->completion_message = file_save_draft_area_files($draftitemid, $context->id, 'mod_ereflect', 'completion_message',
                    0, ereflect::instruction_editors_options($context), $ereflect->completion_message_editor['text']);
        }


        // re-save the record with the replaced URLs in editor fields
        $DB->update_record('ereflect', $ereflect);
    }
    
    return true;
}

/**
 * Removes an instance of the ereflect from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function ereflect_delete_instance($id) {
    global $DB;

    if (! $ereflect = $DB->get_record('ereflect', array('id' => $id))) {
        return false;
    }

    # Delete any dependent records here #

    $DB->delete_records('ereflect', array('id' => $ereflect->id));

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function ereflect_user_outline($course, $user, $mod, $ereflect) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $ereflect the module instance record
 * @return void, is supposed to echp directly
 */
function ereflect_user_complete($course, $user, $mod, $ereflect) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in ereflect activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function ereflect_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link ereflect_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function ereflect_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see ereflect_get_recent_mod_activity()}

 * @return void
 */
function ereflect_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function ereflect_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function ereflect_get_extra_capabilities() {
    return array();
}

////////////////////////////////////////////////////////////////////////////////
// Gradebook API                                                              //
////////////////////////////////////////////////////////////////////////////////

/**
 * Is a given scale used by the instance of ereflect?
 *
 * This function returns if a scale is being used by one ereflect
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $ereflectid ID of an instance of this module
 * @return bool true if the scale is used by the given ereflect instance
 */
function ereflect_scale_used($ereflectid, $scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists('ereflect', array('id' => $ereflectid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of ereflect.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any ereflect instance
 */
function ereflect_scale_used_anywhere($scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists('ereflect', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the give ereflect instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $ereflect instance object with extra cmidnumber and modname property
 * @param mixed optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return void
 */
function ereflect_grade_item_update(stdClass $ereflect, $grades=null) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    /** @example */
    $item = array();
    $item['itemname'] = clean_param($ereflect->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;
    $item['grademax']  = $ereflect->grade;
    $item['grademin']  = 0;

    grade_update('mod/ereflect', $ereflect->course, 'mod', 'ereflect', $ereflect->id, 0, null, $item);
}

/**
 * Update ereflect grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $ereflect instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function ereflect_update_grades(stdClass $ereflect, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    /** @example */
    $grades = array(); // populate array of grade objects indexed by userid

    grade_update('mod/ereflect', $ereflect->course, 'mod', 'ereflect', $ereflect->id, 0, $grades);
}

////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function ereflect_get_file_areas($course, $cm, $context) {
    
    $areas = array();
    $areas['completion_message']          = get_string('completion_message', 'ereflect');
    $areas['feedback_message']          = get_string('feedback_message', 'ereflect');
    
    return $areas;

}

/**
 * File browsing support for ereflect file areas
 *
 * @package mod_ereflect
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function ereflect_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {

    global $CFG, $DB, $USER;
    
    $fs = get_file_storage();    
    
    $arrayareas = array("completion_message","feedback_message");
    
    if(!in_array($filearea, $arrayareas))
    {
        return null;
    }
    
    // always only itemid 0
    $filepath = is_null($filepath) ? '/' : $filepath;
    $filename = is_null($filename) ? '.' : $filename;

    $urlbase = $CFG->wwwroot.'/pluginfile.php';
    if (!$storedfile = $fs->get_file($context->id, 'mod_ereflect', $filearea, $itemid, $filepath, $filename)) {
        if ($filepath === '/' and $filename === '.') {
            $storedfile = new virtual_root_file($context->id, 'mod_ereflect', $filearea, $itemid);
        } else {
            // not found
            return null;
        }
    }
    return new file_info_stored($browser, $context, $storedfile, $urlbase, $areas[$filearea], false, true, true, false);
    
}

/**
 * Serves the files from the ereflect file areas
 *
 * @package mod_ereflect
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the ereflect's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */

function ereflect_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    $debug = false;
    
    if($debug){
        echo 'In ereflect_pluginfile<br />';

        echo 'course<br />';
        echo '<pre>';
        print_r($course);
        echo '</pre>';

        echo 'cm<br />';
        echo '<pre>';
        print_r($cm);
        echo '</pre>';

        echo 'context<br />';
        echo '<pre>';
        print_r($context);
        echo '</pre>';

        echo 'Filearea: '.$filearea.'<br />';

        echo 'Args<br />';
        echo '<pre>';
        print_r($args);
        echo '</pre>';

        echo 'Forcedownload: '.$forcedownload.'<br />';
        
        echo 'Options Array<br />';
        echo '<pre>';
        print_r($options);
        echo '</pre>';
        
       
    }
    
    
    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);
    
    $arrayareas = array("completion_message","feedback_message");    
    
    //$arrayareas = array("completion_message");    
    if(!in_array($filearea, $arrayareas))
    {
        return false;
    }    
    
    $eqid = (int)array_shift($args);    
    
    //array_shift($args); // itemid is ignored here
    $relativepath = implode('/', $args);

    if($debug) {
        echo 'Eq id: '.$eqid.'<br />';
        echo 'Relativepath: '.$relativepath.'<br />';
        echo 'filearea: '.$filearea.'<br />';
        exit();
    }
    
    if(isset($eqid) && strlen($eqid))
    {
        $fullpath = "/$context->id/mod_ereflect/$filearea/$eqid/$relativepath";
    }
    else {
        $fullpath = "/$context->id/mod_ereflect/$filearea/0/$relativepath";
    }

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        send_file_not_found();
    }

    // finally send the file
    send_stored_file($file, null, 0, $forcedownload, $options);
}

////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding ereflect nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the ereflect module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function ereflect_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the ereflect settings
 *
 * This function is called when the context for the page is a ereflect module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $ereflectnode {@link navigation_node}
 */
function ereflect_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $ereflectnode=null) {
}


function ereflect_print_overview( $courses, &$htmlarray )
{
    global $USER, $CFG, $DB;
    //require_once($CFG->libdir.'/gradelib.php');
    
    $debug = false;
    
    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return array();
    }

    if($debug)
    {
        /*echo 'Ejournal<br />';
        echo '<pre>';
        print_r($ejournal);
        echo '</pre>';*/
            
        echo 'In ejournal_print_overview:<br />Courses;<br />';
        echo '<pre>';
        print_r($courses);
        echo '</pre>';
        echo 'HTMLarray;<br />';
        echo '<pre>';
        print_r($htmlarray);
        echo '</pre>';     
    }
    
    if (!$ereflects = get_all_instances_in_courses('ereflect',$courses)) {
        return;
    }
    
    if($debug)
    {
        echo 'All ereflects in courses<br />';
        echo '<pre>';
        print_r($ereflects);
        echo '</pre>';    
    }
    
    // if the ereflect is open i.e. between opendate and closedate
    // OR 

    // Do assignment_base::isopen() here without loading the whole thing for speed.
    foreach ($ereflects as $key => $ereflect) {        
                
        // Check eReflect questionnaire is in corret status i.e. Student Entry
        if( isset($ereflect->status) && $ereflect->status != 'STUDENTENTRY')
        {
            continue;
        }                
        
        //$ereflectids[] = $ereflect->id;
        //$totalpostcount = 0;  // POST count      
        
        if($debug)
        {
            echo 'Ereflect<br />';
            echo '<pre>';
            print_r($ereflect);
            echo '</pre>';
            
            echo 'Now Using ereflect id: '.$ereflect->id.' - '.$ereflect->name.'<br />';
        }
              
        // Definitely something to print, now include the constants we need.
        require_once($CFG->dirroot . '/mod/ereflect/class/locallib.php');                              
        
        $cmid = $ereflect->coursemodule;	
        $cm   = get_coursemodule_from_id('ereflect', $cmid, 0, false, MUST_EXIST); // gets coursemodule description based on the id of the coursemodule    
        $context = context_module::instance($cmid);
        $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        
        $strereflect = get_string('modulename', 'ereflect');
        $strduedate = get_string('duedate', 'ereflect');
        $strduedateno = get_string('duedateno', 'ereflect');
        
        $ereflectc = new ereflect($context, $cm, $course);
        
        // For Teacher
        //   do a count of students that have not completed the eReflect questionnaire
        //    i.e. if count of students > records in mdl_reflect_user_response then show count        
        if($ereflectc->isateacher( $USER->id )) {
         
            // select count from mdl_ereflect_user_response where status = 'COMPLETED'
            // select count of students on the course
            // if counts do not equal then
                       
            // Student enrolled on Course
            $usersarr = $ereflectc->users_who_can_complete();
            $noofstudents = count($usersarr);
            
            $table = 'ereflect_user_response';
            $conditions = array('ereflect_id' => $ereflect->id, 'status' => 'COMPLETED'); // count existing number of questions
            $countrecs = $DB->count_records($table, $conditions);	
            
            if($debug)
            {
                echo 'Users Array:<br />';
                echo '<pre>';
                print_r($usersarr);
                echo '</pre>';
                echo 'No of Students: '.$noofstudents.'<br />';
                echo 'Count Completed: '.$countrecs.'<br />';
            }
            
            // Compare students enrolled to user response table that are completed
            // if not equal then we need to show for Teachers to be aware
            $str = '';            
            if($countrecs != $noofstudents)
            {   
                $dimmedclass = '';
                if (!$ereflect->visible) {
                    $dimmedclass = ' class="dimmed"';
                }
                    
                $urlparams = array('id' => $ereflect->coursemodule, 'ereflect_id' => $ereflect->id );
                $href = new moodle_url('/mod/ereflect/view.php', $urlparams);
                //$href = $CFG->wwwroot . '/mod/ejournal/view.php?id=' . $ejournal->coursemodule .
                //            '&ereflect_id=' . $ereflect->id . '&' ;
                    
                $str = '<div class="ereflect overview">' .
                        '<div class="name">' .$strereflect . ': ' .
                            '<a ' . $dimmedclass . 'title="' . $strereflect . '" ' .
                                'href="' . $href . '">' .format_string($ereflect->name) . '</a></div>';
                    if (isset($ereflect->closedate) && $ereflect->closedate != 0) {
                        $closedate = userdate($ereflect->closedate);
                        $str .= '<div class="info">' . $strduedate . ': ' . $closedate . '</div>';
                    } else {
                        $str .= '<div class="info">' . $strduedateno . '</div>';
                    }
                $outst = $noofstudents - $countrecs;
                $str .= '<div class="info">' . get_string('strpostsoutst','ereflect', $outst) . '</div>';
                $str .= '</div> <!-- ejournal overview -->';   
                
                if (empty($htmlarray[$ereflect->course]['ereflect'])) {
                    $htmlarray[$ereflect->course]['ereflect'] = $str;
                } else {
                    $htmlarray[$ereflect->course]['ereflect'] .= $str;
                }                       
                
            }
        } // is a teacher
        else {
            // is probably a student 
            // for eReflect 
            // 
            // For student
            //  if there is not a record in mdl_ereflect_user_response OR there is but is 'PARTIAL'
            //  and the ereflect status = 'STUDENTENTRY' then need to show the eReflect
            //             
            if (has_capability('mod/ereflect:submit', $context)) 
            { 
                // is a student
                $table = 'ereflect_user_response';
                $conditions = array('ereflect_id' => $ereflect->id, 'status' => 'COMPLETED', 'user_id' => $USER->id); // count existing number of questions
                $countur = $DB->count_records($table, $conditions);	                
                
                $str = '';                
                
                if($countur==0)
                {
                    $dimmedclass = '';
                    if (!$ereflect->visible) {
                        $dimmedclass = ' class="dimmed"';
                    }

                    $urlparams = array('id' => $ereflect->coursemodule, 'ereflect_id' => $ereflect->id );
                    $href = new moodle_url('/mod/ereflect/view.php', $urlparams);                    
                    
                    $str = '<div class="ereflect overview">' .
                        '<div class="name">' .$strereflect . ': ' .
                            '<a ' . $dimmedclass . 'title="' . $strereflect . '" ' .
                                'href="' . $href . '">' .format_string($ereflect->name) . '</a></div>';
                    if (isset($ereflect->closedate) && $ereflect->closedate != 0) {
                        $closedate = userdate($ereflect->closedate);
                        $str .= '<div class="info">' . $strduedate . ': ' . $closedate . '</div>';
                    } else {
                        $str .= '<div class="info">' . $strduedateno . '</div>';
                    }       
                    $str .= '</div> <!-- ejournal overview -->';                     
                    
                    if (empty($htmlarray[$ereflect->course]['ereflect'])) {
                        $htmlarray[$ereflect->course]['ereflect'] = $str;
                    } else {
                        $htmlarray[$ereflect->course]['ereflect'] .= $str;
                    }                       

                }
            }
        }
                 
      
    } // looping through eReflects 
        
    if($debug)
    {
        echo 'htmlarray <br />';
        echo '<pre>';
        print_r($htmlarray);
        echo '</pre>';
    }        
            
    if (empty($ereflectids)) {
        // No eJournals to look at - we're done.
        return true;
    }    
}