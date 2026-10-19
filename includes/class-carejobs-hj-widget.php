<?php
/**
 * The classic widget.
 *
 * @package CareJobs_Healthcare_Jobs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget showing open healthcare jobs from CareJobs.Now.
 */
class CareJobs_HJ_Widget extends WP_Widget {

	/**
	 * Sets up the widget.
	 */
	public function __construct() {
		parent::__construct(
			'carejobs_hj_jobs',
			__( 'CareJobs Healthcare Jobs', 'carejobs-healthcare-jobs' ),
			array(
				'classname'                   => 'widget_carejobs_hj',
				'description'                 => __( 'A live list of open US healthcare jobs from CareJobs.Now.', 'carejobs-healthcare-jobs' ),
				'customize_selective_refresh' => true,
			)
		);
	}

	/**
	 * Prints the widget.
	 *
	 * @param array<string,string> $args     Sidebar arguments.
	 * @param array<string,mixed>  $instance Saved settings.
	 */
	public function widget( $args, $instance ) {
		$settings = carejobs_hj_normalize( $instance );

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sidebar markup from the theme.

		if ( '' !== $settings['title'] ) {
			/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
			$title = apply_filters( 'widget_title', $settings['title'], $instance, $this->id_base );
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sidebar markup from the theme, title escaped.
		}

		echo carejobs_hj_render_list( $settings, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped where the markup is built.

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sidebar markup from the theme.
	}

	/**
	 * Sanitizes the settings on save.
	 *
	 * @param array<string,mixed> $new_instance New settings.
	 * @param array<string,mixed> $old_instance Previous settings.
	 * @return array<string,mixed>
	 */
	public function update( $new_instance, $old_instance ) {
		unset( $old_instance );

		$settings = carejobs_hj_normalize( $new_instance );
		$api      = $settings['api'];

		return array(
			'title'   => $settings['title'],
			'kind'    => isset( $api['kind'] ) ? $api['kind'] : '',
			'state'   => isset( $api['state'] ) ? $api['state'] : '',
			'query'   => isset( $api['query'] ) ? $api['query'] : '',
			'limit'   => (string) $api['limit'],
			'sort'    => $api['sort'],
			'remote'  => isset( $api['remote'] ) ? '1' : '',
			'min_pay' => isset( $api['min_hourly_usd'] ) ? (string) $api['min_hourly_usd'] : '',
			'new_tab' => $settings['new_tab'] ? '1' : '',
		);
	}

	/**
	 * Prints the settings form.
	 *
	 * @param array<string,mixed> $instance Saved settings.
	 */
	public function form( $instance ) {
		$values = array_merge( carejobs_hj_defaults(), is_array( $instance ) ? $instance : array() );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'carejobs-healthcare-jobs' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $values['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'kind' ) ); ?>"><?php esc_html_e( 'Kind of job', 'carejobs-healthcare-jobs' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'kind' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'kind' ) ); ?>">
				<option value=""><?php esc_html_e( 'All kinds of jobs', 'carejobs-healthcare-jobs' ); ?></option>
				<?php foreach ( carejobs_hj_kinds() as $slug => $label ) : ?>
					<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $values['kind'], $slug ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'state' ) ); ?>"><?php esc_html_e( 'State', 'carejobs-healthcare-jobs' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'state' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'state' ) ); ?>">
				<option value=""><?php esc_html_e( 'All states', 'carejobs-healthcare-jobs' ); ?></option>
				<?php foreach ( carejobs_hj_states() as $code => $name ) : ?>
					<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $values['state'], $code ); ?>><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'query' ) ); ?>"><?php esc_html_e( 'Keywords', 'carejobs-healthcare-jobs' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'query' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'query' ) ); ?>" value="<?php echo esc_attr( $values['query'] ); ?>" placeholder="<?php echo esc_attr_x( 'ICU, night, travel', 'keywords example', 'carejobs-healthcare-jobs' ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"><?php esc_html_e( 'Number of jobs (1 to 25)', 'carejobs-healthcare-jobs' ); ?></label>
			<input class="tiny-text" type="number" min="1" max="25" step="1" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" value="<?php echo esc_attr( $values['limit'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'sort' ) ); ?>"><?php esc_html_e( 'Order', 'carejobs-healthcare-jobs' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'sort' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'sort' ) ); ?>">
				<?php foreach ( carejobs_hj_sorts() as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $values['sort'], $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'min_pay' ) ); ?>"><?php esc_html_e( 'Minimum hourly pay in US dollars (optional)', 'carejobs-healthcare-jobs' ); ?></label>
			<input class="tiny-text" type="number" min="1" max="1000" step="1" id="<?php echo esc_attr( $this->get_field_id( 'min_pay' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'min_pay' ) ); ?>" value="<?php echo esc_attr( $values['min_pay'] ); ?>" />
		</p>
		<p>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'remote' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'remote' ) ); ?>" value="1" <?php checked( carejobs_hj_bool( $values['remote'] ) ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'remote' ) ); ?>"><?php esc_html_e( 'Remote jobs only', 'carejobs-healthcare-jobs' ); ?></label>
		</p>
		<p>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'new_tab' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'new_tab' ) ); ?>" value="1" <?php checked( carejobs_hj_bool( $values['new_tab'] ) ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'new_tab' ) ); ?>"><?php esc_html_e( 'Open links in a new tab', 'carejobs-healthcare-jobs' ); ?></label>
		</p>
		<?php
	}
}
