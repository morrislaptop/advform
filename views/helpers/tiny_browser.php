<?php
class TinyBrowserHelper extends AppHelper
{
	var $helpers = array('Form', 'Javascript', 'Html', 'Advform.Wysiwygpro');
	var $embedded = false;

	function embed()
	{
	    if ( $this->embedded ) {
    		return;
	    }
	    $this->embedded = true;
	    $this->Javascript->link('/vendors/tinybrowser/tb_standalone.js.php', false);
	}

	function input($fieldName, $options)
	{
  		$this->embed();
  		$type = $options['type'];

		// what will the field id be?
		if ( empty($options['id']) ) {
			$this->setEntity($fieldName);
			$id = $this->domId();
		}
		else {
			$id = $options['name'];
		}

		// construct button
		$button = $this->Form->button('Select', array(
			'onclick' => "tinyBrowserPopUp('" . $type . "','" . $id . "');"
		));

		// put button in
		if ( !empty($options['after']) ) {
			$options['after'] = $button . $options['after'];
		}
		else {
			$options['after'] = $button;
		}

		$options['type'] = 'text';
		return $this->Form->input($fieldName, $options);
	}
}
?>