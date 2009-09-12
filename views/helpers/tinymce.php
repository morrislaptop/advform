<?php
class TinymceHelper extends AppHelper
{
	var $helpers = array('Form', 'Javascript', 'Html', 'Advform.Wysiwygpro');
	var $embedded = false;
	var $_bodyClasses = array();

	function registerBodyClass($element, $class) {
		$this->_bodyClasses[$element] = $class;
	}

	function embed()
	{
	    if ( $this->embedded ) {
    		return;
	    }
	    $this->embedded = true;
	    $this->Javascript->link('/vendors/tiny_mce/tiny_mce', false);
	    $this->Javascript->link('/vendors/tinybrowser/tb_tinymce.js.php', false);

	    $body_class = http_build_query($this->_bodyClasses, null, ',');

	    $js = <<<JS
	tinyMCE.init({
	    mode: "specific_textareas",
	    theme: "advanced",

	    // @TODO cleanup unneeded plugins
	    plugins: "style,paste,inlinepopups,table",
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
	    file_browser_callback: "tinyBrowser",

	    // Which textareas?
	    editor_selector: "tinymce",

	    // Context CSS
	    body_class: "{$body_class}",

	    // URLs
	    relative_urls: false,
	    remove_script_host: true,
	    document_base_url: 'http://{$_SERVER['SERVER_NAME']}{$this->base}/',

	    // Paste Options


	    // CSS
	    content_css: '{$this->Html->url('/css/content.css')}'
	});

	function TinyMceOpenFileBrowser(field_name, url, type, win)
	{
		OpenFileBrowser(type, function(url) {
			win.document.getElementById(field_name).value = url;
		}, function() {
			return url;
		});
	}
JS;
		$this->Javascript->codeBlock($js, array('inline' => false));
	}

	function input($fieldName, $options) {
  		$this->embed();
		$options['class'] = 'tinymce';
		return $this->Form->input($fieldName, $options);
	}
}
?>