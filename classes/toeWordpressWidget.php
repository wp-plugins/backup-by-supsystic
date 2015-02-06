<?php
abstract class toeWordpressWidgetBup extends WP_Widget {
	public function preWidget($args, $instance) {
		if(frameBup::_()->isTplEditor())
			echo $args['before_widget'];
	}
	public function postWidget($args, $instance) {
		if(frameBup::_()->isTplEditor())
			echo $args['after_widget'];
	}
}
