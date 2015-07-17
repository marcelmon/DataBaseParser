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

	}
	function removeAll_NewEntry_SafeValues_OrChecked(attribute) {

	}


</script>
