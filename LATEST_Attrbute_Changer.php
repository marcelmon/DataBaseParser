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
        function Initialize_Attribute_Changer() {
            //get all attributes and their info
            $query = sprintf('select * from %s', $GLOBALS['tables']['attribute']);
            $attribute_data_return = Sql_Query($query); 
            if($attribute_data_return) {
                $attribute_list = array();
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
                            else{
                                //must query to get the allowed values
                                $value_table_name = $table_prefix."listattr_".$attribute_data["tablename"];
                                $value_query = sprintf("select name from %s", $value_table_name);
                                $allowed_values_res = Sql_Query($value_query);
                                if($allowed_value_res) {
                                    while($row = Sql_Fetch_Row_Query($allowed_values_res)) {
                                        array_push($attribute_list[$attribute_data['name']]['allowed_values'], $row['name']);
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
            array_shift($entry);
            if(!filter_var($email, FILTER_VALIDATE_EMAIL) ){
                return false;
            }
            $entry_query = sprintf('select * from %s where email = "%s"', $GLOBALS['tables']['user'], $email);
            $user_sql_result = Sql_Query($entry_query);
            //0 if there are no attributes, is only existence
            if(!is_array($entry) == 0) {
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
            if($user_sql_result){
                Get_Current_User_Attribute_Values($Current_Users_Values, $email, $attribute_list);
            }
             
            //if there are attributes, must check each value to look for update
            foreach ($entry as $attribute => $new_attribute_value) {
                //these are single choice values
                if($attribute_list[$attribute]['type'] == "radio"|"select"|'checkbox') {
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
                                    Add_Multi_Entry_To_Modify_Or_New_Entry_List($email, $new_attribute_value, $attribute, $Modify_Entry_List);
                                }
                            }
                            else{
                                //no current attributes, can definately add to list, user exists
                                Add_Multi_Entry_To_Modify_Or_New_Entry_List($email, $new_attribute_value, $attribute, $New_Entry_List);                             
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
                        if(filter_var($day , FILTER_VALIDATE_INT,  array('options' => array(  'min_range' => 1, 'max_range' => 31) )) == false){
                        }
                        else if(filter_var($month , FILTER_VALIDATE_INT,  array('options' => array(  'min_range' => 1, 'max_range' => 12) )) == false) {
                        }
                        ##Consider adding date validate to this date as max
                        else if(filter_var($year , FILTER_VALIDATE_INT,  array('options' => array(  'min_range' => 1900, 'max_range' => 3000) )) == false) {
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
                        }
 
                        if(isset($Current_Users_Values[$email])) {
 
                        }
 
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
                else if ($attribute_list[$attribute]['type'] == "textarea"|"textline") {
                    //this is if the returned user has an id, will always have an id if exists in the database
                    if(isset($Current_Users_Values[$email])) {
 
                        if(isset($Current_Users_Values[$email][$attribute]) && $new_attribute_value != $Current_Users_Values[$email][$attribute]) {
 
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
 
 
        function Add_Single_Entry_To_Modify_Or_New_Entry_List($email, $new_attribute_value, $attribute, $Modify_list, $Duplicate_Email_List, $Duplicate_Attributes_List) {
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
        function Add_Multi_Entry_To_Modify_Or_New_Entry_List($email, $new_attribute_value, $attribute, $Modify_list) {
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
            $Current_New_Entries_Display_Amount = 100;
            $New_Entries_Total_Amount = count($New_Entry_List);
            $New_Entires_Number_Of_Blocks = $New_Entries_Total_Amount/$Current_New_Entries_Display_Amount + (($New_Entries_Total_Amount % $Current_New_Entries_Display_Amount)? 1:0);
         
            $Current_New_Entry_Block_Number = 0;
            //need to finish this
             
        }
        function New_Entry_Change_Display_Amount($New_Amount) {
            if($New_Amount != (10|100|1000|10000|'all')) {
                return false;
            }
            if($Current_New_Entries_Display_Amount === 'all') {
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
                return Get_Modify_Entry_Table_Block($Current_New_Entry_Block_Number);
            }
            else{
                //because there are no more blocks
                return false;
            }
        }
        function New_Entry_Display_Previous_Page() {
            if($Current_New_Entry_Block_Number > 0) {
                $Current_New_Entry_Block_Number--;
                return Get_Modify_Entry_Table_Block($Current_New_Entry_Block_Number);
            }
            else{
                //because there are no more blocks
                return false;
            }
        }


        function Get_New_Entry_Table_Block() {
            $Current_New_Entry_Block = array_slice($New_Entry_List, $Current_New_Entry_Block_Number*$Current_New_Entries_Display_Amount, $Current_New_Entries_Display_Amount);
            $HTML_Display_Text = sprintf('<form name="New_Entry_Submit_Form_Block__%d" action="%s" method="post">', $Current_New_Entry_Block_Number, 'self');
            $HTML_Display_Text = $HTML_Display_Text.sprintf('<table id="New_User_Attribute_Select_Table_Block__%d">', $Current_New_Entry_Block_Number);
            $HTML_table_row = sprintf('<tr><td>EMAIL<br><input type="button" id="New_Entry_Include_All_Emails" name="New_Entry_Include_All_Emails" value="Include All Emails" onClick="checkAll_NewEntry_Emails()"></input>/td>';
            $HTML_table_row = sprintf('<tr><td>EMAIL<br><input type="button" id="New_Entry_Remove_All_Emails" name="New_Entry_Include_Remove_Emails" value="Remove All Emails" onClick="removeAll_NewEntry_Emails()"></input>/td>';

            foreach ($attribute_list as $attribute_name => $attribute_info) {
                $HTML_table_row = $HTML_table_row.sprintf('<td>%s<br><input type="CHECKBOX" name="New_Entry_Attribute_Column_Select[%s]" value="checked">Include This Attribute</input>',$attribute_name, $attribute_name);
                if($attribute_info['type'] === 'checkboxgroup') {
                    $HTML_table_row = $HTML_table_row.sprintf('<br><input type="button" name="New_Entry_Include_All_Checkboxgroup_%s" value="Include All Checkboxgroup Values" onClick="checkAll_NewEntry_CheckboxGroup(%s)"></input>', $attribute_name, $attribute_name);
                    $HTML_table_row = $HTML_table_row.sprintf('<br><input type="button" name="New_Entry_Remove_All_Checkboxgroup_%s" value="Remove All Checkboxgroup Values" onClick="removeAll_NewEntry_CheckboxGroup(%s)"></input>', $attribute_name, $attribute_name);
                }
                else{
                    $HTML_table_row = $HTML_table_row.sprintf('<br><input type="button" name="New_Entry_Include_All_Safe_Values_%s" value="Include All Safe Values" onClick="checkAll_NewEntry_SafeValues(%s)></input></td>', $attribute_name, $attribute_name);
                    $HTML_table_row = $HTML_table_row.sprintf('<br><input type="button" name="New_Entry_Include_All_Safe_Values_Or_Checked_%s" value="Include All Safe Values Or Checked" onClick="checkAll_NewEntry_SafeValues_OrChecked(%s)></input></td>', $attribute_name, $attribute_name);
                    $HTML_table_row = $HTML_table_row.sprintf('<br><input type="button" name="New_Entry_Remove_All_Safe_Values_%s" value="Remove All Safe Values" onClick="removeAll_NewEntry_SafeValues(%s)></input></td>', $attribute_name, $attribute_name);
                    $HTML_table_row = $HTML_table_row.sprintf('<br><input type="button" name="New_Entry_Remove_All_Safe_Values_Or_Checked_%s" value="Remove All Safe Values Or Checked" onClick="removeAll_NewEntry_SafeValues_OrChecked(%s)></input></td>', $attribute_name, $attribute_name);

                }
            }
            $HTML_Display_Text = $HTML_Display_Text.$HTML_table_row.'</tr>';
            foreach ($Current_New_Entry_Block as $email_key => $new_user_attributes_and_values) {
                if(isset($Commited_New_Entires[$email_key]) {
                    $HTML_table_row = sprintf('<tr><td>%s<br><input type="checkbox" class="New_Entry_Email" name="New_Entry_List[%s][\'include\']" value="include" checked>Include This Email</input></td>',$email_key, $email_key);
                }
                else{
                    $HTML_table_row = sprintf('<tr><td>%s<br><input type="checkbox" class="New_Entry_Email" name="New_Entry_List[%s][\'include\']" value="include">Include This Email</input></td>',$email_key, $email_key);
                }
                //commited_new_entries[email]: attribute,value
                foreach ($attribute_list as $attribute_name => $attribute_info) {
                    if(!isset($new_user_attributes_and_values[$attribute_name])) {
                        $HTML_table_row = $HTML_table_row.'<td></td>';
                    }
                    else foreach ($new_user_attributes_and_values[$attribute_name] as $key => $attribute_value) {
                        if($attribute_info['type'] == 'checkboxgroup') {
                            if(isset($Commited_New_Entires[$email_key]) && isset($Commited_New_Entires[$email_key][$attribute_name])) {
                                $selectedGroupValues = split(',', $Commited_New_Entires[$email_key][$attribute_name]);
                            }
                        }
                        $HTML_table_row= $HTML_table_row.'<td>';
                        switch($attribute_info['type']){
                            case "textarea"|"textline"|"checkbox"|"hidden"|"date": 
                                if(isset($Commited_New_Entires[$email_key] && isset($Commited_New_Entires[$email_key][$attribute_name]) && $Commited_New_Entires[$email_key][$attribute_name] === $attribute_value)) {
                                    //if the attribute value is the already selected, mark as checked
                                    if($key == 0) {
                                        $HTML_attribute_value_input = sprintf('<input type="radio"  class="New_Entry_Safe_Value_Attribute_%s" name="New_Entry_List[%s][%s]" value="%s" checked>%s</input>', $attribute_name, $email_key, $attribute_name, $attribute_value, $attribute_value);
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
                                if(isset($Commited_New_Entires[$email_key]) && isset($Commited_New_Entires[$email_key][$attribute_name]) && in_array($attribute_value, $selectedGroupValues)) {
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
                $HTML_Display_Text = $HTML_Display_Text.$HTML_table_row.'</tr>';
                 
            }
            $HTML_Display_Text = $HTML_Display_Text.'</table>';
            $HTML_submit_buttons = '<input type="submit" name="New_Entries_Table_Submit_all" value="New_Entries_Table_Submit_all"></input>';
            if($Current_New_Entry_Block_Number > 0) {
                $HTML_submit_buttons = $HTML_submit_buttons.'<input type="submit" name="value="New_Entries_Table_Previous_Page" value="New_Entries_Table_Previous_Page"></input>';
            }
            if($Current_New_Entry_Block_Number < $New_Entires_Number_Of_Blocks) {
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
                case all:
                    $HTML_Display_Size_Submit = '<select name="New_Entries_New_Display_Amount"><option value="10">10</option><option value="100">100</option><option value="1000">1000</option><option value="10000">10000</option><option value="all" checked>all</option>';
            }
            $HTML_Display_Size_Submit = $HTML_Display_Size_Submit.'<input type="submit" name="New_Entry_Change_Display_Amount" value="New_Entry_Change_Display_Amount"></input>';
            $HTML_Display_Text = $HTML_Display_Text.$HTML_submit_buttons.$HTML_Display_Size_Submit.'</form>';
            $HTML_current_table_info = sprintf("Current Block : %d of %d. Displaying %d entires per page.", $Current_New_Entry_Block_Number+1, $New_Entires_Number_Of_Blocks, $Current_New_Entries_Display_Amount);
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
            $Current_Modify_Entries_Display_Amount = 100;
            $Modify_Enties_Total_Amount = count($Modify_Entry_List);
            $Modify_Entires_Number_Of_Blocks = $Modify_Enties_Total_Amount/$Current_Modify_Entries_Display_Amount + (($Current_Modify_Entries_Display_Amount % $Modify_Enties_Total_Amount)? 1:0);
         
            $Current_Modify_Entry_Block_Number = 0;
             
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
            if($New_Amount != (10|100|1000|10000)) {
                return false;
            }
            $Current_Modify_Entries_Display_Amount = $New_Amount;
            $Modify_Entires_Number_Of_Blocks = $Modify_Enties_Total_Amount/$Current_Modify_Entries_Display_Amount + (($Current_Modify_Entries_Display_Amount % $Modify_Enties_Total_Amount)? 1:0);
            $Current_New_Entry_Block_Number = 0;
            return true;
        }
        function Get_Modify_Entry_Table_Block() {
            $Current_Modify_Entry_Block = array_slice($Modify_Entry_List, $Current_Modify_Entry_Block_Number*$Current_Modify_Entries_Display_Amount, $Current_Modify_Entries_Display_Amount);
            $HTML_Display_Text = sprintf('<form name="Modify_Entry_Submit_Form_Block__%d" action="%s" method="post">', $Current_Modify_Entry_Block_Number, 'self');
            $HTML_Display_Text = $HTML_Display_Text.sprintf('<table id="Modify_User_Attribute_Select_Table_Block__%d">', $Current_Modify_Entry_Block_Number);
            $HTML_table_row = '<tr><td>EMAIL</td>';
            foreach ($attribute_list as $attribute_name => $attribute_info) {
                $HTML_table_row = $HTML_table_row.sprintf('<td>%s<input type="CHECKBOX" name="Modify_Entry_Attribute_Column_Select[%s]" value="checked">',$attribute_name, $attribute_name);
                if($attribute_info['type'] === 'checkboxgroup') {100
                    $HTML_table_row = $HTML_table_row.sprintf('<br><input type="button" name="Modify_Entry_Include_All_Checkboxgroup_%s" value="Include All Checkboxgroup Values" onClick="checkAll_ModifyEntry_CheckboxGroup(%s)"></input>', $attribute_name, $attribute_name);
                    $HTML_table_row = $HTML_table_row.sprintf('<br><input type="button" name="Modify_Entry_Remove_All_Checkboxgroup_%s" value="Remove All Checkboxgroup Values" onClick="removeAll_ModifyEntry_CheckboxGroup(%s)"></input>', $attribute_name, $attribute_name);
                }
                else{
                    $HTML_table_row = $HTML_table_row.sprintf('<br><input type="button" name="Modify_Entry_Include_All_Safe_Values_%s" value="Include All Safe Values" onClick="checkAll_ModifyEntry_SafeValues(%s)></input></td>', $attribute_name, $attribute_name);
                    $HTML_table_row = $HTML_table_row.sprintf('<br><input type="button" name="Modify_Entry_Remove_All_Safe_Values_%s" value="Remove All Safe Values" onClick="removeAll_ModifyEntry_SafeValues(%s)></input></td>', $attribute_name, $attribute_name);
                }
            }
            $HTML_Display_Text = $HTML_Display_Text.'</tr>';
            foreach ($Current_Modify_Entry_Block as $email_key => $modify_user_attributes_and_values) {
                //THIS SHOULD NEVER HAPPEN!!!!!!!
                if(!isset($Current_user_values[$email_key])) {
                    Get_Current_User_Attribute_Values()
                }
                 
                if(isset($Commited_Modify_Entries[$email_key])) {
                    $HTML_table_row = sprintf('<tr><td>%s<br><input type="checkbox" class="Modify_Entry_Email" name="Modify_Entry_List[%s][\'include\']" value="include" checked>Include This Email</input></td>',$email_key, $email_key);
                }
                else{
                    $HTML_table_row = sprintf('<tr><td>%s<br><input type="checkbox" class="Modify_Entry_Email" name="Modify_Entry_List[%s][\'include\']" value="include">Include This Email</input></td>',$email_key, $email_key);
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
                }
 
                $HTML_table_row.'</tr>';
                $HTML_Display_Text = $HTML_Display_Text.$HTML_table_row;
 
            }
 
            $HTML_Display_Text = $HTML_Display_Text.'</table>';
            $HTML_submit_buttons = '<input type="submit" name ="Modify_Entries_Table_Submit_all" value="Submit_all"></input>';
            if($Current_New_Entry_Block_Number > 0) {
                $HTML_submit_buttons = $HTML_submit_buttons.'<input type="submit" name ="Modify_Entries_Table_Previous_Page" value="Modify_Entries_Table_Previous_Page"></input>';
            }
            if($Current_New_Entry_Block_Number < $New_Entires_Number_Of_Blocks) {
                $HTML_submit_buttons = $HTML_submit_buttons.'<input type="submit" name ="Modify_Entries_Table_Next_page" value="Modify_Entries_Table_Next_page"></input>';
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
                case all:
                    $HTML_Display_Size_Submit = '<select name="Modify_Entries_New_Display_Amount"><option value="10">10</option><option value="100">100</option><option value="1000">1000</option><option value="10000">10000</option><option value="all" checked>all</option>';

            }
            $HTML_Display_Size_Submit = $HTML_Display_Size_Submit.'<input type="submit" name="New_Entry_Change_Display_Amount" value="New_Entry_Change_Display_Amount"></input>';
            $HTML_Display_Text = $HTML_Display_Text.$HTML_submit_buttons.$HTML_Display_Size_Submit.'</form>';
            $HTML_current_table_info = sprintf("Current Block : %d of %d. Displaying %d entires per page.", $Current_Modify_Entry_Block_Number+1, $Modify_Entires_Number_Of_Blocks, $Current_Modify_Entries_Display_Amount);
            $HTML_Display_Text = $HTML_Display_Text.$HTML_current_table_info;
            return $HTML_Display_Text;
        }

        function Get_Modify_Attribute_Value_Display_Checkboxgroup($All_Committed_Modify_Entries, $current_email, $current_attribute_name, $all_values) {
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
            return $HMTL_value_block;
        }

        function Get_Modify_Attribute_Value_Display_Other_Type($All_Current_User_Values, $All_Committed_Modify_Entries, $current_email, $current_attribute_name, $all_values) {
            $HTML_value_block = '';

            foreach ($all_values as $key => $checkbox_value) {

                if(isset($All_Committed_Modify_Entries[$current_email]) && isset($All_Committed_Modify_Entries[$current_email][$current_attribute_name]) {

                    if(in_array($checkbox_value, $All_Committed_Modify_Entries[$current_email][$current_attribute_name]) ) {
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
                        if($attribute_list[$this_attribute_name]['type'] == 'checkboxgroup') {
                            $this_attribute_value = implode(',', $this_attribute_value);
                        }
                        //need a way for 'STICKY' attributes
                        saveUserAttribute($new_user_id, $attribute_list[$this_attribute_name]['id'], $this_attribute_value);
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
                            $this_attribute_value = implode(',', $this_attribute_value);
                        }
                        //need a way for 'STICKY' attributes
                        saveUserAttribute($new_user_id, $attribute_list[$this_attribute_name]['id'], $this_attribute_value);
                    }
                }
            }
        }
        function Get_Current_User_Attribute_Values($Current_Value_List, $email_key, $set_attribute_list) {
            $current_user_query = sprintf('select id from %s where email = "%s"', $GLOBALS['tables']['user'], $email_key);
            $current_user_sql_result = Sql_Fetch_Row_Query($current_user_query);
            if(!isset($current_user_sql_result[0])) {
                return false;
            }
            if(!isset($Current_Value_List[$email_key])) {
                $Current_Value_List[$email_key] = array();
            }
            foreach ($set_attribute_list as $attribute_name => $attribute_array) {
                 
                $Current_User_Attribute_Values_Query = sprintf("select value from %s where attributeid = %d and userid = %d", $GLOBALS['tables']['user_attribute'], $attribute_array['id'], $user_result['id']);
                $current_attribute_return = Sql_Fetch_Row_Query($current_attribute_return);
                if($current_attribute_return){
                    if($attribute_array['type'] == 'checkboxgroup') {
                        $exploded_current_values_ids = explode(',', $current_attribute_return);
                        $Current_Value_List[$email_key][$attribute_name] = array();
                        foreach ($exploded_current_values_ids as $key => $attribute_value_id) {
                            $attribute_value_from_id_query = sprintf("select name from %s where id = %d", $attribute_array['tablename'], $attribute_value_id);
                            $attribute_value_return = Sql_Fetch_Row_Query($attribute_value_from_id_query);
                            array_push($Current_Value_List[$email_key][$attribute_name], $attribute_value_return);
                         
                        }
                    }
                    else if($attribute_array['type'] == 'checkbox'|'select'|'radio') {
                        $attribute_value_from_id_query = sprintf("select name from %s where id = %d", $attribute_array['tablename'], $current_attribute_return);
                        $attribute_value_return = Sql_Fetch_Row_Query($attribute_value_from_id_query);
                        $Current_Value_List[$email_key][$attribute_name] = $attribute_value_return;
                    }
                    else if($attribute_array['type'] == 'textarea'|'textline') {
                        $Current_Value_List[$email_key][$attribute_name] = $current_attribute_return;
                    }
                    else if($attribute_array['type'] == 'Date') {
                        $Current_Value_List[$email_key][$attribute_name] = $current_attribute_return;
                    }
                }
            }
            return true;
        }
    }
 
// foreach ($Current_Modify_Entry_Block as $email_key => $modify_user_attributes_and_values) {
//                 //THIS SHOULD NEVER HAPPEN!!!!!!!
//                 if(!isset($Current_user_values[$email_key])) {
//                     Get_Current_User_Attribute_Values($Current_Users_Values, $email_key, $attribute_list);
//                 }
 
//                 if(isset($Commited_Modify_Entries[$email_key])) {
//                     $HTML_table_row = sprintf('<tr><td>%s<br><input type="checkbox" class="Modify_Entry_Email" name="Modify_Entry_List[%s][\'include\']" value="include" checked>Include This Email</input></td>',$email_key, $email_key);
//                 }
//                 else{
//                     $HTML_table_row = sprintf('<tr><td>%s<br><input type="checkbox" class="Modify_Entry_Email" name="Modify_Entry_List[%s][\'include\']" value="include">Include This Email</input></td>',$email_key, $email_key);
//                 }
                 
//                 foreach ($attribute_list as $attribute_name => $attribute_info) {
//                     $HTML_table_row = $HTML_table_row.'<td>';
                     
//                     if(isset($Current_user_values[$email_key]['attributes'][$attribute_name])) {
//                         if(isset($Commited_Modify_Entries[$email_key] && isset($Commited_Modify_Entries[$email_key][$attribute_name]))) {
//                             if($attribute_info['type'] == 'checkboxgroup') {
//                                 $selectedGroupValues = split(',', $Commited_Modify_Entires[$email_key][$attribute_name]);
//                                 $currentlySetGroupValues = split(',', $Current_user_values[$email_key]['attributes'][$attribute_name]);
//                                 foreach ($currentlySetGroupValues as $key => $group_attribute_value) {
//                                     //these are the current set values, should make green background or w/e
//                                     if(in_array($group_attribute_value, $selectedGroupValues)) {
//                                         $HTML_attribute_value_input = sprintf('<input type="checkbox" class="Current_Modify_Checkbox_Value_%s" name="Modify_Entry_List[%s][%s][%s]" value="%s" checked>%s</input><br>', $attribute_name, $email_key, $attribute_name, $group_attribute_value, $group_attribute_value, $group_attribute_value);
//                                     }
//                                     else {
//                                         $HTML_attribute_value_input = sprintf('<input type="checkbox" class="Current_Modify_Checkbox_Value_%s" name="Modify_Entry_List[%s][%s][%s]" value="%s">%s</input><br>', $attribute_name, $email_key, $attribute_name, $group_attribute_value, $group_attribute_value, $group_attribute_value);
//                                     }
//                                     $HTML_table_row = $HTML_table_row.$HTML_attribute_value_input;
//                                 }
//                             }
//                             else{
//                                 //will only be one currently set value
//                                 if($Current_user_values[$email_key]['attributes'][$attribute_name] == $Commited_Modify_Entries[$email_key][$attribute_name]) {
//                                     $HTML_attribute_value_input = sprintf('<input type="radio class="Current_Modify_Attribute_Value_%s" name="Modify_Entry_List[%s][%s]" value="%s" checked>%s</input><br>', $attribute_name, $email_key, $attribute_name, $Current_user_values[$email_key]['attributes'][$attribute_name], $Current_user_values[$email_key]['attributes'][$attribute_name]);
//                                 }
//                                 else {
//                                     $HTML_attribute_value_input = sprintf('<input type="radio" class="Current_Modify_Attribute_Value_%s" name="Modify_Entry_List[%s][%s]" value="%s">%s</input><br>', $attribute_name, $email_key, $attribute_name, $Current_user_values[$email_key]['attributes'][$attribute_name], $Current_user_values[$email_key]['attributes'][$attribute_name]);
//                                 }
//                                 $HTML_table_row = $HTML_table_row.$HTML_attribute_value_input;
//                             }
//                         }
//                         //no need to check this attribute for selected
//                         else {
//                             if($attribute_info['type'] == 'checkboxgroup') {
//                                 $currentlySetGroupValues = split(',', $Current_user_values[$email_key]['attributes'][$attribute_name]);
//                                 foreach ($currentlySetGroupValues as $key => $group_attribute_value) {
//                                     $HTML_attribute_value_input = sprintf('<input type="checkbox" class="Current_Modify_Checkbox_Value_%s" name="Modify_Entry_List[%s][%s][%s]" value="%s" checked>%s</input><br>', $attribute_name, $email_key, $attribute_name, $group_attribute_value, $group_attribute_value, $group_attribute_value);
//                                 }
//                                 $HTML_table_row = $HTML_table_row.$HTML_attribute_value_input;
//                             }
//                             else{
//                                 $HTML_attribute_value_input = sprintf('<input type="radio" class="Current_Modify_Attribute_Value_%s" name="Modify_Entry_List[%s][%s]" value="%s" checked>%s</input><br>', $attribute_name, $email_key, $attribute_name, $Current_user_values[$email_key]['attributes'][$attribute_name], $Current_user_values[$email_key]['attributes'][$attribute_name]);
//                                 $HTML_table_row = $HTML_table_row.$HTML_attribute_value_input;
//                             }
                             
//                         }
//                     }
//                     else{
//                     }
//                     $HTML_table_row = $HTML_table_row.'</td>';
//                 }
//                 $HTML_table_row = $HTML_table_row.'</tr>';
//                 $HTML_Display_Text = $HTML_Display_Text.$HTML_table_row;
//                 //now must check the modify choices
//                 $HTML_table_row = '<tr><td></td>'
//                 foreach ($attribute_list as $attribute_name => $attribute_info) {
//                     $HTML_table_row = $HTML_table_row.'<td>';
//                     if(!isset($Current_Modify_Entry_Block[$email_key][$attribute_name])) {
                         
//                     }
//                     else if(isset($Commited_Modify_Entries[$email_key] && isset($Commited_Modify_Entries[$email_key][$attribute_name]))) {
//                         if($attribute_info['type'] == 'checkboxgroup') {
//                             $selectedGroupValues = split(',', $Commited_Modify_Entires[$email_key][$attribute_name]);
//                             foreach ($Current_Modify_Entry_Block[$email_key][$attribute_name] as $key => $group_attribute_value) {
//                                 //these are the current set values, should make green background or w/e
//                                 if(in_array($group_attribute_value, $selectedGroupValues)) {
//                                     $HTML_attribute_value_input = sprintf('<input type="checkbox" class="Modify_Entry_Checkbox_Value_Attribute_%s" name="Modify_Entry_List[%s][%s][%s]" value="%s" checked>%s</input><br>', $attribute_name, $email_key, $attribute_name, $group_attribute_value, $group_attribute_value, $group_attribute_value);
//                                 }
//                                 else {
//                                     $HTML_attribute_value_input = sprintf('<input type="checkbox" class="Modify_Entry_Checkbox_Value_Attribute_%s" name="Modify_Entry_List[%s][%s][%s]" value="%s">%s</input><br>', $attribute_name, $email_key, $attribute_name, $group_attribute_value, $group_attribute_value, $group_attribute_value);
//                                 }
//                                 $HTML_table_row = $HTML_table_row.$HTML_attribute_value_input;
//                             }
//                         }
//                         //is some other type of input
//                         else{
//                             //will only be one currently set value
//                             foreach ($Current_Modify_Entry_Block[$email_key][$attribute_name] as $key => $attribute_value) {
//                                 if($key == 0) {
//                                     if(!isset($Current_user_values[$email_key]['attributes'][$attribute_name])) {
//                                         if($Current_Modify_Entry_Block[$email_key][$attribute_name] == $Commited_Modify_Entries[$email_key][$attribute_name]) {
//                                             $HTML_attribute_value_input = sprintf('<input type="radio" class="Modify_Entry_Safe_Value_Attribute_%s" name="Modify_Entry_List[%s][%s]" value="%s" checked>%s</input><br>', $attribute_name, $email_key, $attribute_name, $Current_Modify_Entry_Block[$email_key][$attribute_name], $Current_Modify_Entry_Block[$email_key][$attribute_name]);
//                                         }
//                                         else {
//                                             $HTML_attribute_value_input = sprintf('<input type="radio" class="MOdify_Entry_Safe_Value_Attribute_%s" name="Modify_Entry_List[%s][%s]" value="%s">%s</input><br>', $attribute_name, $email_key, $attribute_name, $Current_Modify_Entry_Block[$email_key][$attribute_name], $Current_Modify_Entry_Block[$email_key][$attribute_name]);
//                                         }
//                                     }
//                                     else {
//                                         if($Current_Modify_Entry_Block[$email_key][$attribute_name] == $Commited_Modify_Entries[$email_key][$attribute_name]) {
//                                             $HTML_attribute_value_input = sprintf('<input type="radio" name="Modify_Entry_List[%s][%s]" value="%s" checked>%s</input><br>', $email_key, $attribute_name, $Current_Modify_Entry_Block[$email_key][$attribute_name], $Current_Modify_Entry_Block[$email_key][$attribute_name]);
//                                         }
//                                         else {
//                                             $HTML_attribute_value_input = sprintf('<input type="radio" name="Modify_Entry_List[%s][%s]" value="%s">%s</input><br>', $email_key, $attribute_name, $Current_Modify_Entry_Block[$email_key][$attribute_name], $Current_Modify_Entry_Block[$email_key][$attribute_name]);
//                                         }
//                                     }               
//                                 }
//                                 else {
//                                     if($Current_Modify_Entry_Block[$email_key][$attribute_name] == $Commited_Modify_Entries[$email_key][$attribute_name]) {
//                                         $HTML_attribute_value_input = sprintf('<input type="radio" name="Modify_Entry_List[%s][%s]" value="%s" checked>%s</input><br>', $email_key, $attribute_name, $Current_Modify_Entry_Block[$email_key][$attribute_name], $Current_Modify_Entry_Block[$email_key][$attribute_name]);
//                                     }
//                                     else {
//                                         $HTML_attribute_value_input = sprintf('<input type="radio" name="Modify_Entry_List[%s][%s]" value="%s">%s</input><br>', $email_key, $attribute_name, $Current_Modify_Entry_Block[$email_key][$attribute_name], $Current_Modify_Entry_Block[$email_key][$attribute_name]);
//                                     }
//                                 }
//                                 $HTML_table_row = $HTML_table_row.$HTML_attribute_value_input;
//                             }
//                         }
//                     }
//                     else{
//                         if($attribute_info['type'] == 'checkboxgroup') {
//                             foreach ($Current_Modify_Entry_Block[$email_key][$attribute_name] as $key => $attribute_value) {
//                                 $HTML_attribute_value_input = sprintf('<input type="checkbox" class="Modify_Entry_Checkbox_Value_Attribute_%s" name="Modify_Entry_List[%s][%s][%s]" value="%s">%s</input><br>', $attribute_name, $email_key, $attribute_name, $attribute_value, $attribute_value, $attribute_value);                              
//                                 $HTML_table_row = $HTML_table_row.$HTML_attribute_value_input;
//                             }
                                 
                             
//                         }
//                         else{
//                             foreach ($Current_Modify_Entry_Block[$email_key][$attribute_name] as $key => $attribute_value) {
//                                 if($key == 0) {
//                                     if(!isset($Current_user_values[$email_key]['attributes'][$attribute_name])) {
//                                         $HTML_attribute_value_input = sprintf('<input type="radio" class="Modify_Entry_Safe_Value_Attribute_%s" name="Modify_Entry_List[%s][%s]" value="%s">%s</input><br>', $attribute_name, $email_key, $attribute_name, $Current_Modify_Entry_Block[$email_key][$attribute_name], $Current_Modify_Entry_Block[$email_key][$attribute_name]);
//                                     }
//                                     else {
//                                         $HTML_attribute_value_input = sprintf('<input type="radio" name="Modify_Entry_List[%s][%s]" value="%s">%s</input><br>', $attribute_name, $email_key, $attribute_name, $Current_Modify_Entry_Block[$email_key][$attribute_name], $Current_Modify_Entry_Block[$email_key][$attribute_name]);
//                                     }
//                                 }
//                                 else{
//                                     $HTML_attribute_value_input = sprintf('<input type="radio" name="Modify_Entry_List[%s][%s]" value="%s">%s</input><br>', $attribute_name, $email_key, $attribute_name, $Current_Modify_Entry_Block[$email_key][$attribute_name], $Current_Modify_Entry_Block[$email_key][$attribute_name]);
//                                 }
//                                 $HTML_table_row = $HTML_table_row.$HTML_attribute_value_input;
//                             }
//                         }
//                     }
//                     $HTML_table_row = $HTML_table_row.'</td>';
//                 }
//                 $HTML_Display_Text = $HTML_Display_Text.$HTML_table_row.'</tr>';
                 
//             }

?>