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
 * The main ereflect configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_ereflect
 * @copyright  2013 Graeme Roberts
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once(dirname(__FILE__) . '/class/locallib.php');
require_once($CFG->libdir . '/filelib.php');
/**
 * Module instance settings form
 */
class mod_ereflect_mod_form extends moodleform_mod {

    /** @var array $instance - The data passed to this form */

    /**
     * Defines forms elements
     */
    public function definition() {

        $mform = $this->_form;
        $context = $this->context;
        $debug = false;
        
        $field = 'info_text';
        $mform->addElement('textarea', $field, null, array('style' => 'color: red; width: 100%; font-weight: bold; border: 00px #f00 solid;', 'size' => '255'));                       
                
        if($debug)
        {   
            echo 'In ereflect_mod_form, context is; <br />';
            echo '<pre>';
            print_r($context);
            echo '</pre>';
            echo '<hr />';
            echo 'form<br />';
            echo '<pre>';
            print_r($mform);
            echo '</pre>';    
        }
        
        /*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~    General  Section   ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~  START   */		
		
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $field = 'name';
        //'maxlength' => 255, 'size' => 48/
        $mform->addElement('text', $field, get_string('ereflectname', 'ereflect'), array('id' => 'module_heading', 'size' => '255'));
        $mform->setType($field, PARAM_TEXT);
        $mform->addRule($field, null, 'required', null, 'client');
        $mform->addRule($field, get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton($field, 'ereflectname', 'ereflect');

        // Adding the standard "intro" and "introformat" fields
        $this->add_intro_editor();

        //-------------------------------------------------------------------------------
        // Adding the rest of ereflect settings, spreeading all them into this fieldset
        // or adding more fieldsets ('header' elements) if needed for better logic
				
        // Suggested Preparation Time (Hours) 
        /*$mform->addElement('html', '<div style="border: 01px #000 solid; clear: both;">');*/
        /*$mform->addElement('html', '</div>');*/

        // $attributes = array('id' => $fieldname , 'wrap' => 'virtual', 'rows' => '10', 'cols' => '10');		
        $field = 'preparationtime';
        //'maxlength' => 255, 'size' => 48/
        //$attributes = array('optional' => false);
        $mform->addElement('duration', $field, get_string($field, 'ereflect'), 'maxlength="3" size="3" ');
        $mform->addRule($field, null, 'required', null, 'client');				        
        //$mform->addRule('preparationtime', get_string('maximumchars', '', 3), 'maxlength', 3, 'client');		
        //$mform->addRule('preparationtime', null, 'numeric', null, 'client');								

        // Use Traffic Light
        $field = 'usetrafficlight';
        $mform->addElement('selectyesno', $field, get_string( $field, 'ereflect'));		
        $mform->addHelpButton($field, 'usetrafficlight', 'ereflect');
        $mform->setDefault($field, 1);	
		
        // Use Traffic Light Images
	//$field = 'usetrafficimages';
        //$mform->addElement('selectyesno', $field, get_string('usetrafficimages', 'ereflect'));		
        //$mform->addHelpButton($field, 'usetrafficimages', 'ereflect');
        //$mform->setDefault($field, 1);	
	//$mform->disabledIf($field, 'usetrafficlight', 'eq', 0);

        /*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~    General  Section   ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~  END   */				
        
        $mform->addElement('header', 'timinghdr', get_string('timing', 'form'));        
        $mform->setExpanded('timinghdr');		        
        
        $enableopengroup = array();
        $enableopengroup[] =& $mform->createElement('checkbox', 'useopendate', get_string('opendate', 'ereflect'));
        $enableopengroup[] =& $mform->createElement('date_time_selector', 'opendate', '');
        $mform->addGroup($enableopengroup, 'enableopengroup', get_string('opendate', 'ereflect'), ' ', false);
        $mform->addHelpButton('enableopengroup', 'opendate', 'questionnaire');
        $mform->disabledIf('enableopengroup', 'useopendate', 'notchecked');

        $enableclosegroup = array();
        $enableclosegroup[] =& $mform->createElement('checkbox', 'useclosedate', get_string('closedate', 'ereflect'));
        $enableclosegroup[] =& $mform->createElement('date_time_selector', 'closedate', '');
        $mform->addGroup($enableclosegroup, 'enableclosegroup', get_string('closedate', 'ereflect'), ' ', false);
        $mform->addHelpButton('enableclosegroup', 'closedate', 'questionnaire');
        $mform->disabledIf('enableclosegroup', 'useclosedate', 'notchecked');
        
		
        /*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~    Layout Section   ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~  START   */		
		
        $mform->addElement('header', 'layout', get_string('layout', 'ereflect'));		
        $mform->setExpanded('layout');		

        $field = 'questions_per_page';
        //$attributes=array('size'=>'3');
        $attributes=array();
        for ($i = 99; $i >= 1; $i--) {
                $q[$i] = $i;
        }
        $mform->addElement('select', $field, get_string($field, 'ereflect'), $q, $attributes);			

        //
        //$mform->addElement('text', $field, get_string($field, 'ereflect'), $attributes);
        //$mform->setType($field, PARAM_TEXT);
        //$mform->addRule($field, null, 'required', null, 'client');				

        // In built method that does work is replaced with validation function below this function
        //$mform->addRule('questions_per_page', null, 'required', null, 'client');		
        //$mform->addRule('questions_per_page', null, 'numeric', null, 'client');				

        //$mform->registerRule('check_questions_per_page', 'callback', 'checkSomething');
        //$mform->addRule('questions_per_page', 'Questions per page is incorrect', 'check_questions_per_page', false);

        $mform->setDefault($field,99);

        // navigation_method
        //$field = 'navigation_method';
        //$mform->addElement('selectyesno', $field, get_string($field, 'ereflect'));		
        //$mform->addHelpButton($field, 'navigation_method', 'ereflect');
        //$mform->setDefault($field, 1);	

        /*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~    Submission Settings Section   ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~  END  */		

        $mform->addElement('header', 'submissionsettings', get_string('submissionsettings', 'ereflect'));
        $mform->setExpanded('submissionsettings');

        // Email Report
        $field = 'email_notification';
        $mform->addElement('selectyesno', $field, get_string( $field, 'ereflect'));		
        $mform->addHelpButton($field, 'email_notification', 'ereflect');
        $mform->setDefault($field, 1);		// We probably want to email report by default

        // COME FROM MOODLEFORM_MOD.PHP

        //$mform->addHelpButton('completion_message', 'compmessgtext', 'ereflect');		
        
        // Completion Message Editor field i.e. with Picture/File/Movie upload
        $field = 'completion_message';        
        $label = get_string('completion_message', 'ereflect');
        $mform->addElement('editor', 'completion_message_editor', $label, null,
                            ereflect::instruction_editors_options($this->context));
        $mform->setDefault('completion_message_editor', get_string('completion_message_prompt', 'ereflect'));
        
        // Include Mark for Line graph in PDF
        $field = 'include_mark_in_linegraph';
        $mform->addElement('selectyesno', $field, get_string($field, 'ereflect'));		
        $mform->addHelpButton($field, 'include_mark_in_linegraph', 'ereflect');
        $mform->setDefault($field, 1);	
		
        // include_preptime_in_report
        $field = 'include_preptime_in_report';
        $mform->addElement('selectyesno', $field, get_string($field, 'ereflect'));		
        $mform->addHelpButton($field, $field, 'ereflect');
        $mform->setDefault($field, 1);	
 
        //-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        //$this->standard_coursemodule_elements();		
		
        //$mform->addElement('button', 'intro', get_string("buttonlabel"));		

        // GR added from NEWMODULE_DOCUMENTATION
        $features = new object();

        $features->groups           = false;  
        $features->groupings        = false;  
        $features->groupmembersonly = false; 
        $features->idnumber 		= false;

        $this->standard_coursemodule_elements($features);
		
        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        // Different ways of adding action buttons
        //$this->add_action_buttons();
        //$mform->addElement('submit', 'savequickgrades', 'hello test');

        // This is KEY as it will submit with this action and then use the 2nd parameter values below (savechangesanddisplay or savchangesandcontinue)
        // in locallib.view 
        $mform->setType('action', PARAM_ALPHA);
				
        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton2', get_string('savechangesandreturntocourse', 'ereflect'));
        //$buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechangesanddisplay','ereflect'));		
        //$buttonarray[] = &$mform->createElement('submit', 'savechangesandcontinue', get_string('savechangesandcontinue','ereflect'));				

        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechangesandcontinue','ereflect'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
        
        
        if($debug)
        {
            echo '<hr />';            
            echo 'default values<br />';
            echo '<pre>';
            print_r($mform->_defaultValues);
            echo '</pre>';                
            echo '<hr />';  
        }  
    }

    /**
     * Prepares the form before data are set
     *
     * Additional wysiwyg editor are prepared here, the introeditor is prepared automatically by core.
     * Grade items are set here because the core modedit supports single grade item only.
     *
     * @param array $data to be set
     * @return void
     */

    public function data_preprocessing(&$defaultvalues) {
        global $DB;
        
        $debug = false;
        
        
        // This ensures that the 'Use Open Date' and 'Use Close Date' check boxes
        // are set if there is a value in the opendate and closeddate fields
        if (empty($defaultvalues['opendate'])) {
            $defaultvalues['useopendate'] = 0;
        } else {
            $defaultvalues['useopendate'] = 1;
        }
        if (empty($defaultvalues['closedate'])) {
            $defaultvalues['useclosedate'] = 0;
        } else {
            $defaultvalues['useclosedate'] = 1;
        }
        
        if($debug)
        {
            echo 'In datapreprocessing<br />';
            echo '<pre>';
            print_r($this->context);
            echo '</pre>';
            
            echo '<pre>';
            echo 'Default Values<br />';
            print_r($defaultvalues);
            echo '</pre>'; 
        }
        
      
        if ($this->current->instance)
        {   
            $draftid_editor = file_get_submitted_draft_itemid('completion_message');
            $currenttext = file_prepare_draft_area($draftid_editor, $this->context->id, 'mod_ereflect', 'completion_message', 0,
                                ereflect::instruction_editors_options($this->context),
                                $defaultvalues['completion_message']);
           $defaultvalues['completion_message_editor'] = array('text' => $currenttext, 'format' => FORMAT_HTML, 'itemid'=>$draftid_editor);            

           if($debug)
           {
                echo '<pre>';
                echo 'Current Instance<br />';
                print_r($this->current->instance);
                echo '</pre>';
                echo '<pre>';
                echo 'Default Values with extra bits 1<br />';
                print_r($defaultvalues);
                echo '</pre>';
           }
           
           if($defaultvalues['status'] == 'STUDENTENTRY') {
            $defaultvalues['info_text'] = get_string('ereflect_published', 'ereflect');
                if($debug)
                {
                    echo 'Default values for info_text : '.$defaultvalues['info_text'].'<br />';
                }
           }
                   
        }
        else 
        {  
           $draftid_editor = file_get_submitted_draft_itemid('completion_message');
           $currenttext = file_prepare_draft_area($draftid_editor, null, 'mod_ereflect', 'completion_message', 0);
           $defaultvalues['completion_message_editor'] = array('text' => $currenttext, 'format' => FORMAT_HTML, 'itemid'=>$draftid_editor);
           
           if($debug)
           {
                echo '<pre>';
                echo 'Default Values with extra bits 2<br />';
                print_r($defaultvalues);
                echo '</pre>';
           }
           
        }
            
    } 
	

    /**
     * Some basic validation
     *
     * @param $data
     * @param $files
     * @return array
     */
    
    /*  Leave this out for now... check the count when Completing Setup and publish
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        global $DB;
        $debug = true;
        $usetrafficlight = $data['usetrafficlight'];
        
        // If Changing the traffic light to Yes, then ensure that if any options already input
        // that we check the number of options.
        
        if($usetrafficlight==1)
        {
            if (!empty($this->_instance)) 
            {
                $sql = 'SELECT ereflect_question_id, COUNT(*) countrecs
                        FROM mdl_ereflect_options meo
                        JOIN mdl_ereflect_questions meq WHERE meo.ereflect_question_id = meq.id AND meq.ereflect_id = 1
                        GROUP BY ereflect_question_id';
                
                $eo = $DB->get_records_sql($sql, array('id'=>$this->_instance));
                
                $b_error = false;
                foreach($eo as $eovar)
                {
                    if($eovar->countrecs > 3)
                    {
                        $b_error = true;
                    }
                }
                
                if($b_error)
                {    
                    $errors['usetrafficlight'] = 'Cannot alter to use Traffic Light - at least one question has more than 3 options';
                }
            }
                            
            
        }
       
        return $errors;
    }
    */
	
}
