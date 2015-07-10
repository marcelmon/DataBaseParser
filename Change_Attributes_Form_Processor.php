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
     
    if(isset($_POST['New_Entry_Attribute_Column_Select'])) {
 
        $Columns_To_Accept = array();
 
        while($_POST['New_Entry_Attribute_Column_Select']) {
            $Attribute_To_Parse = array_shift($_POST['New_Entry_Attribute_Column_Select']);
 
            if(array_key_exists($Attribute_To_Parse, $attribute_list)) {
                array_push($Columns_To_Accept, $Attribute_To_Parse);
            }
        }
        if(count($Columns_To_Accept) == 0) {
            //change nothing or set to current set 
            return;
        }
        if(isset($_POST['New_Entry_List'])) {
            $New_Entry_List = $_POST['New_Entry_List'];
 
            foreach ($New_Entry_List as $email_key => $attribute_values) {
                if(isset($attribute_values['include']) {
                    if($attribute_values['include'] == 'include') {
 
                        array_shift($attribute_values);
                        $new_entry_to_commit = array();
 
                        foreach ($attribute_values as $attribute_name => $value) {
                            if(in_array($attribute_name, $Columns_To_Accept)) {
                                if(is_array($value)) {
                                    if($attribute_list[$attribute_name]['type'] == 'checkboxgroup') {
                                        foreach ($value as $key => $current_value) {
                                            if(in_array($current_value, $attribute_list[$attribute_name]['allowed_values'])) {
                                                array_push($new_entry_to_commit[$attribute_name], $current_value); 
                                            }
                                        }
                                    }
                                    else{
                                        //only the checkbox group can be an array
                                    }
                                }
                                else{
                                    if($attribute_list[$attribute_name]['type'] == 'checkboxgroup'|"checkbox") {
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
                        $Commited_New_Entires[$email_key] = $new_entry_to_commit;
                    }
                }
                else{
                    //skip this email , not included
                }
            }
        }
    }
 
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
            return;
        }
        if(isset($_POST['Modify_Entry_List'])) {
            $Modify_Entry_List = $_POST['Modify_Entry_List'];
 
            foreach ($Modify_Entry_List as $email_key => $attribute_values) {
                if(isset($attribute_values['include']) {
                    if($attribute_values['include'] == 'include') {
 
                        array_shift($attribute_values);
                        $modify_entry_to_commit = array();
 
                        foreach ($attribute_values as $attribute_name => $value) {
                            if(in_array($attribute_name, $Columns_To_Accept)) {
                                if(is_array($value)) {
                                    if($attribute_list[$attribute_name]['type'] == 'checkboxgroup') {
                                        foreach ($value as $key => $current_value) {
                                            if(in_array($current_value, $attribute_list[$attribute_name]['allowed_values'])) {
                                                array_push($modify_entry_to_commit[$attribute_name], $current_value); 
                                            }
                                        }
                                    }
                                    else{
                                        //only the checkbox group can be an array
                                    }
                                }
                                else{
                                    if($attribute_list[$attribute_name]['type'] == 'checkboxgroup'|"checkbox") {
                                        if(in_array($value, $attribute_list[$attribute_name]['allowed_values'])) {
                                            $modify_entry_to_commit[$attribute_name]=$value; 
                                        }
                                    }
                                    else if($attribute_list[$attribute_name]['type'] == "textarea"|"textline"|"hidden"|"date") {
                                        $modify_entry_to_commit[$attribute_name]=$value; 
                                    }
                                    else{
 
                                    }
                                }
                                 
                            }
                            else{
                                //is not a currently acccepted column
                            }
                        }
                        $Commited_Modify_Entires[$email_key] = $new_entry_to_commit;
                    }
                }
                else{
                    //skip this email , not included
                }
            }

        }
    }   

    if(isset($_POST['New_Entries_Table_Submit_All']) && $_POST['New_Entries_Table_Submit_All'] == 'New_Entries_Table_Submit_All' ) {
        Initialize_Modify_Entries_Display();
        $HTML_TO_DISPLAY = Get_New_Entry_Table_Block(0);
        //HERE NEED TO SETUP HEADERS TO PRINT

    }

    if(isset($_Post['New_Entries_Table_Next_Page']) && $_Post['New_Entries_Table_Next_Page'] == 'New_Entries_Table_Next_Page') {
        $HTML_TO_DISPLAY = New_Entry_Display_Next_Page();
        if($HTML_TO_DISPLAY == false) {
            $HTML_TO_DISPLAY = Get_New_Entry_Table_Block(NEED TO GET CURRENT BLOCK CHANGE THIS FUNC);
        }
           
        //headers
    }

    if(isset($_Post['New_Entries_Table_Previous_Page']) && $_Post['New_Entries_Table_Previous_Page'] == 'New_Entries_Table_Previous_Page') {
        $HTML_TO_DISPLAY = New_Entry_Display_Previous_Page();
        if($HTML_TO_DISPLAY == false) {
            $HTML_TO_DISPLAY = Get_New_Entry_Table_Block(NEED TO GET CURRENT BLOCK CHANGE THIS FUNC);
        }
        //headers
    }
 
    if(isset($_Post['New_Entry_Change_Display_Amount']) && $_Post['New_Entry_Change_Display_Amount'] == 'New_Entry_Change_Display_Amount') {

        if(isset($_POST['New_Entries_New_Display_Amount'])) {
            if($_POST['New_Entries_New_Display_Amount'] != (10|100|1000|10000)){

            }
            else{
                if(New_Entry_Change_Display_Amount($_POST['New_Entries_New_Display_Amount']) != true) {

                }
                else{
                    

                }
            }
        }
        $HTML_TO_DISPLAY = Get_New_Entry_Table_Block();
        
    }



    if(isset($_POST['Modify_Entries_Table_Submit_All']) && $_POST['Modify_Entries_Table_Submit_All'] == 'Modify_Entries_Table_Submit_All' ) {

        //NEED TO ACTUALLY PROCESS ALL THE NEW VALUES
    }

    if(isset($_Post['Modify_Entries_Table_Next_Page']) && $_Post['Modify_Entries_Table_Next_Page'] == 'Modify_Entries_Table_Next_Page') {
        $HTML_TO_DISPLAY = Modify_Entry_Display_Next_Page();
        if($HTML_TO_DISPLAY == false) {
            $HTML_TO_DISPLAY = Get_Modify_Entry_Table_Block(NEED TO GET CURRENT BLOCK CHANGE THIS FUNC);
        }
           
        //headers
    }

    if(isset($_Post['Modify_Entries_Table_Previous_Page']) && $_Post['Modify_Entries_Table_Previous_Page'] == 'Modify_Entries_Table_Previous_Page') {
        $HTML_TO_DISPLAY = Modify_Entry_Display_Previous_Page();
        if($HTML_TO_DISPLAY == false) {
            $HTML_TO_DISPLAY = Get_Modify_Entry_Table_Block(NEED TO GET CURRENT BLOCK CHANGE THIS FUNC);
        }
        //headers
    }
 
    if(isset($_Post['Modify_Entry_Change_Display_Amount']) && $_Post['Modify_Entry_Change_Display_Amount'] == 'Modify_Entry_Change_Display_Amount') {

        if(isset($_POST['Modify_Entries_New_Display_Amount'])) {
            if($_POST['Modify_Entries_New_Display_Amount'] != (10|100|1000|10000)){

            }
            else{
                if(Modify_Entry_Change_Display_Amount($_POST['Modify_Entries_New_Display_Amount']) != true) {

                }
                else{
                    

                }
            }
        }
        $HTML_TO_DISPLAY = Get_Modify_Entry_Table_Block();
        
    }
 
?>