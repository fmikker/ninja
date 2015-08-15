<?php
/* @var $set ObjectSet_Model */
/* @var $command string */
/* @var $command_info string */
/* @var $error string */
/* @var $objs_widget widget_Base */

if (empty( $error )) {
	echo "<h2>" . $command_info['name'] . ":</h2>";
	echo form::open( 'cmd/obj', array (
		'id' => 'command_form',
		'method' => 'post'
	) );
	echo form::hidden( 'command', $command );
	echo form::hidden( 'query', $set->get_query() );
	echo "<table>";
	foreach ( $command_info['params'] as $pname => $pdef ) {
		$pdef = array_merge(array(
			'name' => $pname,
			'description' => '',
			'option' => array(),
			'default' => false
		), $pdef);
		echo "<tr>";
		echo "<td>";
		echo "<label for='field_$pname'>" . html::specialchars($pdef['name']) . "</label>";
		echo "</td>";
		echo "<td>";
		switch ($pdef['type']) {
			case 'string' :
				echo form::input( array (
					'class' => "input-wide autotest-required",
					'name' => $pname,
					'title' => _( 'Required field' ),
					'id' => 'field_' . $pname
				) );
				break;
			case 'int' :
			case 'float' :
				echo form::input( array (
					'class' => "input-wide autotest-required",
					'name' => $pname,
					'title' => _( 'Required field' ),
					'id' => 'field_' . $pname
				) );
				break;
			case 'time' :
				echo form::input( array (
					'class' => "input-wide autotest-date",
					'name' => $pname,
					'title' => _( 'Required field' ),
					'id' => 'field_' . $pname
				) );
				break;
			case 'duration' :
				echo form::input( array (
					'class' => "input-wide autotest-float",
					'name' => $pname,
					'title' => _( 'Required field' ),
					'id' => 'field_' . $pname
				) );
				break;
			case 'select' :
				echo form::dropdown( array (
					'class' => "input-wide autotest-required",
					'name' => $pname,
					'title' => _( 'Required field' ),
					'id' => 'field_' . $pname
				), $pdef['option'] );
				break;
			case 'bool' :
				echo form::checkbox(  array (
					'name' => $pname,
					'id' => 'field_' . $pname
				), true, false, 'class="checkbox"' );
				break;
			case 'object':
				$set = ObjectPool_Model::get_by_query($pdef['query']);
				$objs = array(
					'' => 'None'
				);
				foreach($set->it(array('key'), array(), 1000) as $obj) {
					/* @var $obj Object_Model */
					$objs[$obj->get_key()] = $obj->get_key();
				}
				echo form::dropdown(array(
					'class' => "input-wide",
					'name' => $pname,
					'title' => _( 'Required field' ),
					'id' => 'field_' . $pname,
					'options' => $objs
				));
		}
		if(!empty($pdef['description'])) {
			echo "<br />";
			echo html::specialchars($pdef['description']);
		}
		echo "</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo form::submit( false, 'Submit', 'class="submit"' );
	echo form::close();


	echo "<h2>Affecting:</h2>";
	echo $objs_widget->render('index', false);
} else {
	echo "Error: " . $error;
}