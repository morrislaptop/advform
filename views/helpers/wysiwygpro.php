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
class WysiwygproHelper extends AppHelper
{
	var $helpers = array('Form');
	var $editor = null;
	var $embedded = false;
	var $fileBrowserEmbedded = false;

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
		$this->editor->mediaDir = WWW_ROOT . 'files' . DS . 'Media/';
		$this->editor->mediaURL = '/files/Media/';
		$this->editor->documentDir = WWW_ROOT . 'files' . DS . 'Files/';
		$this->editor->documentURL = '/files/Files/';
		$this->editor->deleteFiles = true;
		$this->editor->deleteFolders = true;
		$this->editor->renameFiles = true;
		$this->editor->renameFolders = true;
		$this->editor->upload = true;
		$this->editor->overwrite = true;
		$this->editor->moveFiles = true;
		$this->editor->moveFolders = true;
		$this->editor->copyFiles = true;
		$this->editor->copyFolders = true;
		$this->editor->createFolders = true;
		$this->editor->editImages = true;
	}
	
	function embedFilebrowser() {
		if ( $this->fileBrowserEmbedded ) {
			return;
		}
		$this->fileBrowserEmbedded = true;
		$this->embed();
		echo $this->editor->fetchFileBrowserJS();
	}

	function input($fieldName, $options)
	{
		if ( 'wysiwyg' == $options['type'] ) {
			return $this->wysiwyg($fieldName, $options);
		}
		else {
			return $this->file($fieldName, $options);
		}
	}
	
	function file($fieldName, $options)
	{
		$this->embedFilebrowser();
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
	
	function wysiwyg($fieldName, $options)
	{
		// Embed.
		$this->embed();
		
		// Config wysiwyg
		$options = $this->_initInputField($fieldName);
		$this->editor->name = $options['name'];
		$this->editor->value = $options['value'];
		
		// Get code from vendor class
		$code = $this->editor->fetch();
		
		// Generate textarea from form
		$textarea = $this->Form->input($fieldName, $options);
		
		// Replace textarea from form with the code from vendor
		$out = preg_replace('#<textarea\b[^>]*>(.*?)</textarea>#', $code, $textarea);
		return $out;
	}
}
?>