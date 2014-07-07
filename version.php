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
 * Defines the version of template
 *
 * This code fragment is called by moodle_needs_upgrading() and
 * /admin/index.php
 *
 * @package    mod_ereflect
 * @copyright  November 2013 Graeme Roberts
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

//$module->version   = 2014021800;  	 // 
//$module->version   = 2014021900;      // completion_message field made nullable
//$module->version   = 2014021901;        // New icon, icon
//$module->version   = 2014022000;        // Language entries for icon image and colour
//$module->version   = 2014022001;        // try again - same as above
//$module->version   = 2014022002;        // try again !! - same as above
//$module->version   = 2014022003;        // try again !! - same as above
//$module->version   = 2014022400;        // cannot copy question in language file for 3 optios only (traffic light system)
//$module->version   = 2014022401;        // try again !! - same as above
//$module->version   = 2014022402;        // new default form literal entries
//$module->version   = 2014022500;        // new literal for Save and submit
//$module->version   = 2014022600;        // Addition of opendate and closedate
//$module->version   = 2014022601;        // Validation literals for open and closed date errors
//$module->version   = 2014022602;        // Literals for open and closed date fields along with help
//$module->version   = 2014022800;        // Literals for open and closed date fields along with help
//$module->version   = 2014030400;        // Literals for no. of questions on open page
//$module->version   = 2014030401;        // Literals for no. of questions on open page
//$module->version   = 2014030500;        // Literals for no. of questions on open page
//$module->version   = 2014030501;        // Literals for completion message
//$module->version   = 2014030502;        // Option image text
//$module->version   = 2014030503;        // Breaks in option literals in main show question body
//$module->version   = 2014030600;        // Extra validation on Complete setup and publish for traffic light system i.e. has to be 3 options
//$module->version   = 2014030601;        // Completion message help in mod_form
//$module->version   = 2014030602;        // Grade error - cannot be zero when entering answers
//$module->version   = 2014030603;        // Assignment Time error - cannot be zero
//$module->version   = 2014031100;        // Option Answer help headings containing {$a}
//$module->version   = 2014031101;        // Option Answer help headings containing {$a}
//$module->version   = 2014031102;        // Option Answer help headings containing {$a}
//$module->version   = 2014031103;        // Change any instances of EReflect to eReflect throughout language file
//$module->version   = 2014031400;        // Lang Changes to feedback
//$module->version   = 2014031700;        // Addition of default prompt entry for Completion Message Editor field
//$module->version   = 2014031701;        // Addition of default prompt entry for Completion Message Editor field
//$module->version   = 2014032001;        // Addition of default prompt entry for Completion Message Editor field
//$module->version   = 2014032100;        // Change option feedback to text (from char 255)
//$module->version   = 2014032102;        // Change option feedback to text (from char 255)
//$module->version   = 2014032500;        // Addition of assignment time length check and validation message
//$module->version   = 2014040702;      // wrong columns in mdl_ereflect_options
//$module->version   = 2014040900;      // readdition of pluginadministration
//$module->version   = 2014042800;      // readdition of pluginadministration
//$module->version   = 2014042900;      // view ejournal button on the Add Questions page
//$module->version   = 2014043000;      // attempt 2 -> view ejournal button on the Add Questions page
//$module->version   = 2014043001;      // attempt 3 -> view ejournal button on the Add Questions page
//$module->version   = 2014043002;      // attempt 3 -> view ejournal button on the Add Questions page
//$module->version   = 2014052200;      // Message in add answers screen to say if student has not yet submitted
//$module->version   = 2014052201;      // Message in add answers screen to say if student has not yet submitted
//$module->version   = 2014052300;      // Message in mod_form (general settings) to inform teacher if eReflect is published
//$module->version   = 2014052700;      // Message in mod_form (general settings) to inform teacher if eReflect is published - reworded
//$module->version   = 2014052701;      // Message in mod_form (general settings) to inform teacher if eReflect is published - reworded attempt 3 !
//$module->version   = 2014052800;      // Language additions for function ereflect_print_overview in lib.php
//$module->version   = 2014052801;      // Language additions for function ereflect_print_overview in lib.php  - 2nd attempt
//$module->version   = 2014052802;      // modulename change in language file
//$module->version   = 2014052803;      // Language amendments for function ereflect_print_overview in lib.php  - 2nd attempt
//$module->version   = 2014061600;      // New feedback_message field in mdl_ereflect_questions
//$module->version   = 2014061601;        // New feedback_message language entries 
//$module->version   = 2014062500;        // Rename of timemcreated to timecreated in ereflect_questions
//$module->version   = 2014062600;      // Addition of course field to mdl_ereflect_user_response
//$module->version   = 2014062601;      // Addition of course field to mdl_ereflect_user_response
//$module->version   = 2014062602;      // Recreation of entire mdl_ereflect_user_response table with more foreign keys
$module->version   = 2014062603;      // Recreation of entire mdl_ereflect_user_response table with more foreign keys - Take 2
$module->requires  = 2010031900;      // Requires this Moodle version (release for moodle 2.5)
$module->cron      = 0;               // Period for cron to check this module (secs)
$module->component = 'mod_ereflect'; // To check on upgrade, that module sits in correct place
