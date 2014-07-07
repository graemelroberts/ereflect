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
 * This file contains a renderer for the ereflect class
 * A custom renderer class that extends the plugin_renderer_base and is used by the ereflect module.
 *
 * @package   mod_ereflect
 * @copyright 2013 G.Roberts Cardiff Met
 * @license   
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/ereflect/class/locallib.php');

class mod_ereflect_renderer extends plugin_renderer_base {

    /**
     * Render the header.
     *
     * @param assign_header $header
     * @return string
     */
    public function render_ereflect_header(ereflect_header $header) {
        
        $o = '';
		
        $debug = false;

        if($debug)
        {
            echo 'in renderer.render_ereflect_header <br />';
            echo '<hr />';
            echo '<pre>';
            print_r($header);
            echo '</pre>';	
        }

        /*GR to possibly add back in
		if ($header->subpage) {
            $this->page->navbar->add($header->subpage);
        }*/

        // Page information i.e. at top level above the LHS menu and the Plug-in area
        $this->page->set_url('/mod/ereflect/view.php', array('id' => $header->coursemoduleid));		
        //$this->page->set_title(get_string('pluginname', 'ereflect'));
        $this->page->set_heading(format_string($header->course->fullname));

        //$this->page->set_context($context);

        $this->page->requires->css('/mod/ereflect/styles/styles.css');
                
        $this->page->requires->js('/mod/ereflect/js/general.js',true);
        // If less than IE10 then include the ie9 stylesheet
        $o .= '<!--[if lt IE 10]>';
        $o .= '<link rel="stylesheet" type="text/css" href="styles/ie9.css" />';
        $o .= '<![endif]-->';
        

        $o .= $this->output->header();
        if ($header->preface) {
            $o .= $header->preface;
        }
		
	// this is the Plug in heading i.e. below the Page heading
        $heading = format_string($header->ereflect->name, false, array('context' => $header->context));
        $o .= $this->output->heading($heading);

        if ($header->showintro) {
            $o .= $this->output->box_start('generalbox boxaligncenter', 'intro');
            $o .= format_module_intro('ereflect', $header->ereflect, $header->coursemoduleid);
            $o .= $this->output->box_end();
        }

        return $o;
    }


	/**
     * Render the generic form
     * @param assign_form $form The form to render
     * @return string
     */
    public function render_ereflect_form(ereflect_form $form) {
	
        $o = '';
		
        /*echo 'In renderer.render_ereflect_form';
        echo '<pre>';
        print_r($form);
        echo '</pre>';*/	
		
        /*gr to place back in at some stage!!!!
        if ($form->jsinitfunction) {
        $this->page->requires->js_init_call($form->jsinitfunction, array());
        }*/
        $o .= $this->output->box_start('boxaligncenter ' . $form->classname);
        $o .= $this->moodleform($form->form);
			
        $o .= $this->output->box_end();
        return $o;
    }
	
    /**
     * Helper method dealing with the fact we can not just fetch the output of moodleforms
     *
     * @param moodleform $mform
     * @return string HTML
     */
    protected function moodleform(moodleform $mform) {

        $o = '';
        ob_start();
        $mform->display();
        $o = ob_get_contents();
        ob_end_clean();		

        return $o;
    }
	
	
    /**
     * Page is done - render the footer.
     *
     * @return void
     */
    public function render_footer() {
        return $this->output->footer();
    }
		
	
	/**
	* Main section of Page
	* 
	*/

	/**
     * Utility function to add a row of data to a table with 2 columns. Modified
     * the table param and does not return a value
     *
     * @param html_table $table The table to append the row of data to
     * @param string $first The first column text
     * @param string $second The second column text
     * @return void
     */
    /*private function add_table_row_tuple(html_table $table, $first, $second) {
        $row = new html_table_row();
        $cell1 = new html_table_cell($first);
        $cell2 = new html_table_cell($second);
        $row->cells = array($cell1, $cell2);
        $table->data[] = $row;
    }*/
	
		
    /* NEW way */	
    public function render_ereflect_viewquestion(ereflect_viewquestion $viewquestion)
    {
        global $DB, $CFG;

        $debug = false;
        
        $o = '';

        if($debug)
        { 	
            echo 'In renderer.render_ereflect_viewquestion_details with id '.$viewquestion->ereflect_id.'<br />';
            echo '<pre>';
            print_r($viewquestion);
            echo '</pre>';
            echo 'show options eq id = '.$viewquestion->show_options_eq_id.'<br />';						
        }

        // Example Code
        $table = new html_table();

        $row = new html_table_row();
        $row->attributes = array('class'=>'tableheader parenttableheader');

        if($viewquestion->screen=='ADDQUESTIONS')		
        {
            $cell1 = new html_table_cell(get_string('show_options','ereflect'));
            $cell1->attributes = array('class'=>'col2 TAC');
        }
        else
        {
            $cell1 = new html_table_cell();			
        }
        $cell1->header = true;

        $cell2 = new html_table_cell(get_string('sequence','ereflect'));
        if($viewquestion->screen=='ADDQUESTIONS')		
        {
            $cell2->attributes = array('class'=>'col8');							
        }
        else
        {
            $cell2->attributes = array('class'=>'col8 TAC');
        }
        $cell2->header = true;        

        //$cell2 = new html_table_cell();
        //$cell2 = new html_table_cell(get_string('question_name','ereflect'));		
        $cell3 = new html_table_cell(get_string('question_text','ereflect'));
        $cell3->header = true;        
        
        $cell4 = new html_table_cell(get_string('options_entered','ereflect'));
        $cell4->attributes = array('class'=>'TAC'); // Centre text
        $cell4->header = true;        

        $cell5 = new html_table_cell(get_string('options_reqd','ereflect'));		
        $cell5->attributes = array('class'=>'TAC');
        $cell5->header = true;        

        if($viewquestion->screen=='ADDQUESTIONS')		
        {			
            $cell6 = new html_table_cell(get_string('edit_question','ereflect'));
            $cell6->attributes = array('class'=>'TAC');
            $cell6->header = true;            

            $cell7 = new html_table_cell(get_string('remove_question','ereflect'));		
            $cell7->attributes = array('class'=>'TAC');		
            $cell7->header = true;            
            
            $cell8 = new html_table_cell(get_string('edit_options','ereflect'));
            $cell8->attributes = array('class'=>'TAC');
            $cell8->header = true;                        
        }
        else
        {
            $cell6 = new html_table_cell();
            $cell7 = new html_table_cell();			
            $cell8 = new html_table_cell();			
        }

        //$cell8 = new html_table_cell();	// completed yes or no

        $row->cells = array($cell1, $cell2, $cell3, $cell4, $cell5, $cell6, $cell7, $cell8);
        $table->data[] = $row;

        //$table->head = array(get_string('order_by','ereflect'), get_string('question_name','ereflect'), get_string('question_text','ereflect'), get_string('no_of_options','ereflect'), null, null, null);

        if($debug){echo 'ereflect id: '.$viewquestion->ereflect_id.'<br />';}
        
        // Get a count of ereflect questions
        $whereclause = array('ereflect_id'=>$viewquestion->ereflect_id);
        $qcount = $DB->count_records('ereflect_questions', $whereclause); // Options Entered*/        

        if($debug)
        {
            echo 'Question count: '.$qcount.'<br />';
        }

        $n = 0;
        $var_meq_id = '';

        foreach($viewquestion->ereflect_questions as $eqvar)
        {
            if($var_meq_id != $eqvar->meq_id)
            {
                $n += 1;

                $no_of_options = '';
                $b_showexpand = false;

                // If NOT an open question then get a count of options entered
                if($eqvar->open_question!=1)
                {
                    //echo 'Ereflect Question id: '.$eqvar->id.'<br />';
                    $conditions = array('ereflect_question_id'=>$eqvar->meq_id);
                    $countrecs = $DB->count_records('ereflect_options', $conditions); // Options Entered

                    if($countrecs>0)
                    {
                        $b_showexpand = true;
                    }
                    else
                    {
                        $b_showexpand = false;
                    }

                    $no_of_options = $eqvar->no_of_options;	 // Options Required
                }
                else
                {
                    // otherwise blank out
                    $countrecs = '';
                    $no_of_options = '';
                }			

                // Only show the Show/Hide Options node if there are options to be entered
                $expand = '';
                $amend = '';
                $addoptions = '';				
                $deleteq = '';

                // Only show the options node if we are on the Add Question page
                // Will be set to true on this page and false on the Add Options page
                /* New Collapse/Expand method is based on Javascript*/
                $table_id_value = 'option_table_id_'.$n;
                $option_id_value = 'option_image_id_'.$n;                
                if($viewquestion->screen=='ADDQUESTIONS')
                {
                    if($b_showexpand)
                    {
                        /* GR old way of doing it via a server refresh
                         * but leave it in just in case javascript way is removed in the future..
                         * 
                        if(isset($viewquestion->show_options_eq_id) && strlen($viewquestion->show_options_eq_id))
                        {
                            if($viewquestion->show_options_eq_id==$eqvar->meq_id)
                            {
                                $expandicon = 'fa-minus';
                                $expandaction = 'HIDEOPTIONS';
                            }
                            else
                            {
                                $expandicon = 'fa-plus';
                                $expandaction = 'SHOWOPTIONS';					
                            }			
                        }
                        else
                        {
                            $expandicon = 'fa-plus';			
                            $expandaction = 'SHOWOPTIONS';
                        }

                        // Old way of doing it via a server refresh
                        // Setting the onclick event for the Expand/Contract Options (Show / Hide Options) for each record
                        $urlparams = array('id' => $viewquestion->courseid, 'eq_id' => $eqvar->meq_id,'action'=> $expandaction);
                        $expandurl = new moodle_url('/mod/ereflect/view.php', $urlparams);			
                        $expand = '&nbsp;&nbsp;<a href="#" onclick="showoptions(\''.$expandurl.'\');"><i class="fa '.$expandicon.'"></i></a>';  			
                         **/	
                        $expando = '&nbsp;&nbsp;<a href="#" onclick="show_hide_options(\''.$table_id_value.'\',\''.$option_id_value.'\');"><i id="'.$option_id_value.'" class="fa fa-plus"></i></a>';        
                    }
                    else
                    {
                        // Set this to blank for
                        $expando = '';
                    }

                    $urlparams = array('id' => $viewquestion->courseid, 'eq_id' => $eqvar->meq_id,'action'=>'AMENDQUESTION');
                    $amendurl = new moodle_url('/mod/ereflect/view.php', $urlparams);
                    $amend = '<a href="'.$amendurl.'"><i class="fa fa-pencil"></i></a>';					

                    if($no_of_options > 0)  // Only want a link to add options if it's not an open question
                    {				
                        $urlparams = array('id' => $viewquestion->courseid, 'eq_id' => $eqvar->meq_id, 'action'=>'ADDOPTIONS');
                        $addoptionsurl = new moodle_url('/mod/ereflect/view.php', $urlparams);							
                        $addoptions = '<a href="'.$addoptionsurl.'"><i class="fa fa-pencil-square-o"></i></a>';					
                    }

                    $urlparams = array('id' => $viewquestion->courseid, 'eq_id' => $eqvar->meq_id, 'action'=>'DELETEQUESTIONPROCESS');
                    $deletequrl = new moodle_url('/mod/ereflect/view.php', $urlparams);
                    $deleteq = '<a href="#" onclick="confirmdeletequestion(\''.$deletequrl.'\');"><i class="fa fa-trash-o"></i></a>';

                    // Build of up and down arrows
                    $urlparams = array('id' => $viewquestion->courseid, 'eq_id' => $eqvar->meq_id, 'action'=>'REORDERPROCESSUP');
                    $upurl = new moodle_url('/mod/ereflect/view.php', $urlparams);
                    $spacer = '<img src="' . $this->output->pix_url('spacer') . '" class="iconsmall" alt="" />';

                    //$order_by = $eqvar->meq_order_by.'&nbsp;';
                    $order_by = str_pad($eqvar->meq_order_by, 2, "0", STR_PAD_LEFT).'&nbsp;';
                    
                    if($eqvar->meq_order_by!=1)
                    {
                        $order_by .= '<a href="'.$upurl.'".><i class="fa fa-arrow-up"></i></a>&nbsp;&nbsp;';
                    }
                    else
                    {
                        $order_by .= $spacer;			
                    }			

                    //$downurl = 'hello DOWN!';
                    //$urlparams = array('id' => $viewquestion->courseid, 'ereflect_id' => $eqvar->ereflect_id, 'eq_id' => $eqvar->id, 'action'=>'REORDERPROCESSDOWN');
                    $urlparams = array('id' => $viewquestion->courseid, 'eq_id' => $eqvar->meq_id, 'action'=>'REORDERPROCESSDOWN');
                    $downurl = new moodle_url('/mod/ereflect/view.php', $urlparams);
                    
                    if($debug){echo 'Order by: '.$eqvar->meq_order_by.', Count = '.$qcount.'<br />';}

                    if($eqvar->meq_order_by!=$qcount)
                    {
                        $order_by .= '<a href="'.$downurl.'".><i class="fa fa-arrow-down"></i></a>';
                    }					
                }  // Screen = 'ADDQUESTIONS' only i.e. do not show on ADDOPTIONS SCREEN
                else
                {
                    //$order_by = $eqvar->meq_order_by;
                    $order_by = str_pad($eqvar->meq_order_by, 2, "0", STR_PAD_LEFT);
                }

                $row = new html_table_row();				

                //$cell1 = new html_table_cell($expand); Old way of doing it via server refresh
                                
                //$expand = '&nbsp;&nbsp;<a href="#" onclick="showoptions(\''.$expandurl.'\');"><i class="fa '.$expandicon.'"></i></a>';  				                
                
                if($viewquestion->screen=='ADDQUESTIONS' &&  $b_showexpand)
                {                
                    $cell1 = new html_table_cell($expando);
                }
                else
                {
                    $cell1 = new html_table_cell();
                }  
                $cell1->header = true;
                
                $cell2 = new html_table_cell($order_by);
                
                if($viewquestion->screen=='ADDQUESTIONS')				
                {
                    $cell2->attributes = array('class'=>'col8');
                }
                else
                {
                    $cell2->attributes = array('class'=>'col8 TAC');
                }

                $cell3 = new html_table_cell($eqvar->question_text);				
                                
                $cell4 = new html_table_cell($countrecs);			
                $cell4->attributes = array('class'=>'TAC');

                $cell5 = new html_table_cell($no_of_options);
                $cell5->attributes = array('class'=>'TAC');           

                $cell6 = new html_table_cell($amend);
                $cell6->attributes = array('class'=>'TAC');           

                $cell7 = new html_table_cell($deleteq);							
                $cell7->attributes = array('class'=>'TAC');           

                $cell8 = new html_table_cell($addoptions);		
                $cell8->attributes = array('class'=>'TAC');

                //$check_html = '<i class="fa fa-check-circle""></i>';
                //$cell8 = new html_table_cell($check_html);
                //$row->cells = array($cell1, $cell2, $cell3, $cell4, $cell5, $cell6, $cell7, $cell8);

                $row->cells = array($cell1, $cell2, $cell3, $cell4, $cell5, $cell6, $cell7, $cell8);

                $table->data[] = $row;

                // Create the options table directly below each record

                $option_count = 0;
                // Only Show the Child options for the question if the boolean is set to true
                // if the Value passed in is equal to this particular records id then SHOW THE OPTIONS
                $var_meo_id = '';
                
                // This is now replaced with javascript, so no server refresh is necessary
                //if($viewquestion->show_options_eq_id==$eqvar->meq_id)
                if($b_showexpand)
                {
                    foreach($viewquestion->ereflect_questions as $eovar)
                    {		
                        if(($eovar->meq_id == $eqvar->meq_id)
                           &&($var_meo_id != $eovar->meo_id))
                        {							
                            if($debug)
                            {
                                echo 'Count: '.$n.', EOVAR ereflect question id == $eqvar.id = '.$eovar->meq_id.'<br />';
                            }

                            $option_count += 1;
                            
                            // only create 2nd table header for first record
                            if($option_count == 1)
                            {
                                if($debug)
                                {
                                    echo 'Option count = '.$option_count.'<br />';
                                }
                                $table2 = new html_table();
                                $table2->id = $table_id_value; // same as above for passing into javascript
                                $table2->width = '100%';
                                
                                // one way of doing it $table2->head = array('Number', 'Name', 'Actions');

                                $row2 = new html_table_row();
                                $row2->attributes = array('class'=>'tableheader childtableheader', 'style'=>'width: 100%;');

                                $cell2_1 = new html_table_cell(get_string('order_by','ereflect'));
                                $cell2_1->attributes = array('class'=>'col5 TAC');
                                $cell2_1->header = true;
                                
                                //$cell1->attributes = array('class'=>'grtest', 'style'=>"width: 25px;");
                                $cell2_2 = new html_table_cell(get_string('option_answer_text','ereflect'));		
                                $cell2_2->attributes = array('class'=>'col20');                                
                                $cell2_2->header = true;
                                
                                $cell2_3 = new html_table_cell(get_string('option_feedback_text','ereflect'));
                                $cell2_3->attributes = array('class'=>'col60');                                              
                                $cell2_3->header = true;
                                
                                $cell2_4 = new html_table_cell(get_string('option_image_text','ereflect'));
                                $cell2_4->attributes = array('class'=>'col5 TAC');
                                $cell2_4->header = true;

                                $row2->cells = array($cell2_1, $cell2_2, $cell2_3, $cell2_4);
                                $table2->data[] = $row2;
                            }

                            //$row2 = new html_table_row(array($eovar->order_by, $eovar->option_answer, $eovar->option_feedback));
                            //$row2->attributes['data-id'] = $option_count;
                            //$table2->data[] = $row2;
                            $row2 = new html_table_row();
                            $cell2_1 = new html_table_cell($eovar->meo_order_by);
                            $cell2_1->attributes = array('class'=>'TAC');
                            
                            //$cell1->attributes = array('class'=>'grtest', 'style'=>"width: 25px;");
                            $cell2_2 = new html_table_cell($eovar->option_answer);		
                            $cell2_3 = new html_table_cell($eovar->option_feedback);
                            
                            $html_image = '<i class="fa '.$eovar->icon_name.'" style="color: '.$eovar->icon_colour.';"></i>';                            
                            $cell2_4 = new html_table_cell($html_image);
                            $cell2_4->attributes = array('class'=>'TAC');

                            $row2->cells = array($cell2_1, $cell2_2, $cell2_3, $cell2_4);
                            $table2->data[] = $row2;																																
                        }
                        
                        $var_meo_id = $eovar->meo_id;

                    }  // ereflect_options loop
                }
                
                if($option_count>0)
                {
                    $row = new html_table_row();
                    $cell1 = new html_table_cell(html_writer::table($table2));
                    $cell1->colspan = 8;
                    $row->cells = array($cell1);
                    
                    $row->attributes = array('class' => 'tablerowtest' );
                    //$n += 1; // add another row
                    $option_count += 1;
                    //$row->attributes['data-id'] = $n;
                    $row->attributes['data-id'] = $option_count;
                    $table->data[] = $row;			
                }
                
            } // var_meqid != $eqvar->meq_id

            $var_meq_id = $eqvar->meq_id;			
            
        }

        // GR LINK test
        //$o .= $this->output->container_start('submissionlinks');
        //$urlparams = array('id' => 49, 'action'=>'grading');
        //$url = new moodle_url('/mod/ereflect/view.php', $urlparams);
        //$o .= $this->output->action_link($url, get_string('amend', 'ereflect'));
        //$o .= $this->output->container_end();

        $o .= $this->output->container_start('viewaddedquestions');
        $o .= $this->output->heading(get_string('viewaddedquestions', 'ereflect'), 3);		
        $o .= $this->output->box_start('boxaligncenter viewaddedquestionstable');	
        $o .= html_writer::table($table);
        $o .= $this->output->box_end();
        $o .= $this->output->container_end();  
        
        // Loop around contracting the option values using Javascript
        $n = 0;
        $var_meq_id = '';
        $o .= '<script type="text/javascript">';
        foreach($viewquestion->ereflect_questions as $eqvar)
        {
            if($var_meq_id != $eqvar->meq_id)
            {
                $n += 1;
                $table_id_value = 'option_table_id_'.$n;
                $o .= 'hide_options("'.$table_id_value.'");';
            }
            $var_meq_id = $eqvar->meq_id;
        }
        $o .= '</script>';
        
        
        return $o;
    }	
	
    public function render_ereflect_viewoption(ereflect_viewoption $viewoption)	
    {
        $debug = false;

        $o = '';

        if($debug)
        { 
            echo 'In renderer.render_ereflect_viewoption<br />';
            echo '<pre>';
            print_r($viewoption);
            echo '</pre>';
        }

        $table = new html_table();
        $table->width = '100%';

        $row = new html_table_row();
        $row->attributes = array('class'=>'tableheader childtableheader');		

        $cell_1 = new html_table_cell(get_string('order_by','ereflect'));
        $cell_1->attributes = array('class'=>'col5 TAC');
        //$cell1->attributes = array('class'=>'grtest', 'style'=>"width: 25px;");

        $cell_2 = new html_table_cell(get_string('option_answer_text','ereflect'));		
        $cell_2->attributes = array('class'=>'col20');        
        
        $cell_3 = new html_table_cell(get_string('option_feedback_text','ereflect'));
        $cell_3->attributes = array('class'=>'col60');
        
        $cell_4 = new html_table_cell(get_string('option_image_text','ereflect'));
        $cell_4->attributes = array('class'=>'col5 TAC');

        $row->cells = array($cell_1, $cell_2, $cell_3, $cell_4);
        $table->data[] = $row;

        foreach($viewoption->ereflect_options as $eovar)
        {				
            $row = new html_table_row();
            $cell_1 = new html_table_cell($eovar->order_by);
            $cell_1->attributes = array('class'=>'TAC');
            
            //$cell1->attributes = array('class'=>'grtest', 'style'=>"width: 25px;");
            $cell_2 = new html_table_cell($eovar->option_answer);		
            $cell_3 = new html_table_cell($eovar->option_feedback);			

            $html_image = '<i class="fa '.$eovar->icon_name.'" style="color: '.$eovar->icon_colour.';"></i>';                            
            $cell_4 = new html_table_cell($html_image);
            $cell_4->attributes = array('class'=>'TAC');

            $row->cells = array($cell_1, $cell_2, $cell_3, $cell_4);
            $table->data[] = $row;						
        }  // question id's match

        $o .= $this->output->container_start('viewoption');
        //$o .= $this->output->heading(get_string('viewoption', 'ereflect'), 3);		
        $o .= $this->output->box_start('boxaligncenter viewoptiontable');	
        $o .= html_writer::table($table);
        $o .= $this->output->box_end();
        $o .= $this->output->container_end();	
        
        return $o;
    }
	
    public function render_ereflect_summary_table(ereflect_summary_table $userresponse)
    {
        global $DB, $CFG;

        $debug = false;

        $o = '';
        
        $o .= '<div class="teacher_summary">';
        $o .= '<div class="B">'.$userresponse->title.'</div><br />';

        if($debug)
        { 	
            echo 'In renderer.render_ereflect_user_response with id '.$userresponse->ereflect_id.'<br />';
            echo '<pre>';
            print_r($userresponse);
            echo '</pre>';
        }

        $table = new html_table();
        $table->width = '100%';        
        //$table->attributes = array('class'=>'teacher_summary');

        $nrows = 0;
        foreach($userresponse->user_details as $userrec)
        {
            if($debug)
            {
                echo '<hr />User Loop;<br />';
                echo '<pre>';
                print_r($userrec);
                echo '</pre>';
            }

            // Unique key values that need to be accounted for 
            // outside of the Summary table
            $key_arr = array();
            $key_arr[] = 'viewbutton';
            $key_arr[] = 'id';
            $key_arr[] = 'viewpdf';			

            $nrows += 1;

            // Header
            if($nrows==1)
            {			
                $n = 0;
                foreach($userrec as $key => $value)
                {
                    if($debug){echo 'Key is '.$key.', Value is '.$value.'<br />';}
                    $n += 1;

                    //$key!='viewbutton' && $key!='id')
                    if(!in_array($key, $key_arr))
                    {
                        $cell[$n] = new html_table_cell($key);
                    }
                    else
                    {
                        // View button Header
                        if($key=='viewbutton')
                        {  
                            if($value=='YES')
                            {
                                $cell[$n] = new html_table_cell(get_string('summary_view_answers','mod_ereflect')); // 
                                $cell[$n]->attributes = array('class'=>'TAC');														
                            }
                            else
                            {
                                $cell[$n] = new html_table_cell(); // 
                                $cell[$n]->attributes = array('class'=>'TAC');														
                            }
                        }
                        // View PDF Header
                        else if ($key=='viewpdf')
                        {
                            if($value=='YES')
                            {
                                $cell[$n] = new html_table_cell(get_string('summary_view_pdf','mod_ereflect')); // 
                                $cell[$n]->attributes = array('class'=>'TAC');
                            }
                            else
                            {
                                $cell[$n] = new html_table_cell(); // 
                                $cell[$n]->attributes = array('class'=>'TAC');							
                            }
                        }
                    }
                }
                $row = new html_table_row();
                $row->attributes = array('class'=>'tableheader parenttableheader');							
                $row->cells = $cell;
                $table->data[] = $row;						
            }
			
            // Data
            $user_id = '';
            $n = 0;
            foreach($userrec as $key => $value)
            {
                if($debug){echo 'Key is '.$key.', Value is '.$value.'<br />';}

                if($key=='id')
                {
                    $user_id = $value;
                }

                $n += 1;

                if(!in_array($key, $key_arr))
                {
                    $celld[$n] = new html_table_cell($value);

                    if($key=='Picture')
                    {
                        $celld[$n]->attributes = array('class'=>'col5');
                    }
                    else if($key=='Profile')
                    {
                        $celld[$n]->attributes = array('class'=>'col15');
                    }
                    else if($key=='Email')
                    {
                        $celld[$n]->attributes = array('class'=>'col20');
                    }
                    else
                    {
                        $celld[$n]->attributes = array('class'=>'col10');
                    }
                }
                else
                {
                    // Only if the viewbutton = YES do we show the link to view their profile
                    //if($key=='viewbutton')&& $value=='YES' && isset($user_id) && strlen($user_id))
                    if($key=='viewbutton')
                    {
                        if($value=='YES' && isset($user_id) && strlen($user_id))
                        {
                            $urlparams = array('id' => $userresponse->courseid, 'student_id' => $user_id, 'view' => 'TEACHERVIEW', 'action' => 'VIEWSTUDENTANSWERS');
                            $viewanswersurl = new moodle_url('/mod/ereflect/view.php', $urlparams);							
                            $viewanswers = '<a href="'.$viewanswersurl.'"><i class="fa fa-eye fa-2x"></i></a>';					
                            //
                            $celld[$n] = new html_table_cell($viewanswers);
                            $celld[$n]->attributes = array('class'=>'TAC col10');							
                        }
                        else
                        {
                            $celld[$n] = new html_table_cell();
                            $celld[$n]->attributes = array('class'=>'TAC col10');
                        }
                    }
                    //else if ($key=='viewpdf' && $value=='YES')
                    else if ($key=='viewpdf')
                    {
                        if($value=='YES')
                        {
                            $urlparams = array('id' => $userresponse->courseid, 'student_id' => $user_id, 'action' => 'VIEWPDF');
                            $viewpdfurl = new moodle_url('/mod/ereflect/view.php', $urlparams);
                            $viewpdf = '<a href="#" onclick="open_window(\''.$viewpdfurl.'\')"><i class="fa fa-file-text-o fa-2x"></i></a>';

                            $celld[$n] = new html_table_cell($viewpdf); // 
                            $celld[$n]->attributes = array('class'=>'TAC col10');

                            /* testing below studygraph.php below
                            $urlparams = array('id' => $userresponse->courseid, 'user_id' => $user_id);
                            $viewgraphurl = new moodle_url('/mod/ereflect/pdf/studygraph.php', $urlparams);
                            $viewgraph = '<a href="#" onclick="open_window(\''.$viewgraphurl.'\')"><i class="fa fa-file-text-o fa-2x"></i></a>';

                            $celld[$n+1] = new html_table_cell($viewgraph); // 
                            $celld[$n+1]->attributes = array('class'=>'TAC');*/
                        }
                        else
                        {
                            $celld[$n] = new html_table_cell(); // 
                            $celld[$n]->attributes = array('class'=>'TAC col10');
                        }
                    }	
                }
								
                //if($key=='viewbutton') && $value=='YES' && $userresponse->view_answers)
                //{
                        //echo 'View answers is true';
                        //$celld[$n] = 'new button '.$n;
                //}
            }
						
            $row2 = new html_table_row();			
            $row2->cells = $celld;
            $table->data[] = $row2;			
        }
		
        $o .= html_writer::table($table);
        
        $o .= '</div> <!-- End of teacher_summary class -->';        
		
        /*echo '<pre>';
        print_r($CFG);
        echo '</pre>';*/
				
        return $o;		
		
    }    	
	
}

