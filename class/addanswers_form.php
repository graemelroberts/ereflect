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
 *
 * @package   mod_ereflect
 * @copyright 2013 G.Roberts Cardiff Met
 * @license   
*/

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');


require_once($CFG->libdir . '/formslib.php');
//require_once($CFG->dirroot . '/mod/ereflect/class/locallib.php');

class mod_ereflect_addanswers_form extends moodleform {

    
    /** @var array $instance - The data passed to this form */
    private $instance;    
    //
    // Define this form - called by the parent constructor
    //
	
    public function definition() {
        $mform = $this->_form;

        $debug = false;

        //list($ereflect, $data, $parameters, $ereflect_questions ) = $this->_customdata; 
        //list($ereflect, $data, $ereflect_questions, $ereflect_user_response ) = $this->_customdata; 
        list($ereflect, $data, $parameters, $ereflect_questions, $ereflect_user_response, $instance ) = $this->_customdata; 

        $this->instance = $instance;
        
        // Set action value as part of the Class
        if(count($parameters)==0)
        {
            $parameters = new stdclass;
        }

        // Check to see if Teacher is accessing info or Student
        $b_teacherview = false;
        if(isset($parameters->view) && $parameters->view=='TEACHERVIEW')
        {
            $b_teacherview = true;
        }

        $b_ejournalview = false;
        if(isset($parameters->view) && $parameters->view=='EJOURNALVIEW')
        {
            $b_ejournalview = true;
        }
        
        if(count($ereflect_questions)==0)
        {
            $ereflect_questions = new stdClass();
        }

        if(count($ereflect_user_response)==0)
        {
            $ereflect_user_response = new stdClass();
        }

        // If Teacher and viewing the Student Marks, then need to go back
        // to the Summary Statistics screen.
        if($b_teacherview)
        {
            $parameters->action = 'VIEWSUMMARYSTATSPROCESS';
        }
        else
        {
            $parameters->action = 'ADDANSWERSPROCESS';
        }
        
        // If come from ejournal, then need to go back to main post screen in
        // the ejournal 
        /*if($b_ejournalview)
        {
            $parameters->action = 'VIEWEJOURNALPOST';
        }*/
        //$parameters->student_id = $ereflect_user_response->user_id;
        //$parameters->student_id = $parameters->user_id;

        if($debug)
        {
            echo 'In mod_ereflect_addanswers_form - After adding action value about to reprint $parameters<br />';			
            echo '<pre>';
            print_r($parameters);
            echo '</pre>';			

            echo 'eReflect Questions<br />';
            echo '<pre>';
            print_r($ereflect_questions);
            echo '</pre>';

            echo 'eReflect User Response<br />';
            echo '<pre>';
            print_r($ereflect_user_response);
            echo '</pre>';
        }		

        $ereflect->add_addanswers_form_elements($mform, $data, $parameters, $ereflect_questions, $ereflect_user_response);

        //$this->add_action_buttons(true, get_string('addfeedbackoption', 'ereflect'));
        $buttonarray=array();
        // Only Allow a submission if the Questionnaire hasn't been completed by the user
        if(!($ereflect->user_completed($parameters->student_id,'COMPLETED')) && !$b_teacherview && !$b_ejournalview)
        {
            $buttonarray[] = &$mform->createElement('submit', 'addanswers_saveandreturn', get_string('addanswers_saveandreturn', 'ereflect'));
            $buttonarray[] = &$mform->createElement('submit', 'addanswers_saveandsubmit', get_string('addanswers_saveandsubmit', 'ereflect'));
        }
        
        //if($ereflect->user_completed($ereflect_user_response->user_id,'COMPLETED'))
        if(isset($parameters->view) && $parameters->view=='EJOURNALVIEW')
        {
            $buttonarray[] = &$mform->createElement('submit', 'viewejournal', get_string('viewejournallit','ereflect'));            
        }
        $buttonarray[] = &$mform->createElement('cancel', 'addanswers_cancelandreturn', get_string('addanswers_cancelandreturn','ereflect'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

        if($debug)
        {
            echo 'after add_action_buttons <br />';
            //exit();
        }

        if ($data) 
        {
            if($debug){echo 'There is data, so about to set/get data <br />';}

            $this->set_data($data);

            if($debug)
            {
                echo 'Data is <br />';
                $params=$this->get_data($data);
                echo '<pre>';
                print_r($data);
                echo '</pre>';
            }
        }
        // Set the id to the Course Module Id as opposed to the Id for the Option
        // We then sort this out within the View page
        //$context = $ereflect->get_context();		
        //$mform->setDefault('id',$context->instanceid);
    }
	
    public function validation($data, $files) 
    {
        $errors = parent::validation($data, $files);
        
        $debug = false;

        if($debug)
        {
            echo 'In validation for addoption_form with id : '.$this->instance->id.'<br />';
            echo '<pre>';
            print_r($this->instance);
            echo '</pre>';
        }        
        
        if($this->instance->include_preptime_in_report == 1)
        {
            $assignment_time = $data['assignment_time'];
            
            if($assignment_time==0)
            {
                // Please enter how long you spent undertaking the assignment
                $errors['assignment_time'] = get_string('assignment_time_error','ereflect'); 
            }
                                    
        }

        return $errors;        
    }
    
}

