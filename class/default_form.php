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
 * ereflect default form form
 *
 * @package   mod_ereflect
 * @copyright 2013 G.Roberts Cardiff Met
 * @license   
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir . '/formslib.php');

class mod_ereflect_default_form extends moodleform {

    /**
     * Define this form - called by the parent constructor
     */
    public function definition() {
        $mform = $this->_form;
		
	$debug = false;	

	list($ereflect, $data, $cm ) = $this->_customdata;			
		
    	if($debug)
	{
            echo 'In default_form.mod_ereflect_default_form, about to print $data <br />';						
        }

       	$mform->addElement('hidden', 'action', 'RETURNTOMENU');
        $mform->setType('action', PARAM_TEXT);
        
        $mform->addElement('hidden', 'id', $cm->id);
        $mform->setType('id', PARAM_INT);        
				
	$buttonarray=array();
	$buttonarray[] = &$mform->createElement('cancel', 'cancelandreturn', get_string('cancelandreturntomenu','ereflect'));
	$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
	$mform->closeHeaderBefore('buttonar');		
    }
	
	
}

