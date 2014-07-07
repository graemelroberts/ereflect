<?php

/**
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_choice_activity_task
 */

/**
 * Define the complete choice structure for backup, with file and id annotations
 */

class backup_ereflect_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {
        
        global $DB;        

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated        
        $ereflect = new backup_nested_element('ereflect', array('id'), array(
            'name', 'intro', 'introformat', 'preparationtime',
            'email_notification', 'usetrafficlight', 'usetrafficimages', 'questions_per_page',
            'sequential_order', 'completion_message', 'include_preptime_in_report', 'include_mark_in_linegraph',
            'status', 'opendate', 'closedate', 'timecreated', 'timemodified'));

        $ereflect_questions = new backup_nested_element('ereflect_questions');

        $ereflect_question = new backup_nested_element('ereflect_question', array('id'), array(
            'question_name', 'question_text', 'open_question', 'no_of_options', 
            'order_by', 'copied_eq_id', 'timecreated', 'timemodified', 'feedback_message'));
        
        $ereflect_options = new backup_nested_element('ereflect_options');
        
        $ereflect_option = new backup_nested_element('ereflect_option', array('id'), array(
            'option_answer','option_feedback','showicon', 'icon_name', 
            'icon_colour', 'order_by', 'timecreated', 'timemodified'));
        
        $ereflect_answers = new backup_nested_element('ereflect_answers');
        
        $ereflect_answer = new backup_nested_element('ereflect_answer', array('id'), array(
            'user_id', 'ereflect_id', 'ereflect_question_id', 'ereflect_option_id',
            'open_question_ind', 'open_answer', 'timecreated', 'timemodified'
        ));
        
        $ereflect_user_response = new backup_nested_element('ereflect_user_response');
        
        $ereflect_ur = new backup_nested_element('ereflect_ur', array('id'), array(
            'course','user_id', 'ereflect_id', 'status', 'grade',
            'assignment_time', 'timecreated','timemodified'
        ));

        // Build the tree
        $ereflect->add_child($ereflect_questions);
        $ereflect_questions->add_child($ereflect_question);

        $ereflect_question->add_child($ereflect_options);
        $ereflect_options->add_child($ereflect_option);
        
        $ereflect_question->add_child($ereflect_answers);                
        $ereflect_answers->add_child($ereflect_answer);
        
        $ereflect->add_child($ereflect_user_response);
        $ereflect_user_response->add_child($ereflect_ur);
                    
        // Define sources
        $ereflect->set_source_table('ereflect', array('id' => backup::VAR_ACTIVITYID));
        $ereflect_question->set_source_table('ereflect_questions', array('ereflect_id' => backup::VAR_PARENTID), 'id ASC');
        $ereflect_option->set_source_table('ereflect_options', array('ereflect_question_id' => backup::VAR_PARENTID), 'id ASC');
        
        if ($userinfo) {
            $ereflect_answer->set_source_table('ereflect_answers',array('ereflect_question_id' => backup::VAR_PARENTID), 'id ASC');
            
            $ereflect_ur->set_source_table('ereflect_user_response',array('ereflect_id' => backup::VAR_PARENTID), 'id ASC');
        }        
        
        // Define id annotations
        $ereflect_answer->annotate_ids('user', 'user_id');
        
        // Define file annotations
        $ereflect->annotate_files('mod_ereflect', 'intro', null); // This file area hasn't itemid
        $ereflect->annotate_files('mod_ereflect', 'completion_message', null );
        $ereflect_question->annotate_files('mod_ereflect', 'feedback_message','id');
        
        // Return the root element (choice), wrapped into standard activity structure
        return $this->prepare_activity_structure($ereflect);
        
    }
}
