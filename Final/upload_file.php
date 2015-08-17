<?php

if (!defined('PHPLISTINIT')) die(); ## avoid pages being loaded directly
if ($GLOBALS["commandline"]) {
 echo 'not to oppened by command line';
 die();
}
require_once(dirname(__FILE__).'New_And_Modify_Entry_Table_Display.php');
require_once(dirname(__FILE__).'Attribute_Changer_Plugin.php');

$javascript_src = dirname(__FILE__) . '/Attribute_Changer_PLugin/Script_For_Attribute_Changer.js';

$page_print =  '
<div>Attribute Changer</div>
<div id="error_printing"></div>
<form action="upload_file.php" method="post" enctype="multipart/form-data" id="file_upload_form">
    Select file to upload:
    (must be comma separated text)
    <input type="file" name="attribute_changer_file_to_upload" id="attribute_changer_file_to_upload">
    <input type="button" value="attribute_changer_upload_file_button" name="attribute_changer_upload_file_button" id="attribute_changer_upload_file_button" onClick="Test_Upload_File()">
</form>
<form action="" method="post" enctype="multipart/form-data" id="text_upload_form">
    Copy file to upload:
    (must be comma separated text)
    <input type="text" name="attribute_changer_text_to_upload" id="attribute_changer_text_to_upload">
    <input type="button" value="attribute_changer_upload_text" name="attribute_changer_upload_text" onClick="Test_Upload_Text()">
    desired_file_name:<input type="text" name="attribute_changer_text_name">
</form>';

$attribute_changer = $GLOBALS['plugins']['Attribute_Changer_Plugin'];
$FILE_LOCATION;

if(!isset($_POST)) {
    
    print('<html><head><script src="'.$javascript_src.'"></script></head><body>'.$page_print.'</body></html>');
}

else if(isset($_POST['attribute_changer_file_to_upload'])) {

    if(!$attribute_changer->Test_Create_Temp_Dir()) {
        print("<html><body>Error with temp directory</body></html>");
        return;
    }

//HERE HAVE A CHECK FOR GOOD SETUP
    $attribute_changer->New_Session();
    


    $target_dir = PLUGIN_ROOTDIR.'/Attribute_Changer_PLugin/temp_table_uploads/';
    $target_file = $target_dir . basename($_FILES["attribute_changer_file_to_upload"]["name"]);
    $uploadOk = 1;
    $new_file_type = pathinfo($target_file,PATHINFO_EXTENSION);

    $new_html = '<html><body>';
    // Check if file already exists
    if (file_exists($target_file)) {
        while(file_exists( ($target_file = $target_file.strval(rand(0,100))))){

        }
        $new_html = $new_html."<div>File already exists, added rand value. File is:. ".basename($target_file).'</div>';
    }
    // Check file size
    if ($_FILES["attribute_changer_file_to_upload"]["size"] > 1000000000) {
        $new_html = $new_html."<div>Sorry, your file is too large > 1GB. </div>";
        $uploadOk = 0;
    }
    // Allow certain file formats

    //add other comma separated
    if($new_file_type != "csv") {
        $new_html = $new_html."<div>Sorry, only csv allowed. </div>";
        $uploadOk = 0;
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $new_html = $new_html."<div>Sorry, your file was not uploaded. </div>".$page_print;
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["attribute_changer_file_to_upload"]["tmp_name"], $target_file)) {
            $new_html = $new_html."<div>The file ". basename($target_file). " has been uploaded.</div>";

            $attribute_changer->$Current_Session->Set_File_Location($target_file);
            $cols_match = $attribute_changer->Get_Attribute_File_Column_Match();

            if($cols_match == ('ERROR NO CURRENT SESSION'|"ERROR WITH SESSION FILE LOCATION"|'') ) {
                $new_html = $new_html.'<div>There was an error with the column select table forming.</div>'.$page_print;

            }
            else{
                $new_html= $new_html.$cols_match;
                
            }
        } 
        else {
            $new_html = $new_html."<div>Sorry, there was an error uploading your file.</div>".$page_print;
        }
    }

    $new_html = $new_html.'</body></html>';
    print($new_html);

}

else if(isset($_POST['submit']['File_Column_Match_Submit'])) {

    if(!isset($_POST['attribute_to_match'])) {
        //shouldnt happen .... else user needs to be WARNEDDDDD
    }
    else if(!isset($_POST['attribute_to_match']['email'])) {

        //this can be done in JS
        $display_html = "<html><body>no email column selected</body></html>";
    }
    else{
        $FILE_LOCATION = $GLOBALS['plugins']['Attribute_Changer_Plugin']->$Current_Session->Get_File_Location();

        asort($_POST['attribute_to_match'], SORT_NUMERIC);
        //so that the columns are matched, easier to read the file from comma to comma
        $fp = fopen($FILE_LOCATION, 'r');
        $current_char;
        while(($current_char = fread($fp, 1)) !== '\n' && !feof($fp)) {
            //skip the first row as this is just the csv defined column name
        }
        if(feof($fp)) {
            //....only 1 line whhaaat
        }

        $file_attribute_value_array = array();

        $current_block = '';
        $lines = array();

        $is_first = 1;

        $previous_last_line = '';


        while(!feof($fp)) {
            //read 10kb at a time
            $current_block = fread($fp, 10260);
            $lines = explode('\n', $current_block);

            //if this is not the first pass, merge the last previous line
            if($is_first == 0) {
                $lines[0] = $previous_last_line.$lines[0];
            }

            $previous_last_line = $lines[count($lines)-1];
            
            //last line is merged with next first line
            for ($i=0 ; $i < count($lines) - 1 ; $i++) { 

                $file_attribute_value_array = explode(',', $lines[$i]);

                if(count($file_attribute_value_array) > 0 && $file_attribute_value_array[0] != '') {

                    $new_attribute_value_array = array();

                    foreach ($_POST['attribute_to_match'] as $col_number => $attribute_name) {
                        if(isset($file_attribute_value_array[$col_number]) && $file_attribute_value_array[$col_number] != '') {
                            $new_attribute_value_array[$attribute_name] = $file_attribute_value_array[$col_number];
                        }
                    }
                    if(isset($new_attribute_value_array['email'])) {
                         $attribute_changer->Test_Entry($new_attribute_value_array);
                    } 
                }
 
            }
        }
        if($previous_last_line != '') {
            $file_attribute_value_array = explode(',', $previous_last_line);
            

            if(count($file_attribute_value_array)> 0 && $file_attribute_value_array[0] != '') {

                $new_attribute_value_array = array();

                foreach ($_POST['attribute_to_match'] as $col_number => $attribute_name) {
                    if(isset($file_attribute_value_array[$col_number]) && $file_attribute_value_array[$col_number] != '') {
                        $new_attribute_value_array[$attribute_name] = $file_attribute_value_array[$col_number];
                    }
                }
                if(isset($new_attribute_value_array['email'])) {
                     $attribute_changer->Test_Entry($new_attribute_value_array);
                } 
            }
        }

        fclose($fp);
        $display_html ='<html><body>';
        $new_entry_table_html = '';
        if(Initialize_New_Entries_Display()!=null) {
            $display_html = $display_html.Get_New_Entry_Table_Block().'</body></html>';
        }
        else{
            if(Initialize_Modify_Entries_Display()!=null) {
                $display_html = $display_html.Get_Modify_Entry_Table_Block().'</body></html>';
            }
            else{
                $display_html = $display_html.'There is nothing new or to modify</body></html>'
            }
        }
    }

    print($display_html);      
}


function Test_Create_Temp_Dir() {
    $temp_dir = PLUGIN_ROOTDIR.'Attribute_Changer_PLugin/temp_table_uploads/';
    if(!file_exists(PLUGIN_ROOTDIR.'Attribute_Changer_PLugin/')) {
        return false;
    }
    else if(!is_dir(PLUGIN_ROOTDIR.'Attribute_Changer_PLugin/')) {
        return false;
    }
    else{
        if(!file_exists($temp_dir)) {
            mkdir($temp_dir);
            return true;
        }
        else{
            if(is_dir($temp_dir)) {
                return true;
            }
            else {
                return false;
            }
        }
    }
}





?>