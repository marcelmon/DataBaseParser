<?php



if (!defined('PHPLISTINIT')) die(); ## avoid pages being loaded directly
if ($GLOBALS["commandline"]) {
 echo 'not to oppened by command line';
 die();
}

$javascript_src = dirname(__FILE__) . '/Attribute_Changer_PLugin/Script_For_Attribute_Changer.js';

$page_print =  '
<div>Attribute Changer</div>
<div id="error_printing"></div>
<form action="" method="post" enctype="multipart/form-data" id="file_upload_form">
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
</form>'

;
if(!isset($_POST)) {
	
    print('<html><head><script src="'.$javascript_src.'"></script></head><body>'.$page_print.'</body></html>');
}



/////////////////////////////////////////////PUT SOMEWHEREE

//used once the file has been loaded, can put into another file instead of php_self
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

else if(isset($_POST['attribute_changer_file_to_upload'])) {
        //possible check if dir exists
    if(!Test_Create_Temp_Dir()) {
        print("<html><body>Error with temp directory</body></html>");
        return;
    }
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
            $new_html = $new_html."<div>The file ". basename( $_FILES["attribute_changer_file_to_upload"]["name"]). " has been uploaded.</div>";

            $cols_match = Get_Attribute_File_Column_Match($target_file);
            if($cols_match == '') {
                $new_html = $new_html.'<div>There was an error with the column select table forming.</div>'.$page_print;

            }
            else{
                $new_html= $new_html.$cols_match;
                $FILE_LOCATION = $target_file;
            }
        } 
        else {
            $new_html = $new_html."<div>Sorry, there was an error uploading your file.</div>".$page_print;
        }
    }

    $new_html = $new_html.'</body></html>';
    print($new_html);

}

if(isset($_POST['submit']['File_Column_Match_Submit'])) {

    if(!isset($_POST['attribute_to_match'])) {
        //shouldnt happen .... else user needs to be WARNEDDDDD
    }
    if(!isset($attribute_to_match['email'])) {
        print("no email col selected");
    }
    else{
        asort($_POST['attribute_to_match'], SORT_NUMERIC);
        //so that the columns are matched, easier to read the file from comma to comma
        $fp = fopen($FILE_LOCATION, 'r');
        $current_char;
        while(($current_char = fread($fp, 1)) !== '\n' && !feof($fp)) {
            //skip the first bit of columns   
        }
        if(feof($fp)) {
            //....
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
                        Updated_Test_Entry($new_attribute_value_array);
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
                    Updated_Test_Entry($new_attribute_value_array);
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

$FILE_LOCATION;

function Get_Attribute_File_Column_Match($new_file_loc) {
    if(!file_exists($new_file_loc)) {
        return '';
    }
    $column_match_return_string = '';
    $fp = fopen($new_file_loc, 'r');

    $columns = array();
    $current_word = '';
    $current_char ='';

    $first_block = '';

    while(!feof($fp)) {
        $first_block = $first_block.fread($fp, 1024);
        if(substr_count($first_block, '\n') >= 10) {
            break;
        }
    }
    fclose($fp);
    $first_few_rows = explode('\n', $first_block);

    $columns = explode(',', $first_few_rows[0]);

    $current_row;
    $number_of_rows = 10;
    if(count($first_few_rows) < 10) {
        $number_of_rows = count($first_few_rows);
    }

    $attribute_names = array();
    $attribute_name_query = sprintf('select name from %s', $GLOBALS['tables']['attribute']);

    $req = Sql_Query($attribute_name_query);

    if(!$req) {
        return '';
    }
    while(($return_attribute = Sql_Fetch_Row($req))) {
        array_push($attribute_names, $return_attribute[0]);
    }

    if(count($attribute_names) == 0){
        return ''; //because lol
    }


    $column_match_return_string = '
    <form action="" method="post" id="file_column_select_form">
    <table id="column_match_table><tr>';
    //create radios for each
    foreach ($columns as $column_key => $column_value) {
        $cell_string = sprintf('<td> Set : %s  to : <br>', $column_value);

        foreach ($attribute_names as $newkey => $attribute_name) {
            $cell_string = $cell_string.sprintf('<input type="radio" name="attribute_to_match[%s]" value="%d" class="%s"><br>', $attribute_name, $column_key, $column_value);
        }
        $cell_string = $cell_string.sprintf('<input type="radio" name="attribute_to_match[%s]" value="%d" class="%s"><br>', 'email', $column_key, "email_class");

        $cell_string = $cell_string.sprintf('<input type="button" id="clear_%s" value="Clear Column" onClick="Clear_Column_Select(\'%s\')"', $column_value, $column_value);
        $column_match_return_string = $cell_string.'</td>';
    }
    $column_match_return_string = $column_match_return_string.'</tr>';

    $value_row = '';
    for(i=1; i < $number_of_rows; i++) {
        $value_row = '<tr>';
        foreach (explode(',', $first_few_rows[$i] as $key => $table_value) {
            $value_row=$value_row.sprintf('<td>%s</td>', $table_value);
        }
        $column_match_return_string = $column_match_return_string.$value_row.'</tr>';
    }

    $column_match_return_string = $column_match_return_string.'</table><input type="submit" name="File_Column_Match_Submit" value="submit"> </form>';

    return $column_match_return_string;
}



?>


<?php
 
 
    $attribute_list;
 
 
    $New_Entry_List;
    //need to indicate which are modifying
    $Modify_Entry_List;
    //THIS HOLDS EMAILS WITH DUPLICATES
    $Duplicate_Attribute_Values_list;
    //THIS HOLDS EMAILS WITH SPECIFIED DUPLICATE ATTRIBUTES
    $Duplicate_Attributes;
 
    $Current_user_values;
 
    $Commited_New_Entires;
    $Commited_Modify_Entries;
    //either 10, 100, 1000, 10000, all
    //default 100
    $Current_New_Entries_Display_Amount;
    $New_Enties_Total_Amount;
    $New_Entires_Number_Of_Blocks;
    $Current_New_Entry_Block_Number;
    $Current_New_Entry_Block;
 
 
    $Current_Modify_Entries_Display_Amount;
    $Current_user_values;
    $Modify_Enties_Total_Amount;
    $Modify_Entires_Number_Of_Blocks;
    $Current_Modify_Entry_Block_Number;
    $Current_Modify_Entry_Block;
    $Commited_Modify_Entries;
     

    if(isset($_POST['New_Entry_Form_Submitted'])) {

        if(!isset($_POST['New_Entry_Attribute_Column_Select'])) {
//here is where only checking email
        }

        else if(isset($_POST['New_Entry_Attribute_Column_Select'])) {
     
            $Columns_To_Accept = array();
     
            while($_POST['New_Entry_Attribute_Column_Select']) {
                $Attribute_To_Parse = array_shift($_POST['New_Entry_Attribute_Column_Select']);
     
                if(array_key_exists($Attribute_To_Parse, $attribute_list)) {
                    array_push($Columns_To_Accept, $Attribute_To_Parse);
                }
            }

            if(count($Columns_To_Accept) == 0) {
                //email is not an attribute, might still have emails
            }
            if(!isset($_POST['Hidden_New_Entry_List'])) {
                //error
                print("<html><body>THERE WAS AN ERROR WITH THE HIDDEN INPUT</body></html>");

            }
            else foreach ($_POST['Hidden_New_Entry_List'] as $hidden_email_key => $include_value) {
                if(!isset($_POST['New_Entry_List'][$hidden_email_key]['include'])) {
                    unset($Commited_New_Entires[$hidden_email_key]);
                }
                else if(count($Columns_To_Accept) == 0) {
                    $Commited_New_Entires[$hidden_email_key] = array();
                }
                else{
                    $attribute_values = $_POST['New_Entry_List'][$hidden_email_key]; 
                    if(isset($attribute_values['include']) {
                        if($attribute_values['include'] == 'include') {
     
                            unset($attribute_values['include']);
                            $new_entry_to_commit = array();
     
                            foreach ($attribute_values as $attribute_name => $value) {
                                if(in_array($attribute_name, $Columns_To_Accept)) {
                                    if(is_array($value)) {
                                        if($attribute_list[$attribute_name]['type'] == 'checkboxgroup') {
                                            foreach ($value as $key => $current_value) {
                                                //consider doing a check here if value is already set (maybe redundant but, you know)
                                                if(in_array($current_value, $attribute_list[$attribute_name]['allowed_values'])) {
                                                    if(!is_array(($new_entry_to_commit[$attribute_name]))) {
                                                        $new_entry_to_commit[$attribute_name] = array();
                                                    }
                                                    array_push($new_entry_to_commit[$attribute_name], $current_value); 
                                                }
                                            }
                                        }
                                        else{
                                            //only the checkbox group can be an array
                                        }
                                    }
                                    else{
                                        if($attribute_list[$attribute_name]['type'] == 'checkboxgroup') {
                                            if(in_array($value, $attribute_list[$attribute_name]['allowed_values'])) {

                                                if(!is_array(($new_entry_to_commit[$attribute_name]))) {
                                                     $new_entry_to_commit[$attribute_name] = array();
                                                }
                                                array_push($new_entry_to_commit[$attribute_name], $value); 
                                            }
                                        }
                                        else if($attribute_list[$attribute_name]['type'] == "checkbox") {
                                            if(in_array($value, $attribute_list[$attribute_name]['allowed_values'])) {
                                                
                                                $new_entry_to_commit[$attribute_name]=$value; 
                                            }
                                        }
                                        else if($attribute_list[$attribute_name]['type'] == "textarea"|"textline"|"hidden"|"date") {
                                            $new_entry_to_commit[$attribute_name]=$value; 
                                        }
                                        else{
     
                                        }
                                    }
                                     
                                }
                                else{
                                    //is not a currently acccepted column
                                }
                            }
                            $Commited_New_Entires[$hidden_email_key] = $new_entry_to_commit;
                        }
                    }
                    else{
                        //skip this email , not included
                    }
                }
            }
        }
    }

    if(isset($_POST['Modify_Entry_Form_Submitted'])) {

        if(isset($_POST['Modify_Entry_Attribute_Column_Select'])) {
     
            $Columns_To_Accept = array();
     
            while($_POST['Modify_Entry_Attribute_Column_Select']) {
                $Attribute_To_Parse = array_shift($_POST['Modify_Entry_Attribute_Column_Select']);
     
                if(array_key_exists($Attribute_To_Parse, $attribute_list)) {
                    array_push($Columns_To_Accept, $Attribute_To_Parse);
                }
            }
            if(count($Columns_To_Accept) == 0) {
                //change nothing or set to current set 

            }
            if(!isset($_POST['Hidden_Modify_Entry_List'])) {
                //error
                print("<html><body>THERE WAS AN ERROR WITH THE HIDDEN INPUT</body></html>");
            }
            else foreach ($_POST['Hidden_Modify_Entry_List'] as $hidden_email_key => $include_value) {
                if(!isset($_POST['Modify_Entry_List'][$hidden_email_key]['include'])) {
                    unset($Commited_Modify_Entires[$hidden_email_key]);
                }
                if(count($Columns_To_Accept) == 0) {
                    $Commited_Modify_Entires[$hidden_email_key] = array();
                }
                else{
                    $attribute_values = $_POST['Modify_Entry_List'][$hidden_email_key]; 
                    if(isset($attribute_values['include']) {
                        if($attribute_values['include'] == 'include') {
     
                            unset($attribute_values['include']);
                            $Modify_entry_to_commit = array();
     
                            foreach ($attribute_values as $attribute_name => $value) {
                                if(in_array($attribute_name, $Columns_To_Accept)) {
                                    if(is_array($value)) {
                                        if($attribute_list[$attribute_name]['type'] == 'checkboxgroup') {
                                            foreach ($value as $key => $current_value) {
                                                if(in_array($current_value, $attribute_list[$attribute_name]['allowed_values'])) {
                                                    if(!is_array(($Modify_entry_to_commit[$attribute_name]))) {
                                                        $Modify_entry_to_commit[$attribute_name] = array();
                                                    }
                                                    array_push($Modify_entry_to_commit[$attribute_name], $current_value); 
                                                }
                                            }
                                        }
                                        else{
                                            //only the checkbox group can be an array
                                        }
                                    }
                                    else{
                                        if($attribute_list[$attribute_name]['type'] == 'checkboxgroup') {
                                            if(in_array($current_value, $attribute_list[$attribute_name]['allowed_values'])) {
                                                if(!is_array(($Modify_entry_to_commit[$attribute_name]))) {
                                                    $Modify_entry_to_commit[$attribute_name] = array();
                                                }
                                                array_push($Modify_entry_to_commit[$attribute_name], $value); 
                                            } 
                                        }
                                        else if($attribute_list[$attribute_name]['type'] == "checkbox") {
                                            if(in_array($value, $attribute_list[$attribute_name]['allowed_values'])) {
                                                $Modify_entry_to_commit[$attribute_name]=$value; 
                                            }
                                        }
                                        else if($attribute_list[$attribute_name]['type'] == "textarea"|"textline"|"hidden"|"date") {
                                            $Modify_entry_to_commit[$attribute_name]=$value; 
                                        }
                                        else{
     
                                        }
                                    }
                                     
                                }
                                else{
                                    //is not a currently acccepted column
                                }
                            }
                            $Commited_Modify_Entires[$hidden_email_key] = $Modify_entry_to_commit;
                        }
                    }
                    else{
                        //skip this email , not included
                    }
                }
            }
        }
    }

    if(isset($_POST['New_Entries_Table_Submit_All']) && $_POST['New_Entries_Table_Submit_All'] === 'New_Entries_Table_Submit_All' ) {
        if(Initialize_Modify_Entries_Display() == null) {
            print(Process_All_New_And_Modify());
        }
        else{
            $HTML_TO_DISPLAY = Get_Modify_Entry_Table_Block();
            print('<html><body><script src="'.$javascript_src.'"></script>'.$HTML_TO_DISPLAY.'</body></html>');
        }

    }

    if(isset($_Post['New_Entries_Table_Next_Page']) && $_Post['New_Entries_Table_Next_Page'] === 'New_Entries_Table_Next_Page') {
        $HTML_TO_DISPLAY = New_Entry_Display_Next_Page();
        if($HTML_TO_DISPLAY == false) {
            $HTML_TO_DISPLAY = Get_New_Entry_Table_Block();
        }
        print('<html><body><script src="'.$javascript_src.'""></script>'.$HTML_TO_DISPLAY.'</body></html>');
    }

    if(isset($_Post['New_Entries_Table_Previous_Page']) && $_Post['New_Entries_Table_Previous_Page'] === 'New_Entries_Table_Previous_Page') {
        $HTML_TO_DISPLAY = New_Entry_Display_Previous_Page();
        if($HTML_TO_DISPLAY == false) {
            $HTML_TO_DISPLAY = Get_New_Entry_Table_Block();
        }
        print('<html><body><script src="'.$javascript_src.'""></script>'.$HTML_TO_DISPLAY.'</body></html>');
    }
 
    if(isset($_Post['New_Entry_Change_Display_Amount']) && $_Post['New_Entry_Change_Display_Amount'] === 'New_Entry_Change_Display_Amount') {

        if(isset($_POST['New_Entries_New_Display_Amount'])) {
            if($_POST['New_Entries_New_Display_Amount'] != (10|100|1000|10000|"all")){

            }
            else{
                if(New_Entry_Change_Display_Amount($_POST['New_Entries_New_Display_Amount']) != true) {

                }
                else{
                    

                }
            }
        }
        $HTML_TO_DISPLAY = Get_New_Entry_Table_Block();
        print('<html><body><script src="'.$javascript_src.'""></script>'.$HTML_TO_DISPLAY.'</body></html>');
        
    }



    if(isset($_POST['Modify_Entries_Table_Submit_All']) && $_POST['Modify_Entries_Table_Submit_All'] == 'Modify_Entries_Table_Submit_All' ) {

        print(Process_All_New_And_Modify());
    }

    if(isset($_Post['Modify_Entries_Table_Next_Page']) && $_Post['Modify_Entries_Table_Next_Page'] == 'Modify_Entries_Table_Next_Page') {
        $HTML_TO_DISPLAY = Modify_Entry_Display_Next_Page();
        if($HTML_TO_DISPLAY == false) {
            $HTML_TO_DISPLAY = Get_Modify_Entry_Table_Block();
        }
           
        print('<html><body><script src="'.$javascript_src.'""></script>'.$HTML_TO_DISPLAY.'</body></html>');
    }

    if(isset($_Post['Modify_Entries_Table_Previous_Page']) && $_Post['Modify_Entries_Table_Previous_Page'] == 'Modify_Entries_Table_Previous_Page') {
        $HTML_TO_DISPLAY = Modify_Entry_Display_Previous_Page();
        if($HTML_TO_DISPLAY == false) {
            $HTML_TO_DISPLAY = Get_Modify_Entry_Table_Block();
        }
        print('<html><body><script src="'.$javascript_src.'""></script>'.$HTML_TO_DISPLAY.'</body></html>');
    }
 
    if(isset($_Post['Modify_Entry_Change_Display_Amount']) && $_Post['Modify_Entry_Change_Display_Amount'] == 'Modify_Entry_Change_Display_Amount') {

        if(isset($_POST['Modify_Entries_New_Display_Amount'])) {
            if($_POST['Modify_Entries_New_Display_Amount'] != (10|100|1000|10000|"all")){

            }
            else{
                if(Modify_Entry_Change_Display_Amount($_POST['Modify_Entries_New_Display_Amount']) != true) {

                }
                else{
                    

                }
            }
        }
        $HTML_TO_DISPLAY = Get_Modify_Entry_Table_Block();
        print('<html><body><script src="'.$javascript_src.'""></script>'.$HTML_TO_DISPLAY.'</body></html>');
        
    }

    function Process_All_New_And_Modify() {
        if(count($Commited_New_Entires) > 0) {
            Push_New_Entries();
        }
        if(count($Commited_Modify_Entries) > 0) {
            Push_Modify_Entries();
        }
        $return_html = '<html><body>Complete</body></html>';
    }
?>







<?php
    require_once dirname(__FILE__).'/commonlib/lib/userlib.php';
/*
    AREA OF PRACTICE IS AN ATTRIBUTE FIELD TO ADD
    //SET VALUES OF CHECKBOX ARE STORED COMMA SEPARATED
*/
    class AttributeChanger {
        global $tables, $DBstruct;
        $AttributeChangerPluggin;
        function turnOnAttributeChangerPluggin(){
            //NEED TO SQL QUERY THIS!!!!!!!!
            if(!isset($$GLOBALS['tables']['pluggins']['AttributeChanger'])){
                $GLOBALS['tables']['pluggins']['AttributeChanger'] = $AttributeChangerInfo = array('isOn' => true);
                $AttributeChangerPluggin = $GLOBALS['tables']['pluggins']['AttributeChanger'];
            }
            else{
                $GLOBALS['tables']['pluggins']['AttributeChanger']['isOn'] = true;
                $AttributeChangerPluggin = $GLOBALS['tables']['pluggins']['AttributeChanger'];
            }
            $tables=$GLOBALS['tables'];
        }
        function testAttributeChangerOn() {
            if(!isset($GLOBALS['tables']['pluggins']['AttributeChanger'])){
                return false;
            }
            if(!isset($GLOBALS['tables']['pluggins']['AttributeChanger']['isOn']) {
                return false;
            }
            if($GLOBALS['tables']['pluggins']['AttributeChanger']['isOn'] == false) {
                return false;
            }
            else{
                if(!isset($AttributeChanger)){
                    $AttributeChanger = $GLOBALS['tables']['pluggins']['AttributeChanger'];
                }
                $tables=$GLOBALS['tables'];
                return true;
            }
        }
        //the attribute changer is to be given a data set containing at least an email
            //it will query the existing list for email existence
            //else it is a new entry, can insert into a temp table or keep in program memory
        $Current_Users_Values;
        //[email] => array[attribute1,value]
        $New_Entry_List;
        //need to indicate which are modifying
        $Modify_Entry_List;
        //THIS HOLDS EMAILS WITH DUPLICATES
        $Duplicate_Attribute_Values_list;
        //THIS HOLDS EMAILS WITH SPECIFIED DUPLICATE ATTRIBUTES
        $Duplicate_Attributes;
        $attribute_list;

        $attribute_value_ids;

        function Initialize_Attribute_Changer() {
            //get all attributes and their info
            $query = sprintf('select * from %s', $GLOBALS['tables']['attribute']);
            $attribute_data_return = Sql_Query($query); 
            if($attribute_data_return) {
                $attribute_list = array();
                $attribute_value_ids = array();
                while($attribute_data = Sql_fetch_array($attribute_data_return)) {
                    if(!isset( ($attribute_data['id']) | ($attribute_data['name']) | ($attribute_data['type']) )) {
                        //not known format, cannot use
                    }
                    else{
                        //use the attribute list to get type and value information
                        $attribute_list[$attribute_data['name']] = $attribute_data;

                        if($attribute_data['type'] == ("radio"|"checkboxgroup"|"select"|"checkbox")) {

                            if(!isset($attribute_data['tablename'])) {
                            }
                            else {
                                if(!isset($attribute_value_ids[$attribute_data['name']])) {
                                    $attribute_value_ids[$attribute_data['name'] = array();
                                }
                                
                                //must query to get the allowed values
                                $value_table_name = $table_prefix."listattr_".$attribute_data["tablename"];
                                $value_query = sprintf("select name from %s", $value_table_name);
                                $allowed_values_res = Sql_Query($value_query);
                                if($allowed_value_res) {
                                    while(($row = Sql_Fetch_Row_Query($allowed_values_res))) {
                                        array_push($attribute_list[$attribute_data['name']]['allowed_values'], $row[0]);

                                        $value_id_query = sprintf("select id from %s where name = %s", $attribute_data["tablename"], $row[0]);
                                        $value_id = Sql_Fetch_Row_Query($value_id_query);
                                        if($value_id[0]) {
                                            $attribute_value_ids[$row[0]] = $value_id[0];
                                        }
                                    }
                                }
                                else{
                                    unset($attribute_list[$attribute_data['name']]['allowed_values']);
                                }
                                
                            }
                        }
                        else{
                            //is other input type
                        }
                        $Duplicate_Attributes[$attribute_data['name']]= array();
                    }
                }
            }
            else{
                //no rows :S
            }
            $New_Entry_List = array();
            $Modify_Entry_List = array();
            $Duplicate_Attribute_Values_list = array();
        }
         
        function Updated_Test_Entry($entry) {
            //entry is [email]=>array (attribute, value)
            $changing_attributes = array();
            if(!array_key_exists("email", $entry)) {
                return false;
            }
            $email = $entry['email'];
            unset($entry['email']);

            if(!filter_var($email, FILTER_VALIDATE_EMAIL) ){
                return false;
            }
            $entry_query = sprintf('select * from %s where email = "%s"', $GLOBALS['tables']['user'], $email);
            $user_sql_result = Sql_Query($entry_query);
            //0 if there are no attributes, is only existence
            if(count($entry) == 0) {
                //if there is a user then already done
                if($user_sql_result){
                    return true;
                }
                else{
                    //will need to create a new user if not already
                    if(isset($New_Entry_List[$email])) {
                        return true;
                    }
                    else{
                        $New_Entry_List[$email] = array();
                        return true;
                    }
                }
            }
            if($user_sql_result && !isset($Current_Users_Values[$email])) {
                Get_Current_User_Attribute_Values($Current_Users_Values, $email, $attribute_list);
            }
             
            //if there are attributes, must check each value to look for update
            foreach ($entry as $attribute => $new_attribute_value) {
                //these are single choice values
                if($attribute_list[$attribute]['type'] === "radio"|"select"|'checkbox') {
                    //must check if the possible new value is an allowed value
                    if(in_array($new_attribute_value, $attribute_list[$attribute]['allowed_values'])) {
                        //this is if the returned user has an id, will always have an id if exists in the database
                        if(isset($Current_Users_Values[$email])) {
                            //the return query for the user,attrubute does not match the new possible attribute value
                            if(isset($Current_Users_Values[$email][$attribute]) && $Current_Users_Values[$email][$attribute] === $new_attribute_value) {    
                                 
                            }
                            else{
                                Add_Single_Entry_To_Modify_Or_New_Entry_List($email, $new_attribute_value, $attribute, $Modify_Entry_List, $Duplicate_Attribute_Values_list, $Duplicate_Attributes);
                            }
                        }
                        else{
                            //no user info, add info to list
                            Add_Single_Entry_To_Modify_Or_New_Entry_List($email, $new_attribute_value, $attribute, $New_Entry_List, $Duplicate_Attribute_Values_list, $Duplicate_Attributes);
                        }
                    }
                    else{
                        //not an allowed value so skip
                    }
                }
                //these are multiple choice types, the new attribute value must match
                else if($attribute_list[$attribute]['type'] == 'checkboxgroup') {
 
                    $exploded_attribute_values_array = explode(',', $new_attribute_value);
 
                    foreach ($exploded_attribute_values_array as $key => $exploded_attribute_value) {
 
                        if(in_array($exploded_attribute_value, $attribute_list[$attribute]['allowed_values'])) {
 
                            if(isset($Current_Users_Values[$email])) {
                                if(isset($Current_Users_Values[$email][$attribute]) && in_array($exploded_attribute_value, $Current_Users_Values[$email][$attribute]) {
 
                                }
                                else{
                                    Add_Multi_Entry_To_Modify_Or_New_Entry_List($email, $exploded_attribute_value, $attribute, $Modify_Entry_List);
                                }
                            }
                            else{
                                //no current attributes, can definately add to list, user exists
                                Add_Multi_Entry_To_Modify_Or_New_Entry_List($email, $exploded_attribute_value, $attribute, $New_Entry_List);                             
                            }
                        }
                    }
 
                }
                 
                else if ($attribute_list[$attribute]['type'] == "date") {
                    $exploded_date =explode('/', $new_attribute_value);
                    if(count($exploded_date) != 3) {
                        //cannot use
                    }
                    else{
                        $day = intval($exploded_date[0]);
                        $month = intval($exploded_date[1]);
                        $year = intval($exploded_date[2]);
                        if(!checkdate($month, $day, $year)) {

                        }

                        else{
                            if($day < 10){
                                $day_string = '0'.strval($day);
                            }
                            else{
                                $day_string = strval($day);
                            }
                            if($month < 10){
                                $month_string = '0'.strval($month);
                            }
                            else{
                                $month_string = strval($month);
                            }
                            $year_string = strval($year);
                            $new_date_value = $day_string.'/'.$month_string.'/'.$year_string;
                        
 
                            if(isset($Current_Users_Values[$email][$attribute])) {
                                if(isset($Current_Users_Values[$email][$attribute]) && $Current_Users_Values[$email][$attribute] != $new_date_value) {
                                    Add_Single_Entry_To_Modify_Or_New_Entry_List($email, $new_date_value, $attribute, $Modify_Entry_List, $Duplicate_Attribute_Values_list, $Duplicate_Attributes);
                                }
                            }
                            else{
                                Add_Single_Entry_To_Modify_Or_New_Entry_List($email, $new_date_value, $attribute, $New_Entry_List, $Duplicate_Attribute_Values_list, $Duplicate_Attributes);
                            }
                        }
                    }
                }
                else if ($attribute_list[$attribute]['type'] == "textarea"|"textline") {
                    //this is if the returned user has an id, will always have an id if exists in the database
                    if(isset($Current_Users_Values[$email])) {
 
                        if(isset($Current_Users_Values[$email][$attribute]) && $new_attribute_value === $Current_Users_Values[$email][$attribute]) {
 
                        }
                        else{
                            Add_Single_Entry_To_Modify_Or_New_Entry_List($email, $new_attribute_value, $attribute, $Modify_Entry_List, $Duplicate_Attribute_Values_list, $Duplicate_Attributes);
                        }
                    }
                    else{
                        Add_Single_Entry_To_Modify_Or_New_Entry_List($email, $new_attribute_value, $New_Entry_List, $Duplicate_Attribute_Values_list, $Duplicate_Attributes);
                    }
                }
            }
        }
 
 
        function Add_Single_Entry_To_Modify_Or_New_Entry_List($email, $new_attribute_value, $attribute, &$Modify_list, &$Duplicate_Email_List, &$Duplicate_Attributes_List) {
            if(!isset($Modify_list[$email])) {
                $Modify_list[$email] = array();
            }
            if(!isset($Modify_list[$email][$attribute])){
                $Modify_list[$email][$attribute] = array($new_attribute_value);
                return;
            }
            if(in_array($new_attribute_value, $Modify_list[$email][$attribute])) {
                return;
            }
            else{
                array_push($Modify_list[$email][$attribute], $new_attribute_value);
                if(!isset($Duplicate_Email_List[$email])){
                    $Duplicate_Email_List[$email] = true;
                }
                //indicate there are multiple entries for this email,attribute pair
                if(!isset($Duplicate_Attributes_List[$attribute][$email])) {
                    $Duplicate_Attributes_List[$attribute][$email] = true;
                }
            }
        }
        function Add_Multi_Entry_To_Modify_Or_New_Entry_List($email, $new_attribute_value, $attribute, &$Modify_list) {
            if(!isset($Modify_list[$email])) {
                $Modify_list[$email] = array();
            }
            if(!isset($Modify_list[$email][$attribute])){
                $Modify_list[$email][$attribute] = array($new_attribute_value);
                return;
            }
            if(in_array($new_attribute_value, $Modify_list[$email][$attribute])) {
                return;
            }
            else{
                array_push($Modify_list[$email][$attribute], $new_attribute_value);
            }
        }


        $Commited_New_Entires = array();
        $Commited_Modify_Entries = array();
        //either 10, 100, 1000, 10000, all
        //default 100
        $Current_New_Entries_Display_Amount;
        $New_Entries_Total_Amount;
        $New_Entires_Number_Of_Blocks;
        $Current_New_Entry_Block_Number;
        $Current_New_Entry_Block;
 
        function Initialize_New_Entries_Display() {
            if(count($New_Entry_List) == 0) {
                return null;
            }
            $Current_New_Entries_Display_Amount = 100;
            $New_Entries_Total_Amount = count($New_Entry_List);
            $New_Entires_Number_Of_Blocks = $New_Entries_Total_Amount/$Current_New_Entries_Display_Amount + (($New_Entries_Total_Amount % $Current_New_Entries_Display_Amount)? 1:0);
         
            $Current_New_Entry_Block_Number = 0;
            return true;
             
        }
        function New_Entry_Change_Display_Amount($New_Amount) {
            if($New_Amount !== (10|100|1000|10000|'all')) {
                return false;
            }
            if($New_Amount === 'all') {
                $New_Entires_Number_Of_Blocks =1;
                $Current_New_Entries_Display_Amount = $New_Entries_Total_Amount;
                $Current_New_Entry_Block_Number = 0;
                return true;
            }
            $Current_New_Entries_Display_Amount = $New_Amount;
            $New_Entires_Number_Of_Blocks = $New_Entries_Total_Amount/$Current_New_Entries_Display_Amount + (($New_Entries_Total_Amount % $Current_New_Entries_Display_Amount)? 1:0);
            $Current_New_Entry_Block_Number = 0;
            return true;
        }
        function New_Entry_Display_Next_Page() {
            if($Current_New_Entry_Block_Number < $New_Entires_Number_Of_Blocks-1) {
                $Current_New_Entry_Block_Number++;
                return Get_Modify_Entry_Table_Block();
            }
            else{
                //because there are no more blocks
                return false;
            }
        }
        function New_Entry_Display_Previous_Page() {
            if($Current_New_Entry_Block_Number > 0) {
                $Current_New_Entry_Block_Number--;
                return Get_Modify_Entry_Table_Block();
            }
            else{
                //because there are no more blocks
                return false;
            }
        }


        function Get_New_Entry_Table_Block() {
            
            $Current_New_Entry_Block = array_slice($New_Entry_List, $Current_New_Entry_Block_Number*$Current_New_Entries_Display_Amount, $Current_New_Entries_Display_Amount);

            $HTML_Display_Text = sprintf('<form name="New_Entry_Submit_Form_Block__%d" action="%s" method="post"><input type="hidden" name="New_Entry_Form_Submitted" value="submitted">', $Current_New_Entry_Block_Number, 'self');
            $HTML_Display_Text = $HTML_Display_Text.sprintf('<table id="New_User_Attribute_Select_Table_Block__%d">', $Current_New_Entry_Block_Number);
            $HTML_table_row = sprintf('<tr><td>EMAIL<br><input type="button" id="New_Entry_Include_All_Emails" name="New_Entry_Include_All_Emails" value="Include All Emails" onClick="checkAll_NewEntry_Emails()"></input>');
            $HTML_table_row = $HTML_table_row.sprintf('<input type="button" id="New_Entry_Remove_All_Emails" name="New_Entry_Include_Remove_Emails" value="Remove All Emails" onClick="removeAll_NewEntry_Emails()"></input></td>');

            foreach ($attribute_list as $attribute_name => $attribute_info) {
                $HTML_table_row = $HTML_table_row.sprintf('<td>Attribute: %s<br><input type="checkbox" name="New_Entry_Attribute_Column_Select[%s]" value="checked">Include This Attribute</input>',$attribute_name, $attribute_name);
                if($attribute_info['type'] === 'checkboxgroup') {
                    $HTML_table_row = $HTML_table_row.sprintf('<br><input type="button" name="New_Entry_Include_All_Checkboxgroup_%s" id="New_Entry_Include_All_Checkboxgroup_%s" value="Include All Checkboxgroup Values" onClick="checkAll_NewEntry_CheckboxGroup(\'%s\')"></input>', $attribute_name, $attribute_name, $attribute_name);
                    $HTML_table_row = $HTML_table_row.sprintf('<br><input type="button" name="New_Entry_Remove_All_Checkboxgroup_%s" id=="New_Entry_Remove_All_Checkboxgroup_%s" value="Remove All Checkboxgroup Values" onClick="removeAll_NewEntry_CheckboxGroup(\'%s\')"></input>', $attribute_name, $attribute_name, $attribute_name);
                }
                else{
                    $HTML_table_row = $HTML_table_row.sprintf('<br><input type="button" name="New_Entry_Include_All_Safe_Values_%s" value="Include All Safe Values" onClick="checkAll_NewEntry_SafeValues(\'%s\')"></input></td>', $attribute_name, $attribute_name);
                    $HTML_table_row = $HTML_table_row.sprintf('<br><input type="button" name="New_Entry_Include_All_Safe_Values_Or_Checked_%s" value="Include All Safe Values Or Checked" onClick="checkAll_NewEntry_SafeValues_OrChecked(\'%s\')"></input></td>', $attribute_name, $attribute_name);
                    $HTML_table_row = $HTML_table_row.sprintf('<br><input type="button" name="New_Entry_Remove_All_Safe_Values_%s" value="Remove All Safe Values" onClick="removeAll_NewEntry_SafeValues(\'%s\')"></input></td>', $attribute_name, $attribute_name);
                    $HTML_table_row = $HTML_table_row.sprintf('<br><input type="button" name="New_Entry_Remove_All_Safe_Values_Or_Checked_%s" value="Remove All Safe Values Or Checked" onClick="removeAll_NewEntry_SafeValues_OrChecked(\'%s\')"></input></td>', $attribute_name, $attribute_name);
                }
            }
            $HTML_Display_Text = $HTML_Display_Text.$HTML_table_row.'</tr>';
            foreach ($Current_New_Entry_Block as $email_key => $new_user_attributes_and_values) {
                if(isset($Commited_New_Entires[$email_key]) {
                    $HTML_table_row = sprintf('<tr><td>%s<br><input type="checkbox" class="New_Entry_Email" name="New_Entry_List[%s][\'include\']" value="include" checked>Include This Email</input><input type="hidden" name="Hidden_New_Entry_List[%s]" value="submitted"></td>',$email_key, $email_key, $email_key);
                }
                else{
                    $HTML_table_row = sprintf('<tr><td>%s<br><input type="checkbox" class="New_Entry_Email" name="New_Entry_List[%s][\'include\']" value="include">Include This Email</input><input type="hidden" name="Hidden_New_Entry_List[%s]" value="submitted"></td>',$email_key, $email_key, $email_key);
                }
                //commited_new_entries[email]: attribute,value
                foreach ($attribute_list as $attribute_name => $attribute_info) {
                    if(!isset($new_user_attributes_and_values[$attribute_name])) {
                        $HTML_table_row = $HTML_table_row.'<td></td>';
                    }
                    else {
                        $HTML_table_row= $HTML_table_row.'<td>';

                        foreach ($new_user_attributes_and_values[$attribute_name] as $key => $attribute_value) {

                            switch($attribute_info['type']){

                                case "textarea"|"textline"|"checkbox"|"hidden"|"date": 
                                    if(isset($Commited_New_Entires[$email_key] && isset($Commited_New_Entires[$email_key][$attribute_name]) && $Commited_New_Entires[$email_key][$attribute_name] === $attribute_value)) {
                                        //if the attribute value is the already selected, mark as checked
                                        if($key == 0) {
                                            $HTML_attribute_value_input = sprintf('<input type="radio" class="New_Entry_Safe_Value_Attribute_%s" name="New_Entry_List[%s][%s]" value="%s" checked>%s</input>', $attribute_name, $email_key, $attribute_name, $attribute_value, $attribute_value);
                                        }
                                        else{
                                            $HTML_attribute_value_input = sprintf('<input type="radio" class="New_Entry_Attribute_%s" name="New_Entry_List[%s][%s]" value="%s" checked>%s</input>', $email_key, $attribute_name, $attribute_value, $attribute_value);
                                        }
                                         
                                    }
                                    else{
                                        //else not yet selected so just create the input
                                        if($key == 0) {
                                            $HTML_attribute_value_input = sprintf('<input type="radio" class="New_Entry_Safe_Value_Attribute_%s" name="New_Entry_List[%s][%s]" value="%s">%s</input>', $attribute_name, $email_key, $attribute_name, $attribute_value, $attribute_value);
                                        }
                                        else{
                                            $HTML_attribute_value_input = sprintf('<input type="radio" name="New_Entry_List[%s][%s]" value="%s">%s</input>', $email_key, $attribute_name, $attribute_value, $attribute_value);
                                        }
                                    }
                                    $HTML_table_row= $HTML_table_row.$HTML_attribute_value_input.'<br>';
                                    break;
                                 
                                case "checkboxgroup": 
                                    if(isset($Commited_New_Entires[$email_key]) && isset($Commited_New_Entires[$email_key][$attribute_name]) && in_array($attribute_value, $Commited_New_Entires[$email_key][$attribute_name])) {
                                        //the current attribute value should already be checked
                                        $HTML_attribute_value_input = sprintf('<input type="checkbox" class="New_Entry_Checkbox_Value_Attribute_%s" name="New_Entry_List[%s][%s][%s]" value="%s" checked>%s</input><br>', $attribute_name, $email_key, $attribute_name, $attribute_value, $attribute_value, $attribute_value);
                                    }
                                    else{
                                        //not already checked
                                        $HTML_attribute_value_input = sprintf('<input type="checkbox" class="New_Entry_Checkbox_Value_Attribute_%s" name="New_Entry_List[%s][%s][%s]" value="%s">%s</input><br>', $attribute_name, $email_key, $attribute_name, $attribute_value, $attribute_value, $attribute_value);
                                    }
                                    $HTML_table_row= $HTML_table_row.$HTML_attribute_value_input.'<br>';
                                    break;
                                default:
                                    break;
                            }
                        }
                        //have cycled through each of possible new values for the attribute
                        $HTML_table_row= $HTML_table_row.'</td>';
                    }

                }
                $HTML_Display_Text = $HTML_Display_Text.$HTML_table_row.'</tr>';
                 
            }
            $HTML_Display_Text = $HTML_Display_Text.'</table>';
            $HTML_submit_buttons = '<input type="submit" name="New_Entries_Table_Submit_All" value="New_Entries_Table_Submit_All"></input>';
            if($Current_New_Entry_Block_Number > 0) {
                $HTML_submit_buttons = $HTML_submit_buttons.'<input type="submit" name="value="New_Entries_Table_Previous_Page" value="New_Entries_Table_Previous_Page"></input>';
            }
            if($Current_New_Entry_Block_Number < $New_Entires_Number_Of_Blocks - 1) {
                $HTML_submit_buttons = $HTML_submit_buttons.'<input type="submit" name="New_Entries_Table_Next_page" value="New_Entries_Table_Next_page"></input>';
            }
            switch($Current_New_Entries_Display_Amount){
                case 10:
                    $HTML_Display_Size_Submit = '<select name="New_Entries_New_Display_Amount"><option value="10" checked>10</option><option value="100">100</option><option value="1000">1000</option><option value="10000">10000</option><option value="all">all</option>';
                case 100:
                    $HTML_Display_Size_Submit = '<select name="New_Entries_New_Display_Amount"><option value="10">10</option><option value="100" checked>100</option><option value="1000">1000</option><option value="10000">10000</option><option value="all">all</option>';
                case 1000:
                    $HTML_Display_Size_Submit = '<select name="New_Entries_New_Display_Amount"><option value="10">10</option><option value="100">100</option><option value="1000" checked>1000</option><option value="10000">10000</option><option value="all">all</option>';
                case 10000:
                    $HTML_Display_Size_Submit = '<select name="New_Entries_New_Display_Amount"><option value="10">10</option><option value="100">100</option><option value="1000">1000</option><option value="10000" checked>10000</option><option value="all">all</option>';
                case 'all':
                    $HTML_Display_Size_Submit = '<select name="New_Entries_New_Display_Amount"><option value="10">10</option><option value="100">100</option><option value="1000">1000</option><option value="10000">10000</option><option value="all" checked>all</option>';
            }
            $HTML_Display_Size_Submit = $HTML_Display_Size_Submit.'<input type="submit" name="New_Entry_Change_Display_Amount" value="New_Entry_Change_Display_Amount"></input>';
            $HTML_Display_Text = $HTML_Display_Text.$HTML_submit_buttons.$HTML_Display_Size_Submit.'</form>';
            $HTML_current_table_info = sprintf("<div>Current Block : %d of %d. Displaying %d entires per page.</div>", $Current_New_Entry_Block_Number+1, $New_Entires_Number_Of_Blocks, $Current_New_Entries_Display_Amount);
            $HTML_Display_Text = $HTML_Display_Text.$HTML_current_table_info;
            return $HTML_Display_Text;
        }

        $Current_Modify_Entries_Display_Amount;
        $Current_user_values;
        $Modify_Enties_Total_Amount;
        $Modify_Entires_Number_Of_Blocks;
        $Current_Modify_Entry_Block_Number;
        $Current_Modify_Entry_Block;
        $Commited_Modify_Entries;
 
        function Initialize_Modify_Entries_Display() {
            if(count($Modify_Entry_List == 0)) {
                return null;
            }
            $Current_Modify_Entries_Display_Amount = 100;
            $Modify_Enties_Total_Amount = count($Modify_Entry_List);
            $Modify_Entires_Number_Of_Blocks = $Modify_Enties_Total_Amount/$Current_Modify_Entries_Display_Amount + (($Current_Modify_Entries_Display_Amount % $Modify_Enties_Total_Amount)? 1:0);
         
            $Current_Modify_Entry_Block_Number = 0;
            return true;
             
        }   
        function Modify_Entry_Display_Next_Page() {
            if($Current_Modify_Entry_Block_Number < $Modify_Entires_Number_Of_Blocks-1) {
                $Current_Modify_Entry_Block_Number++;
                return Get_Modify_Entry_Table_Block($Current_Modify_Entry_Block_Number);
            }
            else{
                //because there are no more blocks
                return false;
            }
        }
        function Modify_Entry_Display_Previous_Page() {
            if($Current_Modify_Entry_Block_Number > 0) {
                $Current_Modify_Entry_Block_Number--;
                return Get_Modify_Entry_Table_Block($Current_Modify_Entry_Block_Number);
            }
            else{
                //because there are no more blocks
                return false;
            }
        }
        function Modify_Entry_Change_Display_Amount($New_Amount) {
            if($New_Amount !== (10|100|1000|10000|"all")) {
                return false;
            }
            if($New_Amount === 'all') {
                $New_Entires_Number_Of_Blocks =1;
                $Current_Modify_Entries_Display_Amount = $Modify_Enties_Total_Amount;
                $Current_Modify_Entry_Block_Number = 0;
                return true;
            }
            $Current_Modify_Entries_Display_Amount = $New_Amount;
            $Modify_Entires_Number_Of_Blocks = $Modify_Enties_Total_Amount/$Current_Modify_Entries_Display_Amount + (($Current_Modify_Entries_Display_Amount % $Modify_Enties_Total_Amount)? 1:0);
            $Current_New_Entry_Block_Number = 0;
            return true;
        }
        function Get_Modify_Entry_Table_Block() {
            $Current_Modify_Entry_Block = array_slice($Modify_Entry_List, $Current_Modify_Entry_Block_Number*$Current_Modify_Entries_Display_Amount, $Current_Modify_Entries_Display_Amount);
            $HTML_Display_Text = sprintf('<form name="Modify_Entry_Submit_Form_Block__%d" action="%s" method="post"><input type="hidden" name="Modify_Entry_Form_Submitted" value="submitted">', $Current_Modify_Entry_Block_Number, 'self');
            $HTML_Display_Text = $HTML_Display_Text.sprintf('<table id="Modify_User_Attribute_Select_Table_Block__%d">', $Current_Modify_Entry_Block_Number);

            $HTML_table_row = sprintf('<tr><td>EMAIL<br><input type="button" id="Modify_Entry_Include_All_Emails" name="Modify_Entry_Include_All_Emails" value="Include All Emails" onClick="checkAll_ModifyEntry_Emails()"></input>');
            $HTML_table_row = $HTML_table_row.sprintf('<input type="button" id="Modify_Entry_Remove_All_Emails" name="Modify_Entry_Remove_All_Emails" value="Remove All Emails" onClick="removeAll_ModifyEntry_Emails()"></input></td>');

            foreach ($attribute_list as $attribute_name => $attribute_info) {
                $HTML_table_row = $HTML_table_row.sprintf('<td>%s<input type="checkbox" name="Modify_Entry_Attribute_Column_Select[%s]" value="checked">',$attribute_name, $attribute_name);
                if($attribute_info['type'] === 'checkboxgroup') {
                    $HTML_table_row = $HTML_table_row.sprintf('<br><input type="button" name="Modify_Entry_Include_All_Checkboxgroup_%s" value="Include All Checkboxgroup Values" onClick="checkAll_ModifyEntry_CheckboxGroup(\'%s\')"></input>', $attribute_name, $attribute_name);
                    $HTML_table_row = $HTML_table_row.sprintf('<br><input type="button" name="Modify_Entry_Remove_All_Checkboxgroup_%s" value="Remove All Checkboxgroup Values" onClick="removeAll_ModifyEntry_CheckboxGroup(\'%s\')"></input></td>', $attribute_name, $attribute_name);
                }
                else{
                    $HTML_table_row = $HTML_table_row.sprintf('<br><input type="button" name="Modify_Entry_Include_All_Safe_Values_%s" value="Include All Safe Values" onClick="checkAll_ModifyEntry_SafeValues(\'%s\')></input>', $attribute_name, $attribute_name);
                    $HTML_table_row = $HTML_table_row.sprintf('<br><input type="button" name="Modify_Entry_Remove_All_Safe_Values_%s" value="Remove All Safe Values" onClick="removeAll_ModifyEntry_SafeValues(\'%s\')></input></td>', $attribute_name, $attribute_name);
                }
            }
            $HTML_Display_Text = $HTML_Display_Text.$HTML_table_row.'</tr>';

            foreach ($Current_Modify_Entry_Block as $email_key => $modify_user_attributes_and_values) {
                //THIS SHOULD NEVER HAPPEN!!!!!!!
                if(!isset($Current_user_values[$email_key])) {
                    Get_Current_User_Attribute_Values();
                }
                 
                if(isset($Commited_Modify_Entries[$email_key])) {
                    $HTML_table_row = sprintf('<tr><td>%s<br><input type="checkbox" class="Modify_Entry_Email" name="Modify_Entry_List[%s][\'include\']" value="include" checked>Include This Email</input><input type="hidden" name="Hidden_Modify_Entry_List[%s]" value="submitted"></td>',$email_key, $email_key, $email_key);
                }
                else{
                    $HTML_table_row = sprintf('<tr><td>%s<br><input type="checkbox" class="Modify_Entry_Email" name="Modify_Entry_List[%s][\'include\']" value="include">Include This Email</input><input type="hidden" name="Hidden_Modify_Entry_List[%s]" value="submitted"></td>',$email_key, $email_key, $email_key);
                }
 
                //first foreach is for current set vals
                foreach ($attribute_list as $attribute_name => $attribute_info) {
                    $HTML_table_row = $HTML_table_row.'<td>';
 
                    if(!isset($Current_user_values[$email_key]['attributes'][$attribute_name]) {
                        $HTML_table_row = $HTML_table_row.'</td>';
                    }
                    else {
 
                        if($attribute_list[$attribute_name]['type'] === 'checkboxgroup') {
 
                            foreach ($Current_user_values[$email_key]['attributes'][$attribute_name] as $key => $current_single_value) {
 
                                if(isset($Commited_Modify_Entries[$email_key]) && isset($Commited_Modify_Entries[$email_key][$attribute_name])) {
                                    if(in_array($current_single_value, $Commited_Modify_Entries[$email_key][$attribute_name])) {
                                        $HTML_attribute_value_input = sprintf('<input type="checkbox" class="Current_Modify_Checkbox_Value_%s" name="Modify_Entry_List[%s][%s][%s]" value="%s" checked>%s</input><br>', $attribute_name, $email_key, $attribute_name, $current_single_value, $current_single_value, $current_single_value);
                                    }
                                    else {
                                        $HTML_attribute_value_input = sprintf('<input type="checkbox" class="Current_Modify_Checkbox_Value_%s" name="Modify_Entry_List[%s][%s][%s]" value="%s">%s</input><br>', $attribute_name, $email_key, $attribute_name, $current_single_value, $current_single_value, $current_single_value);
                                    }
                                }
                                else{
                                    $HTML_attribute_value_input = sprintf('<input type="checkbox" class="Current_Modify_Checkbox_Value_%s" name="Modify_Entry_List[%s][%s][%s]" value="%s">%s</input><br>', $attribute_name, $email_key, $attribute_name, $current_single_value, $current_single_value, $current_single_value);
                                }
                                $HTML_table_row = $HTML_table_row.$HTML_attribute_value_input;
                            }
                        }
                        else {
                            if(isset($Commited_Modify_Entries[$email_key]) && isset($Commited_Modify_Entries[$email_key][$attribute_name])) {
                                if($Current_user_values[$email_key]['attributes'][$attribute_name] === $Commited_Modify_Entries[$email_key][$attribute_name]) {
                                    $HTML_attribute_value_input = sprintf('<input type="radio class="Current_Modify_Attribute_Value_%s" name="Modify_Entry_List[%s][%s]" value="%s" checked>%s</input><br>', $attribute_name, $email_key, $attribute_name, $Current_user_values[$email_key]['attributes'][$attribute_name], $Current_user_values[$email_key]['attributes'][$attribute_name]);                             }
                                else {
                                    $HTML_attribute_value_input = sprintf('<input type="checkbox" class="Current_Modify_Checkbox_Value_%s" name="Modify_Entry_List[%s][%s][%s]" value="%s">%s</input><br>', $attribute_name, $email_key, $attribute_name, $$Current_user_values[$email_key]['attributes'][$attribute_name], $$Current_user_values[$email_key]['attributes'][$attribute_name], $$Current_user_values[$email_key]['attributes'][$attribute_name]);
                                }
                            }
                            else{
                                $HTML_attribute_value_input = sprintf('<input type="checkbox" class="Current_Modify_Checkbox_Value_%s" name="Modify_Entry_List[%s][%s][%s]" value="%s">%s</input><br>', $attribute_name, $email_key, $attribute_name, $$Current_user_values[$email_key]['attributes'][$attribute_name], $$Current_user_values[$email_key]['attributes'][$attribute_name], $$Current_user_values[$email_key]['attributes'][$attribute_name]);
                            }
                            $HTML_table_row = $HTML_table_row.$HTML_attribute_value_input;
                        }
                        $HTML_table_row = $HTML_table_row.'</td>';
                    }
                     
                }
                $HTML_table_row = $HTML_table_row.'</tr>';
                $HTML_Display_Text = $HTML_Display_Text.$HTML_table_row;
 
                $HTML_table_row = '<tr><td></td>';
 
                foreach ($attribute_list as $attribute_name => $attribute_info) {
 
                    $HTML_table_row = $HTML_table_row.'<td>';
                     
                    if(isset($modify_user_attributes_and_values[$attribute_name])) {
 
 
                        if($attribute_info['type'] === 'checkboxgroup') {
 
                            $HTML_table_row = $HTML_table_row.Get_Modify_Attribute_Value_Display_Checkboxgroup($Commited_Modify_Entries, $email_key, $attribute_name, $modify_user_attributes_and_values[$attribute_name]);

                            $HTML_table_row = $HTML_table_row.'</td>';
                        }
                        else{

                            $HTML_table_row = $HTML_table_row.Get_Modify_Attribute_Value_Display_Other_Type($Current_Users_Values, $Commited_Modify_Entries, $email_key, $attribute_name, $modify_user_attributes_and_values[$attribute_name]);
                            
                            $HTML_table_row = $HTML_table_row.'</td>';
                        }
 
                    } 
                    else{
                        $HTML_table_row = $HTML_table_row.'</td>';
                    }  
                }
 
                $HTML_table_row = $HTML_table_row.'</tr>';
                $HTML_Display_Text = $HTML_Display_Text.$HTML_table_row;
 
            }
 
            $HTML_Display_Text = $HTML_Display_Text.'</table>';
            $HTML_submit_buttons = '<input type="submit" name ="Modify_Entries_Table_Submit_all" value="Submit_all">Submit_all</input>';
            if($Current_New_Entry_Block_Number > 0) {
                $HTML_submit_buttons = $HTML_submit_buttons.'<input type="submit" name ="Modify_Entries_Table_Previous_Page" value="Modify_Entries_Table_Previous_Page"></input>';
            }
            if($Current_New_Entry_Block_Number < $New_Entires_Number_Of_Blocks - 1) {
                $HTML_submit_buttons = $HTML_submit_buttons.'<input type="submit" name ="Modify_Entries_Table_Next_Page" value="Modify_Entries_Table_Next_Page"></input>';
            }
            switch($Current_Modify_Entries_Display_Amount){
                case 10:
                    $HTML_Display_Size_Submit = '<select name="Modify_Entries_New_Display_Amount"><option value="10" checked>10</option><option value="100">100</option><option value="1000">1000</option><option value="10000">10000</option><option value="all">all</option>';
                case 100:
                    $HTML_Display_Size_Submit = '<select name="Modify_Entries_New_Display_Amount"><option value="10">10</option><option value="100" checked>100</option><option value="1000">1000</option><option value="10000">10000</option><option value="all">all</option>';
                case 1000:
                    $HTML_Display_Size_Submit = '<select name="Modify_Entries_New_Display_Amount"><option value="10">10</option><option value="100">100</option><option value="1000" checked>1000</option><option value="10000">10000</option><option value="all">all</option>';
                case 10000:
                    $HTML_Display_Size_Submit = '<select name="Modify_Entries_New_Display_Amount"><option value="10">10</option><option value="100">100</option><option value="1000">1000</option><option value="10000" checked>10000</option><option value="all">all</option>';
                case 'all':
                    $HTML_Display_Size_Submit = '<select name="Modify_Entries_New_Display_Amount"><option value="10">10</option><option value="100">100</option><option value="1000">1000</option><option value="10000">10000</option><option value="all" checked>all</option>';
                default:

            }
            $HTML_Display_Size_Submit = $HTML_Display_Size_Submit.'<input type="submit" name="New_Entry_Change_Display_Amount" value="New_Entry_Change_Display_Amount"></input>';
            $HTML_Display_Text = $HTML_Display_Text.$HTML_submit_buttons.$HTML_Display_Size_Submit.'</form>';
            $HTML_current_table_info = sprintf("<div>Current Block : %d of %d. Displaying %d entires per page.</div>", $Current_Modify_Entry_Block_Number+1, $Modify_Entires_Number_Of_Blocks, $Current_Modify_Entries_Display_Amount);
            $HTML_Display_Text = $HTML_Display_Text.$HTML_current_table_info;
            return $HTML_Display_Text;
        }

        function Get_Modify_Attribute_Value_Display_Checkboxgroup(&$All_Committed_Modify_Entries, $current_email, $current_attribute_name, $all_values) {
            $HTML_value_block = '';

            foreach ($all_values as $key => $checkbox_value) {

                if(isset($All_Committed_Modify_Entries[$current_email]) && isset($All_Committed_Modify_Entries[$current_email][$current_attribute_name]) {

                    if(in_array($checkbox_value, $All_Committed_Modify_Entries[$current_email][$current_attribute_name]) ) {
                        $HTML_attribute_value_input = sprintf('<input type="checkbox" class="Modify_Entry_Checkbox_Value_Attribute_%s" name="Modify_Entry_List[%s][%s][%s]" value="%s" checked>%s</input><br>', $current_attribute_name, $current_email, $current_attribute_name, $checkbox_value, $checkbox_value, $checkbox_value);
                    }
                    else{
                        $HTML_attribute_value_input = sprintf('<input type="checkbox" class="Modify_Entry_Checkbox_Value_Attribute_%s" name="Modify_Entry_List[%s][%s][%s]" value="%s">%s</input><br>', $current_attribute_name, $current_email, $current_attribute_name, $checkbox_value, $checkbox_value, $checkbox_value);
                    }

                }
                else{
                    $HTML_attribute_value_input = sprintf('<input type="checkbox" class="Modify_Entry_Checkbox_Value_Attribute_%s" name="Modify_Entry_List[%s][%s][%s]" value="%s">%s</input><br>', $current_attribute_name, $current_email, $current_attribute_name, $checkbox_value, $checkbox_value, $checkbox_value);
                }
                $HTML_value_block = $HTML_value_block.$HTML_attribute_value_input;
            }
            return $HTML_value_block;
        }

        function Get_Modify_Attribute_Value_Display_Other_Type(&$All_Current_User_Values, &$All_Committed_Modify_Entries, $current_email, $current_attribute_name, $all_values) {
            $HTML_value_block = '';

            foreach ($all_values as $key => $checkbox_value) {

                if(isset($All_Committed_Modify_Entries[$current_email]) && isset($All_Committed_Modify_Entries[$current_email][$current_attribute_name]) {

                    if($checkbox_value === $All_Committed_Modify_Entries[$current_email][$current_attribute_name]) ) {
                        if($key == 0) && !isset($All_Current_User_Values[$current_email][$current_attribute_name])) {
                            $HTML_attribute_value_input = sprintf('<input type="radio" class="Modify_Entry_Safe_Value_Attribute_%s" name="Modify_Entry_List[%s][%s][%s]" value="%s" checked>%s</input><br>', $current_attribute_name, $current_email, $current_attribute_name, $checkbox_value, $checkbox_value, $checkbox_value);
                        }
                        else{
                            $HTML_attribute_value_input = sprintf('<input type="radio" name="Modify_Entry_List[%s][%s][%s]" value="%s" checked>%s</input><br>', $current_email, $current_attribute_name, $checkbox_value, $checkbox_value, $checkbox_value);
                        }

                    }
                    else{
                        if($key == 0 && !isset($All_Current_User_Values[$current_email][$current_attribute_name])) {
                            $HTML_attribute_value_input = sprintf('<input type="radio" class="Modify_Entry_Safe_Value_Attribute_%s" name="Modify_Entry_List[%s][%s][%s]" value="%s">%s</input><br>', $current_attribute_name, $current_email, $current_attribute_name, $checkbox_value, $checkbox_value, $checkbox_value);
                        }
                        else{
                            $HTML_attribute_value_input = sprintf('<input type="radio" name="Modify_Entry_List[%s][%s][%s]" value="%s">%s</input><br>', $current_email, $current_attribute_name, $checkbox_value, $checkbox_value, $checkbox_value);
                        }                                   
                    }

                }
                else{
                    if($key == 0 && !isset($All_Current_User_Values[$current_email][$current_attribute_name])) {
                        $HTML_attribute_value_input = sprintf('<input type="radio" class="Modify_Entry_Safe_Value_Attribute_%s" name="Modify_Entry_List[%s][%s][%s]" value="%s">%s</input><br>', $current_attribute_name, $current_email, $current_attribute_name, $checkbox_value, $checkbox_value, $checkbox_value);
                    }
                    else{
                        $HTML_attribute_value_input = sprintf('<input type="radio" name="Modify_Entry_List[%s][%s][%s]" value="%s">%s</input><br>', $current_email, $current_attribute_name, $checkbox_value, $checkbox_value, $checkbox_value);
                    }
                }
                $HTML_value_block = $HTML_table_row.$HTML_attribute_value_input;
            }
            return $HTML_value_block;
        }


        $Failed_New_Entries;
        function Push_New_Entries() {
            foreach ($Commited_New_Entires as $email_key => $new_attributes_and_values) {
                $exists = Sql_Fetch_Row_Query(sprintf('select id from %s where email = "%s"', $GLOBALS['tables']['user'],$email_key));
                if($exists) {
                    $Failed_New_Entries[$email_key] = $new_attributes_and_values;
                }
                else{
                    $new_user_id = addNewUser($email_key);
                    foreach ($new_attributes_and_values as $this_attribute_name => $this_attribute_value) {
                        if($attribute_list[$this_attribute_name]['type'] === 'checkboxgroup') {
                            $new_attribute_value_ids = array();

                            foreach ($this_attribute_value as $this_key => $attribute_new_value) {
                                array_push($new_attribute_value_ids, $attribute_value_ids[$attribute_new_value]);
                            }

                            $proper_this_attribute_value = implode(',', $new_attribute_value_ids);
                        }
                        else{
                            if($attribute_list[$this_attribute_name]['type'] === 'checkbox'|'radio') {
                                $proper_this_attribute_value = $attribute_value_ids[$this_attribute_value];
                            }
                            else{
                                $proper_this_attribute_value = $this_attribute_value;
                            }
                        }
                        //need a way for 'STICKY' attributes
                        saveUserAttribute($new_user_id, $attribute_list[$this_attribute_name]['id'], $proper_this_attribute_value);
                    }   
                }
            }
        }
        $Failed_Modify_Entries;
        function Push_Modify_Entries() {
            foreach ($Commited_Modify_Entries as $email_key => $modify_attributes_and_values) {
                $exists = Sql_Fetch_Row_Query(sprintf('select id from %s where email = "%s"', $GLOBALS['tables']['user'],$email_key));
                if(!$exists) {
                    $Failed_Modify_Entries[$email_key] = $modify_attributes_and_values;
                }
                else{
                    $modify_user_id = $exists[0];
                    foreach ($modify_attributes_and_values as $this_attribute_name => $this_attribute_value) {
                        if($attribute_list[$this_attribute_name]['type'] == 'checkboxgroup') {
                            $modify_attribute_value_ids = array();

                            foreach ($this_attribute_value as $this_key => $attribute_new_value) {
                                array_push($modify_attribute_value_ids, $attribute_value_ids[$attribute_new_value]);
                            }

                            $proper_this_attribute_value = implode(',', $modify_attribute_value_ids);
                        }
                        else{
                            if($attribute_list[$this_attribute_name]['type'] === 'checkbox'|'radio') {
                                $proper_this_attribute_value = $attribute_value_ids[$this_attribute_value];
                            }
                            else{
                                $proper_this_attribute_value = $this_attribute_value;
                            }
                        }
                        //need a way for 'STICKY' attributes
                        saveUserAttribute($modify_user_id, $attribute_list[$this_attribute_name]['id'], $proper_this_attribute_value);
                    }
                }
            }
        }
        function Get_Current_User_Attribute_Values(&$Current_Value_List, $email_key, &$set_attribute_list) {
            $current_user_query = sprintf('select id from %s where email = "%s"', $GLOBALS['tables']['user'], $email_key);
            $current_user_sql_result = Sql_Fetch_Array_Query($current_user_query);
            if(!isset($current_user_sql_result[0])) {
                return false;
            }
            if(!isset($Current_Value_List[$email_key])) {
                $Current_Value_List[$email_key] = array();
            }
            foreach ($set_attribute_list as $attribute_name => $attribute_array) {
                 
                $Current_User_Attribute_Values_Query = sprintf("select value from %s where attributeid = %d and userid = %d", $GLOBALS['tables']['user_attribute'], $attribute_array['id'], $current_user_sql_result[0]);
                $current_attribute_return = Sql_Fetch_Row_Query($Current_User_Attribute_Values_Query);

                if($current_attribute_return[0]){
                    if($attribute_array['type'] == 'checkboxgroup') {
                        $exploded_current_values_ids = explode(',', $current_attribute_return[0]);
                        $Current_Value_List[$email_key][$attribute_name] = array();

                        foreach ($exploded_current_values_ids as $key => $attribute_value_id) {
                            $attribute_value_from_id_query = sprintf("select name from %s where id = %d", $attribute_array['tablename'], $attribute_value_id);
                            $attribute_value_return = Sql_Fetch_Row_Query($attribute_value_from_id_query);
                            array_push($Current_Value_List[$email_key][$attribute_name], $attribute_value_return[0]);
                         
                        }
                    }
                    else if($attribute_array['type'] == 'checkbox'|'select'|'radio') {
                        $attribute_value_from_id_query = sprintf("select name from %s where id = %d", $attribute_array['tablename'], $current_attribute_return[0]);
                        $attribute_value_return = Sql_Fetch_Row_Query($attribute_value_from_id_query);
                        $Current_Value_List[$email_key][$attribute_name] = $attribute_value_return[0];
                    }
                    else if($attribute_array['type'] == 'textarea'|'textline') {
                        $Current_Value_List[$email_key][$attribute_name] = $current_attribute_return[0];
                    }
                    else if($attribute_array['type'] == 'Date') {
                        $Current_Value_List[$email_key][$attribute_name] = $current_attribute_return[0];
                    }
                }
            }
            return true;
        }
    }
?>



<script>

    function Clear_Column_Select(column_class) {
        var column_radios = document.getElementsByClassName(column_class);
        for(i=0; i<column_radios.length; i++) {
            column_radios.checked = false;
        }
    }

</script>


<script>

    function checkAll_NewEntry_Emails() {
        var element_blocks = document.getElementsByClassName('New_Entry_Email');
        var i;
        for(i=0; i<element_blocks.length; i++) {
            element_blocks[i].checked = true;
        }
    }

    function removeAll_NewEntry_Emails() {
        var element_blocks = document.getElementsByClassName('New_Entry_Email');
        var i;
        for(i=0; i<element_blocks.length; i++) {
            element_blocks[i].checked = false;
        }
    }



    function checkAll_NewEntry_CheckboxGroup(attribute) {
        var class_string = 'New_Entry_Checkbox_Value_Attribute_'.concat(attribute);

        var checkboxgroup_element_blocks = document.getElementsByClassName(class_string);
        for(i=0; i<checkboxgroup_element_blocks.length; i++) {
            checkboxgroup_element_blocks[i].checked = true;
        }
    }

    function removeAll_NewEntry_CheckboxGroup(attribute) {
        var class_string = 'New_Entry_Checkbox_Value_Attribute_'.concat(attribute);

        var checkboxgroup_element_blocks = document.getElementsByClassName(class_string);
        for(i=0; i<checkboxgroup_element_blocks.length; i++) {
            checkboxgroup_element_blocks[i].checked = false;
        }
    }

    function checkAll_NewEntry_SafeValues(attribute) {

        var class_string = 'New_Entry_Safe_Value_Attribute_'.concat(attribute);

        var checkboxgroup_safe_element_blocks = document.getElementsByClassName(class_string);
        for(i=0; i<checkboxgroup_element_blocks.length; i++) {
            checkboxgroup_safe_element_blocks[i].checked = true;    
        }
    }

    function checkAll_NewEntry_SafeValues_OrChecked(attribute) {
        var class_string = 'New_Entry_Safe_Value_Attribute_'.concat(attribute);

        var checkboxgroup_safe_element_blocks = document.getElementsByClassName(class_string);
        for(i=0; i<checkboxgroup_element_blocks.length; i++) {
            var current_name = checkboxgroup_safe_element_blocks[i].name;
            var same_name_elements = document.getElementsByName(current_name);
            var has_checked = false;
            for(j=0; j < same_name_elements; j++) {
                if(same_name_elements[j].checked == true) {
                    has_checked = true;
                    break;
                }
            }
            if(has_checked == false) {
                checkboxgroup_safe_element_blocks[i].checked = true;
            }
        }
    }

    function removeAll_NewEntry_SafeValues(attribute) {
        var class_string = 'New_Entry_Safe_Value_Attribute_'.concat(attribute);

        var checkboxgroup_safe_element_blocks = document.getElementsByClassName(class_string);
        for(i=0; i<checkboxgroup_element_blocks.length; i++) {
            checkboxgroup_safe_element_blocks[i].checked = false;
        }
    }
    function removeAll_NewEntry_SafeValues_OrChecked(attribute) {
        var class_string = 'New_Entry_Safe_Value_Attribute_'.concat(attribute);
        var checkboxgroup_safe_element_blocks = document.getElementsByClassName(class_string);
        for(i=0; i<checkboxgroup_element_blocks.length; i++) {
            var current_name = checkboxgroup_safe_element_blocks[i].name;
            var same_name_elements = document.getElementsByName(current_name);
            var has_checked = false;
            for(j=0; j < same_name_elements; j++) {
                same_name_elements[j].checked = false;
            }
        }
    }

    function checkAll_NewEntry_Emails() {
        var element_blocks = document.getElementsByClassName('New_Entry_Email');
        var i;
        for(i=0; i<element_blocks.length; i++) {
            element_blocks[i].checked = true;
        }
    }

    function removeAll_NewEntry_Emails() {
        var element_blocks = document.getElementsByClassName('New_Entry_Email');
        var i;
        for(i=0; i<element_blocks.length; i++) {
            element_blocks[i].checked = false;
        }
    }






    function checkAll_ModifyEntry_CheckboxGroup(attribute) {
        var class_string = 'Modify_Entry_Checkbox_Value_Attribute_'.concat(attribute);

        var checkboxgroup_element_blocks = document.getElementsByClassName(class_string);
        for(i=0; i<checkboxgroup_element_blocks.length; i++) {
            checkboxgroup_element_blocks[i].checked = true;
        }
    }

    function removeAll_ModifyEntry_CheckboxGroup(attribute) {
        var class_string = 'Modify_Entry_Checkbox_Value_Attribute_'.concat(attribute);

        var checkboxgroup_element_blocks = document.getElementsByClassName(class_string);
        for(i=0; i<checkboxgroup_element_blocks.length; i++) {
            checkboxgroup_element_blocks[i].checked = false;
        }
    }

    function checkAll_ModifyEntry_SafeValues(attribute) {

        var class_string = 'Modify_Entry_Safe_Value_Attribute_'.concat(attribute);

        var checkboxgroup_safe_element_blocks = document.getElementsByClassName(class_string);
        for(i=0; i<checkboxgroup_element_blocks.length; i++) {
            checkboxgroup_safe_element_blocks[i].checked = true;    
        }
    }

    function checkAll_ModifyEntry_SafeValues_OrChecked(attribute) {
        var class_string = 'Modify_Entry_Safe_Value_Attribute_'.concat(attribute);

        var checkboxgroup_safe_element_blocks = document.getElementsByClassName(class_string);
        for(i=0; i<checkboxgroup_element_blocks.length; i++) {
            var current_name = checkboxgroup_safe_element_blocks[i].name;
            var same_name_elements = document.getElementsByName(current_name);
            var has_checked = false;
            for(j=0; j < same_name_elements; j++) {
                if(same_name_elements[j].checked == true) {
                    has_checked = true;
                    break;
                }
            }
            if(has_checked == false) {
                checkboxgroup_safe_element_blocks[i].checked = true;
            }
        }
    }

    function removeAll_ModifyEntry_SafeValues(attribute) {
        var class_string = 'Modify_Entry_Safe_Value_Attribute_'.concat(attribute);

        var checkboxgroup_safe_element_blocks = document.getElementsByClassName(class_string);
        for(i=0; i<checkboxgroup_element_blocks.length; i++) {
            checkboxgroup_safe_element_blocks[i].checked = false;
        }
    }
    function removeAll_ModifyEntry_SafeValues_OrChecked(attribute) {
        var class_string = 'Modify_Entry_Safe_Value_Attribute_'.concat(attribute);
        var checkboxgroup_safe_element_blocks = document.getElementsByClassName(class_string);
        for(i=0; i<checkboxgroup_element_blocks.length; i++) {
            var current_name = checkboxgroup_safe_element_blocks[i].name;
            var same_name_elements = document.getElementsByName(current_name);
            var has_checked = false;
            for(j=0; j < same_name_elements; j++) {
                same_name_elements[j].checked = false;
            }
        }
    }

    function checkAll_ModifyEntry_Emails() {
        var element_blocks = document.getElementsByClassName('Modify_Entry_Email');
        var i;
        for(i=0; i<element_blocks.length; i++) {
            element_blocks[i].checked = true;
        }
    }

    function removeAll_ModifyEntry_Emails() {
        var element_blocks = document.getElementsByClassName('Modify_Entry_Email');
        var i;
        for(i=0; i<element_blocks.length; i++) {
            element_blocks[i].checked = false;
        }
    }



//FOR FIRST PAGE
    function Test_Upload_Text(){
        var the_text = document.getElementById("attribute_changer_text_to_upload");
        if(the_text.innerHTML == "") {
            document.getElementById("error_printing").innerHTML="Error: No Text Input";
            return;
        }
        else{
            if(the_text.innerHTML[0].length > 1000000000) {
                document.getElementById("error_printing").innerHTML="Error: Text Cannot Exceed 1 Billion Characters";
                return;
            }
            else{
                document.getElementById("text_upload_form").submit();
            }
        }
    }

    function Test_Upload_File(){
        var the_file = document.getElementById("attribute_changer_file_to_upload");
        if(!the_file.files) {
            document.getElementById("error_printing").innerHTML="Error: Not Supported By This Browser";
            return;
        }
        if(!the_file.files[0]) {
            document.getElementById("error_printing").innerHTML="Error: Must Have File Selected";
            return;
        }
        else{
            if(the_file.files[0].size > 1000000000) {
                document.getElementById("error_printing").innerHTML="Error: File Cannot Exceed 1GB";
                return;
            }
            else{
                document.getElementById("file_upload_form").submit();
            }
        }
    }

</script>