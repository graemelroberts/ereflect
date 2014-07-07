<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains the definition for the renderable classes for the assignment
 *
 * @package   mod_ereflect
 * @copyright 2013 G.Roberts Cardiff Met
 * @license   
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Renderable header
 * @package   mod_ereflect
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ereflect_header implements renderable {
    /** @var stdClass the assign record  */
    public $ereflect = null;
    /** @var mixed context|null the context record  */
    public $context = null;
    /** @var bool $showintro - show or hide the intro */
    public $showintro = false;
    /** @var int coursemoduleid - The course module id */
    public $coursemoduleid = 0;
    /** @var string $subpage optional subpage (extra level in the breadcrumbs) */
    public $subpage = '';
    /** @var string $preface optional preface (text to show before the heading) */
    public $preface = '';
	
	// @var stdClass the ereflect_questions record  
	public $course = null;

    /**
     * Constructor
     *
     * @param stdClass $ereflect  - the ereflect database record
     * @param mixed $context context|null the course module context
     * @param bool $showintro  - show or hide the intro
     * @param int $coursemoduleid  - the course module id
     * @param string $subpage  - an optional sub page in the navigation
     * @param string $preface  - an optional preface to show before the heading
     */
    public function __construct(stdClass $ereflect,
                                $context,
                                $showintro,
                                $coursemoduleid,
								$course,
                                $subpage='',
                                $preface='') {
        $this->ereflect = $ereflect;
        $this->context = $context;
        $this->showintro = $showintro;
        $this->coursemoduleid = $coursemoduleid;
		$this->course = $course;
        $this->subpage = $subpage;
        $this->preface = $preface;
    }
}


class ereflect_form implements renderable {
    /** @var moodleform $form is the edit submission form */
    public $form = null;
    /** @var string $classname is the name of the class to assign to the container */
    public $classname = '';
    /** @var string $jsinitfunction is an optional js function to add to the page requires */
    public $jsinitfunction = '';

    /**
     * Constructor
     * @param string $classname This is the class name for the container div
     * @param moodleform $form This is the moodleform
     * @param string $jsinitfunction This is an optional js function to add to the page requires
     */
    public function __construct($classname, moodleform $form, $jsinitfunction = '') {
        $this->classname = $classname;
        $this->form = $form;
        $this->jsinitfunction = $jsinitfunction;
    }
}

class ereflect_addquestion implements renderable {

    /** @var int STUDENT_VIEW */
    const STUDENT_VIEW     = 10;
    /** @var int GRADER_VIEW */
    const GRADER_VIEW      = 20;

    
    /** @var int courseid */
    public $courseid = 0;
    /** @var int coursemoduleid */
    public $coursemoduleid = 0;
    /** @var int the view (STUDENT_VIEW OR GRADER_VIEW) */
    public $view = self::GRADER_VIEW;
  
    public $feedback_message_options;    

    /**
     * Constructor
     *
     * @param int $coursemoduleid
     * @param int $courseid
     * @param string $view
    
     */
    public function __construct($coursemoduleid,
                                $courseid,
                                $view/*,
                                $feedback_message_options*/) {
        $this->coursemoduleid = $coursemoduleid;
        $this->courseid = $courseid;
        $this->view = $view;
        //$this->feedback_message_options = $feedback_message_options;
    }
}

class ereflect_modifyquestion implements renderable {

    /** @var int courseid */
    public $courseid = 0;
    /** @var int coursemoduleid */
    public $coursemoduleid = 0;
    /** @var int the view (STUDENT_VIEW OR GRADER_VIEW) */
    public $view = self::GRADER_VIEW;
    
    // array
    public $feedback_message_options;

    /**
     * Constructor
     *
     * @param int $coursemoduleid
     * @param int $courseid
     * @param string $view
     */
    public function __construct($coursemoduleid,
                                $courseid,
                                $view/*,
                                $feedback_message_options*/) {
        $this->coursemoduleid = $coursemoduleid;
        $this->courseid = $courseid;
        $this->view = $view;
        //$this->feedback_message_options = $feedback_message_options;
    }
}

class ereflect_viewquestion implements renderable {

  // @var stdClass the ereflect_questions record  
  public $ereflect_questions = null;
  
  // $var stdClass the ereflect_options record 
  public $ereflect_options = null;
  
  // $var int $courseid
  public $courseid;
  
  // @var int $ereflect_id 
  public $ereflect_id = null;
  
  public $context;  
  public $instance;
    
  // $var bool $show_amendlink 
  //public $show_amend_link = true;
  
  // $var bool $show_addoptions_link 
  //public $show_addoptions_link = true;

  // $var bool $show_cancel_link 
  //public $show_cancel_link = true;      
  
  // $var bool $show_options 
  //public $show_options = true;
  
  // $var int $show_options 
  public $show_options_eq_id = null;
	  
  /** 
   * Constructor
   * @param int $ereflect_id
   * @param array $ereflect_questions
   *
  */
  
   public function __construct( stdClass $ereflect_questions,
                                /*stdClass $ereflect_options,*/
                                $courseid,
                                $courseinstance,
                                $screen,
                                $show_options_eq_id,
                                $context,
                                $instance
                                )								
   {
        $this->ereflect_questions = $ereflect_questions;
        //$this->ereflect_options = $ereflect_options;
        $this->courseid = $courseid;
        $this->ereflect_id = $courseinstance;

        $this->screen = $screen;
        $this->show_options_eq_id = $show_options_eq_id;
        
        $this->context = $context;
        $this->instance = $instance;
   }
 
}


class ereflect_viewoption implements renderable {

  /** $var stdClass the ereflect_options record */
  public $ereflect_options = null;
 
  public function __construct( stdClass $ereflect_options )
  {
    $this->ereflect_options = $ereflect_options;
  }
 
}

class ereflect_addanswers implements renderable {

  // @var stdClass the ereflect_questions record  
  public $ereflect_questions = null;
    
  // $var int $courseid
  public $courseid;
  
  // @var int $ereflect_id 
  public $ereflect_id = null;
    	  
  /** 
   * Constructor
   * @param int $ereflect_id
   * @param array $ereflect_questions
   *
  */
  
   public function __construct( stdClass $ereflect_questions,
								$courseid,
                                $courseinstance
								)
   {
		$this->ereflect_questions = $ereflect_questions;
		$this->courseid = $courseid;
		$this->ereflect_id = $courseinstance;		
   }
 
}

class ereflect_summary_table implements renderable {

  // @var stdClass the ereflect_questions record  
  public $user_details = null;
  
  // $var int $courseid
  public $courseid;
  
  // @var int $ereflect_id 
  public $ereflect_id = null;
  
  // $var title
  public $title;
  
  
  /** 
   * Constructor
   * @param int $ereflect_id
   * @param array $ereflect_questions
   *
  */
  
   public function __construct( stdClass $user_details,
								$courseid,
                                $courseinstance,
								$title)								
   {
		$this->user_details = $user_details;
		$this->courseid = $courseid;
		$this->ereflect_id = $courseinstance;		
		$this->title = $title;
   }
 
}



