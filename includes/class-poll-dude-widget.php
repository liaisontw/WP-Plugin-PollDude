<?php
/**
 *
 * This class defines all code related to the plugin's widget class (extends WP_Widget).
 *
 * @link       https://github.com/liaisontw/poll-dude
 * @since      1.0.0
 * @package    poll-dude
 * @subpackage poll-dude/includes
 * @author     Liaison Chang
 */

### Class: WP Widget
 class WP_Widget_Polldude extends WP_Widget {
	// Constructor
	public function __construct() {
		$widget_ops = array('description' => __('Poll Dude', 'poll-dude'));
		parent::__construct('polldude-widget', __('Poll Dude', 'poll-dude'), $widget_ops);
	}

	// Display Widget
	public function widget( $args, $instance ) {
		global $wpdb, $poll_dude;

		$title = apply_filters( 'widget_title', esc_attr( $instance['title'] ) );
		$poll_id = (int) $instance['poll_id'];
		
		echo $args['before_widget'];
		if( ! empty( $title ) ) {
			echo  $args['before_title'].esc_attr($title) . $args['after_title'];
		}
		echo wp_kses_post($poll_dude->shortcode->get_poll( $poll_id, true, true ));
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
		return $instance;
	}

	// Display Widget Control Form
	public function form($instance) {
		global $wpdb, $poll_dude;
		
		$instance = wp_parse_args((array) $instance, array('title' => __('Poll Dude', 'poll-dude'), 'poll_id' => 0, 'display_pollarchive' => 1));
		$title = esc_attr($instance['title']);
		$poll_id = (int) $instance['poll_id'];
?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:', 'poll-dude'); ?> <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label>
		</p>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('poll_id')); ?>"><?php _e('Poll To Display:', 'poll-dude'); ?>
				<select name="<?php echo esc_attr($this->get_field_name('poll_id')); ?>" id="<?php echo esc_attr($this->get_field_id('poll_id')); ?>" class="widefat">
					<option value="-1"<?php selected(-1, $poll_id); ?>><?php _e('Do NOT Display Poll (Disable)', 'poll-dude'); ?></option>
					<option value="-2"<?php selected(-2, $poll_id); ?>><?php _e('Display Random Poll', 'poll-dude'); ?></option>
					<option value="0"<?php selected(0, $poll_id); ?>><?php _e('Display Latest Poll', 'poll-dude'); ?></option>
					<optgroup>&nbsp;</optgroup>
					<?php
					$polls = $wpdb->get_results("SELECT pollq_id, pollq_question FROM $wpdb->polldude_q ORDER BY pollq_id DESC");
					if($polls) {
						foreach($polls as $poll) {
							$pollq_question = wp_kses_post( $poll_dude->utility->removeslashes( $poll->pollq_question ) );
							$pollq_id = (int) $poll->pollq_id;
							if($pollq_id === $poll_id) {
								echo "<option value=\"$pollq_id\" selected=\"selected\">".esc_attr($pollq_question)."</option>\n";
							} else {
								echo "<option value=\"$pollq_id\">".esc_attr($pollq_question)."</option>\n";
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


