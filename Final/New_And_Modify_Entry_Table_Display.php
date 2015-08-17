<?php 

require_once(dirname(__FILE__).'Attribute_Changer_Plugin.php');

$attribute_changer = $GLOBALS['plugins']['Attribute_Changer_Plugin'];


if($attribute_changer->$Current_Session == null) {
    print("ERRORROROR");
}

        $Session = $attribute_changer->$Current_Session;



        //either 10, 100, 1000, 10000, all
        //default 100
        $Session->Current_New_Entries_Display_Amount;
        $Session->$New_Entries_Total_Amount;
        $Session->$New_Entires_Number_Of_Blocks;
        $Session->$Current_New_Entry_Block_Number;


        $Current_New_Entry_Block;
 
        function Initialize_New_Entries_Display() {
            if(count($Session->$New_Entry_List) == 0) {
                return null;
            }

            $Session->$Commited_New_Entires = array();

            $Session->Current_New_Entries_Display_Amount = 100;
            $Session->$New_Entries_Total_Amount = count($Session->$New_Entry_List);
            $Session->$New_Entires_Number_Of_Blocks = $Session->$New_Entries_Total_Amount/$Session->Current_New_Entries_Display_Amount + (($Session->$New_Entries_Total_Amount % $Session->Current_New_Entries_Display_Amount)? 1:0);
         
            $Session->$Current_New_Entry_Block_Number = 0;
            return true;
             
        }
        function New_Entry_Change_Display_Amount($New_Amount) {
            if($New_Amount !== (10|100|1000|10000|'all')) {
                return false;
            }
            if($New_Amount === 'all') {
                $Session->$New_Entires_Number_Of_Blocks =1;
                $Session->Current_New_Entries_Display_Amount = $Session->$New_Entries_Total_Amount;
                $Session->$Current_New_Entry_Block_Number = 0;
                return true;
            }
            $Session->Current_New_Entries_Display_Amount = $New_Amount;
            $Session->$New_Entires_Number_Of_Blocks = $Session->$New_Entries_Total_Amount/$Session->Current_New_Entries_Display_Amount + (($Session->$New_Entries_Total_Amount % $Session->Current_New_Entries_Display_Amount)? 1:0);
            $Session->$Current_New_Entry_Block_Number = 0;
            return true;
        }
        function New_Entry_Display_Next_Page() {
            if($Session->$Current_New_Entry_Block_Number < $Session->$New_Entires_Number_Of_Blocks-1) {
                $Session->$Current_New_Entry_Block_Number++;
                return Get_Modify_Entry_Table_Block();
            }
            else{
                //because there are no more blocks
                return false;
            }
        }
        function New_Entry_Display_Previous_Page() {
            if($Session->$Current_New_Entry_Block_Number > 0) {
                $Session->$Current_New_Entry_Block_Number--;
                return Get_Modify_Entry_Table_Block();
            }
            else{
                //because there are no more blocks
                return false;
            }
        }


        function Get_New_Entry_Table_Block() {
            
            $Current_New_Entry_Block = array_slice($Session->$New_Entry_List, $Session->$Current_New_Entry_Block_Number*$Session->Current_New_Entries_Display_Amount, $Session->Current_New_Entries_Display_Amount);

            $HTML_Display_Text = sprintf('<form name="New_Entry_Submit_Form_Block__%d" action="%s" method="post"><input type="hidden" name="New_Entry_Form_Submitted" value="submitted">', $Session->$Current_New_Entry_Block_Number, 'self');
            $HTML_Display_Text = $HTML_Display_Text.sprintf('<table id="New_User_Attribute_Select_Table_Block__%d">', $Session->$Current_New_Entry_Block_Number);
            $HTML_table_row = sprintf('<tr><td>EMAIL<br><input type="button" id="New_Entry_Include_All_Emails" name="New_Entry_Include_All_Emails" value="Include All Emails" onClick="checkAll_NewEntry_Emails()"></input>');
            $HTML_table_row = $HTML_table_row.sprintf('<input type="button" id="New_Entry_Remove_All_Emails" name="New_Entry_Include_Remove_Emails" value="Remove All Emails" onClick="removeAll_NewEntry_Emails()"></input></td>');

            foreach ($Session->$attribute_list as $attribute_name => $attribute_info) {
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
                if(isset($Session->$Commited_New_Entires[$email_key]) {
                    $HTML_table_row = sprintf('<tr><td>%s<br><input type="checkbox" class="New_Entry_Email" name=$Session->"New_Entry_List[%s][\'include\']" value="include" checked>Include This Email</input><input type="hidden" name="Hidden$Session->_New_Entry_List[%s]" value="submitted"></td>',$email_key, $email_key, $email_key);
                }
                else{
                    $HTML_table_row = sprintf('<tr><td>%s<br><input type="checkbox" class="New_Entry_Email" name=$Session->"New_Entry_List[%s][\'include\']" value="include">Include This Email</input><input type="hidden" name="Hidden$Session->_New_Entry_List[%s]" value="submitted"></td>',$email_key, $email_key, $email_key);
                }
                //commited_new_entries[email]: attribute,value
                foreach ($Session->$attribute_list as $attribute_name => $attribute_info) {
                    if(!isset($new_user_attributes_and_values[$attribute_name])) {
                        $HTML_table_row = $HTML_table_row.'<td></td>';
                    }
                    else {
                        $HTML_table_row= $HTML_table_row.'<td>';

                        foreach ($new_user_attributes_and_values[$attribute_name] as $key => $attribute_value) {

                            switch($attribute_info['type']){

                                case "textarea"|"textline"|"checkbox"|"hidden"|"date": 
                                    if(isset($Session->$Commited_New_Entires[$email_key] && isset($Session->$Commited_New_Entires[$email_key][$attribute_name]) && $Session->$Commited_New_Entires[$email_key][$attribute_name] === $attribute_value)) {
                                        //if the attribute value is the already selected, mark as checked
                                        if($key == 0) {
                                            $HTML_attribute_value_input = sprintf('<input type="radio" class="New_Entry_Safe_Value_Attribute_%s" name=$Session->"New_Entry_List[%s][%s]" value="%s" checked>%s</input>', $attribute_name, $email_key, $attribute_name, $attribute_value, $attribute_value);
                                        }
                                        else{
                                            $HTML_attribute_value_input = sprintf('<input type="radio" class="New_Entry_Attribute_%s" name=$Session->"New_Entry_List[%s][%s]" value="%s" checked>%s</input>', $email_key, $attribute_name, $attribute_value, $attribute_value);
                                        }
                                         
                                    }
                                    else{
                                        //else not yet selected so just create the input
                                        if($key == 0) {
                                            $HTML_attribute_value_input = sprintf('<input type="radio" class="New_Entry_Safe_Value_Attribute_%s" name=$Session->"New_Entry_List[%s][%s]" value="%s">%s</input>', $attribute_name, $email_key, $attribute_name, $attribute_value, $attribute_value);
                                        }
                                        else{
                                            $HTML_attribute_value_input = sprintf('<input type="radio" name=$Session->"New_Entry_List[%s][%s]" value="%s">%s</input>', $email_key, $attribute_name, $attribute_value, $attribute_value);
                                        }
                                    }
                                    $HTML_table_row= $HTML_table_row.$HTML_attribute_value_input.'<br>';
                                    break;
                                 
                                case "checkboxgroup": 
                                    if(isset($Session->$Commited_New_Entires[$email_key]) && isset($Session->$Commited_New_Entires[$email_key][$attribute_name]) && in_array($attribute_value, $Session->$Commited_New_Entires[$email_key][$attribute_name])) {
                                        //the current attribute value should already be checked
                                        $HTML_attribute_value_input = sprintf('<input type="checkbox" class="New_Entry_Checkbox_Value_Attribute_%s" name=$Session->"New_Entry_List[%s][%s][%s]" value="%s" checked>%s</input><br>', $attribute_name, $email_key, $attribute_name, $attribute_value, $attribute_value, $attribute_value);
                                    }
                                    else{
                                        //not already checked
                                        $HTML_attribute_value_input = sprintf('<input type="checkbox" class="New_Entry_Checkbox_Value_Attribute_%s" name=$Session->"New_Entry_List[%s][%s][%s]" value="%s">%s</input><br>', $attribute_name, $email_key, $attribute_name, $attribute_value, $attribute_value, $attribute_value);
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
            if($Session->$Current_New_Entry_Block_Number > 0) {
                $HTML_submit_buttons = $HTML_submit_buttons.'<input type="submit" name="value="New_Entries_Table_Previous_Page" value="New_Entries_Table_Previous_Page"></input>';
            }
            if($Session->$Current_New_Entry_Block_Number < $Session->$New_Entires_Number_Of_Blocks - 1) {
                $HTML_submit_buttons = $HTML_submit_buttons.'<input type="submit" name="New_Entries_Table_Next_page" value="New_Entries_Table_Next_page"></input>';
            }
            switch($Session->Current_New_Entries_Display_Amount){
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
            $HTML_current_table_info = sprintf("<div>Current Block : %d of %d. Displaying %d entires per page.</div>", $Session->$Current_New_Entry_Block_Number+1, $Session->$New_Entires_Number_Of_Blocks, $Session->Current_New_Entries_Display_Amount);
            $HTML_Display_Text = $HTML_Display_Text.$HTML_current_table_info;
            return $HTML_Display_Text;
        }

        $Session->$Current_Modify_Entries_Display_Amount;
        $Session->$Modify_Enties_Total_Amount;
        $Session->$Modify_Entires_Number_Of_Blocks;
        $session->$Current_Modify_Entry_Block_Number;


        $Current_Modify_Entry_Block;
 
        function Initialize_Modify_Entries_Display() {
            if(count($Modify_Entry_List == 0)) {
                return null;
            }

            $Session->$Commited_Modify_Entries = array();

            $Session->$Current_Modify_Entries_Display_Amount = 100;
            $Session->$Modify_Enties_Total_Amount = count($Modify_Entry_List);
            $Session->$Modify_Entires_Number_Of_Blocks = $Session->$Modify_Enties_Total_Amount/$Session->$Current_Modify_Entries_Display_Amount + (($Session->$Current_Modify_Entries_Display_Amount % $Session->$Modify_Enties_Total_Amount)? 1:0);
         
            $session->$Current_Modify_Entry_Block_Number = 0;
            return true;
             
        }   
        function Modify_Entry_Display_Next_Page() {
            if($session->$Current_Modify_Entry_Block_Number < $Session->$Modify_Entires_Number_Of_Blocks-1) {
                $session->$Current_Modify_Entry_Block_Number++;
                return Get_Modify_Entry_Table_Block($session->$Current_Modify_Entry_Block_Number);
            }
            else{
                //because there are no more blocks
                return false;
            }
        }
        function Modify_Entry_Display_Previous_Page() {
            if($session->$Current_Modify_Entry_Block_Number > 0) {
                $session->$Current_Modify_Entry_Block_Number--;
                return Get_Modify_Entry_Table_Block($session->$Current_Modify_Entry_Block_Number);
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
                $Session->$New_Entires_Number_Of_Blocks =1;
                $Session->$Current_Modify_Entries_Display_Amount = $Session->$Modify_Enties_Total_Amount;
                $session->$Current_Modify_Entry_Block_Number = 0;
                return true;
            }
            $Session->$Current_Modify_Entries_Display_Amount = $New_Amount;
            $Session->$Modify_Entires_Number_Of_Blocks = $Session->$Modify_Enties_Total_Amount/$Session->$Current_Modify_Entries_Display_Amount + (($Session->$Current_Modify_Entries_Display_Amount % $Session->$Modify_Enties_Total_Amount)? 1:0);
            $Session->$Current_New_Entry_Block_Number = 0;
            return true;
        }
        function Get_Modify_Entry_Table_Block() {
            $Current_Modify_Entry_Block = array_slice($Modify_Entry_List, $session->$Current_Modify_Entry_Block_Number*$Session->$Current_Modify_Entries_Display_Amount, $Session->$Current_Modify_Entries_Display_Amount);
            $HTML_Display_Text = sprintf('<form name="Modify_Entry_Submit_Form_Block__%d" action="%s" method="post"><input type="hidden" name="Modify_Entry_Form_Submitted" value="submitted">', $session->$Current_Modify_Entry_Block_Number, 'self');
            $HTML_Display_Text = $HTML_Display_Text.sprintf('<table id="Modify_User_Attribute_Select_Table_Block__%d">', $session->$Current_Modify_Entry_Block_Number);

            $HTML_table_row = sprintf('<tr><td>EMAIL<br><input type="button" id="Modify_Entry_Include_All_Emails" name="Modify_Entry_Include_All_Emails" value="Include All Emails" onClick="checkAll_ModifyEntry_Emails()"></input>');
            $HTML_table_row = $HTML_table_row.sprintf('<input type="button" id="Modify_Entry_Remove_All_Emails" name="Modify_Entry_Remove_All_Emails" value="Remove All Emails" onClick="removeAll_ModifyEntry_Emails()"></input></td>');

            foreach ($Session->$attribute_list as $attribute_name => $attribute_info) {
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
                if(!isset($Session->$Current_user_values[$email_key])) {
                    Get_Current_User_Attribute_Values();
                }
                 
                if(isset($Commited_Modify_Entries[$email_key])) {
                    $HTML_table_row = sprintf('<tr><td>%s<br><input type="checkbox" class="Modify_Entry_Email" name="Modify_Entry_List[%s][\'include\']" value="include" checked>Include This Email</input><input type="hidden" name="Hidden_Modify_Entry_List[%s]" value="submitted"></td>',$email_key, $email_key, $email_key);
                }
                else{
                    $HTML_table_row = sprintf('<tr><td>%s<br><input type="checkbox" class="Modify_Entry_Email" name="Modify_Entry_List[%s][\'include\']" value="include">Include This Email</input><input type="hidden" name="Hidden_Modify_Entry_List[%s]" value="submitted"></td>',$email_key, $email_key, $email_key);
                }
 
                //first foreach is for current set vals
                foreach ($Session->$attribute_list as $attribute_name => $attribute_info) {
                    $HTML_table_row = $HTML_table_row.'<td>';
 
                    if(!isset($Session->$Current_user_values[$email_key]['attributes'][$attribute_name]) {
                        $HTML_table_row = $HTML_table_row.'</td>';
                    }
                    else {
 
                        if($Session->$attribute_list[$attribute_name]['type'] === 'checkboxgroup') {
 
                            foreach ($Session->$Current_user_values[$email_key]['attributes'][$attribute_name] as $key => $current_single_value) {
 
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
                                if($Session->$Current_user_values[$email_key]['attributes'][$attribute_name] === $Commited_Modify_Entries[$email_key][$attribute_name]) {
                                    $HTML_attribute_value_input = sprintf('<input type="radio class="Current_Modify_Attribute_Value_%s" name="Modify_Entry_List[%s][%s]" value="%s" checked>%s</input><br>', $attribute_name, $email_key, $attribute_name, $Session->$Current_user_values[$email_key]['attributes'][$attribute_name], $Session->$Current_user_values[$email_key]['attributes'][$attribute_name]);                             }
                                else {
                                    $HTML_attribute_value_input = sprintf('<input type="checkbox" class="Current_Modify_Checkbox_Value_%s" name="Modify_Entry_List[%s][%s][%s]" value="%s">%s</input><br>', $attribute_name, $email_key, $attribute_name, $$Session->$Current_user_values[$email_key]['attributes'][$attribute_name], $$Session->$Current_user_values[$email_key]['attributes'][$attribute_name], $$Session->$Current_user_values[$email_key]['attributes'][$attribute_name]);
                                }
                            }
                            else{
                                $HTML_attribute_value_input = sprintf('<input type="checkbox" class="Current_Modify_Checkbox_Value_%s" name="Modify_Entry_List[%s][%s][%s]" value="%s">%s</input><br>', $attribute_name, $email_key, $attribute_name, $$Session->$Current_user_values[$email_key]['attributes'][$attribute_name], $$Session->$Current_user_values[$email_key]['attributes'][$attribute_name], $$Session->$Current_user_values[$email_key]['attributes'][$attribute_name]);
                            }
                            $HTML_table_row = $HTML_table_row.$HTML_attribute_value_input;
                        }
                        $HTML_table_row = $HTML_table_row.'</td>';
                    }
                     
                }
                $HTML_table_row = $HTML_table_row.'</tr>';
                $HTML_Display_Text = $HTML_Display_Text.$HTML_table_row;
 
                $HTML_table_row = '<tr><td></td>';
 
                foreach ($Session->$attribute_list as $attribute_name => $attribute_info) {
 
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
            if($Session->$Current_New_Entry_Block_Number > 0) {
                $HTML_submit_buttons = $HTML_submit_buttons.'<input type="submit" name ="Modify_Entries_Table_Previous_Page" value="Modify_Entries_Table_Previous_Page"></input>';
            }
            if($Session->$Current_New_Entry_Block_Number < $Session->$New_Entires_Number_Of_Blocks - 1) {
                $HTML_submit_buttons = $HTML_submit_buttons.'<input type="submit" name ="Modify_Entries_Table_Next_Page" value="Modify_Entries_Table_Next_Page"></input>';
            }
            switch($Session->$Current_Modify_Entries_Display_Amount){
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
            $HTML_current_table_info = sprintf("<div>Current Block : %d of %d. Displaying %d entires per page.</div>", $session->$Current_Modify_Entry_Block_Number+1, $Session->$Modify_Entires_Number_Of_Blocks, $Session->$Current_Modify_Entries_Display_Amount);
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

        function Get_Modify_Attribute_Value_Display_Other_Type(&$All$Session->_Current_User_Values, &$All_Committed_Modify_Entries, $current_email, $current_attribute_name, $all_values) {
            $HTML_value_block = '';

            foreach ($all_values as $key => $checkbox_value) {

                if(isset($All_Committed_Modify_Entries[$current_email]) && isset($All_Committed_Modify_Entries[$current_email][$current_attribute_name]) {

                    if($checkbox_value === $All_Committed_Modify_Entries[$current_email][$current_attribute_name]) ) {
                        if($key == 0) && !isset($All$Session->_Current_User_Values[$current_email][$current_attribute_name])) {
                            $HTML_attribute_value_input = sprintf('<input type="radio" class="Modify_Entry_Safe_Value_Attribute_%s" name="Modify_Entry_List[%s][%s][%s]" value="%s" checked>%s</input><br>', $current_attribute_name, $current_email, $current_attribute_name, $checkbox_value, $checkbox_value, $checkbox_value);
                        }
                        else{
                            $HTML_attribute_value_input = sprintf('<input type="radio" name="Modify_Entry_List[%s][%s][%s]" value="%s" checked>%s</input><br>', $current_email, $current_attribute_name, $checkbox_value, $checkbox_value, $checkbox_value);
                        }

                    }
                    else{
                        if($key == 0 && !isset($All$Session->_Current_User_Values[$current_email][$current_attribute_name])) {
                            $HTML_attribute_value_input = sprintf('<input type="radio" class="Modify_Entry_Safe_Value_Attribute_%s" name="Modify_Entry_List[%s][%s][%s]" value="%s">%s</input><br>', $current_attribute_name, $current_email, $current_attribute_name, $checkbox_value, $checkbox_value, $checkbox_value);
                        }
                        else{
                            $HTML_attribute_value_input = sprintf('<input type="radio" name="Modify_Entry_List[%s][%s][%s]" value="%s">%s</input><br>', $current_email, $current_attribute_name, $checkbox_value, $checkbox_value, $checkbox_value);
                        }                                   
                    }

                }
                else{
                    if($key == 0 && !isset($All$Session->_Current_User_Values[$current_email][$current_attribute_name])) {
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
            foreach ($Session->$Commited_New_Entires as $email_key => $new_attributes_and_values) {
                $exists = Sql_Fetch_Row_Query(sprintf('select id from %s where email = "%s"', $GLOBALS['tables']['user'],$email_key));
                if($exists) {
                    $Failed_New_Entries[$email_key] = $new_attributes_and_values;
                }
                else{
                    $new_user_id = addNewUser($email_key);
                    foreach ($new_attributes_and_values as $this_attribute_name => $this_attribute_value) {
                        if($Session->$attribute_list[$this_attribute_name]['type'] === 'checkboxgroup') {
                            $new_attribute_value_ids = array();

                            foreach ($this_attribute_value as $this_key => $attribute_new_value) {
                                array_push($new_attribute_value_ids, $attribute_value_ids[$attribute_new_value]);
                            }

                            $proper_this_attribute_value = implode(',', $new_attribute_value_ids);
                        }
                        else{
                            if($Session->$attribute_list[$this_attribute_name]['type'] === 'checkbox'|'radio') {
                                $proper_this_attribute_value = $attribute_value_ids[$this_attribute_value];
                            }
                            else{
                                $proper_this_attribute_value = $this_attribute_value;
                            }
                        }
                        //need a way for 'STICKY' attributes
                        saveUserAttribute($new_user_id, $Session->$attribute_list[$this_attribute_name]['id'], $proper_this_attribute_value);
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
                        if($Session->$attribute_list[$this_attribute_name]['type'] == 'checkboxgroup') {
                            $modify_attribute_value_ids = array();

                            foreach ($this_attribute_value as $this_key => $attribute_new_value) {
                                array_push($modify_attribute_value_ids, $attribute_value_ids[$attribute_new_value]);
                            }

                            $proper_this_attribute_value = implode(',', $modify_attribute_value_ids);
                        }
                        else{
                            if($Session->$attribute_list[$this_attribute_name]['type'] === 'checkbox'|'radio') {
                                $proper_this_attribute_value = $attribute_value_ids[$this_attribute_value];
                            }
                            else{
                                $proper_this_attribute_value = $this_attribute_value;
                            }
                        }
                        //need a way for 'STICKY' attributes
                        saveUserAttribute($modify_user_id, $Session->$attribute_list[$this_attribute_name]['id'], $proper_this_attribute_value);
                    }
                }
            }
        }

        ?>