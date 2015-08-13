<?php

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
</form>'

;
if(!isset($_POST)) {
    
    print('<html><head><script src="'.$javascript_src.'"></script></head><body>'.$page_print.'</body></html>');
}

if(isset($_POST['attribute_changer_file_to_upload'])) {
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