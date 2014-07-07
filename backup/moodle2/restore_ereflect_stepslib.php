<?php
/**
 * @copyright 2014 G.Roberts Cardiff met
 * Define all the restore steps that will be used by the restore_ereflect_activity_task
 * Structure step to restore one choice activity
**/

class restore_ereflect_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');
        
        $paths[] = new restore_path_element('ereflect', '/activity/ereflect');
        $paths[] = new restore_path_element('ereflect_question', '/activity/ereflect/ereflect_questions/ereflect_question');
        $paths[] = new restore_path_element('ereflect_option', '/activity/ereflect/ereflect_questions/ereflect_question/ereflect_options/ereflect_option');
        
        if ($userinfo) {
            $paths[] = new restore_path_element('ereflect_answer', '/activity/ereflect/ereflect_questions/ereflect_question/ereflect_answers/ereflect_answer');
            $paths[] = new restore_path_element('ereflect_ur', '/activity/ereflect/ereflect_user_response/ereflect_ur');
        }        

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_ereflect($data) {
        global $DB;

        $data = (object)$data;
        
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->opendate = $this->apply_date_offset($data->opendate);
        $data->closedate = $this->apply_date_offset($data->closedate);
        //$data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        
        // insert the choice record
        $newitemid = $DB->insert_record('ereflect', $data);
        
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_ereflect_question($data) {
        global $DB;
        
        $data = (object)$data;
        $oldid = $data->id;
        
        $data->ereflect_id = $this->get_new_parentid('ereflect');
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        
        $newitemid = $DB->insert_record('ereflect_questions', $data);
        $this->set_mapping('ereflect_question', $oldid, $newitemid);
    }

    protected function process_ereflect_option($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        
        $data->ereflect_question_id = $this->get_new_parentid('ereflect_question');
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        
        $newitemid = $DB->insert_record('ereflect_options', $data);        
        $this->set_mapping('ereflect_option', $oldid, $newitemid);
    }

    protected function process_ereflect_answer($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        
        $data->ereflect_question_id = $this->get_new_parentid('ereflect_question');
        $data->ereflect_id = $this->get_new_parentid('ereflect');
        $data->ereflect_option_id = $this->get_new_parentid('ereflect_option');
        
        $data->user_id = $this->get_mappingid('user', $data->user_id);        
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        
        $newitemid = $DB->insert_record('ereflect_answers', $data);        
        // No need to save this mapping as far as nothing depend on it
    }
    
    protected function process_ereflect_ur($data) {
        global $DB;
        
        $data = (object)$data;
        $oldid = $data->id;
        
        $data->ereflect_id = $this->get_new_parentid('ereflect');
        $data->course = $this->get_courseid();
        $data->user_id = $this->get_mappingid('user', $data->user_id);        
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        
        $newitemid = $DB->insert_record('ereflect_user_response', $data);
    }    
    
    protected function after_execute() {
        // Add ereflect related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_ereflect', 'intro', null);
        $this->add_related_files('mod_ereflect', 'completion_message', null);
        $this->add_related_files('mod_ereflect', 'feedback_message', 'ereflect_question');
        
        
    }
}
