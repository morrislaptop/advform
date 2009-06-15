<?php
/**
 * WYSIWYGPro helper aids in the use of WYSIWYGPro within CakePHP applications.
 * WYSIWYGPro must be purchased separately from - http://www.wysiwygpro.com/
 * Developer documentation is located - http://www.wysiwygpro.com/index.php?id=56
 *
 * PHP versions 4 and 5
 *
 * Copyright 2009, Brightball, Inc. (http://www.brightball.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright     Copyright 2009, Brightball, Inc. (http://www.brightball.com)
 * @link          http://github.com/aramisbear/brightball-open-source/tree/master Brightball Open Source
 * @lastmodified  $Date: 2009-06-12 13:23:10 -0500 (Sat, Jun 13 2009) $
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class WysiwygproFileBrowserHelper extends AppHelper
{
	var $helpers = array('Form');
	var $editor = null;
	var $embedded;

	function embed()
	{
		if ( $this->embedded ) {
			return;
		}
		$this->embedded = true;
		require_once("wysiwygPro/wysiwygPro.class.php");
		$this->editor = new wysiwygPro();
		$this->editor->imageDir = WWW_ROOT . 'files' . DS . 'Images/';
		$this->editor->imageURL = '/files/Images/';
		echo $this->editor->fetchFileBrowserJS();
	}

	function input($fieldName, $options)
	{
		$this->embed();
		$type = $options['type'];

		// what will the field name be?
		if ( empty($options['id']) ) {
			$this->setEntity($fieldName);
			$id = $this->domId();
		}
		else {
			$id = $options['name'];
		}

		// construct button
		$button = $this->Form->button('Select', array(
			'onclick' => "OpenFileBrowser('" . $type . "', function(url) {
				\$('#" . $id . "').val(url);
			}, function() {
				return \$('#" . $id . "').val();
			});"
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