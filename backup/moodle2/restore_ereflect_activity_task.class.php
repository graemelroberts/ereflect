<?php

/**
 * @copyright 2014 G.Roberts Cardiff Met
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/ereflect/backup/moodle2/restore_ereflect_stepslib.php'); // Because it exists (must)

/**
 * choice restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 */
class restore_ereflect_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        //  Ereflect only has one structure step
        $this->add_step(new restore_ereflect_activity_structure_step('ereflect_structure', 'ereflect.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('ereflect', array('intro','completion_message'), 'ereflect');
        $contents[] = new restore_decode_content('ereflect_questions', array('feedback_message'), 'ereflect_question');        
        
        /*$contents[] = new restore_decode_content('forum', array('intro'), 'forum');
        $contents[] = new restore_decode_content('forum_posts', array('message'), 'forum_post');*/

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('EREFLECTVIEWBYID', '/mod/ereflect/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('EREFLECTINDEX', '/mod/ereflect/index.php?id=$1', 'course');

        return $rules;

    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * choice logs. It must return one array
     * of {@link restore_log_rule} objects
     */
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('ereflect', 'add', 'view.php?id={course_module}', '{ereflect}');
        $rules[] = new restore_log_rule('ereflect', 'update', 'view.php?id={course_module}', '{ereflect}');
        $rules[] = new restore_log_rule('ereflect', 'view', 'view.php?id={course_module}', '{ereflect}');
        $rules[] = new restore_log_rule('ereflect', 'choose', 'view.php?id={course_module}', '{ereflect}');
        $rules[] = new restore_log_rule('ereflect', 'choose again', 'view.php?id={course_module}', '{ereflect}');
        //$rules[] = new restore_log_rule('ereflect', 'report', 'report.php?id={course_module}', '{ereflect}');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        // Fix old wrong uses (missing extension)
        $rules[] = new restore_log_rule('ereflect', 'view all', 'index?id={course}', null,
                                        null, null, 'index.php?id={course}');
        $rules[] = new restore_log_rule('ereflect', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
