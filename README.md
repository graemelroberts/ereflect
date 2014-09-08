ACTIVITY MODULES - eReflect Questionnaire
-------------------------------------------

This was created by G.Roberts for Cardiff Metropolitan University 2013/2014 and is a bespoke add on to the standard Moodle plugin/activities that are shipped with moodle.
The plugin was created and happily running on Moodle versions 2.5 and 2.6.

The code takes advantange of the following libraries;
- GD Library for graph drawing 
- TCPDF for dynamic PDF creation
- Bootstrap Font Awesome for Icons

Each of these modules contains a number of expected components:

  mod_form.php: a form to setup/update a module instance i.e. General settings screen
  version.php: defines some meta-info and provides upgrading code
  pix/icon.gif: a 16x16 icon for the module
  db/install.xml: an SQL dump of all the required db tables and data
  index.php: a page to list all instances in a course
  view.php: a page to view a particular instance
  lib.php: any/all functions defined by the module should be in here.
         constants should be defined using MODULENAME_xxxxxx
         functions should be defined using modulename_xxxxxx

         Functions of note;

         ereflect_add_instance() - General settings screen i.e. for mod_form.php
         ereflect_update_instance() - General settings screen i.e. for mod_form.php
         ereflect_delete_instance() - General settings screen i.e. for mod_form.php

         ereflect_print_overview() - To enable a student or teacher to view if a particular eReflect questionnaire is outstanding

  class/locallib.php: any/all functions specific to the working of the plugin
         and not to the workings of Moodle i.e. processing for any screens in eReflect other than mod_form.php which 
        automatically uses functions held in lib.php.
       
        view() - Best function to start in order to understand how routing/processing is achieved on all pages other than the 
        general settings screen (mod_form.php)
          

If you are a developer and interested in developing new Modules see:

   Moodle Documentation:  http://moodle.org/doc
   Moodle Community:      http://moodle.org/community

Here is a description about the eReflect questionnaire (as found in lang/en/ereflect.php for modulename_help)
-------------------------------------------------------

The eReflect tool enables users to create questionnaires and provide customised feedback to respondents based on their answers. 
It is typically used to encourage self-assessment, reflection and personal/professional/academic development.
Questionnaires can be used for a variety of purposes, however, they are usually designed to promote student reflection on their own academic performance and their use of feedback. For example, questionnaires typically ask students to comment on their own views of their work and performance (e.g. how many hours did they spend on the coursework? What did they do once they received their feedback? Did they fully understand the assessment criteria? Did they speak to a member of staff about their feedback?).
Questionnaires may contain open questions using a text box, or closed questions using radio buttons. 
When a user creates a questionnaire, they can choose to provide written feedback that corresponds with each response option, as well as a variety of icons (e.g. emoticons or traffic light symbols) used to provide respondents with additional visual feedback. eReflect feedback is usually developmental in nature and may include, for example,  guidance on approaching, planning and completing assignments and advice on time management, how to use assessment criteria and who to contact for further help and advice.
Once a respondent submits their answers to a questionnaire an email can be sent to both the questionnaire creator (typically a teacher) and respondent. Attached to this email is a PDF report that contains the answers provided by the respondent, corresponding feedback and other graphical representations of performance (such as grades and time spent working on an assignment). 
Respondents may be asked to use this report as a tool for reflection and action planning.
A summary screen is visible for the teacher to view participants who have not yet started the questionnaire, those that have partially completed the questionnaire and those that have submitted their questionnaire responses. 
For students who have partially or fully completed the questionnaire, the teacher is able to view answers to the questions.
The student is able to come back at any time to review their responses and access their PDF report.
eReflect may be used in conjunction with the e-Journal tool, which can be used for action planning, recording reflections and closing the feedback loop.


