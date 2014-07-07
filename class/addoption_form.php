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
 * @package   mod_ereflect
 * @copyright 2013 G.Roberts Cardiff Met
 * @license   
*/

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');


require_once($CFG->libdir . '/formslib.php');
//require_once($CFG->dirroot . '/mod/ereflect/class/locallib.php');

class mod_ereflect_addoption_form extends moodleform {

    /** @var array $instance - The data passed to this form */
    private $instance;

    //
    // Define this form - called by the parent constructor
    //
	
    public function definition() {
        $mform = $this->_form;

        $debug = false;
        

        //list($ereflect, $data, $parameters, $ereflect_questions, $ereflect_options) = $this->_customdata; 
        list($ereflect, $data, $parameters, $ereflect_questions, $instance) = $this->_customdata; 
        //list($ereflect, $data) = $this->_customdata;

        //list($ereflect, $data, $ereflect_questions, $instance) = $this->_customdata;			
        // Instance variable is used by the form validation function.
        $this->instance = $instance;
				
        // Set action value as part of the Class
        if(count($parameters)==0)
        {
            $parameters = new stdclass;
        }
		
        if($debug)
        {
            echo 'In addoption_form.mod_ereflect_addoption_form, about to print $parameters<br />';
            echo '<pre>';			
            print_r($parameters);
            echo '</pre>';			
        }
		
        if(count($ereflect_questions)==0)
        {
            if($debug){echo 'creating new stdclass <br />';}
            $ereflect_questions = new stdclass;
        }

        /*if(count($ereflect_options)==0)
        {
            $ereflect_options = new stdclass;
        }*/

        $parameters->action = 'ADDOPTIONSPROCESS';

        if($debug)
        {
            echo 'After adding action value about to reprint $parameters<br />';			
            echo '<pre>';
            print_r($parameters);
            echo '</pre>';			

            echo 'eReflect Questions<br />';
            echo '<pre>';
            print_r($ereflect_questions);
            echo '</pre>';

            /*echo 'eReflect Questions<br />';
            echo '<pre>';
            print_r($ereflect_options);
            echo '</pre>';*/
        }		
		
        //$ereflect->add_addoption_form_elements($mform, $data, $parameters, $ereflect_questions, $ereflect_options);
        $ereflect->add_option_form_elements($mform, $data, $parameters, $ereflect_questions);

        //$this->add_action_buttons(true, get_string('addfeedbackoption', 'ereflect'));
        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'addoptions_saveandreturn', get_string('addoptions_saveandreturn', 'ereflect'));
        $buttonarray[] = &$mform->createElement('cancel', 'addoptions_cancelandreturn', get_string('addoptions_cancelandreturn','ereflect'));
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
        $context = $ereflect->get_context();		
        $mform->setDefault('id',$context->instanceid);        
        
    }
	
    ///
     //Perform minimal validation on the addoption form
     // @param array $data
     // @param array $files
    //
   public function validation($data, $files) {
	
        global $DB;

        $debug = false;

        $errors = parent::validation($data, $files);

        if($debug)
        {
            echo 'In validation for addoption_form with id : '.$this->instance->id.'<br />';
            echo '<pre>';
            print_r($this->instance);
            echo '</pre>';
        }

        if(isset($data['no_of_options']) && strlen($data['no_of_options']>0))
        {
            $no_of_options = $data['no_of_options'];

            for($i=1; $i<=$no_of_options; $i++ )
            {					
                $field = 'option_answer_'.$i;

                if($debug){echo 'Field is '.$field.'<br />';}

                if(!isset($data[$field]) || !strlen($data[$field]))
                {
                    // Option {$a} Answer field cannot be blank.
                    $errors[$field] = get_string('err_answer_field_blank','mod_ereflect',$i);
                }

                $field = 'option_feedback_'.$i;

                if($debug){echo 'Field is '.$field.'<br />';}

                if(!isset($data[$field]) || !strlen($data[$field]))
                {
                    // Option {$a} Feedback field cannot be blank.
                    $errors[$field] = get_string('err_feedback_field_blank','mod_ereflect',$i);
                }

                //option_icon_name_3
                /*$field = 'option_icon_colour_'.$i;
                if($debug){echo 'Field is '.$field.'<br />';}

                if(!isset($data[$field]) || !strlen($data[$field]))
                {
                    // Option {$a} Icon field cannot be blank.
                    $errors[$field] = get_string('err_icon_colour','mod_ereflect',$i);
                }*/

            }
        }
        
        return $errors;
    }			

}

