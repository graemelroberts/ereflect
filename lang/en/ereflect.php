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
 * English strings for ereflect
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_ereflect
 * @copyright  2013 Graeme Roberts  Cardiff Met
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'eReflect Questionnaire'; // Title of Module as it appears  when Adding an activity module
$string['modulenameplural'] = 'eReflect Questionnaire(s)';

//This will be observed when choosing to create an Activity Module
$string['modulename_help'] = '<p>The eReflect Self-Assessment Questionnaire allows teachers to create a series of questions in order for students to fill out post completion of an assignment.</p>
<p>Each question can either be an open question represented as a text box or a closed question with two or more answers against it, represented as radio boxes. Each answer can have a teacher response and a coloured image against it, such as a green, orange or red circle representing traffic lights.</p>
<p>The student will answer a series of open questions or check boxes and submit the answers back to the teacher. As soon as the answers are submitted, an email can be sent to both the teacher(s) and particular student.</p>
<p>Also, a PDF report is attached to the email and visible within the student results page. The PD report contains the answers to the questions, the automatic teacher response to the answers and also an assignment mark graph and a time spend graph</p>
<p>A summary screen is visible for the teacher to view who has not started the questionnaire, who has partially entered the questionnaire and who has completed the questionnaire. For students who have partially or fully completed the questionnaire, the teacher is able to view the answers to the questions.</p>
<p>The student is able to come back at any time to review the answers to their questions and view their PDF report.</p>';


/*~~~~~~~~~~~~~~~~~~~~~~~~   ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

$string['ereflect:view'] = 'Preview eReflect Questionnaire';
$string['ereflect:submit'] = 'Submission of answers to questionnaire';
$string['ereflect:grade'] = 'View eReflect Questionnaire answer summary';
$string['ereflect:addinstance'] = 'Add a new eReflect Questionnaire';

/*~~~~~~~~~~~~~~~~~~~~~~~~~~   Settings Page ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

// Section headings on settings page
$string['layout'] = 'Layout';
$string['submissionsettings'] = 'Submissions Settings';

$string['ereflect_published'] = 'WARNING - This eReflect Questionnaire is now published - Any changes you now make may create inconsistencies between Student reports';

$string['ereflectfieldset'] = '';
$string['ereflectname'] = 'eReflect name';
$string['ereflectname_help'] = 'The name given to the particular eReflect Self-Assessment Questionnaire';


$string['preparationtime'] = 'Suggested Preparation Time';
$string['addquestion'] = 'Add Question';

$string['usetrafficlight'] = 'Use Default Traffic Light System';
$string['usetrafficlight_help'] = 'Determines whether the teacher will see the default 3 options for green, amber and red or whether they will enter their own set of options for each question, differing in quantity.';

$string['usetrafficimages'] = 'Use Default Traffic Light System Images';
$string['usetrafficimages_help'] = 'This field determines whether the Traffic light system above will use the default traffic light images of green, amber and red or whether the Teacher will upload their own images';

$string['questions_per_page'] = 'Questions per page';
//$string['navigation_method'] = 'Questions in Sequential Order';
//$string['navigation_method_help'] = 'Determines whether questions are in sequential order or are completely random.';

$string['email_notification'] = 'Email Notification';
$string['email_notification_help'] = 'Option to email the student the report after subsequently completing the questionnaire. The email will include an attachment report in pdf format.';

$string['completion_message'] = 'Completion Message';
$string['completion_message_help'] = 'This field allows the Creator to display an extra message to the users post-completion of the self-assessment questionnaire';

$string['completion_message_prompt'] = 'Please can you now use the eJournal to expand on the answers that the feedback suggests. Also, please review the documents attached';


$string['include_mark_in_linegraph'] = 'Include mark in line graph';
$string['include_mark_in_linegraph_help'] = 'The teacher will need to determine whether this assignment is to be included in the Line Graph depiction for Assignment marks held within the PDF report.';

$string['include_preptime_in_report'] = 'Include Preparation Time in report';
$string['include_preptime_in_report_help'] = 'The teacher will need to determine whether the Preparation time is valid for this eReflect questionnaire. This setting will also dictate whether the Student Study Graph is displayed';

// What about an email to the Teacher regarding when a student has completed the questionnaire ? too many emails !?

$string['questions_per_page_notvalid'] = 'The Questions per page must be a valid number greater than 0';
$string['preparationtime_notvalid'] = 'The Suggested Preparation time must be a valid number greater than 0';

// Submit buttons
$string['savechangesandreturntocourse'] = 'Save Changes and return to course';
$string['savechangesanddisplay'] = 'Save and display';
$string['savechangesandcontinue'] = 'Save and add feedback questions';


$string['ereflect'] = 'eReflect'; // not sure what this is for ?
$string['pluginadministration'] = 'eReflect administration'; // looks like this is needed, but don't know why
$string['pluginname'] = 'eReflect'; //not sure what this is for ?

// Need to come back to these i.e. Capabilities in db\access.php
$string['ereflect:addinstance'] = 'Add a new simple HTML block';
$string['ereflect:myaddinstance'] = 'Add a new simple HTML block to the My Moodle page';

$string['opendate'] = 'Use Open Date';
$string['opendate_help'] = 'You can specify a date to open the questionnaire here. Check the check box, and select the date and time you want.
 Users will not be able to fill out the questionnaire before that date. If this is not selected, it will be open immediately.';
$string['closedate'] = 'Use Close Date';
$string['closedate_help'] = 'You can specify a date to close the questionnaire here. Check the check box, and select the date and time you want.
 Users will not be able to fill out the questionnaire after that date. If this is not selected, it will never be closed.';


$string['opendateerror'] = 'eReflect Questionnaire window for Student entries ({$a}) has not started yet';
$string['closedateerror'] = 'eReflect Questionnaire window for Student entries ({$a}) has ended';

/*~~~~~~~~~~~~~~~~~~~~~~~~~~   Add Feedback Questions  Page ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

// Complete
$string['complete_error'] = 'The following Questions do not have their options entered:';
$string['traffic_light_error'] = 'The following Questions do not have the correct number of options (3) for a Traffic Light System:';

// Question Bank Options
$string['questionbank'] = 'Question Bank';
$string['viewoptions'] = 'View Options';
$string['addquestion_frombank'] = 'Add Question with Options';
$string['usequestion_tobank'] = 'Use Question and Amend';

// Two titles i.e. One for Questions that already exist and one for the form to Add one or more questions
$string['viewaddedquestions'] = 'Questions currently added';
$string['addfeedbackquestions'] = 'Add Questions';

// Fields in Table at top of Questions that have already been added
$string['order_by'] = 'Order';
$string['question_name'] = 'Question Name';
$string['question_text'] = 'Question Text';

// options
$string['option_answer_text'] = 'Option<br />Answer';
$string['option_feedback_text'] = 'Option<br />Feedback';
$string['option_image_text'] = 'Option<br />Image';

// Links in Table at top of Questions that have already been added
$string['edit_question'] = 'Edit<br />Question';
$string['remove_question'] = 'Remove<br />Question';
$string['edit_options'] = 'Edit<br />Options';

// Help text next to the fields in the form to add one or more questions
$string['question_name_help'] = 'A unique name for the question e.g. Question 1';
$string['question_text_help'] = 'The question text displayed to the student e.g. How clear was the assignment question ?';

// Literals in form to add one or more questions
$string['show_options'] = 'Show<br />Options';
$string['sequence'] = 'Sequence';
$string['open_question'] = 'Open Question';
$string['open_question_help'] = 'If this field is set to Yes, the student must answer in plain text, as opposed to choosing from a number of different options. The Yes/No drop down list is set to No (not an open question) by default.';
$string['no_of_options'] = 'No. of Options';
$string['options_reqd'] = 'Options<br />Required';
$string['options_entered'] = 'Options<br />Entered';

$string['no_of_options_help'] = 'This is the number of options that need to be set up per question. This value must always be greater than 1. When using the default traffic light system this value will always be set to 3 and will not be able to be amended.';

// Buttons for form to add one or more questions
$string['saveaddfeedbackquestion'] = 'Add Feedback Question';
$string['complete_setup'] = 'Complete Setup and Publish';

// 
$string['questioninsertfirst'] = 'You need to enter some questions before you are able to complete the Ereflect Questionnaire';

$string['cannotamendmessage'] = 'eReflect Questionnaire is now live - You will be unable to amend this until it is placed on hold';

$string['eqonhold'] = 'Place Questionnaire on hold to Amend';
$string['canceladdbeedbackquestion'] = 'Cancel and Return';

$string['onholduserstarted'] = 'Cannot place on hold - at least one user has started completing the ereflect questionnaire';

// Up down arrows
$string['whereiscurrentquestionid'] = 'Cannot find current question';
$string['whereisnextquestionid'] = 'Cannot find next question';

// Success or Failure messages

$string['toomanyoptionsforereflect'] = 'Cannot copy - this particular eReflect questionnaire is set up for Traffic Light System i.e. 3 options per question only.';
$string['addquestioninsertfailed'] = 'Insert failed on attempting to Add a Feedback Question';

$string['deletequestionfailed'] = 'Delete failed on attempting to remove a Question';
$string['deletequestionsucceeded'] = 'Succeeded in deleting question';

$string['deleteoptionfailed'] = 'Delete failed on attempting to remvoe an Option';

// complete button
$string['modifyereflectfailed'] = 'Failed to complete the eReflect for Student Entry';

$string['notenoughquestions'] = 'You must enter at least 2 questions in order to complete the eReflect questionnaire';


$string['feedback_message'] = 'Feedback Message';
$string['feedback_message_help'] = 'This field allows the Creator to upload media and files associated with each Question';


/*~~~~~~~~~~~~~~~~~~~~~~~~~~   Modify Questions Page ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/


$string['modifyfeedbackquestions'] = 'Modify Feedback Question';

$string['savechangesandreturntoeditfeedback'] = 'Save Changes and Return';
$string['cancelandreturntoeditfeedback'] = 'Cancel and Return';

$string['modifyquestionfailed'] = 'Update failed when Modifying a Question';
$string['modifyquestionsucceeded'] = 'Succeeded in Modifying the Question';

$string['err_no_of_options'] = 'No. of Options cannot be zero';
$string['err_traffic_light'] = 'You have elected to use the Traffic Light system - therefore you must have three options';

/*~~~~~~~~~~~~~~~~~~~~~~~~~~   Add Feedback Options Page ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

$string['addfeedbackoptions'] = 'Add Feedback Options';

$string['option_answer'] = 'Option {$a} Answer';
$string['option_feedback'] = 'Option {$a} Feedback';
$string['option_traffic_light'] = 'Option {$a} Traffic Light';
$string['option_icon'] = 'Option {$a} Icon Name';
$string['option_image'] = 'Option {$a} Image';
$string['option_icon_colour'] = 'Option {$a} Colour';


$string['option_answer_help'] = 'The answer for an option, which is always part of a non-open question - There should always be at least two option answers to enter.';
$string['option_feedback_help'] = 'The feedback for an option - this is displayed to a user once the self-assessment questionnaire has been completed in the results on page and within the pdf report.';

$string['addoptions_saveandreturn'] = 'Save and Return';
$string['addoptions_cancelandreturn'] = 'Cancel and Return';

$string['nooptionssubmitted'] = 'No options were submitted - please try again';
$string['addoptionsinsertfailed'] = 'Failed to insert new options - please try again';

$string['err_answer_field_blank'] = 'Option {$a} Answer field cannot be blank.';
$string['err_feedback_field_blank'] = 'Option {$a} Feedback field cannot be blank.';
$string['err_icon_colour'] = 'Icon field {$a} cannot be blank.';

//$string['show_image'] = 'Show Image';

/*~~~~~~~~~~~~~~~~~~~~~~~~~~   Add  Answers Page ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

$string['profile_details'] = 'User Details';

$string['addanswerstitle'] = 'Please answer the following questions';
$string['youranswerstitle'] = 'Your answers';

$string['questionscount'] = 'There are {$a} questions';

$string['answersthankyou'] = 'Dear {$a},<br />Thank you very much for taking the time to complete the eReflect questionnaire.<br />
Please find some feedback to your answers below and your PDF available for review<br />';
$string['completionmessage'] = 'Please can you now use the eJournal to expand on the answers that the feedback suggests.';

$string['openanswer'] = 'Answer';

$string['addanswers_saveandreturn'] = 'Save Answers';
$string['addanswers_saveandsubmit'] = 'Save and Submit';
$string['addanswers_cancelandreturn'] = 'Cancel and Return';

$string['answerinsertfailed'] = 'For some reasons Moodle has failed to Insert the answers for the questions - please try again';

$string['add_ereflect_user_response_failed'] = 'Moodle has failed to insert into the user response table';
$string['upd_ereflect_user_response_failed'] = 'Moodle has failed to update the user response table';

$string['student_complete'] = 'Complete Questionnaire and Submit to Teacher';
$string['cannot_student_complete'] = 'Questionnaire is not in the correct status to complete';
$string['cannot_student_complete2'] = 'eReflect Questionnaire is not in the correct status to complete';
$string['field_before_completion'] = 'Please enter the {$a} and save before completion';

$string['grade'] = 'Assignment Mark';
$string['assignment_time'] = 'Actual Assignment Time';
$string['assignment_time_error'] = 'Please enter how long you spent undertaking the assignment';
$string['assign_time_length_error'] = 'Length of assignment time is too long.';

$string['notallquestionsanswered'] = 'Cannot complete eReflect Questionnaire - not all questions have been answered.';
$string['cantsubmit'] = 'Cannot submit the eReflect Questionnaire - Not all the questions have been answered.';


//$string['feedback'] = 'Feedback:';

$string['extra_questions'] = 'Also, Please answer the following questions';

$string['previous_page'] = 'Previous Page';
$string['next_page'] = 'Next Page';

$string['viewejournallit'] = 'View eJournal';
$string['studentnotcomplete'] = 'The Student has not submitted the eReflect Questionnaire';


/*~~~~~~~~~~~~~~~~~~~~~~~~~  Default Form   ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

$string['questionnairenotready'] = 'The eReflect Questionnaire is not currently ready for student entry.';
$string['cancelandreturntomenu'] = 'Return to menu';

/*~~~~~~~~~~~~~~~~~~~~~~~~~~   Statistics Summary Page   ~~~~~~~~~~~~~~~~~~~~~~~~~~*/

$string['respondents'] = 'Students who have partially completed the eReflect Questionnaire';
$string['respondents_c'] = 'Students who have completed the eReflect Questionnaire';
$string['non-respondents'] = 'Students who have yet to start the eReflect Questionnaire';

// These Literals will show as headers in each of the 3 Summary Tables
$string['user_picture'] = 'Picture';
$string['user_profile'] = 'Profile';
$string['user_email'] = 'Email';
$string['last_accessed'] = 'Last Login';
$string['user_created'] = 'First Response';
$string['user_modified'] = 'Last Response';

$string['summary_view_answers'] = 'View Answers';
$string['summary_view_pdf'] = 'View PDF';


/*~~~~~~~~~~~~~~~~~~~~~~~~~~ Default Language entries ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

$string['no_records_found'] = 'No records found';

/* Email entries */
$string['email_subject_user'] = 'eReflect Email - {$a}';
$string['email_dear_user'] = 'Dear {$a},';
$string['email_body_user'] = 'Thank you for completing the eReflect Questionnaire. Please find your PDF report attached.';
$string['email_body_teacher'] = 'Please find an eReflect Questionnaire PDF report attached for user {$a}.';

/* Cohort Report */
$string['view_cohort'] = 'View Cohort Spreadsheet';

$string['cohort_nodata'] = 'No Data Found for Cohort Report';

/*~~~~~~~~~~~~~~~~~~~~~~~~~~   Default Page   ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

$string['submitreturn'] = 'Return';

/****************** course overview page *************************/

//$string['activityoverview'] = 'You have eJournals that need attention';
//$string['notsubmittedyet'] = 'Not submitted yet';
$string['duedate'] = 'Due date';
$string['duedateno'] = 'No due date';
$string['strpostsoutst'] = 'There are {$a} incomplete eReflect questionnaire(s)';
