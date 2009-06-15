<?php
App::import('Helper', 'Form');

class AdvformHelper extends FormHelper {

	var $helpers = array('Html', 'Javascript');

	var $wysiwygEmbedded = false;
	var $calendarEmbedded = false;
	var $focusEmbedded = false;

	/**
	* put your comment there...
	*
	* @var HtmlHelper
	*/
	var $Html;

	function create($model = null, $options = array())
	{
		// include required files.
		$this->embedFocus();

		if ( is_array($model) ) {
			// put the first model into the form helper and then add onto the validations array with further models.
			$out = parent::create(array_shift($model), $options);
			foreach ($model as $model) {
				$this->addToValidates($model);
			}
			return $out;
		}
		else {
			return parent::create($model, $options);
		}
	}

	function addToValidates($model)
	{
		$object = ClassRegistry::getObject($model);
		if (!empty($object->validate)) {
			foreach ($object->validate as $validateField => $validateProperties) {
				if (is_array($validateProperties)) {
					$dims = Set::countDim($validateProperties);
					if (($dims == 1 && !isset($validateProperties['required']) || (array_key_exists('required', $validateProperties) && $validateProperties['required'] !== false))) {
						$validates[] = $validateField;
					} elseif ($dims > 1) {
						foreach ($validateProperties as $rule => $validateProp) {
							if (is_array($validateProp) && (array_key_exists('required', $validateProp) && $validateProp['required'] !== false)) {
								$validates[] = $validateField;
							}
						}
					}
				}
			}
		}
		$this->fieldset['validates'] = array_merge($this->fieldset['validates'], $validates);
	}

	function input($fieldName, $options = array())
	{
		$type = null;
		if ( !empty($options['type']) ) {
			$type = $options['type'];
		}

		if ( in_array($type, array('file', 'image', 'flash')) ) {
			$options['type'] = 'file';
			$options['div'] = array($type);
		}
		else if ( in_array($type, array('number', 'url')) ) {
			$options['type'] = 'text';
			$options['div'] = array($type);
		}
		else if ( 'wysiwyg' == $type ) {
			$this->embedWysiwyg();
			$options['type'] = 'textarea';
			$options['class'] = 'tinymce';
		}
		else if ( 'calendar' == $type ) {
			$this->embedCalendar();
			$options['type'] = 'text';
			$options['class'] = 'calendar';
			if ( strpos($fieldName, '.') === false ) {
				$fieldName = $model->name . '.' . $fieldName . '.date';
			}
			else {
				$fieldName .= '.date';
			}
			$options['div'] = array($type);
		}

		return parent::input($fieldName, $options);
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
			return parent::input($fieldName, $options);
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

		return $this->input($fieldName, array_merge($newOptions, $options));
	}

	function embedWysiwygpro() {
		debug('wysiwefey pro here we come');
	}

	function embedWysiwyg()
	{
		if ( $this->wysiwygEmbedded ) {
			return;
		}
		$this->wysiwygEmbedded = true;
		$type = Configure::read('Advform.wysiwyg');
		$method = 'embed' . ucwords($type);
		$this->$method();
	}

	function embedTinymce() {
		$this->Javascript->link('tiny_mce/tiny_mce', false);
		$js = <<<JS
tinyMCE.init({
    mode: "specific_textareas",
    theme: "advanced",

    // @TODO cleanup unneeded plugins
    plugins: "style,paste,inlinepopups,table,imagemanager,filemanager",
    doctype: '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',

    // Theme options
    theme_advanced_buttons1: "pasteword,bold,italic,|justifyleft,justifycenter,justifyright,|,formatselect,styleselect,removeformat,|,bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,anchor,image,|,table,charmap,code",
	theme_advanced_buttons2: "",
	theme_advanced_buttons3: "",
    theme_advanced_toolbar_location: "top",
    theme_advanced_toolbar_align: "left",
    theme_advanced_statusbar_location: "bottom",
    theme_advanced_resizing: true,
    theme_advanced_resize_horizontal: false,
	theme_advanced_path: true,
    width: '100%',

    // File manager
	plugin_simplebrowser_width : '800', //default
	plugin_simplebrowser_height : '600', //default
    plugin_simplebrowser_browselinkurl : '/uniform/js/tiny_mce/plugins/simplebrowser/browser.html?Connector=connectors/php/connector.php',
    plugin_simplebrowser_browseimageurl : '/uniform/js/tiny_mce/plugins/simplebrowser/browser.html?Type=Image&Connector=connectors/php/connector.php',
    plugin_simplebrowser_browseflashurl : '/uniform/js/tiny_mce/plugins/simplebrowser/browser.html?Type=Flash&Connector=connectors/php/connector.php',

    // Which textareas?
    editor_selector: "tinymce",

    // URLs
    relative_urls: false,
    remove_script_host: true,
    document_base_url: 'http://{$_SERVER['SERVER_NAME']}{$this->base}/',

    // Paste Options


    // CSS
    content_css: '{$this->Html->url('/css/content.css')}'
});
JS;

		$this->Javascript->codeBlock($js, array('inline' => false));
	}

	function embedCalendar()
	{
		if ( $this->calendarEmbedded ) {
			return;
		}
		$this->calendarEmbedded = true;
		$this->Html->css('calendar/css/smoothness/jquery-ui-1.7.1.custom', null, null, false);
		$this->Javascript->link('calendar/js/jquery-ui-1.7.1.custom.min', false);
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

	function embedFocus() {
		if ( $this->focusEmbedded ) {
			return;
		}
		$this->focusEmbedded = true;
		$js = <<<JS
$(function() {
	$("input, select, textarea").focus(function() {
		$(this).parent("div.input").addClass("focused");
	}).blur(function() {
		$(this).parent("div.input").removeClass("focused");
	});
});
JS;
		$this->Javascript->codeBlock($js, array('inline' => false));
	}
}
?>