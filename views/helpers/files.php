<?php
/**
* Stub class to use basic form file inputs
*/
class FilesHelper extends AppHelper {
	var $helpers = array('Form');

	function input($fieldName, $options, $type) {
		$options['type'] = 'file';
		return $this->Form->input($fieldName, $options);
	}

}
?>