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
 * Internal library of functions for module ereflect    
 * 
 * 
 * All the ereflect specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package   mod_ereflect
 * @copyright 2013 G.Roberts Cardiff Met
 * @license   
 */
 
defined('MOODLE_INTERNAL') || die();

//require_once(dirname(__FILE__).'/lib.php');     // we extend this library here
//require_once($CFG->libdir . '/gradelib.php');   // we use some rounding and comparing routines here
//require_once($CFG->libdir . '/filelib.php');

require_once($CFG->dirroot . '/mod/ereflect/class/renderable.php');
require_once($CFG->libdir . '/formslib.php');

/** @var string action to be used to return to this page
*              (without repeating any form submissions etc).
*/


/**
 * Does something really useful with the passed things
 *
 * @param array $things
 * @return object
 */
 
//function wiggy_do_something_useful(array $things) {
//    return new stdClass();
//}


//
// Graeme_r This is based on locallib.php from the Assign module i.e. mod\assign\locallib.php
//

class ereflect {

    /** @var stdClass the assignment record that contains the global settings for this assign instance */
    private $instance;
	
    /** @var assign_renderer the custom renderer for this module */
    private $output;
	
	
    private $returnaction = 'view';
	
	
	/** @var string instructions for the submission phase */
    public $completion_message;
	
	
    /**
     * Constructor for the base ereflect class.
     *
     * @param mixed $coursemodulecontext context|null the course module context
     *                                   (or the course context if the coursemodule has not been
     *                                   created yet).
     * @param mixed $coursemodule the current course module if it was already loaded,
     *                            otherwise this class will load one from the context as required.
     * @param mixed $course the current course  if it was already loaded,
     *                      otherwise this class will load one from the context as required.
     */
    public function __construct ($coursemodulecontext, $coursemodule, $course ) {
	
        global $PAGE;

        $this->context = $coursemodulecontext;
        $this->coursemodule = $coursemodule;
        $this->course = $course;

        // Load the capabilities for this user and questionnaire, if not creating a new one.
        if (!empty($this->coursemodule->id)) {
		
            //echo 'coursemodule id: '.$this->coursemodule->id.'<br />';
		
            //$this->capabilities = questionnaire_load_capabilities($this->coursemodule->id);
            $this->capabilities = $this->questionnaire_load_capabilities($this->coursemodule->id);

            /*echo '<pre>';
            print_r($this->capabilities);
            echo '</pre>';*/
        }
		
        // Temporary cache only lives for a single request - used to reduce db lookups.
        $this->cache = array();

		// GR Look into this later
        //$this->submissionplugins = $this->load_plugins('assignsubmission');
        //$this->feedbackplugins = $this->load_plugins('assignfeedback');
    }
	
    public function view ( stdclass $parameters )
    {
        global $CFG, $DB, $USER;

        $debug = false;
        $o = '';	
        $mform 	= null;			
        $nextpageparams = array();	
        $notices = array();		

        $instance = $this->get_instance();
        $module = $this->get_course_module();
        $module_name = $module->modname;

        // Testing to see what priveleges are
        //$this->users_who_can_teach();
        //$this->users_who_can_complete();

        if($debug)
        {
            echo 'In locallib.view <br />';

            /*echo 'CFG first <br />';
            echo '<pre>';
            print_r($CFG);
            echo '</pre>';*/

            echo 'Instance: <br />';
            echo '<pre>';
            print_r($instance);
            echo '</pre>';

            echo 'Now Module <br />';			
            echo '<pre>';
            print_r($module);
            echo '</pre>';
            echo 'Module Name = '.$module_name.'<br />';

            echo '<hr />';
            echo 'Parameters: <br />';
            echo '<pre>';
            print_r($parameters);
            echo '</pre>';

            if ($this->capabilities->view) {echo 'User Has permissions <br />';}
            else{echo 'User Doesnt have permissions <br />';}

            if($this->capabilities->submit){echo 'User can submit<br />';}
            else{echo 'User cannot submit<br />';}

            if($this->capabilities->grade){echo 'User can grade<br />';}
            else{echo 'User cannot grade<br />';}	
            
        }	
		
        // GR New since passing in class of parameters into View function
        //$ereflect_id = $parameters->ereflect_id;
        //$eq_id = $parameters->eq_id;
        $action = strtoupper($parameters->action);

        // If status is not ready for Student to enter then Teacher cannot amend the Add Questions screen
        $parameters->amend = 'ON';

        // Want to go into Student Entry if status is correct
        if($debug){echo 'Action is '.$action.'<br />';}

        if($instance->status=='STUDENTENTRY')
        {
            if(isset($action) && strlen($action))
            {
                if($action=='ADDQUESTIONPROCESS')
                {
                    $action = '';  // Will redirect back to the View.php i.e. settings
                }
                else
                {
                    // If not about to place the Questionnaire process onhold for extra entry
                    // or the Student isn't trying to enter answers then ensure one cannot amend the addquestion screen
                    $action_arr = array();
                    $action_arr[] = 'ONHOLD_QUESTIONNAIRE_PROCESS'; // Change from Student entry to on Hld for Teacher to modify
                    $action_arr[] = 'ADDANSWERSPROCESS'; // Submission of answers
                    $action_arr[] = 'COMPLETE_STUDENT_PROCESS'; // Student completion of
                    $action_arr[] = 'ADDANSWERS'; // Return to add answers screen
                    $action_arr[] = 'RETURNTOMENU'; // Return to menu
                    $action_arr[] = 'VIEWSUMMARYSTATSPROCESS'; // View Summary Stats (back from Teachers View of Student information
                    $action_arr[] = 'VIEWSTUDENTANSWERS'; // Ability for Teachers to see Student Answers
                    $action_arr[] = 'VIEWSTUDENTANSWERSFROMEJOURNAL'; // Ability for Teachers to see Student Answers
                    $action_arr[] = 'VIEWEJOURNALPOST'; // Ability to return to ejournal post from ereflect
                    $action_arr[] = 'VIEWPDF'; // Ability to View the PDF
                    $action_arr[] = 'VIEWCOHORTREPORT';

                    if($debug)
                    {
                        echo 'Action array is <br />';
                        echo '<pre>';
                        print_r($action_arr);
                        echo '</pre>';
                    }

                    /*if(   $action != 'ONHOLD_QUESTIONNAIRE_PROCESS' && $action!= 'ADDANSWERSPROCESS'  && action != 'COMPLETE_STUDENT_PROCESS'
                       && $action !='ADDANSWERS' && $action != 'RETURNTOMENU') // these are both coming from save or cancel from 'ADDANSWERS' i.e. not first time.
                       */
                    if(!in_array($action,$action_arr))
                    {
                        $parameters->amend = 'OFF';				
                        $action = 'ADDQUESTION';
                        $notices[] = get_string('cannotamendmessage', 'mod_ereflect'); //Unable to Amend Questionnaire Details until questionnaire is placed on hold
                    }
                }
            }
            else  // $action is not set
            {	
                // If teacher OR management, then go to summary statistics page.....
                if($this->capabilities->grade)
                {
                    $action = 'VIEWSUMMARYSTATS';
                    if($debug){echo 'Is a Teacher <br />';}
                }
                else
                {                
                    // If Student then go to addanswers, (View or Edit Mode)
                    if($this->capabilities->submit)
                    {                    
                        $b_useopen = false;
                        $b_useclose = false;
                        $b_defaultform = false;

                        $opendate = 0;
                        $closedate = 0;
                        if($instance->opendate!=0)
                        {
                            $b_useopen = true;
                            //$opendate = format_time($instance->opendate, $datestring);
                        }
                        if($instance->closedate!=0)
                        {
                            $b_useclose = true;
                            //$closedate = format_time($instance->opendate, $datestring);
                        }
                    
                        $odate = getdate($instance->opendate);
                        $vopendate = str_pad($odate['mday'],2,"0", STR_PAD_LEFT).'/';                    
                        $vopendate .= str_pad($odate['mon'],2,"0", STR_PAD_LEFT).'/';
                        $vopendate .= str_pad($odate['year'],2,"0", STR_PAD_LEFT).' ';
                        $vopendate .= str_pad($odate['hours'],2,"0", STR_PAD_LEFT).':';
                        $vopendate .= str_pad($odate['minutes'],2,"0", STR_PAD_LEFT).':';
                        $vopendate .= str_pad($odate['seconds'],2,"0", STR_PAD_LEFT);

                        $cdate = getdate($instance->closedate);
                        $vclosedate = str_pad($cdate['mday'],2,"0", STR_PAD_LEFT).'/';                    
                        $vclosedate .= str_pad($cdate['mon'],2,"0", STR_PAD_LEFT).'/';
                        $vclosedate .= str_pad($cdate['year'],2,"0", STR_PAD_LEFT).' ';
                        $vclosedate .= str_pad($cdate['hours'],2,"0", STR_PAD_LEFT).':';
                        $vclosedate .= str_pad($cdate['minutes'],2,"0", STR_PAD_LEFT).':';
                        $vclosedate .= str_pad($cdate['seconds'],2,"0", STR_PAD_LEFT);
                    
                        //$vclosedate = $cdate['mday'].'/'.$cdate['mon'].'/'.$cdate['year'].' '.$cdate['hours'].':'.$cdate['minutes'].':'.$cdate['seconds'];

                        if($debug)
                        {
                            echo '<hr />';
                            echo 'Open Date values<br />';
                            echo '<pre>';
                            print_r($odate);
                            echo '</pre>';
                            echo '<hr />';
                            echo 'Close Date values<br />';
                            echo '<pre>';
                            print_r($cdate);
                            echo '</pre>';
                        }
                                                            
                        if($b_useopen && !(time() >= $instance->opendate) )
                        {
                            $action = 'VIEWNOTREADY';
                            // eReflect Questionnaire window has not begun ({$a})
                            $notices[] = get_string('opendateerror', 'mod_ereflect', $vopendate );                         

                            $b_defaultform = true;
                        }

                        if($b_useclose && !(time() <= $instance->closedate) )
                        {
                            $action = 'VIEWNOTREADY';
                            // eReflect Questionnaire window for Student entries ({$a}) has ended
                            $notices[] = get_string('closedateerror', 'mod_ereflect', $vclosedate );

                            $b_defaultform = true;
                        }
                    
                    
                        if($debug)
                        {
                            echo 'Open date: '.$instance->opendate.', Close Date: '.$instance->closedate.', Time: '.time().'<br />';
                        }

                        //First Time in from the menu
                        // If no action set but in student entry mode, then go to Student entry screen
                        if($b_defaultform)
                        {
                            $action = 'VIEWNOTREADY';
                        }
                        else
                        {
                            $action = 'ADDANSWERS';
                        }

                        if($debug){echo 'Is a student <br />';}
                    }  // Capabilities->submit
                }
                /*else
                {
                    // If teacher, then go to summary statistics page.....
                    if($this->capabilities->grade)
                    {
                        $action = 'VIEWSUMMARYSTATS';
                        if($debug){echo 'Is a Teacher <br />';}
                    }
                    else
                    {
                        echo 'Not a student or a Teacher - need to work on this<br />';
                    }
                }*/
            }
        }
        else
        {
            // Status of ereflect is 'INPROGRESS'
            
            $action_arr = array();
            $action_arr[] = 'RETURNTOMENU'; // Return to menu
            
            if(!in_array($action,$action_arr))
            {            
                // Teacher views the Add Question Page
                if($this->capabilities->grade)
                {        
                    //echo 'Status is '.$instance->status.'<br />';
                    if(!isset($action) || !strlen($action))
                    {
                        $action = 'ADDQUESTION';
                    }
                }
                else
                {
                    // Student Views the Assignment not ready page
                    // 
                    // The eReflect Questionnaire is not currently ready for student entry.
                    $notices[] = get_string('questionnairenotready','mod_ereflect');           
                    $action = 'VIEWNOTREADY';
                }
            }   
        }

        if($action == 'ADDQUESTIONFROMBANKPROCESS')
        {
            if($debug){$o .= 'In locallib.view and in action  '.$action;}

            $action = 'ADDQUESTION';	

            if ($this->process_addquestionfrombank($parameters, $notices))
            {
                // Ensure you redirect so that you can't get a resubmit
                if($debug){echo 'Passed process <br /> ';}

                $action = 'redirect';
                $nextpageparams['id'] = $module->id;				
                $nextpageparams['action'] = 'ADDQUESTION';
            }
        }						
        else if($action == 'ADDQUESTIONPROCESS') // coming from first part of adding questions
        {			
            if($debug){echo 'In locallib.view and in action ADDQUESTIONPROCESS <br />';}					
			
            $action = 'ADDQUESTION'; // original form submitted from
			
            if (optional_param('addquestion_cancelandreturn', null, PARAM_RAW)) 
            {			
                if($debug){echo 'Optional Parameter is addquestion_cancelandreturn <br /> ';}					
                $action = 'redirect';
                $nextpageparams['id'] = $module->id;				
                $nextpageparams['action'] = 'SETTINGS'; // Cancel should go to Settings Page
            }
            else if(optional_param('addquestion_saveandreturn', null, PARAM_RAW)) 
            {
                if($debug){echo 'Optional Parameter is addquestion_saveandreturn <br /> ';}								

                if ($this->process_add_question($mform, $notices))
                {
                    // Ensure you redirect so that you can't get a resubmit
                    if($debug){echo 'Passed process <br /> ';}

                    $action = 'redirect';
                    $nextpageparams['id'] = $module->id;				
                    $nextpageparams['action'] = 'ADDQUESTION'; // back to same page to show new question at top 
                }
            }
        }	
        else if($action == 'AMENDQUESTIONPROCESS')
        {
            $action = 'AMENDQUESTION';			

            if($debug){echo 'In locallib->view.AMENDQUESTIONPROCESS <br />';}

            if (optional_param('modifyquestion_cancelandreturn', null, PARAM_RAW)) 
            {
                $action = 'redirect';
                $nextpageparams['id'] = $module->id;				
                $nextpageparams['action'] = 'ADDQUESTION';				
            }
            else if (optional_param('modifyquestion_saveandreturn', null, PARAM_RAW)) 
            {
                if($debug){echo 'In Save and Return section <br />';}

                if ($this->process_amend_question($mform, $notices))
                {
                    // Ensure you redirect so that you can't get a resubmit
                    if($debug){echo 'Passed process <br /> ';}
             
                    $action = 'redirect';
                    $nextpageparams['id'] = $module->id;				
                    $nextpageparams['action'] = 'ADDQUESTION';
                }			
            }
        }
        else if($action == 'DELETEQUESTIONPROCESS')
        {
            if($debug){echo 'In DELETEQUESTIONPROCESS <br />';}

            $action = 'ADDQUESTION';			

            if ($this->process_delete_question($parameters, $notices))
            {
                $action = 'redirect';
                $nextpageparams['id'] = $module->id;				
                $nextpageparams['action'] = 'ADDQUESTION';								
            }
        }
        else if($action == 'ADDOPTIONSPROCESS')
        {
            if($debug){echo 'In ADDOPTIONSPROCESS <br />';}		

            $action = 'ADDOPTIONS';

            if (optional_param('addoptions_cancelandreturn', null, PARAM_RAW)) 
            {
                $action = 'redirect';
                $nextpageparams['id'] = $module->id;				
                $nextpageparams['action'] = 'ADDQUESTION';				
            }
            else if (optional_param('addoptions_saveandreturn', null, PARAM_RAW)) 
            {			
                //$params->eq_id = $eq_id;				

                if($debug){echo 'In Save and Return section of ADDOPTIONSPROCESS, eq_id = '.$parameters->eq_id.'<br />';}				

                if ($this->process_add_options($mform, $notices, $parameters))
                {
                    // Ensure you redirect so that you can't get a resubmit
                    if($debug){echo 'Passed process <br /> ';}

                    $action = 'redirect';
                    $nextpageparams['id'] = $module->id;				
                    $nextpageparams['action'] = 'ADDQUESTION';
                }
            }
        }
        else if($action == 'REORDERPROCESSUP')
        {
            //if($debug){echo 'In REORDERPROCESSUP for ereflect_id : '.$parameters->ereflect_id.', eq_id : '.$parameters->eq_id.' <br />';}
            if($debug){echo 'In REORDERPROCESSUP for ereflect_id : '.$instance->id.', eq_id : '.$parameters->eq_id.' <br />';}

            $action = 'ADDQUESTION';	

            $parameters->direction = 'UP';

            if ($this->process_update_question_order($parameters, $notices))
            {
                // Ensure you redirect so that you can't get a resubmit
                if($debug){echo 'Passed process <br /> ';}

                $action = 'redirect';
                $nextpageparams['id'] = $module->id;				
                $nextpageparams['action'] = 'ADDQUESTION';
            }
        }
        else if($action == 'REORDERPROCESSDOWN')
        {		
            if($debug){echo 'In REORDERPROCESSDOWN <br />';}						

            $action = 'ADDQUESTION';	

            $parameters->direction = 'DOWN';

            if ($this->process_update_question_order($parameters, $notices))
            {
                // Ensure you redirect so that you can't get a resubmit
                if($debug){echo 'Passed process <br /> ';}

                $action = 'redirect';
                $nextpageparams['id'] = $module->id;				
                $nextpageparams['action'] = 'ADDQUESTION';
            }						
        }
        else if($action == 'COMPLETE_QUESTIONNAIRE_PROCESS')
        {
            if($debug){echo 'In COMPLETE_QUESTIONNAIRE_PROCESS <br />';}

            $action = 'ADDQUESTION';

            if ($this->process_complete_questionnaire($notices))
            {
                // Ensure you redirect so that you can't get a resubmit
                if($debug){echo 'Passed process <br /> ';}

                $action = 'redirect';
                $nextpageparams['id'] = $module->id;				
                $nextpageparams['action'] = '';  // Should go to Settings Page
            }		
        }
        else if($action == 'ONHOLD_QUESTIONNAIRE_PROCESS')
        {
            if($debug){echo 'In ONHOLD_QUESTIONNAIRE_PROCESS <br />';}

            $action = 'ADDQUESTION';

            if ($this->process_onhold_questionnaire($notices))
            {
                // Ensure you redirect so that you can't get a resubmit
                if($debug){echo 'Passed process <br /> ';}

                $action = 'redirect';
                $nextpageparams['id'] = $module->id;
                $nextpageparams['action'] = 'ADDQUESTION';  // Should go to ADDQUESTION page
            }					
        }
        else if($action == 'ADDANSWERSPROCESS')
        {
            if($debug){echo 'In ADDANSWERSPROCESS <br />';}

            $action = 'ADDANSWERS';			

            if (optional_param('addanswers_cancelandreturn', null, PARAM_RAW)) 
            {
                $action = 'RETURNTOMENU';
            }
            else if (optional_param('viewejournal', null, PARAM_RAW)) 
            {                
                $action = 'VIEWEJOURNALPOST';
            }            
            else if(optional_param('addanswers_saveandreturn', null, PARAM_RAW))
            {
                if ($this->process_add_answers($mform, $notices, $parameters ))
                {
                    // Ensure you redirect so that you can't get a resubmit
                    if($debug){echo 'Passed process <br /> ';}

                    $action = 'redirect';
                    $nextpageparams['id'] = $module->id;
                    $nextpageparams['action'] = 'ADDANSWERS';  // Should go to Answers page automatically 
                    $nextpageparams['pageno'] = $parameters->pageno;
                    $nextpageparams['student_id'] = $parameters->student_id;                    
                    //$nextpageparams = $parameters;
                }
            }	
            else if(optional_param('addanswers_saveandsubmit', null, PARAM_RAW))
            {
                // if we have checked and saved the answers
                if ($this->process_add_answers($mform, $notices, $parameters ))
                {
                    // Ensure you redirect so that you can't get a resubmit
                    if($debug){echo 'Passed process <br /> ';}
                    
                    // we need to check and complete the answers
                    if ($this->process_complete_student_answers($notices))
                    {
                        // Ensure you redirect so that you can't get a resubmit
                        if($debug){echo 'Passed process <br /> ';}

                        $action = 'redirect';
                        $nextpageparams['id'] = $module->id;				
                        $nextpageparams['action'] = '';  // Should go to Results Page i.e. View Layout with PDF report
                    }		                    

                    /*$action = 'redirect';
                    $nextpageparams['id'] = $module->id;
                    $nextpageparams['action'] = 'ADDANSWERS';  // Should go to Answers page automatically */
                }
            }	
        }
        else if($action == 'COMPLETE_STUDENT_PROCESS')
        {		
            $action = 'ADDANSWERS';

            if ($this->process_complete_student_answers($notices))
            {
                // Ensure you redirect so that you can't get a resubmit
                if($debug){echo 'Passed process <br /> ';}

                $action = 'redirect';
                $nextpageparams['id'] = $module->id;				
                $nextpageparams['action'] = '';  // Should go to Results Page i.e. View Layout with PDF report
            }		
        }
        else if($action == 'VIEWSUMMARYSTATSPROCESS')  // iS A PROCSS COMMAND AS WELL I.E. SUBMITTING FROM FORM
        {
            if(optional_param('viewejournal', null, PARAM_RAW)) 
            {                
                $action = 'VIEWEJOURNALPOST';
            }            
            else
            {        
               if($debug){echo 'Hello we are in action of '.$action.'<br />';}
            
               $action = 'VIEWSUMMARYSTATS';
            }
        }        

        if($debug){echo 'action = '.$action.'<br />';}
		
        $returnparams = ''; /*'useridlistid'=>optional_param('useridlistid', 0, PARAM_INT));*/		
        $this->register_return_link($action, $returnparams);
                        

        //  Now show the right view pane
        if ($action == 'redirect') 
        {
            //echo 'In redirect <br />';
            $nextpageurl = new moodle_url('/mod/ereflect/view.php', $nextpageparams);

            //$nextpageurl = new moodle_url('/course/modedit.php', $nextpageparams);			
            //echo 'Next page url: '.$nextpageurl.'<br />';
            //exit();
            redirect($nextpageurl);
            return;
        }
        else if($action == 'SHOWQUESTIONBANKOPTIONS')
        {
            if($debug){$o .= 'In locallib.view with action of '.$action;}

            //$params->eq_id = $eq_id;
            //questionbank_eq_id

            $o .= $this->view_addquestion_page($mform, $notices, $parameters);		
        }
        /*else if($action == 'HIDEQUESTIONBANKOPTIONS')
        {
                if($debug){$o .= 'In locallib.view with action of '.$action;}

                //$params->eq_id = $eq_id;
                $parameters->questionbank_eq_id = '';

                $o .= $this->view_addquestion_page($mform, $notices, $parameters);		
        }*/			
        else if($action == 'USEQUESTIONFROMBANK')
        {
            if($debug){$o .= 'In locallib.view and in action  '.$action;}

            $action = 'ADDQUESTION';	

            $parameters->dataaction = 'USEQUESTIONFROMBANK';

            $o .= $this->view_addquestion_page($mform, $notices, $parameters);					
        }								

        else if($action == 'ADDQUESTION')
        {
            if($debug){$o .= 'In locallib.view with action of ADDQUESTION';}

            $o .= $this->view_addquestion_page($mform, $notices, $parameters);		
        }
        else if($action == 'SHOWOPTIONS')
        {
            if($debug){$o .= 'In locallib.view with action of SHOWOPTIONS';}

            $parameters->show_options_eq_id = $parameters->eq_id;

            $o .= $this->view_addquestion_page($mform, $notices, $parameters);		
        }
        else if($action == 'HIDEOPTIONS')
        {
            if($debug){$o .= 'In locallib.view with action of HIDEOPTIONS';}

            $parameters->show_options_eq_id = '';

            $o .= $this->view_addquestion_page($mform, $notices, $parameters);		
        }
        else if($action == 'AMENDQUESTION')
        {
            if($debug){$o .= 'Hello we are in AMENDQUESTION with eq_id of '.$eq_id.'<br />';}

            $o .= $this->view_modifyquestion_page($mform, $notices, $parameters);
        }
        else if($action == 'ADDOPTIONS')
        {
            if($debug){$o .= 'Hello we are in ADDOPTIONS<br />';}

            $o .= $this->view_edit_addoptions_page($mform, $notices, $parameters);		
        }
        /*else if($action == 'VIEWQUESTION')
        {
                $o .= $this->view_question_page();				
        }*/
        else if($action == 'ADDANSWERS')
        {
            if($debug){echo 'Hello we are in action of '.$action.'<br />';}

            // If a Student then ability to edit or view answers
            $o .= $this->view_answer_questions_page($mform, $notices, $parameters );
        }
        else if ($action == 'VIEWSUMMARYSTATS')
        {
           if($debug){echo 'Hello we are in action of '.$action.'<br />';}
            
           // If a Teacher then ability to view response statistics
           $o .= $this->view_response_stats($notices, $parameters );			
           
        }
        else if ($action == 'VIEWSTUDENTANSWERS')
        {
            if($debug){echo 'Hello we are in action of '.$action.'<br />';}

            if($this->capabilities->grade && isset($parameters->view) && $parameters->view == 'TEACHERVIEW')
            {	
                if($debug){echo 'Teacher has ability to grade and has come across view student answers button in correct place';}

                // If a Teacher then ability to view Student answers
                $o .= $this->view_answer_questions_page($mform, $notices, $parameters );
            }
            else
            {
                echo 'You don\'t have the ability to grade and view student answers';
            }						
        }
        else if ($action == 'VIEWSTUDENTANSWERSFROMEJOURNAL')
        {
            if($debug){echo 'Hello we are in action of '.$action.'<br />';}
            
            if(isset($parameters->view) && $parameters->view == 'EJOURNALVIEW')
            {
                // If a Teacher then ability to view Student answers
                $o .= $this->view_answer_questions_page($mform, $notices, $parameters );
            }
            else
            {
                echo 'You don\'t have access to view this information.';
            }						
        }        
        /*else if($action == 'VIEWEJOURNALPOST')  // Now not required
        {
            // Get the course module id for the ereflect in question
            // 
            $sql = 'SELECT e.id, cm.id coursemoduleid, e.name 
                    FROM mdl_ejournal e
                    JOIN mdl_course_modules cm ON (e.course = cm.course AND e.id = cm.instance)
                    JOIN mdl_modules m ON (cm.module = m.id AND m.name = \'ejournal\')
                    WHERE e.course = ?
                    AND EXISTS  ( SELECT * FROM mdl_ejournal_details j 
                                  WHERE j.ejournal_id = e.id 
                                  AND j.student_id = ?)';
            
            $ej = $DB->get_records_sql($sql, array($instance->course, $parameters->student_id));
            
            $eja = new stdClass();
            foreach ($ej as $eja)
            {
                //echo 'Key : '.$key.' Value : '.$value.'<br />';
                if($debug)
                {
                    echo 'in loop<br /><pre>';
                    print_r($eja);
                    echo '</pre>';
                }
            }

            if($debug)
            {                
                echo 'Parameters:<br />';
                echo '<pre>';
                print_r($parameters);
                echo '</pre>';
            }
                    
            $nextpageparams = array("id" => $eja->coursemoduleid, "student_id" => $parameters->student_id, "action" => "VIEWPOSTBYUSERFROMEREFLECT", "ereflect_id" => $instance->id );
            $nextpageurl = new moodle_url('/mod/ejournal/view.php', $nextpageparams);

            redirect($nextpageurl);
            return;            
            
        } */
        else if ($action == 'VIEWNOTREADY')
        {   
            $o .= $this->view_ereflect_not_ready( $mform, $notices );
        }
        else if ($action == 'VIEWPDF')
        {
            if($debug){echo 'Hello we are in action of '.$action.' with student_id of '.$parameters->student_id.'<br />';}
                $this->print_pdf( $parameters->student_id , 'I');
            }
        else if ($action == 'VIEWCOHORTREPORT')
        {
            //if($debug){echo 'Hello we are in action of '.$action.' with user_id of '.$parameters->user_id.'<br />';}
            // Then call a function below that will export to CSV
            $o .= $this->view_cohort_report();			
        }
        else if($action == 'RETURNTOMENU')
        {
            $course = $this->get_course();
            $section = 1;
            redirect(course_get_url($course, $section, array('sr' => 0)));
            return;
        }		
        else if($action == 'SETTINGS')
        {	
            //View Settings Page
            // 1 parameter
            $nextpageparams['update'] = $module->id;
            $nextpageparams['return'] = 0;

            // 2 or more parameters doesn't work !
            //$nextpageparams = array('update'=>$module->id,'return'=>0 );

            $nextpageurl = new moodle_url('/course/modedit.php', $nextpageparams);
			
            if($debug)
            {
                echo 'next page url = '.$nextpageurl.'<br />';
                //exit();			
            }
            redirect($nextpageurl);
            return;								
        }
        else
        {
            //View Settings Page
            // 1 parameter
            $nextpageparams['update'] = $module->id;
            $nextpageparams['return'] = 0;

            // 2 or more parameters doesn't work !
            //$nextpageparams = array('update'=>$module->id,'return'=>0 );
			
            $nextpageurl = new moodle_url('/course/modedit.php', $nextpageparams);
			
            if($debug)
            {
                echo 'next page url = '.$nextpageurl.'<br />';
                //exit();			
            }
            redirect($nextpageurl);
            return;							
        }
												
        return $o;
    }
	   
    private function view_ereflect_not_ready ( $mform, $notices )
    {
        global $CFG;

        require_once($CFG->dirroot . '/mod/ereflect/class/default_form.php');

        $instance = $this->get_instance();

        $o = '';	

        $o .= $this->get_renderer()->render(new ereflect_header($instance,
                                              $this->get_context(),
                                              $this->show_intro(),
                                              $this->get_course_module()->id,
                                              $this->get_course()),
                                              get_string('addfeedbackquestions','ereflect'));

        /* Notices - This is out of the box!! */
        foreach ($notices as $notice) {
            $o .= $this->get_renderer()->notification($notice);
        }

        $data = new stdClass();		
        if (!$mform) {
            $mform = new mod_ereflect_default_form(null, array($this, $data, $this->get_course_module()));
        }		

        $o .= $this->get_renderer()->render(new ereflect_form('defaultform', $mform));

        $o .= $this->view_footer();            

        return $o;
    }
    
    /* This is the main AddQuestion page */
    private function view_addquestion_page ($mform, $notices, $params ) {
        global $CFG, $DB, $USER, $PAGE;
		
        $debug = false;
		
        require_once($CFG->dirroot . '/mod/ereflect/class/addquestion_form.php');
		
        $instance = $this->get_instance();
        $context = $this->get_context();

        $o = '';	

        $o .= $this->get_renderer()->render(new ereflect_header($instance,
                                                $this->get_context(),
                                                $this->show_intro(),
                                                $this->get_course_module()->id,
                                                $this->get_course()),
                                                get_string('addfeedbackquestions','ereflect'));
													  		
	
        if($debug)
        {
            $o .= 'Debug On - About to enter a question in view_addquestion_page<br />';			
            echo 'Debug On - About to enter a question in view_addquestion_page - Parameter settings<br />';

            echo '<hr>';
            echo 'Parameters: <br />';
            echo '<pre>';
            print_r($params);
            echo '</pre>';
            echo '</hr>';			

            echo '<hr>';
            echo 'Instance: <br />';			
            echo '<pre>';
            print_r($instance);
            echo '</pre>';
            echo '</hr>';
        }

        /* Notices - This is out of the box!! */
        foreach ($notices as $notice) {
            $o .= $this->get_renderer()->notification($notice);
        }
		
        $ereflect_questions = new stdclass();
        //$ereflect_questions->ereflect_id = $this->get_course_module()->instance;				
        $ereflect_questions->ereflect_id = $instance->id;

        // Complete Questionnaire Section
        //$o .= $this->get_complete_button(ns, $params);

        $o .= '<hr />';		
        $o .= $this->get_complete_button($params);
        $o .= '<hr />';		

        // Get drop down list of Questions from question bank
        $o .= $this->output->container_start('questionbank');
        $o .= $this->output->heading(get_string('questionbank', 'ereflect'), 3);
        //$o .= $this->output->box_start('boxaligncenter addfeedbackquestions');
        //$o .= $this->get_question_bank(ect_questions, $params);		
        $o .= $this->get_question_bank($params);		
        //$o .= $this->output->box_end();
        $o .= $this->output->container_end();		

        $o .= '<hr />';

        $ereflect_questions->screen = 'ADDQUESTIONS';

        //if($debug){$o .= 'Id = '.$ereflect_questions->ereflect_id.'<br />';} // ereflect id
        if($debug){$o .= 'Id = '.$instance->id.'<br />';} // ereflect id

        // View Ereflect Questions table
        $o .= $this->show_ereflect_questions($ereflect_questions, $params, $mform);		
        
        $o .= '<hr />';		
		
        $eqvar = new stdClass();
        if(isset($params->dataaction) && strlen($params->dataaction) &&($params->dataaction=='USEQUESTIONFROMBANK')
           && isset($params->questionbank_eq_id) && strlen($params->questionbank_eq_id) )
        {
            if($debug){echo 'In right place for getting particular ereflect question details<br />';}

            $conditions = array('id'=>$params->questionbank_eq_id);
            $eq = $DB->get_record('ereflect_questions',$conditions);
            foreach ($eq as $key => $value)
            {
                $eqvar->$key = $value;
            }					
        }
				
        /*$entry = new stdClass();
        $entry->id = '';
        $feedback_message_options = array('trusttext'=>true, 'maxfiles'=>99, 'maxbytes'=>0, 'context'=>$context,
                            'subdirs'=>file_area_contains_subdirs($context, 'mod_ereflect', 'feedback_message', $entry->id));
        $entry = file_prepare_standard_editor($entry, 'feedback_message', $feedback_message_options, $context, 'mod_ereflect', 'entry', $entry->id);*/
        
        $data = new stdClass();	        
        if (!$mform) {
            //$mform = new mod_ereflect_addquestion_form(null, array($this, $data, $eqvar, $instance, $feedback_message_options));
            $mform = new mod_ereflect_addquestion_form(null, array($this, $data, $eqvar, $instance));
        }		
		
        $o .= $this->output->container_start('addfeedbackquestions');
        $o .= $this->output->heading(get_string('addfeedbackquestions', 'ereflect'), 3);
        //$o .= $this->output->box_start('boxaligncenter addfeedbackquestions');
		
        $o .= $this->get_renderer()->render(new ereflect_form('addfeedbackquestions', $mform));
		
        //$o .= $this->output->box_end();
        $o .= $this->output->container_end();

        //$o .= $this->output->confirm('Are you sure?', '/index.php?delete=1', '/index.php');

        $o .= $this->view_footer();

        return $o;
    }

	//protected function get_complete_button ( stdclass $ereflect_questions, stdclass $parameters )
    private function get_complete_button ( stdclass $parameters )
    {
        $debug = false;
        $o = '';
        $instance = $this->get_instance();

        if($debug)
        {
            echo 'in get_complete_button with parameter settings: <br />';
            echo '<pre>';
            print_r($parameters);
            echo '</pre>';
        }

        if($parameters->amend=='OFF')
        {
            //$urlparams = array('id' => $this->get_course_module()->id, 'ereflect_id' => $ereflect_questions->ereflect_id, 'action'=> 'ONHOLD_QUESTIONNAIRE_PROCESS');
            $urlparams = array('id' => $this->get_course_module()->id, 'action'=> 'ONHOLD_QUESTIONNAIRE_PROCESS');
            $completeurl = new moodle_url('/mod/ereflect/view.php', $urlparams);						
            $complete = '<button type="button" onclick="changestatus(\''.$completeurl.'\',\'amend\');">'.get_string('eqonhold', 'ereflect').'</button>';		
        }
        else
        {
            //$urlparams = array('id' => $this->get_course_module()->id, 'ereflect_id' => $ereflect_questions->ereflect_id, 'action'=> 'COMPLETE_QUESTIONNAIRE_PROCESS');
            $urlparams = array('id' => $this->get_course_module()->id, 'action'=> 'COMPLETE_QUESTIONNAIRE_PROCESS');
            $completeurl = new moodle_url('/mod/ereflect/view.php', $urlparams);						
            $complete = '<button type="button" onclick="changestatus(\''.$completeurl.'\',\'publish\');">'.get_string('complete_setup', 'ereflect').'</button>';
        }

        $o .= $this->output->container_start('completebuttons');
        //$o .= $this->output->box_start('boxaligncenter addusebuttons');	
        $o .= $complete;
        //$o .= $this->output->box_end();
        $o .= $this->output->container_end();				

        return $o;
    }
	
    //protected function get_question_bank ( stdclass $ereflect_questions, stdclass $parameters )
    private function get_question_bank ( stdclass $parameters )
    {
        global $DB;
        $debug = false;
        $o = '';

        $conditions = array( 'copied_eq_id' => null );
        $sort = 'id desc';
        $instance = $this->get_instance();

        if($eq = $DB->get_records('ereflect_questions',$conditions, $sort))
        {
            if($debug)
            { 
                echo 'in function locallib.get_question_bank<br />';

                echo '<hr />';
                echo '<pre>';
                print_r($eq);
                echo '</pre>';
                echo '<hr />';			

                echo 'parameters<br />';

                echo '<hr />';
                echo '<pre>';
                print_r($parameters);
                echo '</pre>';
                echo '<hr />';							
            }	

            $options = array(); 

            foreach ($eq as $key => $value)
            {
                //echo 'Question Text Id : '.$value->id.'<br />';

                //$sql = 'SELECT count(*) FROM mdl_ereflect_options WHERE ereflect_question_id = ?';
                //$params = array($value->id);
                //$countrecs = $DB->get_field_sql($sql, $params);
                $table = 'ereflect_options';
                $conditions = array('ereflect_question_id'=>$value->id); // count existing number of questions
                $countrecs = $DB->count_records($table, $conditions);				

                $options[$value->id] = $value->question_text.' ('.$countrecs.')';
                //$options[$value->question_text] = $value->question_text.' ('.$countrecs.')';
            }

            $selected = '';
            if(isset($parameters->questionbank_eq_id) && strlen($parameters->questionbank_eq_id))
            {
                $selected = $parameters->questionbank_eq_id;
            }

            $o .= '<div id="view_options_button">';
            $o .= html_writer::select($options, 'questionbankoptions', $selected);

            // Setting the onclick event for the Expand/Contract Options (Show / Hide Options) for each record
            $action = 'SHOWQUESTIONBANKOPTIONS';
            //$urlparams = array('id' => $this->get_course_module()->id, 'ereflect_id' => $ereflect_questions->ereflect_id, 'action'=> 'SHOWQUESTIONBANKOPTIONS');
            $urlparams = array('id' => $this->get_course_module()->id, 'action'=> 'SHOWQUESTIONBANKOPTIONS');
            $viewoptionsurl = new moodle_url('/mod/ereflect/view.php', $urlparams);
            $viewoptions = '<button type="button" id="options_button" onclick="viewqbankoptions(\''.$viewoptionsurl.'\');">'.get_string('viewoptions', 'ereflect').'</button></div>';		

            $o .= $viewoptions;

            if($eo = $DB->get_records('ereflect_options', array('ereflect_question_id'=>$parameters->questionbank_eq_id),'order_by'))
            {
                if($debug)
                {
                    echo '<hr />';
                    echo 'Getting Question Bank Options for id: '.$parameters->questionbank_eq_id.'<br />';
                    echo '<pre>';
                    print_r($eo);
                    echo '</pre>';
                    echo '<hr />';			
                }
                $eovar = new stdClass(); // ereflect_options				
                    
                if(isset($eo))
                {
                    foreach ($eo as $key => $value)
                    {
                        $eovar->$key = $value;
                    }

                    $view_option_details = new ereflect_viewoption( $eovar );

                    $o .= $this->get_renderer()->render($view_option_details);									
                }
            }
			
            //$urlparams = array('id' => $this->get_course_module()->id, 'ereflect_id' => $ereflect_questions->ereflect_id, 'action'=> 'ADDQUESTIONFROMBANKPROCESS');
            $urlparams = array('id' => $this->get_course_module()->id, 'action'=> 'ADDQUESTIONFROMBANKPROCESS');
            $addquestionurl = new moodle_url('/mod/ereflect/view.php', $urlparams);			
            $addquestion = '<button type="button" onclick="viewqbankoptions(\''.$addquestionurl.'\');">'.get_string('addquestion_frombank', 'ereflect').'</button>';		

            $urlparams = array('id' => $this->get_course_module()->id, 'action'=> 'USEQUESTIONFROMBANK');
            $usequestionurl = new moodle_url('/mod/ereflect/view.php', $urlparams);						
            $usequestion = '<button type="button" onclick="viewqbankoptions(\''.$usequestionurl.'\');">'.get_string('usequestion_tobank', 'ereflect').'</button>';					

            $o .= $this->output->container_start('addusebuttons');
            //$o .= $this->output->box_start('boxaligncenter addusebuttons');	
            $o .= $addquestion.'&nbsp;'.$usequestion;
            //$o .= $this->output->box_end();
            $o .= $this->output->container_end();							
        }
        return $o;
    }		
	
	//Potential new way 
	//This will get the options at the same time as getting the Questions
	
    protected function show_ereflect_questions( stdclass $eq, stdclass $params, &$mform )
    {
        //global $CFG, $DB, $USER, $PAGE;
        global $DB;
        $debug = false;
        $o = '';	

        $instance = $this->get_instance();
        $context = $this->get_context();

        if($debug)
        { 
            echo 'in show_ereflect_questions<br />';
            echo '<pre>';
            print_r($eq);
            echo '</pre>';
        }
		
        // If showoptions has been clicked against a record, then show the options
        // for that record
        $show_options_eq_id = '';
        if(isset($params->show_options_eq_id) && strlen($params->show_options_eq_id))
        {
            $show_options_eq_id = $params->show_options_eq_id;
        }
				
        if(isset($eq->id) && strlen($eq->id))
        {
            if($debug)
            {
                echo 'In 1st select <br />';
            }

            //$eq = $DB->get_records('ereflect_questions', array('id'=>$ereflect->id), 'order_by'); // one way of doing it
            ///Get all records from 'table' where foo = bar
            //$result = $DB->get_records_sql('SELECT * FROM {table} WHERE foo = ?', array('bar'));

            $sql = 'SELECT 	coalesce(CONCAT(CAST(meq.id AS CHAR), \'_\', CAST(meo.id AS CHAR)), CONCAT(CAST(meq.id AS CHAR), \'_\') ) as unique_id,
                                meq.id meq_id, meq.ereflect_id, meq.question_name, meq.question_text, 
                                meq.open_question, meq.no_of_options, meq.order_by meq_order_by,
                                meo.id meo_id, meo.ereflect_question_id, meo.option_answer, meo.option_feedback,
                                meo.showicon, meo.icon_name, meo.icon_colour, meo.order_by meo_order_by
                    FROM {ereflect_questions} meq
                    LEFT OUTER JOIN {ereflect_options} meo ON meq.id = meo.ereflect_question_id
                    WHERE meq.id = ?
                    ORDER by meq.order_by';
					
            $eqr = $DB->get_records_sql($sql, array('id'=>$eq->id));
        }
        else
        {
            //$eq = $DB->get_records('ereflect_questions', array('ereflect_id'=>$ereflect->ereflect_id), 'order_by'); // third param is the sort by

            if($debug)
            {
                echo 'In 2nd select, ereflect id: '.$eq->ereflect_id.' <br />'; 
            }

            $sql = 'SELECT coalesce(CONCAT(CAST(meq.id AS CHAR), \'_\', CAST(meo.id AS CHAR)), CONCAT(CAST(meq.id AS CHAR), \'_\') ) as unique_id,
                        meq.id meq_id, meq.ereflect_id, meq.question_name, meq.question_text, 
                        meq.open_question, meq.no_of_options, meq.order_by meq_order_by,
                        meo.id meo_id, meo.ereflect_question_id, meo.option_answer, meo.option_feedback,
                        meo.showicon, meo.icon_name, meo.icon_colour, meo.order_by meo_order_by
                     FROM {ereflect_questions} meq
                     LEFT OUTER JOIN {ereflect_options} meo ON meq.id = meo.ereflect_question_id
                     WHERE meq.ereflect_id = ?
                     ORDER by meq.order_by';

            $eqr = $DB->get_records_sql($sql, array('ereflect_id'=>$eq->ereflect_id));			
        }					
	
        // View Ereflect Questions table
	//if($eq = $DB->get_records('ereflect_questions', array('ereflect_id'=>$ereflect_id)))
        if(isset($eqr))
        {
            if($debug)
            {
                echo '$eq is set <br />';
                echo '<pre>';
                print_r($eqr);
                echo '</pre>';
            }
						
            $eqvar = new stdClass();  // ereflect_questions
			
            //and loop through your array while re-assigning the values
            foreach ($eqr as $key => $value)
            {
                $eqvar->$key = $value;
            }		
            
            if($debug)
            {
                echo 'in show_ereflect_questions - Context';
                echo '<pre>';
                print_r($context);
                echo '</pre>';
            }
					
            $view_addquestion_details = new ereflect_viewquestion(  $eqvar, 
                                                                    /*$eovar,*/
                                                                    $this->get_course_module()->id, 
                                                                    $this->get_instance()->id, 
                                                                    $eq->screen,
                                                                    $show_options_eq_id,
                                                                    $context,
                                                                    $instance );  // Show options integer value

                                                                    //$ereflect->b_amend, 
                                                                    //$ereflect->b_addoptions,
                                                                    //$ereflect->b_cancel,
                                                                    //$ereflect->b_show_options,

            $o .= $this->get_renderer()->render($view_addquestion_details);
						
            if(isset($eq->screen) && strlen($eq->screen) && $eq->screen=='ADDOPTIONS')
            {
                //foreach ($notices as $notice) {
                        //$o .= $this->get_renderer()->notification($notice);
                //}

                $data = new stdClass();		

                $b_recall_javascript = false;
                if (!$mform) {
                    if($debug){echo 'getting mform ready<br />';}			

                    //$formparams = array($this, $data, $params, $eqvar, $eovar);
                    $formparams = array($this, $data, $params, $eqvar, $instance);
                    $mform = new mod_ereflect_addoption_form(null, $formparams);			
                }
                else
                {
                  if($debug){echo 'not getting mform ready<br />';}       
                  $b_recall_javascript = true;
                }		

                $o .= $this->get_renderer()->render(new ereflect_form('addfeedbackoptions', $mform));

                // This is a workaround to refire Javascript when the form fails
                if($b_recall_javascript)
                {
                    if($debug){ echo 'In recall of javascript'; }
                    foreach($eqvar as $eovar)
                    {
                        if($debug)
                        {
                            echo 'In New bit for revalidation <br />';
                            echo '<pre>';
                            print_r($eovar);
                            echo '</pre>';
                        }

                        $o .= '<script type="text/javascript">changeiconname("'.$eovar->meo_order_by.'");';                                
                        $o .= 'changeiconcolour("'.$eovar->meo_order_by.'");</script>';                              
                   }
                }
            }
        }		
    	return $o;
    }
	
	/*  Main form elements and default rules  */
    //public function add_addquestion_form_elements(MoodleQuickForm $mform, stdClass $data, $params, $ereflect_questions, $feedback_message_options ) 
    public function add_addquestion_form_elements(MoodleQuickForm $mform, stdClass $data, $params, $ereflect_questions ) 
    {	
	// $action can be AMENDQUESTIONPROCESS or ADDQUESTIONPROCESS
		
        global $USER;
		
        $debug = false;

        $context = $this->get_context();
        $instance = $this->get_instance();
        //$module = $this->get_course_module();
        //$module_name = $module->modname;		
		
        if($debug)
        {
            echo 'in locallib.add_addquestion_form_elements  - showing data<br />';		

            echo '<hr />';
            echo 'ereflect_questions passed in:<br />';
            echo '<pre>';
            print_r($ereflect_questions);
            echo '</pre>';
            echo '<hr />';		

            echo 'Now module <br />';
            echo '<pre>';
            print_r($data);
            echo '</pre>';

            echo 'Parameters passed <br />';
            echo '<pre>';
            print_r($params);
            echo '</pre>';

            $course = $this->get_course();
            echo 'Course first <br />';
            echo '<pre>';
            print_r($course);		
            echo '</pre>:';

            $instance = $this->get_instance();			
            echo 'Instance <br />';
            echo '<pre>';
            print_r($instance);		
            echo '</pre>:';


            echo 'Context <br />';
            echo '<pre>';
            print_r($context);		
            echo '</pre>';

            echo 'instance id: '.$context->instanceid.'<br />';

            //echo 'module name: '.$module_name.'<br />';

            //echo 'Instance: '.$this->get_course_module()->instance.'<br />';
            //exit();
        }		
		// Header
        //$mform->addElement('header', 'general', get_string('general', 'form'));		
		
	$mform->addElement('html', '<div class="div_question">');		
		
	// Question Name
	$field = 'question_name';
        $mform->addElement('text', $field, get_string($field, 'ereflect'), array('size'=>'64'));		
	$mform->setType( $field, PARAM_TEXT);
	$mform->addRule( $field, null, 'required', null, 'client');		
        $mform->addRule( $field, get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
	$mform->addHelpButton( $field, 'question_name', 'ereflect');
	//$mform->setDefault('question_name', 'Question 1');
		
	// Question Text
	$field = 'question_text';
	$mform->addElement('textarea', $field, get_string($field, 'ereflect'), 'wrap="virtual" rows="4 cols="10"');				
	$mform->setType($field, PARAM_TEXT);		
	$mform->addRule($field, null, 'required', null, 'client');
        $mform->addRule($field, get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton($field, 'question_text', 'ereflect');		
		
	// Open Question (Yes or No)
	$field = 'open_question';
        $mform->addElement('selectyesno', $field, get_string($field, 'ereflect'));		
        $mform->addHelpButton($field, 'open_question', 'ereflect');
        $mform->setDefault($field, 0);
		
	// No. of Options (Number field)
	/*$attributes=array('size'=>'3');		
	$mform->addElement('text', 'no_of_options', get_string('no_of_options', 'ereflect'), $attributes);
        $mform->setType('no_of_options', PARAM_TEXT);
	$mform->disabledIf('no_of_options', 'open_question', 'eq', 1);		
        $mform->addHelpButton('no_of_options', 'no_of_options', 'ereflect');				
	$mform->setDefault('no_of_options',3);	*/

        $field = 'no_of_options';
        for ($i=9; $i>= 2; $i--) {
            $no_of_options[$i] = $i;
        }        
        
        if($instance->usetrafficlight==1)
        {
            $attributes = array('disabled'=>'disabled');        
            $mform->addElement('select', $field, get_string($field, 'ereflect'), $no_of_options, $attributes);
            $mform->setDefault($field, 3);					            
        }
        else 
        {
            $mform->addElement('select', $field, get_string($field, 'ereflect'), $no_of_options);
            $mform->disabledIf($field, 'open_question', 'eq', 1);		
            
            if(isset($ereflect_questions->no_of_options))
            {
                $mform->setDefault('no_of_options',$ereflect_questions->no_of_options);
            }
            else
            {
                $mform->setDefault($field, 3);					                            
            }
        }
        $mform->addHelpButton($field, 'no_of_options', 'ereflect');        
        
        
        // feedback Message Editor field i.e. with Picture/File/Movie upload
        /*$mform->addElement('editor', 'definition_editor', get_string('definition', 'glossary'), null, $definitionoptions);
        $mform->setType('definition_editor', PARAM_RAW);
        $mform->addRule('definition_editor', get_string('required'), 'required', null, 'client');*/
                
        if(!isset($ereflect_questions))
        {
            $ereflect_questions = new stdclass();
        }

        $field = 'feedback_message';        
        $label = get_string('feedback_message', 'ereflect');
        $mform->addElement('editor', 'feedback_message_editor', $label, null,
                            ereflect::instruction_editors_options($this->context, $ereflect_questions));
        
        
        // Hidden params.
        //$mform->addElement('hidden', 'id', $this->get_course_module()->id);
				
        if(isset($ereflect_questions->question_name) && strlen($ereflect_questions->question_name))
        {
            $mform->setDefault('question_name',$ereflect_questions->question_name);
            $mform->setDefault('question_text',$ereflect_questions->question_text);
            $mform->setDefault('open_question',$ereflect_questions->open_question);
            //$mform->setDefault('no_of_options',$ereflect_questions->no_of_options);		
        }				
        		
        // Setting to instanceid doesn't work here as when we set the data, the id gets autopopulated with
        // the enquiry_question.id. Thus have to set the default after doing  $this->set_data($data)
        // in modifyquestion_form.php
        $mform->addElement('hidden', 'id', $context->instanceid);
        $mform->setType('id', PARAM_INT);

        // I'm wondering if we need this or can use $this->get_instance()->id instead.. have to check at some stage
        //$mform->addElement('hidden', 'ereflect_id', $this->get_course_module()->instance);
	//$mform->addElement('hidden', 'ereflect_id', $instance->id);
        //$mform->setType('ereflect_id', PARAM_INT);

        $eq_id = '';		
        if($params->action == 'AMENDQUESTIONPROCESS')
        {
            //$data = $mform->get_data(); this fails... $data is already set !			
            if(isset($data->id) && strlen($data->id))
            {	
                $eq_id = $data->id;
            }
        }
		
        $order_by = '';
        if(isset($ereflect_questions->order_by) && strlen($ereflect_questions->order_by))
        {
            $order_by = $ereflect_questions->order_by;
        }
        $mform->addElement('hidden', 'order_by', $order_by);
        $mform->setType('order_by', PARAM_INT);


        $mform->addElement('hidden', 'eq_id', $eq_id);
        $mform->setType('eq_id', PARAM_INT);
		
        //$mform->addElement('hidden', 'action', 'ADDQUESTIONPROCESS');
	$mform->addElement('hidden', 'action', $params->action);
        $mform->setType('action', PARAM_TEXT);
		
	$mform->addElement('html', '</div>');				
	
    }
	
    private function process_addquestionfrombank( &$parameters, &$notices) 
    {
        global $DB, $USER, $CFG;

        $debug = false;

        if($debug)
        {
            echo 'In locallib.process_addquestionfrombank <br />';

            echo '<pre>';
            print_r($parameters);
            echo '</pre>';			
        }

        $instance = $this->get_instance();
        
        if(isset($parameters->questionbank_eq_id) && strlen($parameters->questionbank_eq_id))
        {
            //
            // Check to see how many options exist for the question
            //       
            if($instance->usetrafficlight==1)
            {
                $table = 'ereflect_options';
                $conditions = array('ereflect_question_id'=>$parameters->questionbank_eq_id); // count existing number of options
                $countopt = $DB->count_records($table, $conditions);
                
                if($countopt>3)
                {
                    $notices[] = get_string('toomanyoptionsforereflect', 'mod_ereflect');                    
                    return false;
                }
            }
            
            if($eq = $DB->get_record('ereflect_questions', array('id'=>$parameters->questionbank_eq_id), '*', MUST_EXIST))
            {
                if($debug){echo '<pre>';print_r($eq);echo '</pre>';}
				
                $table = 'ereflect_questions';
                //$conditions = array('ereflect_id'=>$parameters->ereflect_id); // count existing number of questions
                $conditions = array('ereflect_id'=>$instance->id); // count existing number of questions
                $countrecs = $DB->count_records($table, $conditions);
                $order_by = $countrecs+1;			

                try 
                { 
                    $transaction = $DB->start_delegated_transaction();     			

                    $insert = new stdClass();
                    //$insert->ereflect_id = $parameters->ereflect_id; // use existing ereflect id as opposed to one to copy !
                    $insert->ereflect_id = $instance->id; // use existing ereflect id as opposed to one to copy !
                    $insert->question_name = $eq->question_name;
                    if($instance->usetrafficlight==1)
                    {
                        $insert->no_of_options = 3;
                    }
                    else
                    {                    
                        $insert->no_of_options = $eq->no_of_options;
                    }
                    $insert->question_text = $eq->question_text;
                    $insert->open_question = $eq->open_question;                                            
                    $insert->order_by = $order_by; // from count above
                    $insert->timecreated = time(); // ?? should be timecreated
                    //$insert->timemodified = time(); // ?? should be timecreated
                    $insert->copied_eq_id = $parameters->questionbank_eq_id;

                    if($debug){echo 'ereflect_id is set to '.$eq->ereflect_id.' and '.$insert->ereflect_id.'<br />';}			

                    $eq_id = $DB->insert_record('ereflect_questions', $insert);

                    if($debug){echo '<pre>';print_r($this->instance);echo '</pre>';}

                    if(!isset($eq_id)&&!strlen($eq_id))
                    {
                            $notices[] = get_string('addquestioninsertfailed', 'mod_ereflect');
                            return false;
                    }
                    else
                    {                        
                        // Need to select the options and insert these as well
                        //$table = 'ereflect_options';
                        //$conditions = array('ereflect_question_id'=>$eq->id);
                        //$countrecs = $DB->count_records($table, $conditions);
					
                        if($eo = $DB->get_records('ereflect_options', array('ereflect_question_id'=>$eq->id), 'order_by'))
                        {
                            $order_by = 0;
                            foreach($eo as $key => $value)
                            {
                                if($debug)
                                {
                                    echo 'Showing ereflect options for id '.$eq->id.' <br />';
                                    echo '<pre>';
                                    print_r($value);
                                    echo '</pre>';
                                }

                                $update2 = new stdClass();
                                $update2->ereflect_question_id = $eq_id; // insert the new eq_id from above
                                $update2->option_answer = $value->option_answer;
                                $update2->option_feedback = $value->option_feedback;
                                $update2->showicon = $value->showicon;
                                $update2->icon_name = $value->icon_name;
                                $update2->icon_colour = $value->icon_colour;
                                $update2->order_by = $value->order_by;
                                $update2->timecreated = time();							

                                if($debug)
                                {	
                                    echo 'ereflect_question_id is set to '.$eq_id.' and '.$update2->ereflect_question_id.'<br />';
                                }			

                                $eo_id = $DB->insert_record('ereflect_options', $update2);

                                if($debug){echo '<pre>';print_r($eo_id);echo '</pre>';}

                                if(!isset($eo_id)&&!strlen($eo_id))
                                {
                                    $notices[] = get_string('addoptionsinsertfailed', 'mod_ereflect');
                                    return false;
                                }
                            } // end foreach							
                        } 						
                        // Irrespective of whether there are options or not, the
                        // Commit needs to be a success in order for the question to be inserted
                        $transaction->allow_commit();							
                        return true;
												
                    } // if eq_id is set 					
                } // end of try
                catch(Exception $e) 
                {     
                    $transaction->rollback($e);
                    $notices[] = $e;
                    return false;
                }
            }
            else
            {
                return false;
            }			
        }
        else
        {
            return false;
        }  //any records returned from ereflect questions
    }
	
    private function process_add_question(&$mform, &$notices) {
        
        global $DB, $USER, $CFG;
		
        // Include submission form.
        require_once($CFG->dirroot . '/mod/ereflect/class/addquestion_form.php');		
		
        $instance = $this->get_instance();	
        $context = $this->get_context();
        
        $debug = false;

        $data = new stdClass();
        //$feedback_message_options = new stdClass();        
        
        //$mform = new mod_ereflect_addquestion_form(null, array($this, $data, null, $instance, $feedback_message_options));
        $mform = new mod_ereflect_addquestion_form(null, array($this, $data, null, $instance));
        /*if ($mform->is_cancelled()) {
            return true;
        }*/
        $data = $mform->get_data();

        if($debug)
        {
            echo 'In locallib.process_add_question getting data<br />';
            echo '<pre>';
            print_r($data);
            echo '</pre>';

            echo '<pre>';
            print_r($instance);
            echo '</pre>';
            //exit();
        }

        // Extra Validation to go here before doing the insert ... not here!!! but it in the form validation class

        // Get it to fail on purpose to see the error message
        //$notices[] = get_string('addquestioninsertfailed', 'mod_ereflect');
        //return false;
		
        if($data = $mform->get_data())
        {

            ///Get all records from table foo where bar = 6			
            $table = 'ereflect_questions';
            //$conditions = array('ereflect_id'=>$data->ereflect_id);
            $conditions = array('ereflect_id'=>$instance->id);
            $countrecs = $DB->count_records($table, $conditions);
            $order_by = $countrecs+1;			

            if($debug)
            {
                echo 'obtaining max Order by from ereflect_questions<br />';
                echo 'count of records = '.$countrecs.'<br />';
                echo 'Order_by = '.$order_by.'<br />';
            }

            try 
            { 
                $transaction = $DB->start_delegated_transaction();     			

                $insert = new stdClass();
                //$insert->ereflect_id = $data->ereflect_id;
                $insert->ereflect_id = $instance->id;
                $insert->question_name = $data->question_name;
                $insert->no_of_options = $data->no_of_options;
                $insert->question_text = $data->question_text;
                $insert->open_question = $data->open_question;
                $insert->order_by = $order_by;
                $insert->timecreated = time();
                $insert->feedback_message  = '';          // updated later                    

                if($debug){echo 'ereflect_id is set to '.$insert->ereflect_id.'<br />';}			

                $returnid = $DB->insert_record('ereflect_questions', $insert);

                if($debug){ echo 'Ereflect_questions returnid = '.$returnid.'<br />';}

                if(!isset($returnid)&&!strlen($returnid))
                {					
                    $notices[] = get_string('addquestioninsertfailed', 'mod_ereflect');
                    return false;
                }
                else
                {
                    // new update here
                    if ($draftitemid = $data->feedback_message_editor['itemid']) 
                    {
                        $update = new stdClass();
                        $update->id = $returnid;

                        if($debug) {
                            echo 'Update Id: '.$update->id.'<br />';
                            echo 'Feedback Message itemid: '.$data->feedback_message_editor['itemid'].'<br />';
                            echo 'Feedback Message Text: '.$data->feedback_message_editor['text'].'<br />';
                            echo 'Draftitemid: '.$draftitemid.', Context ID: '.$context->id.'<br />';

                            echo '<pre>';
                            echo 'Editors Options:<br />';
                            print_r(ereflect::instruction_editors_options($context));
                            echo '</pre>';
                        }

                        // save and relink embedded images and save attachments
                        //$entry = file_postupdate_standard_editor($entry, 'definition', $definitionoptions, $context, 'mod_glossary', 'entry', $entry->id);
                        //$update = file_postupdate_standard_editor($update, 'definition', $definitionoptions, $context, 'mod_glossary', 'entry', $entry->id);
                        
                        $update->feedback_message = file_save_draft_area_files($draftitemid, $context->id, 'mod_ereflect', 'feedback_message',
                                 $update->id, ereflect::instruction_editors_options($context ), $data->feedback_message_editor['text']);      

                        if($debug){
                            echo '<pre>';
                            print_r($update);
                            echo '</pre>';

                            echo 'Hello after call to file_save_draft_area_files<br />';
                            echo 'Feedback Message: '.$update->feedback_message.'<br />';
                        }

                        // re-save the record with the replaced URLs in editor fields
                        $DB->update_record('ereflect_questions', $update);
                        $transaction->allow_commit();       

                        return true;
                    }                                        
                }	
            } 
            catch(Exception $e) 
            {     
                $transaction->rollback($e);
                $notices[] = $e;
                return false;
            }
        }
        else
        {
            return false;
        }
    }
	
    private function process_amend_question(&$mform, &$notices) 
    {
        global $DB, $USER, $CFG;
		
        // Include submission form.
        require_once($CFG->dirroot . '/mod/ereflect/class/modifyquestion_form.php');		
		
        $debug = false;
        $instance = $this->get_instance();
        $context = $this->get_context();

        $data = new stdClass();
        $mform = new mod_ereflect_modifyquestion_form(null, array($this, $data, null, $instance));
        /*if ($mform->is_cancelled()) {
            return true;
        }*/
        $data = $mform->get_data();

        if($debug)
        {
            echo 'In locallib.process_modify_question<br />';
            echo '<pre>';
            print_r($data);
            echo '</pre>';

            echo '<pre>';
            print_r($instance);
            echo '</pre>';
        }

        // Extra Validation to go here before doing the insert ...		
        // Get it to fail on purpose to see the error message
        //$notices[] = get_string('addquestioninsertfailed', 'mod_ereflect');
        //return false;
		
        //if($data = $mform->get_data())
        if(count($data)>0)
        {
            if($debug)
            {			
                echo 'showing data before update <br />';
                echo '<pre>';
                print_r($data);
                echo '</pre>';

                echo 'ereflect_id is set to '.$instance->id.'	<br />';				
            }
			
            try 
            { 
                $transaction = $DB->start_delegated_transaction();     			

                // Set the Id to Eq_id (since id is the course module id
                $data->id = $data->eq_id;
                $data->timemodified = time();

                if($debug)
                {			
                    echo 'showing data again after updating id<br />';
                    echo '<pre>';
                    print_r($data);
                    echo '</pre>';
                }

                if(!$DB->update_record('ereflect_questions', $data, $bulk=false))
                {
                    $notices[] = get_string('modifyquestionfailed', 'mod_ereflect');			
                    $transaction->rollback($e);                    
                    return false;
                }
                else
                {
                    // new update here
                    if ($draftitemid = $data->feedback_message_editor['itemid']) 
                    {
                        $update = new stdClass();
                        $update->id = $data->id;
                        $update->timemodified = time();

                        if($debug) {
                            echo 'Update Id: '.$update->id.'<br />';
                            echo 'Feedback Message itemid: '.$data->feedback_message_editor['itemid'].'<br />';
                            echo 'Feedback Message Text: '.$data->feedback_message_editor['text'].'<br />';
                            echo 'Draftitemid: '.$draftitemid.', Context ID: '.$context->id.'<br />';
                            echo 'Editors Options:<br />';
                            print_r(ereflect::instruction_editors_options($context));
                            echo '</pre>';
                        }
                        
                        $update->feedback_message = file_save_draft_area_files($draftitemid, $context->id, 'mod_ereflect', 'feedback_message',
                                 $update->id, ereflect::instruction_editors_options($context), $data->feedback_message_editor['text']);  
                        
                        if($debug) {
                            echo '<pre>';
                            print_r($update);
                            echo '</pre>';
                            echo 'Hello after call to file_save_draft_area_files<br />';
                            echo 'Feedback Message: '.$update->feedback_message.'<br />';
                        }

                        // re-save the record with the replaced URLs in editor fields
                        $DB->update_record('ereflect_questions', $update);
                    }                                        
                    
                    // get a count from options if more records than data->no_of_options then
                    //  delete from options where order_by > data->no_of_options
                    ///
                    //echo 'EQ id: '.$data->eq_id.', Order by = '.$data->order_by.'<br />';
                    $table = 'ereflect_options';
                    $conditions = array('ereflect_question_id'=>$data->eq_id);
                    $countrecs = $DB->count_records($table, $conditions);
                    if($countrecs > $data->no_of_options)
                    {
                        // delete from options where order_by > no_of_options
                        // usual way if(!$DB->delete_records('ereflect_options', array('ereflect_question_id'=>$eq_id)))
                        $tab = 'ereflect_options';
                        //$select = 'ereflect_question_id = 131 AND order_by > 3';
                        $select = 'ereflect_question_id = ? AND order_by > ?';
                        $cond = array($data->eq_id, $data->no_of_options);
                        //
                        // $select = '';
                        // jon = 'doe' AND bob <> 'tom'
                        //
                        // Delete one or more records from a table which match a particular WHERE clause.
                        //if(!$DB->delete_records('ereflect_options', array('ereflect_question_id'=>$data->eq_id)))
                        $run = $DB->delete_records_select($tab, $select, $cond);

                        if(!$run)
                        {
                            $notices[] = get_string('deleteoptionfailed', 'mod_ereflect');
                            return false;
                        }						
                    }

                    $notices[] = get_string('modifyquestionsucceeded', 'mod_ereflect');
                    $transaction->allow_commit();				   
                    return true;
                }
            }
            catch(Exception $e) 
            {     
                $transaction->rollback($e);
                $notices[] = $e;
                return false;
            }
        }  

        return false;		
    }	
	
	// Ability to remove the question 
	
    //protected function process_delete_question($ereflect_id, $eq_id, &$notices) {
    private function process_delete_question($parameters, &$notices) {
        global $DB, $USER, $CFG;
		
        $debug = false;

        $b_success = false;

        $instance = $this->get_instance();

        if($debug)
        {
            //echo 'ereflect id = '.$parameters->ereflect_id.', ereflect question id = '.$parameters->eq_id.'<br />';
            echo 'ereflect id = '.$instance->id.', ereflect question id = '.$parameters->eq_id.'<br />';

            echo 'Before showing instance array <br />';
            echo '<pre>';
            print_r($instance);
            echo '</pre>';
        }
		
        $eq_id = $parameters->eq_id;
        //$ereflect_id = $parameters->ereflect_id;
        $ereflect_id = $instance->id;

        // Extra Validation to go here before doing the insert ...

        // Get it to fail on purpose to see the error message
        //$notices[] = get_string('addquestioninsertfailed', 'mod_ereflect');
        //return false;

        try 
        { 
            if(isset($eq_id) && strlen($eq_id))
            {		
                $transaction = $DB->start_delegated_transaction();     			

                //testing to bypass the delete and concentrate on the reordering below
                //$b_success = true; // remember to hash this out

                if(!$DB->delete_records('ereflect_options', array('ereflect_question_id'=>$eq_id)))
                {
                    $notices[] = get_string('deleteoptionfailed', 'mod_ereflect');			
                    return false;
                }
                else
                {
                    if(!$DB->delete_records('ereflect_questions', array('id'=>$eq_id)))
                    {
                        $notices[] = get_string('deletequestionfailed', 'mod_ereflect');			
                        return false;
                    }
                    else
                    {
                        if($debug){echo 'successful delete <br />';}
                        $notices[] = get_string('deletequestionsucceeded', 'mod_ereflect');	

                        $b_success = true;
                        $transaction->allow_commit();
                    }
                }
            }  // isset ($eq_id)
            else
            {
                $notices[] = get_string('deletequestionfailed', 'mod_ereflect');			
                return false;
            }

        } // end of try
        catch(Exception $e) 
        {     
            $transaction->rollback($e);
            $notices[] = $e;
            return false;
        }
				
        // REORDERING - Now need to loop through the records reassigning the order_by values
        if($b_success)
        {			
            $eq = $DB->get_records('ereflect_questions', array('ereflect_id'=>$ereflect_id), 'order_by'); 

            if(isset($eq))
            {				
                if($debug)
                {
                    echo '$eq is set <br />';
                    echo '<pre>';
                    print_r($eq);
                    echo '</pre>';
                }

                $eqvar = new stdClass();  // ereflect_questions

                //and loop through your array while re-assigning the values
                $n = 0;

                try
                {
                    $transaction = $DB->start_delegated_transaction();

                    foreach ($eq as $key => $value)
                    {					
                        $eqvar->$key = $value;

                        if($debug)
                        {
                            echo 'Id = '.$eqvar->$key->id.'<br />';
                            echo 'Ereflect Id = '.$eqvar->$key->ereflect_id.'<br />';					
                        }

                        $n += 1;

                        $table = 'ereflect_questions';
                        $newfield = 'order_by';
                        $newvalue = $n;
                        $params = array('id'=>$eqvar->$key->id);

                        if($debug)
                        {
                            echo 'Table:'.$table.', Field: '.$newfield.', Value: '.$newvalue.'<br />';
                        }

                        if(!$DB->set_field($table, $newfield, $newvalue, $params))  /// Set a single field in every table record where all the given conditions met.			
                        {
                            if($debug){echo 'Record '.$n.' update failed <br />';}

                            $notices[] = get_string('modifyquestionfailed', 'mod_ereflect');							

                            return false;				
                        }
                        else
                        {
                            if($debug){echo 'Record '.$n.' update succeeded <br />';}

                            //$notices[] = get_string('modifyquestionsucceeded', 'mod_ereflect');			
                            //return false;				
                        }
                    }

                    $transaction->allow_commit();					

                }
                catch(Exception $e) 
                {     
                    $transaction->rollback($e);
                    $notices[] = $e;
                    return false;
                }
            }
		
            return true;			
	}
    }		
	
    //		
    private function process_update_question_order($params, &$notices) 
    {
        global $DB, $USER, $CFG;
		
        $debug = false;

        if($debug)
        {
            echo 'in process_update_question_order <br />';

            //$instance = $this->get_instance();			
            echo '<hr>';
            echo 'Showing all parameters passed<br />';
            echo '<pre>';
            print_r($params);
            echo '</pre>';
            echo '</hr>';

        }		
		
        if (isset($params->eq_id) && strlen($params->eq_id))
        {
            $eq_id = $params->eq_id;					

            if(!$eq = $DB->get_records('ereflect_questions', array('id'=>$eq_id)))
            {
                $notices[] = get_string('whereiscurrentquestionid', 'mod_ereflect');  // 'Cannot find current question';

                if($debug)
                {
                    echo 'Failed to update order_by <br />';
                }				
                return false;
            }
			
            if($debug)
            {
                echo '<hr>';
                echo 'Before showing Ereflect questions Record<br />';
                echo '<pre>';
                print_r($eq);
                echo '</pre>';
                echo '</hr>';
            }		

            $order_by = $eq[$eq_id]->id;			
		
            if($debug)
            {
                echo 'Eq id = '.$eq_id.'<br />';			
                echo 'Id = '.$eq[$eq_id]->id.'<br />';			
                echo 'Order by = '.$eq[$eq_id]->order_by.'<br />';
            }
		
            // Direction of Up means that id is decremented by 1
            if($params->direction=='UP')
            {
                if($debug){echo 'Direction is UP<br />';}

                if($eq[$eq_id]->order_by==1)
                {
                    //$notices[] = get_string('modifyquestionfailed', 'mod_ereflect');

                    $notices[] = 'Cannot move record upwards as this is the first record';
                    if($debug)
                    {
                        echo 'Failed to update order_by <br />';
                    }				
                    return false;
                }
                else
                {				
                    // 2ND RECORDS 'ORDER BY'
                    $order_by_tochange = ($eq[$eq_id]->order_by)-1;
                }
            }
            else
            {	
                // 2ND RECORDS 'ORDER BY'
                $order_by_tochange = ($eq[$eq_id]->order_by)+1;				
            }

            $sql = 'SELECT id FROM {ereflect_questions} WHERE ereflect_id = ? AND order_by = ?';
            $params = array($eq[$eq_id]->ereflect_id, $order_by_tochange);
            $eq_id2 = $DB->get_field_sql($sql, $params);

            if($debug){echo 'Second Records MIN Id = '.$eq_id2.'<br />';}
			
            if($debug){'Record 2 id = '.$eq_id2.'<br />';}

            // Obtain 2nd record details 
            if(!$eq2 = $DB->get_records('ereflect_questions', array('id'=>$eq_id2)))
            {
                $notices[] = get_string('whereisnextquestionid', 'mod_ereflect');  // 'Cannot find next question'

                if($debug)
                {
                    echo 'Failed to update order_by <br />';
                }				
                return false;
            }
				
            if($debug)
            {
                echo '<hr>';
                echo '2nd Record array for update for id '.$eq_id2.'<br />';
                echo '<pre>';
                print_r($eq2);
                echo '</pre>';
                echo '</hr>';			
            }
				
            //Update order_by on first record to second record

            $table = 'ereflect_questions';
            $newfield = 'order_by';
            $newvalue = $eq2[$eq_id2]->order_by;
            $params = array('id'=>$eq[$eq_id]->id);
			
            if($debug)
            {
                echo 'Table:'.$table.', Field: '.$newfield.', Value: '.$newvalue.'<br />';
                echo '<hr>';
                echo 'Parameters passed in: <br />';
                echo '<pre>';
                print_r($params);
                echo '</pre>';
                echo '<hr>';
            }			
            
            try
            {
                $transaction = $DB->start_delegated_transaction();

                // Set the first Record with the second records order by
                if(!$DB->set_field($table, $newfield, $newvalue, $params))  /// Set a single field in every table record where all the given conditions met.			
                {
                    if($debug){echo 'Record 1 update failed';}

                    $notices[] = get_string('modifyquestionfailed', 'mod_ereflect');							

                    return false;				
                }
                else
                {
                    if($debug){echo 'Record 1 update succeeded';}

                    // Set the second record with the first records order_by
                    $newvalue = $eq[$eq_id]->order_by;
                    $params = array('id'=>$eq2[$eq_id2]->id);
                    if(!$DB->set_field($table, $newfield, $newvalue, $params))  /// Set a single field in every table record where all the given conditions met.			
                    {
                        if($debug){echo 'Record 2 update failed';}

                        $notices[] = get_string('modifyquestionfailed', 'mod_ereflect');							

                        return false;				
                    }
                    else
                    {
                        if($debug){echo 'Record 2 update succeeded';}				
                        $transaction->allow_commit();						
                    }
                }				
            }
            catch(Exception $e) 
            {     
                $transaction->rollback($e);
                $notices[] = $e;
                return false;
            }

        }

        return true;
    }
	
	
    /* Screen is specific for Modifying the questions only  */
    private function view_modifyquestion_page($mform, $notices, $params ) {
       global $CFG, $DB, $USER, $PAGE;
		
        $debug = false;
		
        require_once($CFG->dirroot . '/mod/ereflect/class/modifyquestion_form.php');		
		
        $instance = $this->get_instance();		
        $context = $this->get_context();

        $o = '';	
        $o .= $this->get_renderer()->render(new ereflect_header($instance,
                                              $this->get_context(),
                                              $this->show_intro(),
                                              $this->get_course_module()->id,
                                                $this->get_course()),
                                            get_string('modifyfeedbackquestions','ereflect'));
													  		
	
        if($debug)
        {
            /*echo '<pre>';
            print_r($PAGE);
            echo '</pre>';*/

            $o .= 'In locallib.view_modifyquestion_page, Ereflect Questions id '.$params->eq_id.'<br />';
        }

        //echo 'Ereflect Questions id = '.$eq_id.'<br />';				

        // View Ereflect Questions table
        $eqvar = new stdClass();
        if($eq = $DB->get_record('ereflect_questions', array('id'=>$params->eq_id)))
        {
            if($debug)
            {
                    echo 'In database call to get records <br />';
                    echo '<pre>';
                    print_r($eq);
                    echo '</pre>';
            }

            //and loop through your array while re-assigning the values
            //foreach ($eq[$params->eq_id] as $key => $value)
            //foreach ($eq as $key => $value)
            //{
                    //$eqvar->$key = $value;
            //}			

        }

        $entry = new stdClass();
        $entry->id = $eq->id;

        /*$definitionoptions = array('trusttext'=>true, 'maxfiles'=>$maxfiles, 'maxbytes'=>$maxbytes, 'context'=>$context,
                            'subdirs'=>file_area_contains_subdirs($context, 'mod_glossary', 'entry', $entry->id));
        $entry = file_prepare_standard_editor($entry, 'definition', $definitionoptions, $context, 'mod_glossary', 'entry', $entry->id);
        $entry->cmid = $cm->id;
        // create form and set initial data
        $mform = new mod_glossary_entry_form(null, array('current'=>$entry, 'cm'=>$cm, 'glossary'=>$glossary,
                                             'definitionoptions'=>$definitionoptions, 'attachmentoptions'=>$attachmentoptions));*/
        /*$feedback_message_options = array('trusttext'=>true, 'maxfiles'=>99, 'maxbytes'=>0, 'context'=>$context,
                            'subdirs'=>file_area_contains_subdirs($context, 'mod_ereflect', 'feedback_message', $entry->id));

        $entry = file_prepare_standard_editor($entry, 'feedback_message', $feedback_message_options, $context, 'mod_ereflect', 'entry', $entry->id);*/
            
        if (!$mform) {
            //$mform = new mod_ereflect_modifyquestion_form(null, array($this, $eq, $params, $instance, $feedback_message_options));
            $mform = new mod_ereflect_modifyquestion_form(null, array($this, $eq, $params, $instance));
        }

        /*
        $data = $mform->get_data();			
        
        if ($data) {
            if($debug)
            {
                echo 'Data before<br />';
                echo '<pre>';
                print_r($data);
                echo '</pre>';
            }
            
            // Get the
            $draftid_editor = file_get_submitted_draft_itemid('feedback_message');
            $currenttext = file_prepare_draft_area($draftid_editor, $context->id, 'mod_ereflect', 'feedback_message', $data->id,
                                ereflect::instruction_editors_options($context), $data->feedback_message);
            $data->feedback_message_editor = array('text' => $currenttext, 'format' => FORMAT_HTML, 'itemid'=>$draftid_editor);            
            
            if($debug)
            {
                echo 'Data After<br />';
                echo '<pre>';
                print_r($data);
                echo '</pre>';
            }
            
            $mform->set_data($data);                        
        }*/
		
        /* Noticess - This is out of the box!! */
        foreach ($notices as $notice) {
            $o .= $this->get_renderer()->notification($notice);
        }
				
        $o .= $this->output->container_start('modifyfeedbackquestions');
        $o .= $this->output->heading(get_string('modifyfeedbackquestions', 'ereflect'), 3);
        //$o .= $this->output->box_start('boxaligncenter modifyfeedbackquestions');
		
        $o .= $this->get_renderer()->render(new ereflect_form('modifyfeedbackquestions', $mform));

        //$o .= $this->output->box_end();
        $o .= $this->output->container_end();	
        
        $o .= $this->view_footer();
	
        return $o;
    }
	
	
    private function view_edit_addoptions_page($mform, $notices, $params) 
    {
        global $CFG, $DB, $USER, $PAGE;
		
        $debug = false;

        if($debug){echo 'In locallib.view_edit_addoptions_page';}
		
        require_once($CFG->dirroot . '/mod/ereflect/class/addoption_form.php');		
		
        $instance = $this->get_instance();		

        $o = '';

        $o .= $this->get_renderer()->render(new ereflect_header($instance,
                                              $this->get_context(),
                                              $this->show_intro(),
                                              $this->get_course_module()->id,
                                            $this->get_course()),
                                            get_string('addfeedbackoptions','ereflect'));
													  		
        if($debug){$o .= 'Hello, good to see you are about to enter OPTIONS with an eq_id of '.$params->eq_id.'<br />';}

        // View Ereflect Questions table															
        //$eq_d = $this->get_course_module()->instance;

        if($debug)
        {
            echo '<pre>';
            print_r($params);
            echo '</pre>';
        }

	/* Notices - This is out of the box!! */
        foreach ($notices as $notice) {
            $o .= $this->get_renderer()->notification($notice);
        }
				
        $ereflect_questions = new stdclass();
        $ereflect_questions->id = $params->eq_id;		
        $ereflect_questions->screen = 'ADDOPTIONS';
				
        //if($debug){$o .= 'Id = '.$er_id.'<br />';} // ereflect id

        // View Ereflect Questions table by Id (Single record)		
        $o .= $this->show_ereflect_questions($ereflect_questions, $params, $mform);

        $o .= $this->view_footer();
        
        return $o;
    }	
	
    //public function add_option_form_elements(MoodleQuickForm $mform, stdClass $data, $params, $ereflect_questions, $ereflect_options ) {
    public function add_option_form_elements(MoodleQuickForm $mform, stdClass $data, $params, $ereflect_questions) 
    {
        global $USER;
		
        $debug = false;

        //$data = $mform->get_data();		

        $instance = $this->get_instance();

        if($debug)
        {
            echo 'in locallib.add_addoption_form_elements  - showing data<br />';

            echo 'Count of ereflect options: '.COUNT($ereflect_questions).'<br />';

            echo 'Showing data <br />';
            echo '<pre>';
            print_r($ereflect_questions);
            echo '</pre>';			

            echo 'Showing data <br />';
            echo '<pre>';
            print_r($data);
            echo '</pre>';

            echo 'Showing parameters<br />';
            echo '<pre>';
            print_r($params);
            echo '</pre>';

            /*echo 'Instance: '.$this->get_course_module()->instance.'<br />';

            echo 'Showing instance <br />';
            echo '<pre>';
            print_r($this->instance);
            echo '</pre>';*/
        }	
		
        $no_of_options = 0;
        foreach($ereflect_questions as $key => $value)
        {
            //$no_of_options += 1;
            $no_of_options = $value->no_of_options;
            break;
        }
		
        if($debug){echo 'no of options is : '.$no_of_options.'<br />';}

        /*$eq_id = '';
        if(isset($params->eq_id) && strlen($params->eq_id))
        {
                $eq_id = $params->eq_id;
        }
        $no_of_options = '';
        if(isset($ereflect_questions->$eq_id->no_of_options) && strlen($ereflect_questions->$eq_id->no_of_options))
        {
                $no_of_options = $ereflect_questions->$eq_id->no_of_options;
        }*/
                
        if($instance->usetrafficlight==1)
        {
            $colour_arr = array(    '' => 'please choose' ,
                                    'red' => 'red', 
                                    '#FF6600' => 'orange',
                                    'green' => 'green' );
        }
        else
        {
            // Blue, Red, Orange, Green, Black, Yellow 
            $colour_arr = array(    '' => 'please choose' ,
                                    'red' => 'red', 
                                    '#FF6600' => 'orange',
                                    'green' => 'green',
                                    'black' => 'black',
                                    'yellow' => 'yellow',
                                    'blue' => 'blue');
        }       
                        
        $icon_arr = array ( '' => 'please choose',
                            'fa-smile-o' => 'happy face',
                            'fa-meh-o' => 'neutral face',
                            'fa-frown-o' => 'sad face',
                            'fa-check' => 'tick',
                            'fa-times' => 'cross',
                            'fa-circle' => 'traffic light circle');
		        
		
        if(isset($no_of_options) && strlen($no_of_options))
        {
            $mform->addElement('html', '<div class="div_option_answer">');
            
            for($i=1; $i<=$no_of_options; $i++ )
            {					
                if($debug){echo 'Found '.$i.'<br />';}
                // Option Answer	
                $fieldname = 'option_answer_'.$i;
                $mform->addElement('text', $fieldname, get_string('option_answer', 'ereflect', $i), array('id' => 'option_answer_'.$i, 'size'=>'64'));
                $mform->setType($fieldname, PARAM_TEXT);
                //$mform->addRule($fieldname, null, 'required', null, 'client');
                $mform->addRule($fieldname, get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
                $mform->addHelpButton($fieldname, 'option_answer', 'ereflect');

                // Option Feedback
                $fieldname = 'option_feedback_'.$i;				
                $attributes = array('id' => $fieldname , 'wrap' => 'virtual', 'rows' => '4', 'cols' => '20');
                $mform->addElement('textarea', $fieldname, get_string('option_feedback', 'ereflect', $i), $attributes);
                $mform->setType($fieldname, PARAM_TEXT);
                //$mform->addRule($fieldname, null, 'required', null, 'client');
                //$mform->addRule($fieldname, get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
                $mform->addHelpButton($fieldname, 'option_feedback', 'ereflect');

                // Option Icon 
                if($instance->usetrafficlight==1)
                {
                    if($debug){echo 'Use Traffic Light is on<br />';}
                    $field = 'option_icon_name_'.$i;

                    $mform->addElement('hidden', $field, 'fa-circle'); // always a circle for traffic light
                    $mform->setType($field, PARAM_TEXT);

                    $field = 'option_icon_colour_'.$i;                    
                    $attributes = array('id'=>'iconoptions_'.$i, 'onchange'=>'changeiconcolour(\''.$i.'\')');
                    // onclick'=>'togglePasswordMask(\'id_password\')'                            
                    $mform->addElement('select', $field, get_string('option_icon_colour', 'ereflect', $i), $colour_arr, $attributes);					
                 
                    $mform->addElement('html','<span class="literal_name B">'.get_string('option_image','ereflect', $i).'</span><span class="option_icon"><i id="option_icon_symbol_'.$i.'"class="fa fa-circle fa-2x"></i></span>');
                }
                else
                {
                    if($debug){echo 'Use Traffic light is off <br />';}
                    
                    $field1 = 'option_icon_name_'.$i;
                    $attributes1 = array('id'=>'option_icon_name_'.$i, 'onchange'=>'changeiconname(\''.$i.'\')');                    
                    $mform->addElement('select', $field1, get_string('option_icon', 'ereflect', $i), $icon_arr, $attributes1);
                    
                    $field2 = 'option_icon_colour_'.$i;                    
                    $attributes2 = array('id'=>'iconoptions_'.$i, 'onchange'=>'changeiconcolour(\''.$i.'\')');
                    $mform->addElement('select', $field2, get_string('option_icon_colour', 'ereflect', $i), $colour_arr, $attributes2);					
                                        
                     // $mform->addElement('html','<span class="literal_name B">Option '.$i.' Image</span><span class="option_icon"><i id="option_icon_'.$i.'" class="fa fa-circle fa-2x"></i></span>');                    
                    
                    $mform->addElement('html','<span class="literal_name B">'.get_string('option_image','ereflect', $i).'</span><span class="option_icon"><i id="option_icon_symbol_'.$i.'" class=""></i></span>');
                }

                if($i!=$no_of_options)
                {
                    $mform->addElement('html', '<hr />');
                }
            }
     
            // Setting Option fields by looping through the Options array
         
            foreach($ereflect_questions as $eovar)
            {
                if($debug)
                {
                    echo 'In New bit for revalidation <br />';
                    echo '<pre>';
                    print_r($eovar);
                    echo '</pre>';
                }

                $mform->setDefault('option_answer_'.$eovar->meo_order_by,$eovar->option_answer);
                $mform->setDefault('option_feedback_'.$eovar->meo_order_by,$eovar->option_feedback);
                $mform->setDefault('option_icon_colour_'.$eovar->meo_order_by,$eovar->icon_colour);				
                
                if($instance->usetrafficlight==1)
                {
                    $mform->setDefault('option_icon_name_'.$eovar->meo_order_by,'fa-circle');
                }
                else
                {
                    $mform->setDefault('option_icon_name_'.$eovar->meo_order_by,$eovar->icon_name);
                }
                
                if($debug){echo 'just before value '.$eovar->meo_order_by.'<br />';}
                $mform->addElement('html','<script type="text/javascript">changeiconname("'.$eovar->meo_order_by.'");</script>');                                
                if($debug){echo 'in between<br />';}
                $mform->addElement('html','<script type="text/javascript">changeiconcolour("'.$eovar->meo_order_by.'");</script>');                
                if($debug){echo 'just after<br />';}
            }

										
            // eq_id (ereflect_questions.id)
            $eq_id = '';
            if(isset($params->eq_id) && strlen($params->eq_id))
            {
                $eq_id = $params->eq_id;
            }
            $mform->addElement('hidden', 'eq_id', $eq_id);
            $mform->setType('eq_id', PARAM_INT);

            // eo_id (ereflect_options.id)
            $eo_id = '';
            if(isset($data->id) && strlen($data->id))
            {
                $eo_id = $data->id;
            }
            // eo_id
            $mform->addElement('hidden', 'eo_id', $eo_id);
            $mform->setType('eo_id', PARAM_INT);

            // action
            $mform->addElement('hidden', 'action', $params->action);
            $mform->setType('action', PARAM_TEXT);

            // no_of_options
            $mform->addElement('hidden', 'no_of_options', $no_of_options);
            $mform->setType('no_of_options', PARAM_INT);

            $mform->addElement('html', '</div>');
        }
		
        $mform->addElement('hidden', 'id', $this->instance->id);
        $mform->setType('id', PARAM_INT);

    }	
	
    private function process_add_options(&$mform, &$notices, &$params) 
    {
        global $DB, $USER, $CFG;
	
		
        // Include submission form.
        require_once($CFG->dirroot . '/mod/ereflect/class/addoption_form.php');
		
        $instance = $this->get_instance();

        $debug = false;

        if($debug)
        {
            echo 'in process_add_options, instance details; <br />';
            echo '<pre>';
            print_r($instance);
            echo '</pre>';
        }

        $data = new stdClass();
        $eqvar = new stdClass();		
        $sql = 'SELECT 	coalesce(CONCAT(CAST(meq.id AS CHAR), \'_\', CAST(meo.id AS CHAR)), CONCAT(CAST(meq.id AS CHAR), \'_\') ) as unique_id,
                        meq.id meq_id, meq.ereflect_id, meq.question_name, meq.question_text, 
                                    meq.open_question, meq.no_of_options, meq.order_by meq_order_by,
                                    meo.id meo_id, meo.ereflect_question_id, meo.option_answer, meo.option_feedback,
                                    meo.showicon, meo.icon_name, meo.icon_colour, meo.order_by meo_order_by
                FROM {ereflect_questions} meq
                LEFT OUTER JOIN {ereflect_options} meo ON meq.id = meo.ereflect_question_id
                WHERE meq.id = ?
                ORDER by meq.order_by';

					
        $eqr = $DB->get_records_sql($sql, array('ereflect_id'=>$params->eq_id));		
        foreach($eqr as $key => $value)
        {
            $eqvar->key = $value; 
        }
							
        //$mform = new mod_ereflect_addoption_form(null, array($this, $data, $params, $eqvar, $eovar));		
        $mform = new mod_ereflect_addoption_form(null, array($this, $data, $params, $eqvar, $instance));
        //$mform = new mod_ereflect_addoption_form(null, array($this, $data));		
        $data = $mform->get_data();
		
        if($debug)
        {
            echo 'In locallib.process_add_options<br />';
            //echo 'Question Name: '.$data->question_name_1;

            /*echo 'this<br />';
            echo '<pre>';
            print_r($this);
            echo '</pre>';*/

            echo 'All data <br />';
            echo '<pre>';
            print_r($data);
            echo '</pre>';

            echo 'Getting instance information <br />';
            //$instance = $this->get_instance();
            echo '<pre>';
            print_r($instance);
            echo '</pre>';

            echo 'No of options: '.$data->no_of_options.'<br />';
            //exit();
        }
		
        $no_of_options = '';

        if(isset($data->no_of_options) && strlen($data->no_of_options))
        {
            $no_of_options = $data->no_of_options;
        }
        else
        {
            return false;
        }

        try
        {
            $transaction = $DB->start_delegated_transaction();

            // Delete Records from ereflect_options
            $DB->delete_records('ereflect_options', array('ereflect_question_id'=>$data->eq_id));
            /// Delete the records from a table where all the given conditions met.				

            for($i=1; $i<=$no_of_options; $i++)
            {		
                if($data = $mform->get_data())
                {
                    $opt_ans = 'option_answer_'.$i;
                    $answer = $data->$opt_ans;

                    $opt_fdb = 'option_feedback_'.$i;
                    $feedback = $data->$opt_fdb;
                            
                    $opt_icon_name = 'option_icon_name_'.$i;
                    $icon_name = $data->$opt_icon_name;

                    $opt_icon_colour = 'option_icon_colour_'.$i;
                    $icon_colour = $data->$opt_icon_colour;

                    $ins = new stdClass();
                    $ins->ereflect_question_id = $data->eq_id;
                    $ins->option_answer = $answer;
                    $ins->option_feedback = $feedback;
                    $ins->showicon = 1;
                    $ins->icon_colour = $icon_colour;
                    $ins->icon_name = $icon_name;
                    $ins->order_by = $i;

                    $ins->timecreated = time();
                    $ins->timemodified = time();
                }
                else
                {
                    $notices[] = get_string('nooptionssubmitted', 'mod_ereflect');
                    return false;
                }

                if(!($returnid = $DB->insert_record('ereflect_options', $ins)))
                {
                    $notices[] = get_string('addoptionsinsertfailed', 'mod_ereflect');
                    return false;							
                }

                if($debug)
                {
                    echo 'Ereflect_questions returnid = '.$returnid.'<br />';
                    //exit();				
                }
				
                /*$this->instance = $DB->get_record('ereflect_options', array('id'=>$returnid), '*', MUST_EXIST);			
                if(!isset($this->instance->id)&&!strlen($this->instance->id))
                {
                        $notices[] = get_string('addoptionsinsertfailed', 'mod_ereflect');
                        return false;
                }*/				
            }
			
            $transaction->allow_commit();
        }
        catch(Exception $e)
        {     
            $transaction->rollback($e);
            $notices[] = $e;
            return false;
        }		
		
        if($debug)
        {
            echo 'reached end of process_add_options<br />';
        }

        return true;		
    }
	
    private function process_onhold_questionnaire( &$notices )
    {
        global $DB;

        $debug = false;
        $instance = $this->get_instance();
		
        if($debug)
        {
            echo 'in protected function process_onhold_questionnaire with instance: <br />';

            echo '<pre>';
            print_r($instance);
            echo '</pre>';
        }		
		
        // Check to see if a student has started
        if($this->user_started())
        {
            $notices[] = get_string('onholduserstarted', 'mod_ereflect'); // Cannot place on hold as at least one user has started completing the ereflect questionnaire
            return false;		
        }				
		
        try
        {
            $transaction = $DB->start_delegated_transaction();

            // Use Update_record to update more than one field
            $data = new stdClass();
            //$data->id = $params->ereflect_id;
            $data->id = $instance->id;
            $data->status = 'INPROGRESS';
            $data->timemodified = time();

            if(!$DB->update_record('ereflect', $data, $bulk=false))
            {
                if($debug){echo 'Record update failed <br />';}
                $notices[] = get_string('modifyereflectfailed', 'mod_ereflect');							
                return false;				
            }
            else
            {
                if($debug){echo 'Record update succeeded <br />';}
                //$notices[] = get_string('modifyquestionsucceeded', 'mod_ereflect');
                $transaction->allow_commit();						
                return true;
            }
        }
        catch(Exception $e) 
        {     
            $transaction->rollback($e);
            $notices[] = $e;
            return false;
        }			
    }
	
    //protected function process_complete_questionnaire(&$params, &$notices)
    private function process_complete_questionnaire(&$notices)
    {
        global $DB;
        $debug = false;

        if($debug)
        {
            echo 'in protected function process_complete_questionnaire with parameters: <br />';
            echo '<pre>';
            print_r($params);
            echo '</pre>';		
        }		
		
        $instance = $this->get_instance();

        // Get a count from ereflect_questions
        // if < 2 then error

        $b_enoughquestions = $this->check_enough_questions($notices);

        if(!$b_enoughquestions)
        {
            $notices[] = get_string('notenoughquestions', 'mod_ereflect');
            return false;
        }
		
        // Check to see if already
        /*$table = 'ereflect_questions';
        $conditions = array('ereflect_id'=>$instance->id); // count existing number of questions
        $countrecs = $DB->count_records($table, $conditions);				

        if($countrecs<2)
        {
                $notices[] = get_string('notenoughquestions', 'mod_ereflect');
                return false;				
        }*/


        // For each record in mdl_ereflect_questions
        // Get a count of the options to check against the expected no_of_options
        $option_errors = '';
        $errors = '';

        // Get the fields from questions and a count of options for each in one select for performance gains

        $sql = 'SELECT 	meq.id, meq.ereflect_id, meq.question_name, meq.question_text, 
                                meq.open_question, meq.no_of_options, meq.order_by,
                                IFNULL(meo.total,0) actual_options
                FROM {ereflect_questions} meq
                LEFT JOIN ( SELECT count(1) total, ereflect_question_id
                                        FROM {ereflect_options}
                                        GROUP BY ereflect_question_id ) meo 
                ON (meo.ereflect_question_id = meq.id)
                WHERE meq.ereflect_id = ?';
		
											
        //if($eq = $DB->get_records('ereflect_questions', array('ereflect_id'=>$params->ereflect_id)))
        //if($eq = $DB->get_records_sql($sql, array('ereflect_id'=>$params->ereflect_id)))
        if($eq = $DB->get_records_sql($sql, array('ereflect_id'=>$instance->id)))
        {
            foreach ($eq as $key => $value)
            {
                //echo 'Question Text Id : '.$value->id.'<br />';

                //$table = 'ereflect_options';
                //$conditions = array('ereflect_question_id'=>$value->id); // count existing number of questions
                //$countrecs = $DB->count_records($table, $conditions);				
				
                if($debug)
                {
                    //echo 'EQ Id: '.$value->id,' Expected Option Count = '.$value->no_of_options.', Actual Option Count = '.$countrecs.'<br />';
                    echo 'Open Question: '.$value->open_question.', EQ Id: '.$value->id,' Expected Option Count = '.$value->no_of_options.', Actual Option Count = '.$value->actual_options.'<br />';
                }
                
                // Need to check if its a Traffic Light system there should always be 3 options
                if($instance->usetrafficlight == 1 && $value->no_of_options!= 3)
                {
                    $option_errors .= '('.$value->order_by.') '.$value->question_text.'<br />';                    
                }

                //if($value->no_of_options!=$countrecs)
                if($value->open_question==0 && $value->no_of_options!=$value->actual_options)
                {
                    $errors .= '('.$value->order_by.') '.$value->question_text.'<br />';
                }		
            }	
			
            if($debug)
            {			
                echo 'Record Values <br />';
                echo '<pre>';
                print_r($eq);
                echo '</pre>';

                echo 'Errors <br />';
                echo '<pre>';
                print_r($errors);
                echo '</pre>';
            }

            if(isset($option_errors) && strlen($option_errors))
            {
                $notices[] = get_string('traffic_light_error', 'mod_ereflect').'<br />'.$option_errors;                
                return false;
            }
    
            if(isset($errors) && strlen($errors))
            {				
                // The following Questions do not have their Options entered
                $notices[] = get_string('complete_error', 'mod_ereflect').'<br />'.$errors;
                
                if($debug)
                {			
                    echo 'Object Errors <br />';
                    echo '<pre>';
                    print_r($notices);
                    echo '</pre>';
                }

                return false;
            }
                        	
            try
            {
                $transaction = $DB->start_delegated_transaction();

                // Use Update_record to update more than one field
                $data = new stdClass();
                $data->id = $instance->id;
                $data->status = 'STUDENTENTRY';
                $data->timemodified = time();

                if(!$DB->update_record('ereflect', $data, $bulk=false))
                {
                    if($debug){echo 'Record update failed <br />';}

                    $notices[] = get_string('modifyereflectfailed', 'mod_ereflect');							

                    return false;				
                }
                else
                {
                    if($debug){echo 'Record update succeeded <br />';}

                    //$notices[] = get_string('modifyquestionsucceeded', 'mod_ereflect');			

                    $transaction->allow_commit();						
                    return true;
                }
            }
            catch(Exception $e) 
            {     
                $transaction->rollback($e);
                $notices[] = $e;
                return false;
            }

        }
        else
        {
            $notices[] = get_string('questioninsertfirst', 'mod_ereflect'); // You need to insert some question first before completing the

            return false;
        }
    }
	
    private function process_complete_student_answers(&$notices)
    {	
        global $USER, $DB, $CFG;

        $debug = false;

        $instance = $this->get_instance();

        if($debug)
        {
            echo '<hr />';
            echo 'Instance information; <br />';
            echo '<pre>';
            print_r($instance);
            echo '</pre>';
            echo '<hr />';
            echo '<hr />';
            echo 'User information; <br />';
            echo '<pre>';
            print_r($USER);
            echo '</pre>';
            echo '<hr />';
        }
		
        // Check that there is a record in the response table for the particular user in question
        $table = 'ereflect_user_response';
        if(!$eur = $DB->get_record($table, array('ereflect_id' => $instance->id, 'user_id' => $USER->id)))
        {
            $notices[] = get_string('cannot_student_complete', 'mod_ereflect'); 
            // Questionnaire is not in the correct status to complete

            return false;				
        }
		
        // If there is a record, check that it is in a 'PARTIAL' status
        if($eur->status!='PARTIAL')
        {
            $notices[] = get_string('cannot_student_complete2', 'mod_ereflect'); 
            // Questionnaire is not in the correct status to complete
            return false;						
        }
		
        if($instance->include_preptime_in_report==1)
        {
            if(!isset($eur->assignment_time) || !strlen($eur->assignment_time))
            {
                //Please enter the {$a} and save before completion                
                $notices[] = get_string('field_before_completion', 'mod_ereflect',get_string('assignment_time','mod_ereflect')); 
                return false;						
            }
        }
		
        if($instance->include_mark_in_linegraph==1)
        {
            if(!isset($eur->assignment_time) || !strlen($eur->assignment_time))
            {
                //Please enter the {$a} and save before completion
                $notices[] = get_string('field_before_completion', 'mod_ereflect',get_string('grade','mod_ereflect')); 
                return false;						
            }
        }
				
        // Check count from questions matches count from answers
        $table1 = 'ereflect_questions';
        $conditions1 = array('ereflect_id'=>$instance->id); // count existing number of questions
        $countrecs1 = $DB->count_records($table1, $conditions1);

        //$table2 = 'ereflect_answers';
        //$conditions2 = array('ereflect_id'=> $instance->id, 'user_id' => $USER->id );
        //$countrecs2 = $DB->count_records($table2, $conditions2);
        // Above not good enough , as need to check that of the answers present
        // that the option is set for the closed question and text set for the open question
        $sql = 'SELECT count(*) cnt
                FROM {ereflect_answers}
                WHERE ereflect_id = ?
                AND user_id = ?
                AND ( (open_question_ind =  0 and ereflect_option_id IS NOT NULL )
                        OR (open_question_ind =1 and open_answer IS NOT NULL))';
        $cr = $DB->get_records_sql($sql, array($instance->id, $USER->id ));    
        
        foreach ($cr as $key => $value)
        {
            $countrecs2 = $value->cnt;
        }        

        if($countrecs1 != $countrecs2)
        {
            //  produce errors, saying that not all questions have been answers for this particular ereflect_id
            $notices[] = get_string('cantsubmit', 'mod_ereflect'); 
            return false;						
        }
        else
        {
            // update the status of ereflect_user_response to COMPLETED along with timemodified

            $update = new stdClass();
            $update->id = $eur->id;
            $update->timemodified = time();
            $update->status = 'COMPLETED';

            if(!$DB->update_record($table, $update, $bulk=false))
            {
                $notices[] = get_string('upd_ereflect_user_response_failed', 'mod_ereflect');			
                return false;
            }				
            else
            {
                // Create the PDF on the fly and email it to the User
                $filename = 'pdf_ereflect_'.$instance->id.'_user_'.$USER->id.'.pdf';
                $copyfolder = $CFG->cachedir.'/tcpdf/'.$filename;
                $this->print_pdf( $USER->id, 'F', $filename);

                // wait up to 5 seconds before bombing out and giving up on the email
                for($i=0; $i<5; $i++)
                {					
                    if(file_exists( $copyfolder ))
                    {
                        // moodlelib.php email_to_user function already looks in default Moodle Directory ($CFG->dataroot) i.e. /MoodleData
                        // so just add the extra folders onto the end of it
                        $emailattachment = 'cache/tcpdf/'.$filename;

                        // Only email to the student if its set on the first screen
                        if($instance->email_notification==1)
                        {
                            //$subject = 'eReflect Email - '.$instance->name;
                            $subject = get_string('email_subject_user','ereflect', $instance->name);

                            //'Dear '.$USER->firstname.' '.$USER->lastname.'<br />';
                            //'Please find your PDF report attached for the eReflect Assignment.<br />';
                            $body = get_string('email_dear_user', 'ereflect', ucfirst(strtolower($USER->firstname)).' '.ucfirst(strtolower($USER->lastname))).'<br />'.get_string('email_body_user','ereflect');
                            $this->sendemail( $USER->id, $subject, $body, $emailattachment, $filename );
                        }

                        // Send to each Teacher on the course
                        $uwct = $this->users_who_can_teach();
                        foreach($uwct as $key => $value)
                        {
                            $subject = get_string('email_subject_user','ereflect', $instance->name);
                            
                            $name = ucfirst(strtolower($value->firstname)).' '.ucfirst(strtolower($value->lastname));
                            
                            $body = get_string('email_dear_user', 'ereflect', $name).'<br />'.get_string('email_body_teacher','ereflect',ucfirst(strtolower($USER->firstname)).' '.ucfirst(strtolower($USER->lastname)));

                            $this->sendemail( $value->id, $subject, $body, $emailattachment, $filename );
                        }

                        break;						
                    }
                    else
                    {
                        sleep(1);
                    }
                }												
                return true;                    
            }
        }		
    }
    
    /**
     * Get the piece of code as determined in the extended plugin_renderer_base (class mod_ereflect_renderer )
     *
     * @return string
    */
    		
    public function get_renderer() {
        global $PAGE;
        if ($this->output) {
            return $this->output;
        }
        $this->output = $PAGE->get_renderer('mod_ereflect');
        return $this->output;
    }		
	
    /**
     * Display the page footer.
     *
     * @return string
     */
    protected function view_footer() {
        return $this->get_renderer()->render_footer();
    }
	   
	   
    /**
     * get the capabilities for the questionnaire
     * @param int $cmid
     * @return object the available capabilities from current user
     */
    //function questionnaire_load_capabilities($cmid) {
    function questionnaire_load_capabilities($cmid) {
        static $cb;

        $debug = false;

        if (isset($cb)) {
            return $cb;
        }

        if($debug)
        {
            echo 'in questionnaire_load_capabilities';
        }

        //$context = questionnaire_get_context($cmid);
        $context = $this->get_context();

        if($debug)
        {
            echo '<pre>';
            print_r($context);
            echo '</pre>';
        }

        $cb = new stdClass();
        $cb->view   = has_capability('mod/ereflect:view', $context);
        $cb->submit = has_capability('mod/ereflect:submit', $context); // Student submissions
        $cb->grade = has_capability('mod/ereflect:grade', $context); // Teachers ability to grade work

        return $cb;
    }
	 
    
    /**
     * Get the settings for the current instance of this assignment
     *
     * @return stdClass The settings
     */
    public function get_instance() {
        global $DB;
        if ($this->instance) {
            return $this->instance;
        }
        if ($this->get_course_module()) {
            $params = array('id' => $this->get_course_module()->instance);
            $this->instance = $DB->get_record('ereflect', $params, '*', MUST_EXIST);
        }
        if (!$this->instance) {
            throw new coding_exception('Improper use of the assignment class. ' .
                                       'Cannot load the assignment record.');
        }
        return $this->instance;
    }

    /**
     * Get the current course module.
     *
     * @return mixed stdClass|null The course module
     */
    public function get_course_module() {
        if ($this->coursemodule) {
            return $this->coursemodule;
        }
        if (!$this->context) {
            return null;
        }

        if ($this->context->contextlevel == CONTEXT_MODULE) {
            $this->coursemodule = get_coursemodule_from_id('ereflect',
                                                           $this->context->instanceid,
                                                           0,
                                                           false,
                                                           MUST_EXIST);
            return $this->coursemodule;
        }
        return null;
    }
	
    /**
     * Get the current course.
     *
     * @return mixed stdClass|null The course
     */
    public function get_course() {
        global $DB;

        if ($this->course) {
            return $this->course;
        }

        if (!$this->context) {
            return null;
        }
        $params = array('id' => $this->get_course_context()->instanceid);
        $this->course = $DB->get_record('course', $params, '*', MUST_EXIST);

        return $this->course;
    }
	

    /**
     * Set the context.
     *
     * @param context $context The new context
     */
    /*public function set_context(context $context) {
        $this->context = $context;
    }*/
	
	/**
     * Get context module.
     *
     * @return context
    */
    public function get_context() {
        return $this->context;
				
    }
	
	
    /**
     * Based on the current assignment settings should we display the intro.
     *
     * @return bool showintro
     */
    protected function show_intro() {
        /*if ($this->get_instance()->alwaysshowdescription ||
                time() > $this->get_instance()->allowsubmissionsfromdate) {
            return true;
        }*/
        return true;
    }

    
    public function register_return_link($action, $params) {
        global $PAGE;
        $params['action'] = $action;
        $currenturl = $PAGE->url;

        $currenturl->params($params);
        $PAGE->set_url($currenturl);
    }
	
    /**
     * This is the main AddQuestion page 
     *  
     * 
     */
    
    public function view_answer_questions_page ($mform, $notices, $params ) {
        global $CFG, $DB, $USER, $PAGE;
		
	$debug = false;
        $submitted = false;$submitted = true;
        
        require_once($CFG->dirroot . '/mod/ereflect/class/addanswers_form.php');		
		
        $o = '';	
        $context = $this->get_context();
        $instance = $this->get_instance();		
        $module = $this->get_course_module();
        
        $o .= $this->get_renderer()->render(new ereflect_header($instance,
                                              $this->get_context(),
                                              $this->show_intro(),
                                              $this->get_course_module()->id,
                                              $this->get_course()),get_string('addanswerstitle','ereflect'));
													  		
        if($debug)
        {
            echo 'Debug On - About to enter a question in view_answer_question_pages - Parameter settings<br />';

            echo '<hr>';
            echo 'Parameters: <br />';
            echo '<pre>';
            print_r($params);
            echo '</pre>';
            echo '</hr>';			

            echo '<hr>';
            echo 'Instance: <br />';			
            echo '<pre>';
            print_r($instance);
            echo '</pre>';
            echo '</hr>';
        }
		
        // If we have a User id passed in (i.e. we are viewing it from Teachers perspective), then
        // use the user_id parameter as opposed to the USER logged in
        //
        /*if(isset($params->user_id) && strlen($params->user_id))
        {
            $user_id = $params->user_id;
        }
        else
        {
            $user_id = $USER->id;
        }
	$completed = $this->user_completed($user_id, 'COMPLETED' ); //Call once here instead of calling multiple times*/
        
        // If parameter student id not set, then it's the student, so just
        // check that it is a student and then set the parameter student id value
        if(!isset($params->student_id) && !strlen($params->student_id)){
            if($this->capabilities->submit) { // one of students
                $params->student_id = $USER->id;
            }
        }
        
        $completed = $this->user_completed($params->student_id, 'COMPLETED');         
        
        $b_teacherview = false;
        if(isset($params->view) && $params->view=='TEACHERVIEW')
        {
            $b_teacherview = true;
        }

        /*$b_ejournalview = false;
        if(isset($params->view) && $params->view=='EJOURNALVIEW')    
        {
            $b_ejournalview = true;
        }*/
		
	// Get Student Profile
	//$o .= $this->get_student_profile($user_id);
        $o .= $this->get_student_profile($params->student_id);

	// Complete Questionnaire Section
	//if(!$completed && !$b_teacherview)
	//{
          //  $o .= $this->get_student_complete_button();
	//}
				
        // coalesce is the same as NVL
        // Have to CAST the integers as a char so as not to return a blob as opposed to a string in the results
        $sql = 'SELECT 	coalesce(CONCAT(CAST(meq.id AS CHAR), \'_\', CAST(meo.id AS CHAR)), CONCAT(CAST(meq.id AS CHAR), \'_\') ) as unique_id,
                                meq.id meq_id, meq.ereflect_id, meq.question_name, meq.question_text, 
                                meq.open_question, meq.no_of_options, meq.order_by meq_order_by, meq.feedback_message, 
                                meo.id meo_id, meo.ereflect_question_id, meo.option_answer, meo.option_feedback,
                                meo.order_by meo_order_by, meo.showicon,  meo.icon_name, meo.icon_colour,
                                mea.ereflect_option_id, mea.open_answer
                        FROM {ereflect_questions} meq
                        LEFT OUTER JOIN {ereflect_options} meo ON meq.id = meo.ereflect_question_id
                        LEFT OUTER JOIN {ereflect_answers} mea ON meq.id = mea.ereflect_question_id AND mea.user_id = ?
                        WHERE meq.ereflect_id = ?
                        ORDER BY meq.order_by';
																			
        $eqvar = new stdClass();
        //if($eq = $DB->get_records_sql($sql, array($USER->id, $instance->id)))
        //if($eq = $DB->get_records_sql($sql, array($user_id, $instance->id)))
        if($eq = $DB->get_records_sql($sql, array($params->student_id, $instance->id)))
        {
            foreach ($eq as $key => $value)
            {
                $eqvar->$key = $value;
                if($debug)
                {
                    echo 'Unique Id: '.$value->unique_id.'<br />';
                }
            }
        }
		
	// Get hold of the ereflect-user_response record (if exists) to obtain the mark and assignment time
	$equr = new stdClass();
	$table = 'ereflect_user_response';
	//if($eur = $DB->get_record($table, array('ereflect_id' => $instance->id, 'user_id' => $USER->id)))
	//if($eur = $DB->get_record($table, array('ereflect_id' => $instance->id, 'user_id' => $user_id)))
        if($eur = $DB->get_record($table, array('ereflect_id' => $instance->id, 'user_id' => $params->student_id)))
	{
            foreach ($eur as $key => $value)
            {
                $equr->$key = $value;
            }
        }
        /*else
        {
            $submitted = false;
            $notices[] = get_string('studentnotcomplete', 'mod_ereflect');     
            // The student has not completed the eReflect questionnaire
        }*/
        
        
	/* Notices - This is out of the box!! */
        foreach ($notices as $notice) {
            $o .= $this->get_renderer()->notification($notice);
        }        
		
        if($debug)
        {
            echo '<hr>';
            echo 'Questions/Answers Array: <br />';

            echo '<pre>';
            print_r($eq);
            echo '</pre>';
            echo '</hr>';				

            echo '<hr>';
            echo 'Questions/Answers Object: <br />';
            echo '<pre>';
            print_r($eqvar);
            echo '</pre>';
            echo '</hr>';

            echo '<hr>';
            echo 'Ereflect User Response Array: <br />';
            echo '<pre>';
            print_r($eur);
            echo '</pre>';            
            echo 'Ereflect User Response Object: <br />';
            echo '<pre>';
            print_r($equr);
            echo '</pre>';
            echo '</hr>';

        }		
        
		
        $data = new stdClass();		
        if (!$mform) {
            $mform = new mod_ereflect_addanswers_form(null, array($this, $data, $params, $eqvar, $equr, $instance));
            //$mform = new mod_ereflect_addanswers_form(null, array($this, $data, $eqvar, $equr));
        }		
	
        if($completed)
        {
           // If in teacher view, then ensure that we get the student name details, since USER details
           // will be that of the teacher and NOT the student
           if($b_teacherview /*|| $b_ejournalview*/)
           {
                $table = 'user';
                //$student = $DB->get_record($table, array('id' => $params->user_id));
                $student = $DB->get_record($table, array('id' => $params->student_id));
                // Student viewTeacher view   
                $name = $student->firstname.' '.$student->lastname;
           }
           else
           {   
                // Student viewTeacher view   
                $name = $USER->firstname.' '.$USER->lastname;
           }
           
           $o .= '<div><div class="div_answers_thankyou">'.get_string('answersthankyou','ereflect', $name).'</div>';           

            //$urlparams = array('id' => $module->id, 'user_id' => $USER->id, 'action' => 'VIEWPDF');
           $urlparams = array('id' => $module->id, 'student_id' => $params->student_id, 'action' => 'VIEWPDF');
           $viewpdfurl = new moodle_url('/mod/ereflect/view.php', $urlparams);
           $viewpdf = '<a href="#" onclick="open_window(\''.$viewpdfurl.'\')"><i class="fa fa-file-text-o fa-2x"></i>&nbsp;&nbsp;PDF Report</a>';

           $o .= '<div class="div_file_element">'.$viewpdf.'</div>';

           // Function to display all files and images nicely
           // params 1-field, 2-context, 3-areaname, 4-fieldid 
           $o .= $this->display_filesandimages( $instance->completion_message, $context, 'completion_message', 0 );
        }           
      
        	                                 
        $o .= $this->get_renderer()->render(new ereflect_form('addanswers', $mform));		        
        $o .= $this->view_footer();
        
        return $o;
    }	
    
    public function display_filesandimages( $editor_field, $context, $filearea, $fileareaid )
    {
        global $USER, $DB, $CFG;

        $debug = false;        
        $o = '';
        
        if($debug)
        {
            echo 'In display_filesandimages function <br />';
            echo 'Editor Field: '.$editor_field.'<br />';
            echo 'Context <br />';
            echo '<pre>';
            print_r($context);
            echo '</pre>';
            echo 'Filearea: '.$filearea.', Fileareaid: '.$fileareaid.'<br />';
        }
        
        if (trim($editor_field)) 
        {    
            /*$itemid = 0;
            if(isset($fileareaid)){
                $itemid = $fileareaid;
            }*/
                
            $mesg = file_rewrite_pluginfile_urls($editor_field, 'pluginfile.php', $context->id,
                            'mod_ereflect', $filearea, $fileareaid, ereflect::instruction_editors_options($context));

            $o .= $this->output->box(format_text($mesg, FORMAT_HTML, array('overflowdiv'=>true)), array('generalbox', 'instructions', 'div_answers_thankyou'));
        }

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_ereflect', $filearea, $fileareaid);
        
        $o .= '<div class="div_file_uploads">';
        foreach ($files as $file) {
            if($debug)
            {
                echo 'file <br />';
                echo '<pre>';
                print_r($file);
                echo '</pre>';
                echo 'filesize: '.$file->get_filesize();
                echo 'File item id: '.$file->get_itemid();
            }
            
            $filename = $file->get_filename();
            $filesize = $file->get_filesize();
            
            //echo 'Filename: '.$filename.', Filesize: '.$filesize.', Itemid: '.$itemid.'<br />';
            
            if($filesize>0)
            {
                $url = moodle_url::make_pluginfile_url($context->id, $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $filename);            
                $o .= '<div class="div_file_element"><a href="'.$url.'" target="_blank"><i class="fa fa-file-text-o fa-2x"></i>&nbsp;&nbsp;'.$filename.'</a></div><br />';
            }
        }    
        $o .= '</div> <!-- End of div_file_uploads-->';
        
        return $o;        
    }
		
    public function add_addanswers_form_elements(MoodleQuickForm $mform, stdClass $data, $params, $ereflect_questions, $ereflect_user_response ) 
    {			
        global $USER, $DB;

        $debug = false;
        
        $o = '';
        
        if($debug)
        {
            echo 'in add_addanswers_form_elements with parameters;<br />';
            echo '<pre>';
            print_r($params);
            echo '</pre>';
        }

        $context = $this->get_context();
        $instance = $this->get_instance();
        //$course = $this->get_course();
        $module = $this->get_course_module();
        //$module_name = $module->modname;		

        /*if(isset($params->user_id) && strlen($params->user_id))
        {
            $user_id = $params->user_id; // teacher view
            if($debug){echo 'Teacher View <br />';}
        }
        else
        {
            $user_id = $USER->id;
            if($debug){echo 'Student View <br />';}
        }
        $b_completed = $this->user_completed($user_id, 'COMPLETED'); //Call once here instead of calling multiple times*/
        
        $b_completed = $this->user_completed($params->student_id, 'COMPLETED'); //Call once here instead of calling multiple times

        if($debug)
        {            
            if($b_completed) {
                echo 'completed';
            } else {echo 'NOT completed';}
        }

        if($debug)
        {
            echo 'in locallib.add_addanswers_form_elements  - showing data<br />';		

            echo '<hr />';
            echo 'user passed in:<br />';
            echo '<pre>';
            print_r($USER);
            echo '</pre>';
            echo '<hr />';		

            echo '<hr />';
            echo 'ereflect_questions passed in:<br />';
            echo '<pre>';
            print_r($ereflect_questions);
            echo '</pre>';
            echo '<hr />';		

            echo 'Now module <br />';
            echo '<pre>';
            print_r($data);
            echo '</pre>';

            echo 'Parameters passed <br />';
            echo '<pre>';
            print_r($params);
            echo '</pre>';

            echo 'Course Module <br />';
            echo '<pre>';
            print_r($module);		
            echo '</pre>';

            echo 'Instance <br />';
            echo '<pre>';
            print_r($instance);		
            echo '</pre>';


            echo 'Context <br />';
            echo '<pre>';
            print_r($context);		
            echo '</pre>';

            echo 'instance id: '.$context->instanceid.'<br />';

            //echo 'module name: '.$module_name.'<br />';

            //echo 'Instance: '.$this->get_course_module()->instance.'<br />';
            //exit();
        }

        if(isset($params->view) && $params->view=='TEACHERVIEW')
        {
            $b_teacherview = true;
        }
        else
        {
            $b_teacherview = false;
        }
        
        // Comint from Ejournal view
        // Currently won't let Student or Teacher Edit or submit
        //
        if(isset($params->view) && $params->view=='EJOURNALVIEW'
           /* && $USER->id != $params->student_id*/  )
        {
            $b_teacherview = true;            
        }    
        
        // Get the count of questions
        $table = 'ereflect_questions';
        $where_clause = array('ereflect_id'=> $instance->id );
        $totalcount = $DB->count_records($table, $where_clause);                
        
        $mform->addElement('header', 'general', get_string('addanswerstitle', 'ereflect'));	                    
        $mform->addElement('html', get_string('questionscount','ereflect', $totalcount));        

        $var_meq_id = '';
        
        
        // loop showing questions
        $field_total = 0;
        $field_number = 0;
        $page_number = 1;
        $perpage = 99;
        
        if(isset($instance->questions_per_page) && strlen($instance->questions_per_page))
        {
            $perpage = $instance->questions_per_page;
        }
        
        $total_pages = CEIL($totalcount/$perpage);
            
        if($debug){echo 'Number per page: '.$perpage.', Number of pages: '.$total_pages.'<br />';}
                                                
        foreach ($ereflect_questions as $key => $value)
        {
            //echo 'Var meq id = '.$var_meq_id.', Loops MEQ id = '.$value->meq_id.'<br />';
            
            if($var_meq_id != $value->meq_id)
            {
                $var_feedback = '';

                // New Paging Stuff !!
                $field_total++;
                $field_number++;  // Add a field to the count
                if($debug){echo 'Field Number: '.$field_number.', Page Number: '.$page_number.', Field Total: '.$field_total.'<br />';}
                
                // If the field number is the first in the group, then
                if($field_number == 1)  // e.g. field 1 of 2,
                {
                    $mform->addElement('html', '<div id="div_page_'.$page_number.'" class="div_page">'."\n");
                    //$mform->addElement('html', 'start of div_page_'.$page_number);
                }                
                
                $mform->addElement('html', '<div class="div_question"><!-- Start of div_question -->'."\n");
                
                $mform->addElement('html', '<div class="div_question_title">'.str_pad($value->meq_order_by,10).$value->question_text.'</div>'."\n");                     

                //$radioarray=array();		
                //	
                // If is an open question, then show a text field
                
                // Description field

                if($value->open_question==1)
                {
                    //$field = 'answer_'.$value->meq_id;
                    $field = 'answer_'.$value->unique_id;

                    // If in Teacher Mode
                    // Using Moodles Helper class creates an extra open div with no closing tag, so will have to rewrite
                    $attributes = array('');
                    if($b_teacherview || $b_completed)
                    {
                        $attributes = array('id' => $field , 'wrap' => 'virtual', 'rows' => '10', 'cols' => '10', 'readonly' => 'true');
                    }
                    else
                    {
                        $attributes = array('id' => $field , 'wrap' => 'virtual', 'rows' => '10', 'cols' => '10');				
                    }
                    $mform->addElement('textarea', $field, get_string('openanswer', 'ereflect'), $attributes );
                    $mform->setType($field, PARAM_TEXT);		
                    //$mform->addRule($field, get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
                    if(isset($value->open_answer) && strlen($value->open_answer))
                    {
                        $mform->setDefault($field,$value->open_answer);					
                    }

                    /*
                    if($b_teacherview || $b_completed)
                    {
                        $attr_inline = ' disabled="disabled"';
                    }
                    $var_textarea = '';
                    if(isset($value->open_answer) && strlen($value->open_answer))
                    {
                        $var_textarea = $value->open_answer;
                    }
                    $mform->addElement('html','<div class="div_textarea"><textarea id="'.$field.'" rows="10" cols="10" wrap="virtual" '.$attr_inline.'>'.$var_textarea.'</textarea></div>');*/
                    
                } // Open Question 

                // Closed option fields
                if($value->open_question==0)
                {
                    // otherwise, show a series of options
                    $var_meo_id = '';
                    $var_icon = '';
                    $var_colour = '';                        
                    foreach( $ereflect_questions as $key2 => $value2)
                    {
                        //if($debug)
                        //{
                          //  echo '<hr />';
                            //echo 'in prep for loop array <br />';
                            //echo '<pre>';
                            //print_r($value2);
                            //echo '</pre>';
                            //echo '<hr />';
                        //}

                        // If the Ereflect question id in 1st loop is the same as Eqid in 2nd looped value
                        // but the option id from last time is not the same then add it to the radio array
                        if(($value2->meq_id == $value->meq_id)
                           &&($var_meo_id != $value2->meo_id))
                        {
                            $field = 'answer_'.$value->unique_id;

                            //if($debug){echo 'Field : '.$field.'<br />';}

                            // If in Teacher Mode or Stuent Mode
                            $attributes = array('');
                            if($b_teacherview || $b_completed )
                            {
                                $attributes = array('id' => $field, 'disabled' => 'disabled' );
                                $attr_inline = 'disabled="disabled"';
                            }
                            else
                            {
                                $attributes = array('id' => $field );
                            }

                            // This is simulating an extra div with no closing div !!!????
                            $mform->addElement('radio', $field, '', $value2->option_answer, $value2->meo_id, $attributes);
                            
                            
                            // Set the Feedback response for the particular question answered
                            //$attr_inline = '';
                            if($value2->meo_id == $value2->ereflect_option_id)
                            {
                                $var_feedback = $value2->option_feedback;  // Set the feedback variable
                                $var_icon = $value2->icon_name;
                                $var_colour = $value2->icon_colour; // Set the colour of traffic light
                                
                                //$attr_inline .= 'checked="checked"';
                            }                                                                
                            //$mform->addElement('html', '<div class="div_radio"><input type="radio" id="'.$field.'" name="'.$field.'" value="'.$value2->meo_id.'" '.$attr_inline.'>'.$value2->option_answer.'</div>');
                        }

                        $var_meo_id = $value2->meo_id;
                    } // END  for each to show options

                    // Do not use addGroup in this instance as it places them all on one line and we dont' want this, since we don't 
                    // know how many questions they are going to ask !!
                    //$mform->addElement('html', '<div class="div_eq_radio">');									
                    //$mform->addGroup($radioarray, 'option_'.$value->unique_id, '', array(' '), false);			
                    
                    //old way of doint it
                    if(isset($value->ereflect_option_id) && strlen($value->ereflect_option_id))
                    {
                        $mform->setDefault($field, $value->ereflect_option_id);
                    }
                    
                } // open question                    

                // Show Feedback information if the user has completed the Questionnaire
                //echo 'Option Id: '.$value->ereflect_option_id.', MEO id: '.$value->meo_id.'<br />';				
                //if($this->user_completed() && ($value->ereflect_option_id == $value->meo_id))
                if($b_completed & isset($var_feedback) && strlen($var_feedback))
                {
                    //if($debug){ echo 'IN HERE, symbol = '.$var_icon.', Colour = '.$var_colour.'<br />'; }

                    $mform->addElement('html', '<div><p style="color: '.$var_colour.'"><span class="option_icon"><i id="option_icon_symbol" class="fa '.$var_icon.' fa-2x"></i></span>'.$var_feedback.'</p></div>');
                }

                if($b_completed)
                {
                    // function to display all files and images nicely
                    // params 1-field, 2-context, 3-areaname, 4-fieldid 
                    //$o .= $this->display_filesandimages( $instance->completion_message, $context, 'completion_message', null );                    
                    //echo 'Just before feedback message; value: '.$value->meq_id.'<br />';
                    $filedet = $this->display_filesandimages( $value->feedback_message, $context, 'feedback_message', $value->meq_id );
                    $mform->addElement('html', $filedet);
                }
                
                $mform->addElement('html', '</div> <!-- End of div_question --> '."\n");  
                

                // Last Page
                if($page_number == $total_pages && $field_total == $totalcount)  // if field counter equals total pages then show save and cancel button
                {
                    if($total_pages>1)
                    {
                        $previous_page = $page_number-1;
                        $mform->addElement('html', '<div id="div_prev_next">');                           
                        $mform->addElement('html', '<span style="align: center;"><a href="#" onClick="go_page( \''.$previous_page.'\',\''.$total_pages.'\');">'.get_string('previous_page','mod_ereflect').'</a></span>');
                        $mform->addElement('html', '</div>');
                        //$mform->addElement('html', 'Will show Save and Cancel button<br />');
                        //if($debug){echo 'will show save and cancel <br />';}
                    }

                    if(($instance->include_preptime_in_report == 1) || ($instance->include_mark_in_linegraph == 1))
                    {
                        $mform->addElement('html', '<div class="div_question_title">'.get_string('extra_questions','mod_ereflect').'</div>');
                    }

                    if($instance->include_preptime_in_report == 1)
                    {
                        $field = 'assignment_time';
                        // Disable the answers for the questionnaire
                        $attributes = array("maxlength"=>"6", "size"=>"10");

                        if($b_teacherview)
                        {
                            $attributes = array('disabled' => 'disabled' );
                        }
                        else
                        {
                            // If the User has completed the questionnaire, then disable the answers
                            if($b_completed)
                            {
                                $attributes = array('disabled' => 'disabled' );					
                            }
                        }
                        $mform->addElement('duration', $field, get_string($field, 'ereflect'), array('optional' => false), $attributes);		
                        $mform->addRule($field, null, 'required', null, 'client');
                        //$mform->addRule($field, get_string('maximumchars', '', 10), 'maxlength', 10, 'client');                        

                        if(isset($ereflect_user_response->$field) && strlen($ereflect_user_response->$field))
                        {
                            $mform->setDefault($field, $ereflect_user_response->$field);
                        }
                    }

                    if($instance->include_mark_in_linegraph == 1)
                    {
                        $field = 'grade';
                        $attributes = array('');
                        if($b_teacherview)
                        {
                            $attributes = array('disabled' => 'disabled' );
                        }
                        else
                        {
                            // If the User has completed the questionnaire, then disable the answers
                            if($b_completed)
                            {
                                $attributes = array('disabled' => 'disabled' );					
                            }
                        }			

                        for ($i = 100; $i >= 1; $i--) {
                            $grades[$i] = $i;
                        }
                        $mform->addElement('select', $field, get_string($field, 'ereflect'), $grades, $attributes);			
                        if(isset($ereflect_user_response->$field) && strlen($ereflect_user_response->$field))
                        {
                            $mform->setDefault($field, $ereflect_user_response->$field);			
                        }
                    }                    
                    
                    
                    $mform->addElement('html', '</div> <!-- End of div_page_'.$page_number.' -->'); // div_page_$number                      
                }           
                
                if($debug){echo 'Field no: '.$field_number.', Per Page: '.$perpage.'<br />';}
                        
                if($field_number==$perpage)
                {    
                   if($page_number != $total_pages)  // if count equals number per page the show next button
                   {    
                        $mform->addElement('html', '<div id="div_prev_next">');                         
                        if($page_number!=1)
                        {
                            $previous_page = $page_number-1;
                            $mform->addElement('html', '<a href="#" onClick="go_page( \''.$previous_page.'\',\''.$total_pages.'\');">'.get_string('previous_page','mod_ereflect').'</a>&nbsp;&nbsp;&nbsp;&nbsp;');
                        }                    
                        $next_page = $page_number+1;
                        $mform->addElement('html', '<a href="#" onClick="go_page( \''.$next_page.'\',\''.$total_pages.'\');">'.get_string('next_page','mod_ereflect').'</a>'); 
                        $mform->addElement('html', '</div>');                        
                   }
                   $mform->addElement('html', '</div> <!-- End of div_page_'.$page_number.' -->'); // div_page_$number                        
                   //$mform->addElement('html', 'End of div_page_'.$page_number ); // 
                                        
                   $page_number++; // Next Page page effectively                       
                   $field_number=0;  // Reset the field number count
                }        
               
                //if($debug){echo 'Page number: '.$page_number.', Total Pages: '.$total_pages.'<br />';}

            }

            $var_meq_id = $value->meq_id;
    
        } // End loop

        // Hidden params.        
        $mform->addElement('hidden', 'id', $this->get_course_module()->id);
        $mform->setType('id', PARAM_INT);						

        $mform->addElement('hidden', 'action', $params->action);
        $mform->setType('action', PARAM_TEXT);		
        
        
        if(isset($params->pageno) && strlen($params->pageno))
        {
            //echo 'Using parameter settings for page: '.$params->pageno.'<br />';
            $pageno = $params->pageno;
        }
        else
        {
            //echo 'setting to 1<br />';
            $pageno = 1;
        }
        
        if(isset($params->ejournal_id) && strlen($params->ejournal_id))
        {
            $mform->addElement('hidden', 'ejournal_id', $params->ejournal_id);
            $mform->setType('ejournal_id', PARAM_INT);						    
        }

        if(isset($params->student_id) && strlen($params->student_id))
        {
            $mform->addElement('hidden', 'student_id', $params->student_id);
            $mform->setType('student_id', PARAM_INT);
        }
        
        //$mform->addElement('hidden', 'pageno', $pageno, array("id"=>"hiddenpageno"));
        $mform->addElement('hidden', 'pageno', $pageno);
        $mform->setType('pageno', PARAM_INT);						
        
        $mform->addElement('html', '<script>go_page( \''.$pageno.'\',\''.$total_pages.'\');</script>');         
        
    }
	

    /*protected function get_student_complete_button()
    {	
        $debug = false;
        $o = '';

        if($debug)
        {
            echo 'in get_student_complete_button function<br />';
        }

        $instance = $this->get_instance(); // set this to get the Ereflect_id

        $urlparams = array('id' => $this->get_course_module()->id, 'action'=> 'COMPLETE_STUDENT_PROCESS');
        $completeurl = new moodle_url('/mod/ereflect/view.php', $urlparams);						
        //$complete = '<button type="button" onclick="studentcomplete(\''.$completeurl.'\');">'.get_string('student_complete', 'ereflect').'</button>';
        $complete = '<button type="button" href="#" onclick="javascript: document.getElementById(\'saveandsubmit\').submit();">'.get_string('student_complete', 'ereflect').'</button>';

        $o .= $this->output->container_start('completebuttons');
        $o .= $complete;
        $o .= $this->output->container_end();				

        return $o;
    }*/
	
    protected function process_add_answers(&$mform, &$notices) {

        global $DB, $USER, $CFG;

        // Include submission form.
        require_once($CFG->dirroot . '/mod/ereflect/class/addanswers_form.php');

        $debug = false;

        if($debug)
        {
            echo 'In locallib.process_add_answers function<br />';
        }

        $instance = $this->get_instance();

        $data = new stdClass();
        $eqvar = new stdClass();

        // Get the 
        $sql = 'SELECT 	coalesce(CONCAT(CAST(meq.id AS CHAR), \'_\', CAST(meo.id AS CHAR)), CONCAT(CAST(meq.id AS CHAR), \'_\') ) as unique_id,
                                meq.id meq_id, meq.ereflect_id, meq.question_name, meq.question_text, 
                                meq.open_question, meq.no_of_options, meq.order_by meq_order_by,
                                meo.id meo_id, meo.ereflect_question_id, meo.option_answer, meo.option_feedback,
                                meo.order_by meo_order_by, meo.showicon, meo.icon_name, meo.icon_colour,
                                mea.ereflect_option_id, mea.open_answer
                FROM {ereflect_questions} meq
                LEFT OUTER JOIN {ereflect_options} meo ON meq.id = meo.ereflect_question_id
                LEFT OUTER JOIN {ereflect_answers} mea ON meq.id = mea.ereflect_question_id AND mea.user_id = ?
                WHERE meq.ereflect_id = ?
                ORDER BY meq.order_by';

        //if($eq = $DB->get_records_sql($sql, array($params->ereflect_id)))
        if($eq = $DB->get_records_sql($sql, array($USER->id, $instance->id)))
        {
            foreach ($eq as $key => $value)
            {
                $eqvar->$key = $value;

                if($debug)
                {
                    echo 'Unique Id: '.$value->unique_id.'<br />';
                }
            }
        }
		
        // Get hold of the ereflect-user_response record (if exists) to obtain the mark and assignment time
        $equr = new stdClass();
        $table = 'ereflect_user_response';
        if($eur = $DB->get_record($table, array('ereflect_id' => $instance->id, 'user_id' => $USER->id)))
        {
            foreach ($eur as $key => $value)
            {
                $equr->$key = $value;
            }
        }

        if($debug)
        {
            echo '<hr />';
            echo 'Questions/Options returned for Ereflect id ';
            echo '<pre>';
            print_r($eqvar);
            echo '</pre>';
            echo '<hr />';
        }

        //$mform = new mod_ereflect_addanswers_form(null, array($this, $data, $params, $eqvar));		
        $params = new stdClass();
        $mform = new mod_ereflect_addanswers_form(null, array($this, $data, $params, $eqvar, $equr, $instance));
        
        // if($data = $mform->get_data())
        if(!($data = $mform->get_data()))
        {
            //echo 'Went into this instead and returning false';
            return false;
        }
        else
        {
            if($debug)
            {
                echo 'In locallib.process_add_asnwers<br />';
                //echo 'Question Name: '.$data->question_name_1;

                echo 'this<br />';
                echo '<pre>';
                print_r($this);
                echo '</pre>';

                echo 'All data <br />';
                echo '<pre>';
                print_r($data);
                echo '</pre>';			
            }

            try
            {
                $transaction = $DB->start_delegated_transaction();

                // Delete Records from ereflect_answers and insert again
                //$DB->delete_records('ereflect_answers', array('ereflect_id'=>$params->ereflect_id));
                $DB->delete_records('ereflect_answers', array('ereflect_id' => $instance->id, 'user_id' => $USER->id ));

                /// Delete the answer records successful

                // Loop through all the answers and try and set them to the questions
                foreach($data as $key => $value)
                {
                    // Trap each value that is an answer, and get hold of the MEQ id

                    if($debug)
                    {
                        echo '<hr />';
                        echo 'loop';
                        echo '<pre>';
                        print_r($value);
                        echo '</pre>';
                        echo '<hr />';
                    }
                    $pos = strpos($key, 'answer_');

                    if(isset($pos) && strlen($pos))
                    {
                        $pieces = explode("_", $key);
                        $eqid = $pieces[1]; // piece 2 i.e. the mdl_ereflect_question_id

                        if($debug)
                        {
                            echo 'Found a key with answer: Key is "'.$key.', Pieces: "<br />';
                            echo '<pre>';
                            print_r($pieces);
                            echo '</pre>';

                            echo '<hr />';
                            echo 'Eq Id will be set to : '.$eqid.'<br />';
                        }				

                        $insert = new stdClass();
                        $insert->user_id = $USER->id;
                        //$insert->user_id = 50; not validating against the mdl_user table!??
                        $insert->ereflect_id = $instance->id;
                        // The last n digits of a string with 'answer_' on the front
                        $insert->ereflect_question_id = $eqid;  

                        $unique_id = substr($key, 7);

                        if($debug)
                        { 
                            echo 'Unique Id: '.$unique_id;
                            //exit();
                        }

                        if($eq[$unique_id]->open_question==1)
                        {
                            // Get question id
                            // Check open
                            // Add if statement as inserting a blank space for some reason
                            if(isset($value) && strlen($value))
                            {    
                                $insert->open_answer = $value;
                            }
                            $insert->open_question_ind = 1;
                        }
                        else
                        {
                            $insert->ereflect_option_id = $value;
                            $insert->open_question_ind = 0;					
                        }
                        $insert->timecreated = time();

                        if($ea_id = $DB->insert_record('ereflect_answers', $insert))
                        {
                            if($debug){echo 'In insert record section <br />';}
                        }
                        else
                        {
                            if($debug){echo 'Failed to insert record <br />';}
                            $notices[] = get_string('answerinsertfailed', 'mod_ereflect');
                            return false;
                        }	
                    }
                }  // end foreach


                // Need to either insert a new record in ereflect_user_response Or update an existing record

                $table = 'ereflect_user_response';
                if(!$eur = $DB->get_record($table, array('ereflect_id' => $instance->id, 'user_id' => $USER->id, 'status' => 'PARTIAL')))
                {
                    $insert2 = new stdClass();
                    $insert2->course = $instance->course;
                    $insert2->user_id = $USER->id;
                    $insert2->ereflect_id = $instance->id;
                    if($instance->include_preptime_in_report == 1)
                    {
                        $insert2->assignment_time = $data->assignment_time;
                    }		
                    if($instance->include_mark_in_linegraph == 1)
                    {
                        $insert2->grade = $data->grade;
                    }
                    $insert2->status = 'PARTIAL';
                    $insert2->timecreated = time();				

                    if($debug)
                    {
                        echo '<hr />';
                        echo 'About to insert record <br />';
                        echo '<pre>';
                        print_r($eur);
                        echo '</pre>';
                        echo '<hr />';
                    }

                    if(!$DB->insert_record($table, $insert2))
                    {
                        $notices[] = get_string('add_ereflect_user_response_failed', 'mod_ereflect');
                        return false;
                    }
                }
                else
                {
                    if($debug)
                    {
                        echo '<hr />';
                        echo 'About to update record for '.$table.'<br />';
                        echo '<pre>';
                        print_r($eur);
                        echo '</pre>';
                        echo '<hr />';
                    }

                    $update = new stdClass();
                    $update->id = $eur->id;
                    if($instance->include_mark_in_linegraph == 1)
                    {
                        $update->grade = $data->grade;
                    }
                    if($instance->include_preptime_in_report == 1)
                    {
                        $update->assignment_time = $data->assignment_time;
                    }
                    $update->timemodified = time();

                    if(!$DB->update_record($table, $update, $bulk=false))
                    {
                        $notices[] = get_string('upd_ereflect_user_response_failed', 'mod_ereflect');			
                        return false;
                    }						
                }			

                // otherwise update existing record

                $transaction->allow_commit();																		
            }
            catch(Exception $e) 
            {     
                $transaction->rollback($e);
                $notices[] = $e;
                return false;
            }		

            return true;		
        }
    }            
	
    public function view_response_stats($notices, $params )
    {
        global $DB, $CFG, $USER, $PAGE;
		
        $debug = false;
	
        if($debug)
        {
            echo 'In function view_response_stats <br />';
        }
        $instance = $this->get_instance();		
        $course = $this->get_course();
		
        $o = '';	
		
        // Header
        $o .= $this->get_renderer()->render(new ereflect_header($instance,
                                              $this->get_context(),
                                              $this->show_intro(),
                                              $this->get_course_module()->id,
                                            $this->get_course()),'HELLO'); //get_string('viewstatistics','ereflect')
				
        $datestring = new stdClass();
        $datestring->year  = get_string('year');
        $datestring->years = get_string('years');
        $datestring->day   = get_string('day');
        $datestring->days  = get_string('days');
        $datestring->hour  = get_string('hour');
        $datestring->hours = get_string('hours');
        $datestring->min   = get_string('min');
        $datestring->mins  = get_string('mins');
        $datestring->sec   = get_string('sec');
        $datestring->secs  = get_string('secs');

        $data_response = array();
        $data_complete = array();
        $data_noresponse = array();

        // Function to get Students Enroled on the course who all should complete
        $students = $this->users_who_can_complete();		
		
        foreach ($students as $student) 
        {
            //$user = $DB->get_record('user', array('id' => $student->id));
			
            $profileurl = $CFG->wwwroot.'/user/view.php?id='.$student->id.'&amp;course='.$course->id;
            $profilelink = '<strong><a href="'.$profileurl.'">'.fullname($student).'</a></strong>';
			
            $last_access = format_time(time() - $student->lastaccess, $datestring);										

            // Get literals - these will show as heading literals in each table
            $picture = get_string('user_picture','mod_ereflect');
            $profile = get_string('user_profile','mod_ereflect');
            $user_email = get_string('user_email','mod_ereflect');
            $last_accessed = get_string('last_accessed','mod_ereflect');
			
            // Assign key value pairs in an array that will get passed into class below to create a summary table
            $data = array ( 'id' => $student->id,
                            $picture => $this->output->user_picture($student, array('courseid' => $course->id)), 
                            $profile => $profilelink, 
                            $user_email => $student->email, 
                            $last_accessed => $last_access);
			
            // Check if partially or fully completed questionnaire
            $table = 'ereflect_user_response';
            $conditions = array('ereflect_id' => $instance->id, 'user_id' => $student->id);		
            if($eur = $DB->get_record($table, $conditions))
            {
                if($debug)
                {
                    echo '<pre>';
                    print_r($eur);
                    echo '</pre>';
                }
								
                $created = format_time(time() - $eur->timecreated, $datestring);
                if( (isset($eur->timemodified) && strlen($eur->timemodified)) && $eur->timemodified!=0)
                {
                    $modified = format_time(time() - $eur->timemodified, $datestring);
                    //echo 'Timemodified: '.$eur->timemodified.', Modified: '.$modified.'<br />';
                }
                else
                {
                    $modified = '';
                }
			
                $user_created = get_string('user_created','mod_ereflect');
                $user_modified = get_string('user_modified','mod_ereflect');

                $data[$user_created] = $created;
                $data[$user_modified] = $modified;

                if($eur->status == 'COMPLETED')
                {
                    // Collect the completed students
                    $data['viewbutton'] = 'YES';
                    $data['viewpdf'] = 'YES';

                    $data_complete[] = $data;
                    //$table->add_data($data);
                }
                else
                {
                    // Collect the partially completed (responded) students
                    $data['viewbutton'] = 'YES';
                    $data['viewpdf'] = 'NO';

                    $data_response[] = $data;
                }
            }						
            else
            {
                // Collect the students who answered anything 
                $data['viewbutton'] = 'NO';
                $data['viewpdf'] = 'NO';

                $data[''] = '';
                $data[''] = '';

                $data_noresponse[] = $data;
            }
        }

        if($debug)
        {
            echo 'Respondents/Non-respondents<br />';
            echo 'respondents first: Partial<br />';
            echo '<pre>';
            print_r($data_response);
            echo '</pre>';
            echo 'complete; <br />';
            echo '<pre>';
            print_r($data_complete);
            echo '</pre>';
            echo 'Non-Respondents<br />';			
            echo '<pre>';
            print_r($data_noresponse);
            echo '</pre>';
        }	
				
        /*$viewpdfurl = new moodle_url('/mod/ereflect/class/cohort_report.php');		
        $viewpdf = '<a href="#" onclick="open_window(\''.$viewpdfurl.'\')"><i class="fa fa-file-text-o fa-2x"></i>The Other Way</a>';
        $o .= $viewpdf;*/
        
        // This will allow the teacher to place the eReflect On-Hold.
        if(count($data_complete)==0 && count($data_response)==0)
        {
            $params->amend = 'OFF';  
            
            $o .= '<hr />';		
            $o .= $this->get_complete_button($params);
            $o .= '<hr />';		
        }        
		
        if(count($data_complete)>0)
        {
            $urlparams = array('id' => $this->get_course_module()->id, 'action' => 'VIEWCOHORTREPORT');
            $viewpdfurl = new moodle_url('/mod/ereflect/view.php', $urlparams);		
            $viewpdf = '<a href="#" onclick="open_window(\''.$viewpdfurl.'\')"><i class="fa fa-file-text-o fa-2x"></i>&nbsp;&nbsp;&nbsp;'.get_string('view_cohort','ereflect').'</a>';

            $o .= '<p>'.$viewpdf.'</p>';																					
        }
        		
        // Show a Table of Students who have completed			
        if(count($data_complete)>0)
        {
            $dc = new stdClass();		
            foreach($data_complete as $key => $value)
            {
                $dc->$key = $value;
            }
            // Students who have completed
            $view_users_completed_details = new ereflect_summary_table(	$dc, 
                                                                        $this->get_course_module()->id, 
                                                                        $instance->id,
                                                                        get_string('respondents_c', 'ereflect') );

            $o .= $this->get_renderer()->render($view_users_completed_details);
        }
					
        // Show a table of Students who have partially completed				
        if(count($data_response)>0)
        {
            $dr = new stdClass();
            foreach($data_response as $key => $value)
            {
                $dr->$key = $value;
            }
            $view_users_completed_details = new ereflect_summary_table(	$dr, 
                                                                        $this->get_course_module()->id, 
                                                                        $instance->id,
                                                                        get_string('respondents', 'ereflect') ); 																		
																			
            $o .= $this->get_renderer()->render($view_users_completed_details);
        }
				
        // Students who have not responded yet
        if(count($data_noresponse)>0)
        {
            $dn = new stdClass();
            foreach($data_noresponse as $key => $value)
            {
                $dn->$key = $value;
            }
            // Students who have completed
            $view_users_completed_details = new ereflect_summary_table(	$dn, 
                                                                        $this->get_course_module()->id, 
                                                                          $instance->id,
                                                                          get_string('non-respondents', 'ereflect') ); 

            $o .= $this->get_renderer()->render($view_users_completed_details);
        }

        // Footer
        $o .= $this->view_footer();

        return $o;	
    }
	
    public function users_who_can_complete()
    {
        global $DB;

        $debug = false;

        if($debug){echo 'in users_who_can_complete';}

        $context = $this->get_context();
        //$context = context_module::instance($cm->id);

    // First get all users who can complete this questionnaire.
        $group = false;
        $sort = 'u.lastname';
        $cap = 'mod/ereflect:submit';
        //$fields = 'u.id, u.username, u.email, u.lastaccess'; Select all fields instead of including this!
        if (!$allusers = get_users_by_capability($context,
                                        $cap,
                                        '',  /* $fields would normally go here */
                                        $sort,
                                        '',
                                        '',
                                        $group,
                                        '',
                                        true)) 
        {
            return false;
        }
        //$allusers = array_keys($allusers);

        if($debug)
        {
            echo '<hr />';
            echo 'All Users who are able to complete questionnaire';
            echo '<pre>';
            print_r($allusers);
            echo '</pre>';
            echo '<hr />';
        }

        return $allusers;

    }
    
    
    public function isateacher( $user_id )
    {
        global $DB;

        $debug = false;
        
        $teachers = $this->users_who_can_teach();
        
        if($debug)
        {
            echo 'in function isateacher with $user_id '.$user_id.'<br />';
        }
        
        $b_match = false;
        foreach($teachers as $t)
        {
            if($debug)
            {
                echo '<pre>';
                print_r($t);
                echo '</pre>';
            }
            
            if($t->id == $user_id)
            {
                if($debug){echo 'Found Teacher: '.$t->id.'<br />';}
                
                $b_match = true;
                break;
            }
        }
        
        return $b_match;
        
    }    

    public function users_who_can_teach()
    {
        global $DB;

        $debug = false;

        $context = $this->get_context();

        //$context = context_module::instance($cm->id);

        if($debug)
        {
            echo '<hr />';
            echo 'in locallib.users_who_can_teach, context:';
            echo '<pre>';
            print_r($context);
            echo '</pre>';
            echo '<hr />';
        }		

    // First get all users who can complete this questionnaire.
        $group = false;
        $sort = 'u.lastname';
        //$cap = 'mod/ereflect:submit';
        $cap = 'mod/ereflect:grade';
        //$fields = 'u.id, u.username, u.email, u.lastaccess'; Select all fields instead of including this!

        if (!$allusers = get_users_by_capability($context,
                                        $cap,
                                        '',  /* $fields would normally go here */
                                        $sort,
                                        '',
                                        '',
                                        $group,
                                        '',
                                        true)) 
        {
            return false;
        }
            //$allusers = array_keys($allusers);

        if($debug)
        {
                echo '<hr />';
                echo 'All Users who are able to teach';
                echo '<pre>';
                print_r($allusers);
                echo '</pre>';
                echo '<hr />';
        }

        return $allusers;
	
    }	
	
    public function get_student_data($user_id)
    {
        global $DB;
        $instance = $this->get_instance();

        $sql = 'SELECT meq.order_by, mea.ereflect_id, mea.user_id, meq.question_text, meq.open_question, IFNULL(meo.option_answer, mea.open_answer) option_answer, meo.option_feedback
                        FROM {ereflect_answers} mea
                        JOIN {ereflect_questions} meq ON meq.id = mea.ereflect_question_id
                        LEFT OUTER JOIN {ereflect_options} meo ON meo.id = mea.ereflect_option_id
                        WHERE mea.ereflect_id = ?
                        AND mea.user_id = ?
                        ORDER BY meq.order_by';

        $eq = $DB->get_records_sql($sql, array($instance->id, $user_id));

        $eqvar = new stdClass();
        foreach($eq as $key => $value)
        {
            $eqvar->$key = $value;
        }

        return $eqvar;
    }
	
	
    public function user_completed( $user_id, $status = NULL )
    {
        global $DB, $USER;

        $instance = $this->get_instance();

        if(isset($user_id) && strlen($user_id))
        {
            $v_user_id = $user_id;
        }
        else
        {
            $v_user_id = $USER->id;
        }

        $table = 'ereflect_user_response';
        if(isset($status) && strlen($status))
        {
            $conditions = array('ereflect_id' => $instance->id, 'user_id' => $v_user_id, 'status' => $status);
        }
        else
        {
            $conditions = array('ereflect_id' => $instance->id, 'user_id' => $v_user_id );
        }

        if(!$eur = $DB->get_record($table, $conditions))
        {
            return false;
        }
        return true;

    }
	
    public function user_started($user_id = '')
    {
        global $DB, $USER;		
        $instance = $this->get_instance();		

        // Either user User Id passed in or use the Logged in User
        if(isset($user_id) || strlen($user_id))
        {
            $uid = $user_id;
        }
        else
        {
            $uid = $USER->id;
        }

        $table = 'ereflect_user_response';
        $conditions = array('ereflect_id' => $instance->id, 'user_id' => $uid);
        if(!$eur = $DB->get_record($table, $conditions))
        {
            return false;
        }
        return true;
    }	

	
    public function check_enough_questions(&$notices)
    {
        global $DB;

        $instance = $this->get_instance();
        $table = 'ereflect_questions';
        $conditions = array('ereflect_id'=>$instance->id); // count existing number of questions
        $countrecs = $DB->count_records($table, $conditions);				
        if($countrecs<2)
        {
                return false;				
        }	
        return true;
    }

    public function get_student_profile($user_id)
    {
        global $DB;		
        $debug = false;

        if($debug)
        {
            echo 'In get_student_profile <br />';
        }

        //$instance = $this->get_instance();		
        $course = $this->get_course();

        $o = '';

        $table = 'user';
        $conditions = array('id' => $user_id);		
        if($ur = $DB->get_record($table, $conditions))
        {
            if($debug)
            {
                echo '<pre>';
                print_r($ur);
                echo '</pre>';
            }
        }

        $picture = $this->output->user_picture($ur, array('courseid' => $course->id)); 		

        $o .= '<div class="div_profile_title">'.get_string('profile_details','mod_ereflect').'</div>';
        $o .= '<span>'.$picture.'</span>&nbsp;<span>'.$ur->firstname.' '.$ur->lastname.'</span>';
        $o .= '<div>&nbsp;</div>';

        return $o;

    }
	
			
    function print_pdf( $user_id, $output_type = 'I', $pfilename = '')
    {
        global $CFG, $DB, $USER;

        require_once("$CFG->libdir/pdflib.php");
        require_once($CFG->dirroot . '/mod/ereflect/class/chart.php');		
        
        set_time_limit(0);

        $debug = false;

        $instance = $this->instance;
        $course = $this->course;
        $cm = $this->coursemodule;

        // Get Student Details
        $table = 'user';	
        $conditions = array('id' => $user_id);
        $student = $DB->get_record($table, $conditions);	

        // Get Questions, Answers and Feedback
        $qanda = $this->get_student_data($user_id);
		
        if($debug)
        {
            echo 'In locallib.print_pdf <br />';
            echo 'Student Details; <br />';
            echo '<pre>';
            print_r($student);
            echo '</pre>';
            echo 'QandA; <br />';
            echo '<pre>';
            print_r($qanda);
            echo '</pre>';
        }

        make_cache_directory('tcpdf');

        define ('PDF_HEADERS_STRING', "\n".$cm->name); // header description string
        define ('PDF_AUTHORS', 'Graeme Roberts'); // document author 
        define ('PDF_HEADER_LOGOS', 'mod/ereflect/pdf/CMET_landscape_logo_blue.jpg'); // header logo image width [mm]
        define ('PDF_HEADER_LOGOS_WIDTH', 60); // image logo
        define ('PDF_HEADERS_TITLE', 'eReflect Questionnaire Report'); // header title 

        //define ('PDF_FONT_NAME_MAIN', 'helvetica'); 	//default main font name
        //define ('PDF_FONT_SIZE_MAIN', 10); 	// default main font size
        //define ('PDF_FONT_MONOSPACED', 'courier'); 	// default monospaced font name
        //define ('PDF_MARGIN_HEADER', 5); // header margin
        //define ('PDF_MARGIN_FOOTER', 10); // footer margin
        //define ('PDF_MARGIN_TOP', 27);	// top margin
        //define ('PDF_MARGIN_BOTTOM', 25); // bottom margin
        //define ('PDF_MARGIN_LEFT', 15);	// left margin
        //define ('PDF_MARGIN_RIGHT', 15);	//right margin
        //define ('PDF_FONT_NAME_DATA', 'helvetica');	// default data font name
        //define ('PDF_FONT_SIZE_DATA', 8);	// default data font size
        //define ('PDF_IMAGE_SCALE_RATIO', 1);	// ratio used to adjust the conversion of pixels to user units
        define ('PDF_FONT_STYLE_BOLD', 'B'); // Font style bold

        $pdf = new PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true); // Portrait

        //$pdf->SetCreator(PDF_CREATORS);
        $pdf->SetAuthor(PDF_AUTHORS);
        $pdf->SetTitle(PDF_HEADERS_TITLE);

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGOS, PDF_HEADER_LOGOS_WIDTH, PDF_HEADERS_TITLE, PDF_HEADERS_STRING, array(1,44,86), array(1,44,86));

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

        // 1st page
        $pdf->AddPage();

        $pdf->SetTextColor(1,44,86); // Cardiff Met Blue
        $pdf->SetFont(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN);

        // Set some content to print
        $html = '<div style="margin-bottom: 100px;">Please find the questions and your answers included along with some feedback.<br />';
        $html .= 'After reviewing this document, can you please enter any further comments within the E-Journal.</div>';

        // Print text using writeHTMLCell()
        $pdf->writeHTMLCell(0, 20, '', '', $html, 0, 1, 0, true, '', true);
        
        $tbl = '<table border="0" cellpadding="2">
         <tr>
          <td width="50%" style="height:50px;"><b>Name</b></td>
          <td width="50%" style="height:50px;">'.$student->firstname.' '.$student->lastname.'</td>
         </tr>
         <tr>
          <td width="50%" style="height:50px;"><b>Course</b></td>
          <td width="50%" style="height:50px;">'.$course->fullname.'</td>
         </tr> 
         <tr>
          <td width="50%" style="height:50px;"><b>Assessment</b></td>
          <td width="50%" style="height:50px;">'.$cm->name.'</td>
         </tr> 
        </table>';
   
        $pdf->writeHTML($tbl, true, false, false, false, '');
       
        //2nd Page

        $pdf->AddPage();
        $pdf->SetFont(PDF_FONT_NAME_MAIN, PDF_FONT_STYLE_BOLD, PDF_FONT_SIZE_MAIN+6);
        $pdf->Write(0, 'Student Feedback', '', 0, 'C', true, 0, false, false, 0);
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

        foreach($qanda as $key => $value)
        {
            $tbl .= '
             <tr>
              <td width="4%" align="center">'.$key.'</td>
              <td width="32%">'.$value->question_text.'</td>
              <td width="32%">'.$value->option_answer.'</td>
              <td width="32%"><i class="fa fa-anchor"></i>'.$value->option_feedback.'</td>
             </tr>
             ';
        }
        $tbl .= '</table>';

        $pdf->writeHTML($tbl, true, false, false, false, '');	

        // Only Include Student Study Prep time Bar Graph if its set 
        if($instance->include_preptime_in_report==1)
        {
            // Get completed fields e.g. Mark and actual time spent
            $table = 'ereflect_user_response';
            $conditions = array('ereflect_id' => $instance->id, 'user_id' => $user_id);
            $response = $DB->get_record($table, $conditions);
            
            if($debug)
            {                     
                echo '<pre>';
                print_r($response);
                echo '</pre>';			
            }
                                    
            $preptime = $instance->preparationtime/60/60;
            $actualtime = $response->assignment_time/60/60;

            if(isset($preptime) && strlen($preptime))
            {
                ob_clean();
                ob_start();

                // Set Variables for Bar Graph
                $keyvalues = array( 'Suggested Hours' => $preptime, 'Actual Hours' => $actualtime	); 		
                // these are default values and so don't need to be mentioned

                $graph = new chart($keyvalues);
                $filename = 'timegraph_ereflect_'.$instance->id.'_user_'.$student->id.'.png';

                $studygraph = $graph->create_bar_graph($filename);
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

            if($debug){echo 'User id: '.$user_id.', Course Id: '.$course->id.' <br />';}

            // Cant user $response as need to get each individual mark for the user in question
            $sql = "SELECT er.id, er.name, eur.grade 
                    FROM {ereflect_user_response} eur
                    JOIN {ereflect} er ON  eur.ereflect_id = er.id
                    WHERE eur.user_id = ?
                    AND eur.course = ?
                    AND eur.status = 'COMPLETED'
                    ORDER BY eur.ereflect_id";

            if($eq = $DB->get_records_sql($sql, array($user_id, $course->id)))
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

            ob_clean();
            ob_start();

            // Set Variables for Line Graph
            //$keyvalues = array( 'Mark 1' => '61', 'Mark 2' => '46', 'Mark 3' => '71'	); 		
            //$graph = new chart($keyvalues);
            $graph = new chart($eqarr);
            $graph->maxvalue = 100;		

            $filename = 'markgraph_ereflect_'.$instance->id.'_user_'.$student->id.'.png';

            $markgraph = $graph->create_line_graph($filename, $horizontallines = 10);
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
               	
        // If the filename is not set then set it !
        if(!isset($pfilename) || !strlen($pfilename))
        {
            $pfilename = 'ereflect_'.$instance->id.'_user_'.$student->id.'.pdf';
        }

        $copyfolder = $CFG->cachedir.'/tcpdf/'.$pfilename;
        // $output_type is default to 'I' i.e. screen
        $pdfoutput = $pdf->Output($copyfolder, $output_type); // open in browser $output_type is 'I' Screen or 'F' Fiole

        if($debug)
        {
            echo 'Filename: '.$pfilename.', Copyfolder: '.$copyfolder.', output Type: '.$output_type.'<br />';		
        }

        return $pdfoutput;
    }
	
    function sendemail( $recipient_id, $subject, $body, $attachment, $filename  )
    {
        global $DB, $USER, $CFG;

        $debug = false;

        $recipient = $DB->get_record('user', array('id' => $recipient_id));
        //$recipient->email = 'glroberts@cardiffmet.ac.uk';

        $sender = get_admin();				

        if($debug)
        {
            echo '<hr />User Details<br />';
            echo '<pre>';
            print_r($recipient);
            echo '</pre>';
            echo '<hr />';		

            echo '<hr />Sender Details<br />';		
            echo '<pre>';
            print_r($sender);
            echo '</pre>';
            echo '<hr />';

            echo '<hr />';		
            echo 'Dataroot: '.$CFG->dataroot.'<br />';
            echo '<hr />';		

            echo 'attachment: '.$attachment.'<br />';
        }

        email_to_user($recipient, $sender, $subject, $body, $body, $attachment, $filename);
    }
	
    function view_cohort_report()
    {
        global $DB;

        $debug = false;

        $instance = $this->get_instance();
        
        $table = 'ereflect_questions';
        $conditions = array('ereflect_id'=>$instance->id); // count existing number of questions
        $countrecs = $DB->count_records($table, $conditions);				

        $sql = 'SELECT REPLACE(meq.question_text,\',\',\' \') question_text
                FROM {ereflect_questions} meq
                WHERE meq.ereflect_id = ?
                ORDER BY meq.order_by';
        
        $q = $DB->get_records_sql($sql, array($instance->id));
                
        //  Build Header for Spreadsheet
        $data = 'Student';
        foreach($q as $key => $value)
        {		
            // Append a Question and Answer title for each Question
            $data .= ','.$value->question_text;
        }
        $data .= ",Grade, Assignment Time\n";                
        
        // Test data line
        //$data .= 'Graeme Roberts,What is?,It is..,Why is?,It is..,Who is?,It is..'."\n";
        $sql = 'SELECT coalesce(CONCAT(CAST(meq.id AS CHAR), \'_\', CAST(meo.id AS CHAR), \'_USER_\', CAST(mea.user_id AS CHAR)), CONCAT(CAST(meq.id AS CHAR), \'_USER_\', CAST(mea.user_id AS CHAR)) ) as unique_id,
                                   meq.order_by, mea.ereflect_id, mea.user_id, concat(u.firstname,\' \', u.lastname) as student_name,
                                   REPLACE(REPLACE(meq.question_text,\',\',\' \'),\'CHR(13)\',\' \') question_text, meq.open_question, 
                                   IFNULL(meo.option_answer, REPLACE(REPLACE(mea.open_answer,\',\',\' \'),\'CHR(13)\',\' \')) option_answer,
                                   mur.grade, ROUND((mur.assignment_time/60/60),1) acttime
                FROM {ereflect_answers} mea
                JOIN {user} u ON u.id = mea.user_id
                JOIN {ereflect_questions} meq ON meq.id = mea.ereflect_question_id
                JOIN {ereflect_user_response} mur ON mea.user_id = mur.user_id AND mea.ereflect_id = mur.ereflect_id AND mur.status = \'COMPLETED\'
                LEFT OUTER JOIN {ereflect_options} meo ON meo.id = mea.ereflect_option_id
                WHERE mea.ereflect_id = ?
                ORDER BY mea.user_id, meq.order_by';
        
        if($debug){echo 'Data: '.$data.'<br />';}

        if($eq = $DB->get_records_sql($sql, array($instance->id)))
        {			
            $student_id = '';	
            $grade = '';
            $atime = '';
            
            $qa = '';
            $n = 0;
            foreach($eq as $key => $value)
            {		
                if($debug){echo 'In loop with value '.$value->unique_id.'<br />';}
                $n++;                                

                // Trap Question and answer for this row
                //$qanda = ','.$value->question_text.','.$value->option_answer; 
                $qanda = ','.$value->option_answer; 

                // If not the same student id, then go to another line
                if(isset($student_id) && strlen($student_id)>0 && $student_id != $value->user_id)
                {	
                    $qa .= ",".$grade.",".$acttime."\n";
                    if($debug){echo 'In right place string: '.$qa.'<br />';}
                    // reset counter to zero for Student
                    $n = 1;
                }

                if($n==1)
                {
                    $qa .= $value->student_name.$qanda;
                }
                else
                {
                    $qa .= $qanda;
                }

                if($debug){echo 'String: '.$qa.'<br />';}

                // Set User-id at end ready for next record
                $student_id = $value->user_id;	
                $grade = $value->grade;
                $acttime = $value->acttime;
            }			
            
            // for last record which doesn't go through loop iteration again....
            $qa .= ",".$grade.",".$acttime;

            // Add body to headings...
            $data .= $qa;

            session_cache_limiter('public'); 
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: public"); 
            header("Content-Description: File Transfer");
            header("Content-Type: text/plain"); 
            header("Content-Disposition: attachment; filename=ereflect_".$instance->id."_cohort_report_".date('Ymd').'_'.date('Hi')."hrs.csv"); 

            echo $data; 
            exit(); 
        }
        /*else
        {
                get_string('cohort_nodata','mod_ereflect'); // No Data Found for Cohort Report
        }*/
				
    }
    
    /**
     * Returns an array of options for the editors that are used for submitting and assessing instructions
     *
     * @param stdClass $context
     * @uses EDITOR_UNLIMITED_FILES hard-coded value for the 'maxfiles' option
     * @return array
     */
    public static function instruction_editors_options(stdclass $context) {
        return array('subdirs' => 1, 'maxbytes' => 0, 'maxfiles' => 99,
                     'changeformat' => 1, 'context' => $context, 'noclean' => 1, 'trusttext' => 0);
    }
        
    
}