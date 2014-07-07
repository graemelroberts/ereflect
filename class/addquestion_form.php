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
 * ereflect add Question Submission form
 *
 * @package   mod_ereflect
 * @copyright 2013 G.Roberts Cardiff Met
 * @license   
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/mod/ereflect/class/locallib.php');

class mod_ereflect_addquestion_form extends moodleform {

    /** @var array $instance - The data passed to this form */
    private $instance;

    /**
     * Define this form - called by the parent constructor
     */
    public function definition() {
        $mform = $this->_form;
		
        $debug = false;	
        
        //list($ereflect, $data, $ereflect_questions, $instance, $feedback_message_options) = $this->_customdata;			
        
        list($ereflect, $data, $ereflect_questions, $instance) = $this->_customdata;			
        // Instance variable is used by the form validation function.
        $this->instance = $instance;
		
        if($debug)
        {
            echo 'In addquestion_form.mod_ereflect_addquestion_form, about to print $data <br />';

            echo '<pre>';			
            print_r($ereflect_questions);
            echo '</pre>';	
            echo '<hr />';

            if(isset($errors))
            {
                echo 'Errors: ';
                echo '<pre>';
                print_r($errors);
                echo '</pre>';
                echo '<hr />';
            }
        }

        $params = new stdClass();
        $params->action = 'ADDQUESTIONPROCESS';

        //$ereflect->add_addquestion_form_elements($mform, $data, $params, $ereflect_questions, $feedback_message_options);				
        $ereflect->add_addquestion_form_elements($mform, $data, $params, $ereflect_questions);				

        if($debug)
        {
            $grdata = $this->get_data();

            echo 'showing data <br />';
            echo '<pre>';
            print_r($grdata);
            echo '</pre>';
            echo 'end of showing data <br />';
        }
		
        //$this->add_action_buttons(true, get_string('addfeedbackquestion', 'ereflect'));
        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'addquestion_saveandreturn', get_string('saveaddfeedbackquestion', 'ereflect'));
        //$buttonarray[] = &$mform->createElement('submit', 'addquestion_completeprocess', get_string('complete_setup','ereflect'));		
        $buttonarray[] = &$mform->createElement('cancel', 'addquestion_cancelandreturn', get_string('canceladdbeedbackquestion','ereflect'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');		

        if($debug)
        {
            echo 'after add_action_buttons <br />';

            echo 'Data is <br />';
            $params=$this->get_data($data);
            echo '<pre>';
            print_r($data);
            echo '</pre>';
        }
		
        if ($data) {
            $this->set_data($data);
        }
    }
	
    public function validation($data, $files) {
	
        global $DB;
        $debug = false;		
        $errors = parent::validation($data, $files);
        
        if($debug)
        {
            echo 'In validation for addquestion_form with id : '.$this->instance->id.'<br />';
            echo '<pre>';
            print_r($this->instance);
            echo '</pre>';
        }

        if(($data['open_question']==0) && $data['no_of_options']==0)
        {
            $errors['no_of_options'] = get_string('err_no_of_options','mod_ereflect');
        }

        if(($this->instance->usetrafficlight==1) && ($data['no_of_options']!=3))
        {
            // You have elected to use the Traffic Light system - therefore you must have three options
            $errors['no_of_options'] = get_string('err_traffic_light','mod_ereflect');
        }			   

        return $errors;
    }			
	
	    
}

