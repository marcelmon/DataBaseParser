
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