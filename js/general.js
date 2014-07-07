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

// Button on the index.php screen (called from Activities block)
function ereflectgo( p_url )
{
    //alert('in function ereflectgo');
    
    var x = document.getElementById("menuereflect_id").selectedIndex;
    p_value = document.getElementsByTagName("option")[x].value;
    
    var res = p_value.split(':');
    
    //alert('value split: '+res);
    
    var v_ereflect_id = res[0];
    var v_course_id = res[1];
    
    //alert('ereflect_id: '+v_ereflect_id+', course_id: '+v_course_id);
    
    var v_url = p_url+'&id='+v_course_id+'&ereflect_id='+v_ereflect_id;
    
    //alert('in ereflectgo with url '+ v_url );        
    
    if(p_value=='')
    {
        alert('Please select an eReflect Questionnaire from the drop down list');
    }
    else
    {
        location.href = v_url;	
    }
}

function showoptions( p_url )
{
    //alert('In general.js->showoptions file with url of '+p_url);
    location.href = p_url;
}

function confirmdeletequestion( p_url )
{
    //alert('In general.js->confirmdeletequestion file with url of '+p_url);
    var r=confirm("Are you sure you want to delete this record? Please press the 'OK' button if you are sure, otherwise press the 'Cancel' button to return to the screen.");

    if (r==true)
    {
        location.href = p_url;
    }
}

function viewqbankoptions( p_url )
{
    var x = document.getElementById("menuquestionbankoptions").selectedIndex;  // why is the word "menu" added to the front of the name !!?? annoying !!	
    p_value = document.getElementsByTagName("option")[x].value;

    var v_url = p_url+'&questionbank_eq_id='+p_value;

    //alert('In viewqbankoptions, URL = '+v_url);

    if(p_value=='')
    {
        alert('Please select a question from Question Bank drop down list');
    }
    else
    {
        location.href = v_url;	
    }
}

function changestatus( p_url, p_status )
{
    
    if(p_status=='amend')
    {
        v_message = 'Are you sure you want to place the Questionnaire on hold?';
    }
    else if(p_status=='publish')
    {
        v_message = 'Are you sure you want to publish the Questionnaire?';
    }
    //alert('In changestatus with url : '+p_url);
    var r=confirm(v_message+ " Please press the 'OK' button if you are sure, otherwise press the 'Cancel' button to return to the screen.");
    if (r==true)
    {
        location.href = p_url;
    }	
}


function studentcomplete( p_url )
{
    //alert('In changestatus with url : '+p_url);
    var r=confirm("Are you sure you want to complete the questionnaire and submit to the Teacher? After this point you will be able to view but not amend your answers (Ok to Submit, Cancel otherwise).");
    if (r==true)
    {
        alert('It is in right bit');
        document.getElementById("id_addanswers_saveandsubmit").submit();
    }
    
   
}

function myFunction()
{
    var x = document.getElementById("mySelect").selectedIndex;
    alert(document.getElementsByTagName("option")[x].value);
}

function open_window( p_url )
{
    //alert('in open window with url ' +p_url);
    window.open(p_url,"_blank","toolbar=yes, scrollbars=yes, resizable=yes, top=500, left=500, width=1000, height=1000");
}

function changeiconname( p_id )
{    
    var x = document.getElementById("option_icon_name_"+p_id).selectedIndex;  
    p_value = document.getElementById("option_icon_name_"+p_id)[x].value;

    //alert('In changeicon with value '+p_value+' and id '+p_id);

    //fa fa_circle fa-2x
    document.getElementById("option_icon_symbol_"+p_id).className = "fa "+p_value+" fa-2x";
}

function changeiconcolour( p_id )
{    
    var y = document.getElementById("iconoptions_"+p_id).selectedIndex;  // why is the word "menu" added to the front of the name !!?? annoying !!	
    p_value = document.getElementById("iconoptions_"+p_id)[y].value;
    //alert('In changeiconcolour with value '+p_value+' and id '+p_id);
    document.getElementById("option_icon_symbol_"+p_id).style.color=""+p_value+"";    
}

function hide_options(p_id)
{
    //alert('in hide_options with id '+p_id);
    
    var myElem = document.getElementById(p_id);

    if(myElem != null)
    {
        //document.getElementById(p_id).style.display = 'none';    
        myElem.style.display = 'none';
    }
}

function show_hide_options(p_id, p_image_id)
{    
    if(document.getElementById(p_id).style.display=='table')
    {
        //alert('about to hide '+p_id);
        document.getElementById(p_id).style.display = 'none';
        document.getElementById(p_image_id).className = "fa fa-plus";
    }
    else if(document.getElementById(p_id).style.display=='none')
    {
        //alert('about to show '+p_id);
        document.getElementById(p_id).style.display = 'table';
        document.getElementById(p_image_id).className = "fa fa-minus";
    }
    /*else
    {
        document.getElementById(p_id).style.display = 'none';        
    }*/
}

function go_page( p_page_no, p_total_pages)
{
    //alert('in function go_page for page '+p_page_no+', Total Pages:' +p_total_pages);
    
    for(var i=1; i<=p_total_pages; i++)
    {
        //alert('in loop for record '+i);
        
        var p_div = 'div_page_'+i;
        //alert('in loop for '+p_div);
        
        if(i==p_page_no)
        {
            //alert('will show page '+i+' for p_div '+p_div);
            document.getElementById(p_div).style.display = 'block';
            document.getElementsByName("pageno")[0].value = p_page_no;
            //alert("page no value: "+document.getElementsByName("pageno")[0].value);            
        }
        else
        {
            //alert('will hide page '+i+' for p_div '+p_div);            
            document.getElementById(p_div).style.display = 'none';
        }
    }    
}

function jssubmitform()
{
    alert('in jssubmitform()');
    
    
}

