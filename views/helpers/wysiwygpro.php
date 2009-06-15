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

require_once("wysiwygPro/wysiwygPro.class.php");

class WysiwygproHelper extends AppHelper {

   var $helpers = array('Form');

   var $headers = '';
   var $directory_permissions = null;
   var $defaults = null;

   //Apply special handling to these setting arguments
   var $special_settings = array(
         'directories' => array('filters')
      );

   function beforeRender() {
     $this->_set_defaults(); //Initialize
   }

   function afterLayout() { //If inline headers is false, this is a failsafe
      if(!empty($this->headers)) echo $this->getHeaders();
   }

   function _set_defaults() { //Defaults are set in app/config/wysiwygpro.php
      if($this->defaults == null) {
         $this->special_settings = Set::normalize($this->special_settings);

         Configure::load('wysiwygpro');
         $this->defaults = Configure::read('Wysiwygpro');
      }
   }

   function input($fieldName, $options = array(),$settings = array()) {
      $model =& ClassRegistry::getObject($this->Form->model());
      $ret = $this->Form->input($fieldName,$options);

      if($model->getColumnType($this->field()) == 'text') //Convert only text areas
         return $this->replaceTextArea($fieldName,$options,$ret,$settings);
      else return $ret;
   }

   function addHeaders($hc = '') {
      if(!empty($hc)) $this->headers .= $hc . "\n";
   }

   function getHeaders() {
      if(!empty($this->headers)) {
         $out = $this->headers;
         $this->headers = '';
         return $this->output($out);
      }
   }

   function replaceTextArea($fieldName, $options, $input_html, $settings = array()) { //Convert textarea html into wysiwygpro html

      list($model,$field) = explode((strpos('/',$fieldName) !== false ? '/' : '.'),$fieldName);
      //Configure WYSIWYGPro
      $editor = new wysiwygPro();
      $editor->name = 'data[' . $model . '][' . $field . ']';
      $editor->value = isset($this->data[$model][$field]) ? $this->data[$model][$field] : '';

      $editor->clearFontMenu();
      $editor->clearSizeMenu();

      $empty = Set::normalize($this->defaults);
      $settings = Set::normalize($settings);

      if(!empty($settings['directories'])) {
         foreach($settings['directories'] AS $sd) {
            $empty['directories'][] = $sd;
         }
         unset($settings['directories']);
      }

      $settings = Set::merge($empty,$settings);

      $this->directory_permissions = $settings['_directory_permissions'];

      foreach($settings AS $st => $val) {
         if(!is_scalar($st)) {
            trigger_error('Invalid setting match: ' . print_r($st,1));
         }
         elseif(!array_key_exists($st, $this->special_settings)) { //Normal
            if(substr($st,0,1) == '_') continue; //Helper settings, ignore
            elseif(method_exists($editor,$st)) { //Single arg functions
               if(!is_array($val)) $val = array($val);
               call_user_func_array(array($editor,$st),$val);
            }
            elseif(property_exists($editor,$st)) { //Variable
               $editor->{$st} = $val;
            }
            else {
               trigger_error($st . ' is not a WYSIWYGPro method or property and the helper is not yet configured to handle this function');
            }
         }
         else { //Special handlers
            if($st == 'directories') {
               foreach($val AS $args) {
                  $this->addDirectory($editor, $args);
               }
            }
            else {
              trigger_error($st . ' is a special setting that has not been handled yet.');
            }
         }
      }

      //Get all the HTML the way that Cake wanted to build it
      //But move the error after the label and before the editor for better read-ability

      //The Helper::output function is not used because $editor->display immediately outputs the editor HTML
      list($begin,$junk) = explode('<textarea',$input_html);
      $error = $this->Form->error($fieldName);
      echo $begin . (empty($error) ? '' : $error) . '<div class="wysiwygpro">';

      //Output the editor
      if(!$settings['_inline_headers']) $this->addHeaders($editor->fetchHeadContent());

      $editor->display($settings['_editor_width'],$settings['_editor_height']); //width, height
      echo '</div></div>';
   }

   function addDirectory(&$editor, $settings = array()) {

      $type = $settings['type'];

      if(!isset($this->defaults['_directory_settings'][$type])) {
         trigger_error($type . ' is not a valid directory type');
         return false;
      }

      $settings = Set::merge($this->defaults['_directory_settings'][$type],$settings);
      //unset($settings['type']);

      $special = $this->special_settings['directories'];

      $dir = new wproDirectory();

      foreach($settings AS $st => $val) {
         if(!in_array($st,$special)) { //Default settings
            if(method_exists($dir,$st)) { //Single arg functions
               $dir->{$st}($val);
            }
            else { //Variable
               $dir->{$st} = $val;
            }
         }
         else { //Special handlers
            if($st == 'filters') {
               $dir->filters = $this->formatFilters($val);
            }
            else {
              trigger_error($st . ' is a special setting that has not been handled yet.');
            }
         }
      }

      if(!file_exists($dir->dir)) mkdir($dir->dir,$this->directory_permissions,true);

      $editor->addDirectory($dir);
   }

   function formatFilters($filters) {
      $out = array();
      foreach($filters AS $f) {
         $out[] = '#' . $f . '#';
      }

      return $out;
   }

}
?>