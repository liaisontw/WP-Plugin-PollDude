<?php

### Class: WP Widget
 class WP_Widget_Polldude extends WP_Widget {
	// Constructor
	public function __construct() {
		$widget_ops = array('description' => __('Poll Dude', 'poll-dude'));
		parent::__construct('polls-widget', __('Polls', 'poll-dude'), $widget_ops);
	}

	// Display Widget
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', esc_attr( $instance['title'] ) );
		$poll_id = (int) $instance['poll_id'];
		$display_pollarchive = (int) $instance['display_pollarchive'];
		echo $args['before_widget'];
		if( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		get_poll( $poll_id );
		if( $display_pollarchive ) {
			display_polls_archive_link();
		}
		echo $args['after_widget'];
	}

	// When Widget Control Form Is Posted
	public function update($new_instance, $old_instance) {
		if (!isset($new_instance['submit'])) {
			return false;
		}
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['poll_id'] = (int) $new_instance['poll_id'];
		$instance['display_pollarchive'] = (int) $new_instance['display_pollarchive'];
		return $instance;
	}

	// DIsplay Widget Control Form
	public function form($instance) {
		global $wpdb;
		$instance = wp_parse_args((array) $instance, array('title' => __('Polls', 'poll-dude'), 'poll_id' => 0, 'display_pollarchive' => 1));
		$title = esc_attr($instance['title']);
		$poll_id = (int) $instance['poll_id'];
?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'poll-dude'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('poll_id'); ?>"><?php _e('Poll To Display:', 'poll-dude'); ?>
				<select name="<?php echo $this->get_field_name('poll_id'); ?>" id="<?php echo $this->get_field_id('poll_id'); ?>" class="widefat">
					<option value="-1"<?php selected(-1, $poll_id); ?>><?php _e('Do NOT Display Poll (Disable)', 'poll-dude'); ?></option>
					<option value="-2"<?php selected(-2, $poll_id); ?>><?php _e('Display Random Poll', 'poll-dude'); ?></option>
					<option value="0"<?php selected(0, $poll_id); ?>><?php _e('Display Latest Poll', 'poll-dude'); ?></option>
					<optgroup>&nbsp;</optgroup>
					<?php
					$polls = $wpdb->get_results("SELECT pollq_id, pollq_question FROM $wpdb->pollsq ORDER BY pollq_id DESC");
					if($polls) {
						foreach($polls as $poll) {
							$pollq_question = wp_kses_post( removeslashes( $poll->pollq_question ) );
							$pollq_id = (int) $poll->pollq_id;
							if($pollq_id === $poll_id) {
								echo "<option value=\"$pollq_id\" selected=\"selected\">$pollq_question</option>\n";
							} else {
								echo "<option value=\"$pollq_id\">$pollq_question</option>\n";
							}
						}
					}
					?>
				</select>
			</label>
		</p>
		<input type="hidden" id="<?php echo $this->get_field_id('submit'); ?>" name="<?php echo $this->get_field_name('submit'); ?>" value="1" />
<?php
	}
}


