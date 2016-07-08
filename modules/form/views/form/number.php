<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Text_Model */

$default = $form->get_value($field->get_name(), 0);
$element_id = 'element_id_'.uniqid();

echo '<div class="nj-form-field nj-form-field-number">';
echo '<label>';
echo '<div class="nj-form-label"><label for="'.$element_id.'">' . html::specialchars($field->get_pretty_name()) . '</label></div>';
echo '<input type="number" id="'.$element_id.'" class="nj-form-option" name="'.html::specialchars($field->get_name()).'" value="'.html::specialchars($default).'" />';
echo '</label>';
echo '</div>';
