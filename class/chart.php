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
 * Internal library of functions for module ereflect
 *
 * All the ereflect specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *

 * @package   mod_ereflect
 * @copyright 2013 G.Roberts Cardiff Met
 * @license   
*/
 
defined('MOODLE_INTERNAL') || die();

//
// Graeme_r This is based on locallib.php from the Assign module i.e. mod\assign\locallib.php
//

class chart {

	public $imgwidth = 400;
	public $imgheight = 200;
	public $margins = 20;
	public $barwidth = 70;
	public $horizontallines = '';
	public $maxvalue = '';
	
	//public keyvalues array();
	
    /**
     * Constructor for the base chart class.
     *
     */
	public function __construct ( $keyvalues ) 
	{			
		$this->keyvalues = $keyvalues;		
    }
			
	function create_bar_graph( $filename = 'bargraph.png')
	{
		global $CFG;
		
		$debug = false;
		
		$values = $this->keyvalues;
		
		if($debug)
		{
			echo '<pre>';
			print_r($values);
			echo '</pre>';
			
			echo 'Image Width: '.$this->imgwidth.'<br />';
			echo 'Image Height: '.$this->imgheight.'<br />';
			echo 'Margins: '.$this->margins.'<br />';
		}

		//Now define the size of image, i have used an image of size 600x400 for this tutorial.
		$img_width = $this->imgwidth;
		$img_height = $this->imgheight;

		//The graph we are going to create has a border around it, i have declared a variable $margins to create that border around the four sides. 
		$margins = $this->margins;

		//Now find the size of graph by subtracting the size of borders.  
		$graph_width=$img_width - $margins * 2; // e.g. 400- (20*2) = 360
		$graph_height=$img_height - $margins * 2;  // 250 (20*2) = 210

		// Create an image of the size defined above 
		$img=imagecreate($img_width,$img_height); // 360, 210

		//Define the width of bar. Gap between the bars will depend upon the width and number of bars and the gaps will be 
		//one more than the total number of bars as there is gap on the right and left of the graph. You can see in our example, we have 12 bars but 13 gaps, thats why you see ($total_bars+1) in the denominator. 
		$bar_width = $this->barwidth;
		
		$total_bars = count($values); // e.g. 2
		$gap= ($graph_width- $total_bars * $bar_width ) / ($total_bars +1); // 360-(2*70) = 220
		 
		// Define colors to be used in the graph 
		$bar_color=imagecolorallocate($img,0,64,128);
		$background_color=imagecolorallocate($img,240,240,255);
		$border_color=imagecolorallocate($img,200,200,200);
		$line_color=imagecolorallocate($img,220,220,220); 

		// Create a border around the graph by filling in rectangle 
		imagefilledrectangle($img,1,1,$img_width-2,$img_height-2,$border_color); 
		imagefilledrectangle($img,$margins,$margins,$img_width-1-$margins,$img_height-1-$margins,$background_color); 

		// Now the maximum value is required to adjust the scale. Ratio is calculated by dividing graph height by maximum graph value.
		// Each value will be multiplied with ratio, so that no bar goes beyond the graph height. 
		
		if(isset($this->maxvalue) && strlen($this->maxvalue))
		{
			$max_value = $this->maxvalue;
		}
		else
		{
			$max_value=max($values);  // e.g. number of hours for an assignment might only be 7
		}
		$max_value = ceil($max_value/10)*10;
		$ratio= $graph_height/$max_value; // (210/7) = 38.57				

		//Drawing horizontal lines is optional, note that the margin variable is subtracted from image height so that first line is positioned inside the graph area (Discarding the margins). 
		//If you have trouble understanding this code, use a paper and pencil to manually find the values of variable at each repitition of the loop 

		if(isset($this->horizontallines) && strlen($this->horizontallines))
		{
			$horizontal_lines = $this->horizontallines; // Used when showing larger numbers on Y Axis e.g. 10 horizontal lines up to 100 or more 
		}
		else
		{
			if($max_value > 10)
			{
				$horizontal_lines = 5;
			}
			else
			{
				// Show as many horizontal lines are there are values
				$horizontal_lines = $max_value; // Used when showing smaller numbers on Y Axis e.g. < 10
			}
		}	
		
		$horizontal_gap=$graph_height/$horizontal_lines; // 210/5 = 42
		
		for($i=1;$i<=$horizontal_lines;$i++)
		{
			$y=$img_height - $margins - $horizontal_gap * $i ;  //(250-20-(42*1)) = 192 
			imageline($img,$margins,$y,$img_width-$margins,$y,$line_color);
			$v=intval($horizontal_gap * $i /$ratio);
			imagestring($img,0,5,$y-5,$v,$bar_color);
		}

		//Here comes the most crucial part of our graph, drawing the bars. Each of the 8 lines in the for loop are individually explained below 
		//1. Extract key and value pair from the current pointer position, each iteration of loop moves the internal pointer of array to the next entry
		//2. The x1 value (i.e. left) of each bar gets and increment by $gap+$bar_width with each iteration of loop
		//3. The x2 value (i.e. right) is calculated by adding bar width with x1 
		//4. y1 is the top of each bar. ratio is multiplied with individual values to mare sure that bars remain inside the graph boundries.
		//5. y2 (i.e bottom) is fix for all bars. Can also be placed outside the loop 
		//6. Draw the graph with calculated left, top, right and bottom positions 
		//7. The numeric value of each bar is shown at the top. Some plus or minus will be required to center align the displayed value with the bar
		//8. Display the legend i.e. Month names 

		for($i=0;$i< $total_bars; $i++){
			list($key,$value)=each($values);
			$x1= $margins + $gap + $i * ($gap+$bar_width) ;
			$x2= $x1 + $bar_width;
			$y1=$margins +$graph_height- intval($value * $ratio) ;
			$y2=$img_height-$margins;
			imagefilledrectangle($img,$x1,$y1,$x2,$y2,$bar_color);
			imagestring($img,0,$x1+3,$y1-10,$value,$bar_color);
			imagestring($img,0,$x1+3,$img_height-15,$key,$bar_color);
		} 

		$copyfolder = $CFG->cachedir.'/tcpdf/'.$filename;		
		imagepng($img, $copyfolder );		
		return $copyfolder;
	}
	
	function create_line_graph( $filename = 'linegraph.png' )
	{
		global $CFG;		
		$debug = true;		
		$values = $this->keyvalues;
				
		$img_width = $this->imgwidth;
		$img_height = $this->imgheight;

		//The graph we are going to create has a border around it, i have declared a variable $margins to create that border around the four sides. 
		$margins = $this->margins;
		
		$graph_width=$img_width - $margins * 2; // e.g. 400- (20*2) = 360
		$graph_height=$img_height - $margins * 2;  //		
		
		$img = imagecreate($img_width,$img_height);
 		
		// Define colors to be used in the graph 
		$bar_color=imagecolorallocate($img,0,64,128);
		$background_color=imagecolorallocate($img,240,240,255);
		$border_color=imagecolorallocate($img,200,200,200);
		$line_color=imagecolorallocate($img,220,220,220); 		
				
 		// Create a border around the graph by filling in rectangle 
		imagefilledrectangle($img,1,1,$img_width-2,$img_height-2,$border_color);   
		// $img, $x, $y, 
		imagefilledrectangle($img,$margins,$margins,$img_width-1-$margins,$img_height-1-$margins,$background_color); 	
		
		if(isset($this->maxvalue) && strlen($this->maxvalue))
		{		
			$max_value= $this->maxvalue;
		}
		else
		{
			$max_value=max($values);  // e.g. number of hours for an assignment might only be 7
		}
		
		if($debug){echo 'Max Value from Array: '.$max_value.'<br />';}
		$max_value = ceil($max_value/10)*10;
		
		if($debug){echo 'Hence Graph Max Value : '.$max_value.'<br />';}
		$ratio= $graph_height/$max_value; // 
		
		if($debug){echo 'Ratio: '.$ratio.'<br />';}
		
		if(isset($horizontal_lines) && strlen($this->horizontallines))
		{
			$horizontal_lines = $this->horizontallines; // ensure default 10 lines shown (or whatever user might set it to).
		}
		else
		{
			if($max_value>10)
			{
				$horizontal_lines = 10;
			}
			else
			{
				$horizontal_lines = $max_value; // Used when showing smaller numbers on Y Axis e.g. < 10
			}
		}
		
		$horizontal_gap=$graph_height/$horizontal_lines;		
		
		// Setting of horizontal Lines on the graph
		for($i=1;$i<=$horizontal_lines;$i++)
		{					
			$y=$img_height - $margins - $horizontal_gap * $i ;  //(250-20-(42*1)) = 192 
			imageline($img,$margins,$y,$img_width-$margins,$y,$line_color);
			$v=intval($horizontal_gap * $i /$ratio);
			imagestring($img,0,5,$y-5,$v,$bar_color);
			
			if($debug){echo 'Y value = '.$y.', $v = '.$v.'<br />';}
		}
		
		// Get Total count of Values
		$totalcount = count($values); // e.g. 10		
		
		// Set Distance between each point
		$distance= ($graph_width-$totalcount) / ($totalcount +1); // 
		
		if($debug){echo 'Total Count: '.$totalcount.', distance: '.$distance.'<br />';}
		
		// Generate an array to set the horizontal axis
		// Also, set a new array with key number format (as opposed to titles)
		// ready for the drawing of the graph (for loop code directly following foreach)
		$keyvalues = array();
		$n = 0;
		$i = 0;
		foreach($values as $key => $value)
		{
			$i++;
			// image, font size , x coords, y coords, 
			imagestring($img,0,($i*$distance)+$margins,$img_height-15,$key,$bar_color);

			$keyvalues[$n] = $value;
			$n++;
		}
		
		if($debug){
			echo '<hr />';
			echo 'Printing new keyvalues loop;<br />';
			echo '<pre>';
			print_r($keyvalues);
			echo '</pre>';
			echo '<hr />';
		}
		 
		$countloop = $totalcount-1;		
		$n=0;
		
		// If only 1 value for line chart, then just show the literal above
		if($countloop == 0)
		{
			$n++;
			$x1 = $margins + ($n*$distance); // e.g. 20 + 30 = 50
			$y1= $margins +$graph_height- intval($keyvalues[0] * $ratio) ;
			
			if($debug)
			{
				echo 'For the 1 point, X margin: '.$x1.', Y margin: '.$y1.', Keyvalues: '.$keyvalues[0].'<br />';
				//exit();
			}
			imagefilledellipse($img,$x1,$y1,4,4,$bar_color);
			imagestring($img,0,$x1,$y1-10,$keyvalues[0],$bar_color);
			
		}
		else
		{
			for($i=0; $i<$countloop; $i++)
			{			
				$n++;
				$x1 = $margins + ($n*$distance); // e.g. 20 + 30 = 50
				$x2 = $x1 + $distance; // e.g. 50 + 30 = 80
				
				if($debug){echo 'Value1: '.$keyvalues[$i].',Value2: '.$keyvalues[$i+1].'<br />';}
							
				$y1= $margins +$graph_height- intval($keyvalues[$i] * $ratio) ;
				$y2= $margins +$graph_height- intval($keyvalues[$i+1] * $ratio) ;
				
				imageline($img, $x1, $y1, $x2, $y2, $border_color);		
				
				imagefilledellipse($img,$x1,$y1,4,4,$bar_color);
				imagestring($img,0,$x1,$y1-10,$keyvalues[$i],$bar_color);
			}		
		}
		
		// Set the last mark just above the point, since not included in loop above
		imagefilledellipse($img,$x2,$y2,4,4,$bar_color);
		imagestring($img,0,$x2,$y2-10,$keyvalues[$countloop],$bar_color);		
  
 		$copyfolder = $CFG->cachedir.'/tcpdf/'.$filename;		
		imagepng($img, $copyfolder );		
		return $copyfolder;

	}

}