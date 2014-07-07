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
 * This file keeps track of upgrades to the graemetest module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * 
 * @package   mod_ereflect
 * @copyright 2013 G.Roberts Cardiff Met
 * @license   
*/

defined('MOODLE_INTERNAL') || die();

/**
 * Execute graemetest upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_ereflect_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    // And upgrade begins here. For each one, you'll need one
    // block of code similar to the next one. Please, delete
    // this comment lines once this file start handling proper
    // upgrade code.

    // if ($oldversion < YYYYMMDD00) { //New version in version.php
    //
    // }

    // Lines below (this included)  MUST BE DELETED once you get the first version
    // of your module ready to be installed. They are here only
    // for demonstrative purposes and to show how the graemetest
    // iself has been upgraded.

    // For each upgrade block, the file graemetest/version.php
    // needs to be updated . Such change allows Moodle to know
    // that this file has to be processed.

    // To know more about how to write correct DB upgrade scripts it's
    // highly recommended to read information available at:
    //   http://docs.moodle.org/en/Development:XMLDB_Documentation
    // and to play with the XMLDB Editor (in the admin menu) and its
    // PHP generation posibilities.
	
    if($oldversion< 2014021900)
    {		
        $table = new xmldb_table('ereflect');
        if($dbman->table_exists($table))
        {
            $dbman->drop_table($table, $continue=true, $feedback=true);
        }

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('intro', XMLDB_TYPE_TEXT, NULL, null, XMLDB_NOTNULL, null, null);
        $table->add_field('introformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, null);
        $table->add_field('preparationtime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('email_notification', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('usetrafficlight', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null,'1');		
        $table->add_field('usetrafficimages', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');		        
        $table->add_field('questions_per_page', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '99' );
        $table->add_field('sequential_order', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('completion_message', XMLDB_TYPE_TEXT, NULL, null, null, null, null);
        $table->add_field('include_preptime_in_report', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1');		
        $table->add_field('include_mark_in_linegraph', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('status', XMLDB_TYPE_CHAR, '12', null, XMLDB_NOTNULL, null, 'INPROGRESS');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table assign_user_mapping.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for assign_user_mapping.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Adding indexes to table assign_user_flags.
        $table->add_index('course', XMLDB_INDEX_NOTUNIQUE, array('course'));		

        // Assignment savepoint reached.
        upgrade_mod_savepoint(true, 2014021900, 'ereflect');
    }	

    /*
     *  <FIELDS>
       	<FIELD NAME="id" TYPE="int" LENGTH="10" NOTNULL="true" SEQUENCE="true" COMMENT="Standard Moodle primary key."/>
	<FIELD NAME="ereflect_question_id" TYPE="int" LENGTH="10" NOTNULL="true" DEFAULT="0" SEQUENCE="false" COMMENT="Foreign key reference to the ereflect question that was entered."/>
        <FIELD NAME="option_answer" TYPE="char" LENGTH="255" NOTNULL="true" SEQUENCE="false"/> 		
        <FIELD NAME="option_feedback" TYPE="char" LENGTH="255" NOTNULL="true" SEQUENCE="false"/> 				
	<FIELD NAME="showlogo" TYPE="int" LENGTH="10" NOTNULL="true" DEFAULT="0" SEQUENCE="false" COMMENT="Show Logo, 0=No, 1=Yes"/>	
        <FIELD NAME="option_logo" TYPE="char" LENGTH="255" SEQUENCE="false"/> 						
	<FIELD NAME="order_by" TYPE="int" LENGTH="10" NOTNULL="true" DEFAULT="0" SEQUENCE="false" COMMENT=""/> 
        <FIELD NAME="timecreated" TYPE="int" LENGTH="10" NOTNULL="true" DEFAULT="0" SEQUENCE="false"/>				
        <FIELD NAME="timemodified" TYPE="int" LENGTH="10" NOTNULL="true" DEFAULT="0" SEQUENCE="false"/>			  
      </FIELDS>
      <KEYS>
	<KEY NAME="primary" TYPE="primary" FIELDS="id" COMMENT="Primary key for Ereflect Options"/>
	<KEY NAME="ereflect_question_id" TYPE="foreign" FIELDS="ereflect_question_id" REFTABLE="ereflect_questions" REFFIELDS="id" COMMENT="The instance of ereflect_questions this submission belongs to."/>
      </KEYS>
     */
    
    /* Need to replace field option_logo with option_icon and Add option_icon_colour */
    
    
    if($oldversion< 2014021901)
    {		
        $table = new xmldb_table('ereflect_options');
        if($dbman->table_exists($table))
        {
            $dbman->drop_table($table, $continue=true, $feedback=true);
        }

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('ereflect_question_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('option_answer', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('option_feedback', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('showicon', XMLDB_TYPE_INTEGER, '10', null, null, null, null, '0');
        $table->add_field('icon_name', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('icon_colour', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('order_by', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table assign_user_mapping.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('ereflect_question_id', XMLDB_KEY_FOREIGN, array('ereflect_question_id'), 'ereflect_questions', array('id'));		
		
        // Conditionally launch create table for assign_user_mapping.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Assignment savepoint reached.
        upgrade_mod_savepoint(true, 2014021901, 'ereflect');
    }	    
    

    /*
        <FIELD NAME="opendate" TYPE="int" LENGTH="10" NOTNULL="true" DEFAULT="0" SEQUENCE="false" COMMENT="Timestamp to open access"/>
        <FIELD NAME="closedate" TYPE="int" LENGTH="10" NOTNULL="true" DEFAULT="0" SEQUENCE="false" COMMENT="Timestamp to close access on"/>        
     * 
     */

    if($oldversion< 2014022600)
    {		        
        $table = new xmldb_table('ereflect');	
	$field = new xmldb_field('opendate', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, '0','status');
		
        // Add field questions_per_page
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
        $table = new xmldb_table('ereflect');	
        $field = new xmldb_field('closedate', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, '0','questions_per_page');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
				
        upgrade_mod_savepoint(true, 2014022600, 'ereflect');	
        
        //$table->add_field('opendate', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        //$table->add_field('closedate', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    }
        

    if($oldversion< 2014032102)
    {		        
        $table = new xmldb_table('ereflect_options');	
        $field = new xmldb_field('option_answer', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null, null);

		
        // Add field questions_per_page
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_type($table, $field, true, true);
        }
		        
	$field = new xmldb_field('option_feedback', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_type($table, $field, true, true);
        }
        
        
        upgrade_mod_savepoint(true, 2014032102, 'ereflect');	        
    }


    /*(if($oldversion< 2014040702)
    {		
        $table = new xmldb_table('ereflect_options');
        if($dbman->table_exists($table))
        {
            $dbman->drop_table($table, $continue=true, $feedback=true);
        }

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('ereflect_question_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('option_answer', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('option_feedback', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('showicon', XMLDB_TYPE_INTEGER, '10', null, null, null, null, '0');
        $table->add_field('icon_name', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('icon_colour', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('order_by', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table assign_user_mapping.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('ereflect_question_id', XMLDB_KEY_FOREIGN, array('ereflect_question_id'), 'ereflect_questions', array('id'));		
		
        // Conditionally launch create table for assign_user_mapping.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Assignment savepoint reached.
        upgrade_mod_savepoint(true, 2014040702, 'ereflect');
    }	    
     for elearning dev only on 07/04/2014
     */
    
    if($oldversion< 2014061600)
    {		        
        $table = new xmldb_table('ereflect_questions');	
        $field = new xmldb_field('feedback_message', XMLDB_TYPE_TEXT, NULL, NULL, NULL, NULL, NULL);
        		
        // Add field questions_per_page
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
					
        upgrade_mod_savepoint(true, 2014061600, 'ereflect');	
    }
            
    if($oldversion< 2014062500)
    {		        
        $table = new xmldb_table('ereflect_questions');	
        $field = new xmldb_field('timemcreated', XMLDB_TYPE_INTEGER, '10', NULL, XMLDB_NOTNULL, NULL, '0');
        $newname = 'timecreated';
            
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, $newname, $continue=true, $feedback=true);
        }
        
        upgrade_mod_savepoint(true, 2014062500, 'ereflect');	        
     }
     
    if($oldversion< 2014062603)
    {		        
        $table = new xmldb_table('ereflect_user_response');        
        if($dbman->table_exists($table))
        {
            $dbman->drop_table($table, $continue=true, $feedback=true);
        }        
        
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('ereflect_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('status', XMLDB_TYPE_CHAR, '12', null, XMLDB_NOTNULL, null, 'PARTIAL');        
        $table->add_field('grade', XMLDB_TYPE_INTEGER, '10', null, null, null, null);                
        $table->add_field('assignment_time', XMLDB_TYPE_INTEGER, '10', null, null, null, null);        
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        
        // Adding keys to table assign_user_mapping.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('ereflect_id', XMLDB_KEY_FOREIGN, array('ereflect_id'), 'ereflect', array('id'));	
        $table->add_key('user_id', XMLDB_KEY_FOREIGN, array('user_id'), 'user', array('id'));        
        
        // Conditionally launch create table for assign_user_mapping.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        // Adding indexes to table assign_user_flags.
        $table->add_index('course', XMLDB_INDEX_NOTUNIQUE, array('course'));		

        // Assignment savepoint reached.
        upgrade_mod_savepoint(true, 2014062603, 'ereflect');
    }	     
	
    // And that's all. Please, examine and understand the 3 example blocks above. Also
    // it's interesting to look how other modules are using this script. Remember that
    // the basic idea is to have "blocks" of code (each one being executed only once,
    // when the module version (version.php) is updated.

    // Lines above (this included) MUST BE DELETED once you get the first version of
    // yout module working. Each time you need to modify something in the module (DB
    // related, you'll raise the version and add one upgrade block here.

    // Final return of upgrade result (true, all went good) to Moodle.
    return true;
}
