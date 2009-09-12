<?php

class AdvformHelper extends AppHelper {

	var $helpers = array('Html', 'Javascript', 'Form', 'Advform.Wysiwygpro', 'Advform.Tinymce', 'Advform.Files', 'Advform.JqueryCalendar',  'Advform.TinyBrowser');

	var $wysiwygEmbedded = false;
	var $calendarEmbedded = false;
	var $customTypes = array('document', 'image', 'media', 'number', 'link', 'wysiwyg', 'calendar');

	/**
	* @var HtmlHelper
	*/
	var $Html;
	/**
	* @var FormHelper
	*/
	var $Form;

	/**
	* Generic input function, will use the helper methods in this class
	* 
	* @param mixed $fieldName
	* @param mixed $options
	*/
	function input($fieldName, $options = array())
	{
		$type = null;
		if ( !empty($options['type']) ) {
			$type = $options['type'];
		}

		// Custom methods provided by this class
		if ( in_array($type, $this->customTypes) ) {
			return $this->$type($fieldName, $options);
		}
		return $this->Form->input($fieldName, $options);
	}

	function _file($fieldName, $options, $type = null) {
		$type = Configure::read('Advform.file');
		return $this->$type->input($fieldName, $options, $type);
	}

	function document($fieldName, $options) {
		return $this->_file($fieldName, $options, 'document');
	}
	function image($fieldName, $options) {
		return $this->_file($fieldName, $options, 'image');
	}
	function media($fieldName, $options) {
		return $this->_file($fieldName, $options, 'media');
	}
	function link($fieldName, $options) {
		return $this->_file($fieldName, $options, 'link');
	}

	/**
	* @todo
	*/
	function number($fieldName, $options) {
		$options['type'] = 'text';
		return $this->Form->input($fieldName, $options);
	}

	function wysiwyg($fieldName, $options)
	{
		$type = Configure::read('Advform.wysiwyg');
		if ( $type ) {
			return $this->$type->input($fieldName, $options);
		}
		else {
			return $this->Form->input($fieldName, $options);
		}
	}

	function calendar($fieldName, $options) {
		$type = Configure::read('Advform.calendar');
		if ( $type ) {
			return $this->$type->input($fieldName, $options);
		}
		else {
			return $this->Form->input($fieldName, $options);
		}
	}

	function inputWithDefault($fieldName, $default, $options) {
		$this->setEntity($fieldName);
		$value = $this->value();
		if ( !empty($options['id']) ) {
			$id = $options['id'];
		}
		else {
			$id = $this->domId();
		}

		// if there is a value, return as normal
		if ( !empty($value['value']) ) {
			return $this->Form->input($fieldName, $options);
		}

		$blurColor = '#808080';
		if ( !empty($options['blurColor']) ) {
			$blurColor = $options['blurColor'];
			unset($options['blurColor']);
		}

		// start the fun!
		$jsDefault = $this->Javascript->escapeString($default);

		$js = '
$("#' . $id . '").focus(function() {
	if ( this.value == "' . $jsDefault . '" ) {
		this.value = "";
		$(this).css("color", "");
	}
}).blur(function() {
	if ( this.value == "" ) {
		this.value = "' . $jsDefault . '";
		$(this).css("color", "' . $blurColor . '");
	}
});
		';

		$newOptions = array(
			'value' => $default,
			'after' => $this->Javascript->codeBlock($js),
			'style' => 'color: ' . $blurColor
		);

		return $this->Form->input($fieldName, array_merge($newOptions, $options));
	}
}
?>