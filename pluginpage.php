<?php



if (!defined('PHPLISTINIT')) die(); ## avoid pages being loaded directly
if ($GLOBALS["commandline"]) {
 echo 'not to oppened by command line';
 die();
}

if(!isset($_POST)) {
	$page_print =  '<html>
<body>
<div>Attribute Changer</div>
<div id="error_printing"></div>
<form action="" method="post" enctype="multipart/form-data" id="file_upload_form">
    Select file to upload:
    (must be comma separated text)
    <input type="file" name="attribute_changer_file_to_upload" id="attribute_changer_file_to_upload">
    <input type="button" value="attribute_changer_upload_file_button" name="attribute_changer_upload_file_button" id="attribute_changer_upload_file_button" onClick="Test_Upload_File()">
    file_name:<input type="text" name="attribute_changer_file_name">
</form>
<form action="" method="post" enctype="multipart/form-data" id="text_upload_form">
    Copy file to upload:
    (must be comma separated text)
    <input type="text" name="attribute_changer_text_to_upload" id="attribute_changer_text_to_upload">
    <input type="button" value="attribute_changer_upload_text" name="attribute_changer_upload_text" onClick="Test_Upload_Text()">
    desired_file_name:<input type="text" name="attribute_changer_text_name">
</form>

</body>
</html>
<script>
function Test_Upload_Text(){
    var the_text = document.getElementById("attribute_changer_file_to_upload");
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
    var the_file = document.getElementById("attribute_changer_text_to_upload");
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
}';
}

else{
    if(isset($_POST['attribute_changer_file_to_upload'])) {
        //possible check if dir exists
    $target_dir = dirname(__FILE__) . '/Attribute_Changer_PLugin/temp_table_uploads/';
    $target_file = $target_dir . basename($_FILES["attribute_changer_file_to_upload"]["name"]);
    $uploadOk = 1;
    $new_file_type = pathinfo($target_file,PATHINFO_EXTENSION);

    $new_html = '<html><body>';
    // Check if file already exists
    if (file_exists($target_file)) {
        $new_html = $new_html."Sorry, file already exists.";
        $uploadOk = 0;
    }
    // Check file size
    if ($_FILES["attribute_changer_file_to_upload"]["size"] > 1000000000) {
        $new_html = $new_html."Sorry, your file is too large > 1BG.";
        $uploadOk = 0;
    }
    // Allow certain file formats

    //add other comma separated
    if($imageFileType != "csv") {
        $new_html = $new_html."Sorry, only csv allowed.";
        $uploadOk = 0;
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $new_html = $new_html."Sorry, your file was not uploaded.";
        $new_html = $new_html.'</body></html>';
        print($new_html);
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["attribute_changer_file_to_upload"]["tmp_name"], $target_file)) {
            $new_html = $new_html."The file ". basename( $_FILES["attribute_changer_file_to_upload"]["name"]). " has been uploaded.";

            $cols_match = Get_Attribute_File_Column_Match($target_file);
            if($cols_match == '') {
                $new_html = $new_html.'There was an error with the table forming';
                $new_html = $new_html.'</body></html>';
                print($new_html);
            }
            else{
                $new_html= $new_html.$cols_match;
                $new_html = $new_html.'</body></html>';
                print($new_html);
            }
        } 
        else {
            $new_html = $new_html."Sorry, there was an error uploading your file.";
            $new_html = $new_html.'</body></html>';
            print($new_html);
        }
    }

}

if(isset($_POST['submit']['File_Column_Match_Submit'])) {
    if(!isset($_POST['attribute_to_match'])) {
        //shouldnt happen .... else user needs to be WARNEDDDDD
    }
    else{
        foreach ($_POST['attribute_to_match'] as $attribute => $column_key_to_match) {
            ///START CaLLING DAS OTHER STUFFS
            /////////////////////////////////////////////////////
            //USE DISSSS
            Updated_Test_Entry($entry) {
            //entry is [email]=>array (attribute, value)
        }
    }
}

function Get_Attribute_File_Column_Match($new_file_loc) {
    if(!file_exists($new_file_loc)) {
        return '';
    }
    $column_match_return_string = '';
    $fp = fopen($new_file_loc, 'r');

    $columns = array();
    $current_word = '';
    $current_char ='';
    while(($current_char = fread($fp, 1)) != '\n') {
        if($current_char == ',') {
            array_push($columns, $current_word);
            $current_word = '';
        }
        else{

        }
    }
    $attribute_name_query = sprintf('select name from %s', $GLOBALS['tables']['attribute']);
    $return_attributes = Sql_Fetch_Array_Query($attribute_name_query);
    if(!$return_attributes){
        return ''; //because lol
    }

    $column_match_return_string = '
    <form action="" method="post" id="file_column_select_form">
    <table id="column_match_table><tr>';
    //create radios for each
    foreach ($columns as $column_key => $column_value) {
        $cell_string = sprintf('<td> Set : %s  to : <br>');

        foreach ($return_attributes as $newkey => $attribute_name) {
            $cell_string = $cell_string.sprintf('<input type="radio" name="attribute_to_match[%s]" value="%d"><br>', $attribute_name, $column_key);
        }
        $column_match_return_string = $cell_string.'</td>';
    }
    $column_match_return_string = $column_match_return_string.'</table><input type="submit" name="File_Column_Match_Submit" </form>';
    return $column_match_return_string;
}



?>