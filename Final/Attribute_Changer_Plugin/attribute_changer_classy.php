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
            if(!isset($GLOBALS['tables']['pluggins']['AttributeChanger'])){
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

        //'atribute_name' => array(name=>'',type=>'',table=>'')
        $attribute_list;
        //'attribute_name'=>'value'=>'id'
        $attribute_value_ids;

        function AttributeChanger() {

            //get all attributes and their info
            $query = sprintf('select * from %s', $GLOBALS['tables']['attribute']);
            $attribute_data_return = Sql_Query($query); 
            if($attribute_data_return) {
                $attribute_list = array();

                $attribute_value_ids = array();

                while(($attribute_data = Sql_fetch_array($attribute_data_return))) {
                    if(!isset( ($attribute_data['id']) | ($attribute_data['name']) | ($attribute_data['type']) )) {
                        //not known format, cannot use
                    }
                    else{
                        if(isset($attribute_list[$attribute_data['name']])) {
                            //cannot have duplicates
                            continue;
                        }
                        //use the attribute list to get type and value information
                        $attribute_list[$attribute_data['name']] = $attribute_data;

                        //must check tables for values
                        if($attribute_data['type'] === ("radio"|"checkboxgroup"|"select"|"checkbox")) {

                            if(!isset($attribute_data['tablename'])) {
                                unset($attribute_list[$attribute_data['name']]);
                            }

                            else {

                                if(isset($attribute_value_ids[$attribute_data['name']])) {
                                    continue;
                                }

                                $attribute_value_ids[$attribute_data['name'] = array();

                                //must query to get the allowed values
                                $value_table_name = $table_prefix."listattr_".$attribute_data["tablename"];
                                $value_query = sprintf("select name from %s", $value_table_name);
                                $allowed_values_res = Sql_Query($value_query);

                                if($allowed_value_res) {
                                    while(($row = Sql_Fetch_Row_Query($allowed_values_res))) {

                                        $value_id_query = sprintf("select id from %s where name = %s", $attribute_data["tablename"], $row[0]);
                                        $value_id = Sql_Fetch_Row_Query($value_id_query);

                                        if($value_id[0]) {
                                            $attribute_value_ids[$attribute_data['name']][$row[0]] = $value_id[0];
                                            array_push($attribute_list[$attribute_data['name']]['allowed_values'], $row[0]);
                                        }
                                    }
                                }
                                else{
                                    unset($attribute_list[$attribute_data['name']]['allowed_values']);
                                    unset($attribute_value_ids[$attribute_data['name']]);
                                } 
                            }
                        }
                        else{
                            //is other input type
                        }
                    }
                }
            }
            else{
                //no rows :S

                //PRINT AN ERROR I GUESS LOL
            }
            $New_Entry_List = array();
            $Modify_Entry_List = array();

            $Current_Users_Values = array();
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

            $first_block = '';

            while(!feof($fp)) {
                $first_block = $first_block.fread($fp, 4056);
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
            <input type="hidden" name="file_location">
            <table id="column_match_table><tr>';
            $column_match_return_string = $column_match_return_string.sprintf('<input type="hidden" name="file_location" value="%s">', $new_file_loc);
            //create radios for each
            foreach ($columns as $column_key => $column_value) {
                $cell_string = sprintf('<td> Set : %s  to : <br>', $column_value);

                foreach (this->$attribute_list as $attribute_name => $attribute_info) {
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
                foreach ( (explode(',', $first_few_rows[$i])) as $key => $table_value) {
                    $value_row=$value_row.sprintf('<td>%s</td>', $table_value);
                }
                $column_match_return_string = $column_match_return_string.$value_row.'</tr>';
            }

            $column_match_return_string = $column_match_return_string.'</table><input type="submit" name="File_Column_Match_Submit" value="submit"> </form>';

            return $column_match_return_string;
        }



    }



?>