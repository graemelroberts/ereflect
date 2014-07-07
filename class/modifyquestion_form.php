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
 * This file contains the submission form used by the ereflect modify question module.
 *
 * @package   mod_ereflect
 * @copyright 2013 G.Roberts Cardiff Met
 * @license   
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');


require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/mod/ereflect/class/locallib.php');

class mod_ereflect_modifyquestion_form extends moodleform {

    /** @var array $instance - The data passed to this form */
    private $instance;
    private $context;

    /**
     * Define this form - called by the parent constructor
     */
    public function definition() {
        $mform = $this->_form;
		
        $debug = false;

        //list($ereflect, $data, $params, $instance, $feedback_mesage_options) = $this->_customdata;		
        list($ereflect, $data, $params, $instance) = $this->_customdata;		
        
        // Instance variable is used by the form validation function.
        $this->instance = $instance;
		
        if(count($params)==0)
        {
            $params = new stdclass();
        }
        $params->action = 'AMENDQUESTIONPROCESS';
        
        /*if(!isset($feedback_message_options))
        {
            $feedback_message_options = new stdclass();
        }

        $ereflect->add_addquestion_form_elements($mform, $data, $params, null, $feedback_message_options);*/
        
        $ereflect->add_addquestion_form_elements($mform, $data, $params, null);        
        $context = $ereflect->get_context();
        $this->context = $context;
        
        if($debug)
        {
            echo 'in modifyquestion_form.definition params = <br />';
            echo '<pre>';
            print_r($params);
            echo '</pre>';

            echo 'data = <br />';
            echo '<pre>';
            print_r($data);
            echo '</pre>';

            echo 'in context <br />';
            echo '<pre>';
            print_r($context);
            echo '</pre>';
        }


        //$this->add_action_buttons(true, get_string('addfeedbackoption', 'ereflect'));
        //$attributes = array("id"=>"saveandreturn","onclick"=>"jssubmitform();");
        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'modifyquestion_saveandreturn', get_string('savechangesandreturntoeditfeedback', 'ereflect'));
        //$buttonarray[] = &$mform->createElement('button', 'modifyquestion_saveandreturn', get_string('savechangesandreturntoeditfeedback', 'ereflect'), '','', $attributes);
        $buttonarray[] = &$mform->createElement('cancel', 'modifyquestion_cancelandreturn', get_string('cancelandreturntoeditfeedback','ereflect'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');		

        // Only run this as part of showing the initial data i.e. not during process_amend_
        if (isset($data->id)) {
        
            if($debug)
            {
                echo 'Data before<br />';
                echo '<pre>';
                print_r($data);
                echo '</pre>';
            }
            
            // Get the
            $draftid_editor = file_get_submitted_draft_itemid('feedback_message');
            $currenttext = file_prepare_draft_area($draftid_editor, $this->context->id, 'mod_ereflect', 'feedback_message', $data->id,
                                ereflect::instruction_editors_options($this->context), $data->feedback_message);
            $data->feedback_message_editor = array('text' => $currenttext, 'format' => FORMAT_HTML, 'itemid'=>$draftid_editor);
            
            if($debug)
            {
                echo 'Data After<br />';
                echo '<pre>';
                print_r($data);
                echo '</pre>';
            }
            
            $this->set_data($data);                        
        }
        
        $mform->setDefault('id',$context->instanceid);
        
		
    }
	
    public function validation($data, $files) {
	
        global $DB;

	$debug = false;
		
        $errors = parent::validation($data, $files);
        
        if($debug)
        {
            echo 'In validation for modifyquestion_form with id : '.$this->instance->id.'<br />';
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
    
    public function data_preprocessing(&$defaultvalues) {
        
        global $DB;
        $debug = true;
        
        echo 'In data_preprocessing<br />';
     
        $draftid_editor = file_get_submitted_draft_itemid('feedback_message');
        $currenttext = file_prepare_draft_area($draftid_editor, $this->context->id, 'mod_ereflect', 'feedback_message', $data->id,
                            ereflect::instruction_editors_options($this->context), $defaultvalues->feedback_message);
        $defaultvalues->feedback_message_editor = array('text' => $currenttext, 'format' => FORMAT_HTML, 'itemid'=>$draftid_editor);
                    
        
    }
}


