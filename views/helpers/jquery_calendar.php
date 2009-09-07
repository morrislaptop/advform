<?php
class JqueryCalendarHelper extends AppHelper
{
	var $helpers = array('Form', 'Javascript', 'Html');
	var $embedded = false;

	function input($fieldName, $options) {
		$this->embed();
		$options['type'] = 'text';
		$options['class'] = 'calendar';
		if ( strpos($fieldName, '.') === false ) {
			$fieldName = $model->name . '.' . $fieldName . '.date';
		}
		else {
			$fieldName .= '.date';
		}
		$options['div'] = array('calendar');
		return $this->Form->input($fieldName, $options);
	}

	function embed()
	{
	    if ( $this->embedded ) {
    		return;
	    }
	    $this->embedded = true;
		$this->Html->css('/vendors/calendar/css/ui-lightness/jquery-ui-1.7.2.custom', null, null, false);
		$this->Javascript->link('/vendors/calendar/js/jquery-ui-1.7.2.custom.min', false);
		$js = <<<JS
$(function() {
    $(".calendar").datepicker({
    	dateFormat: 'yy-mm-dd' ,
    	duration: ''
    });
});
JS;
		$this->Javascript->codeBlock($js, array('inline' => false));
	}
}
?>
