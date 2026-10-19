<?php
/**
 * Output: the shortcode, the block's server-side render and the shared list markup.
 *
 * @package CareJobs_Healthcare_Jobs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads the stylesheet. Called only when something renders.
 */
function carejobs_hj_enqueue_style() {
	wp_enqueue_style( 'carejobs-healthcare-jobs' );
}

/**
 * Splits a posted pay string into the short pay and an optional note.
 *
 * "$40 to $55/hr (typical of ... at Select Medical, measured from its open jobs;
 * not a rate this posting states)" becomes "$40 to $55/hr" with the note
 * "typical at Select Medical". A parenthesis that does not start with "typical"
 * is dropped.
 *
 * @param string $pay      Pay as the API returns it.
 * @param string $employer Employer name.
 * @return array{0:string,1:string} Short pay, note.
 */
function carejobs_hj_split_pay( $pay, $employer ) {
	$pay = trim( $pay );
	if ( '' === $pay ) {
		return array( '', '' );
	}
	$pos = strpos( $pay, ' (' );
	if ( false === $pos ) {
		return array( $pay, '' );
	}
	$short = trim( substr( $pay, 0, $pos ) );
	$note  = '';
	if ( 0 === strpos( substr( $pay, $pos + 2 ), 'typical' ) && '' !== $employer ) {
		/* translators: %s: employer name, as in "typical at Select Medical". */
		$note = sprintf( __( 'typical at %s', 'carejobs-healthcare-jobs' ), $employer );
	}
	return array( $short, $note );
}

/**
 * Formats a "YYYY-MM-DD" date for display in the site's date format.
 *
 * @param string $listed Date from the API.
 * @return string Formatted date, or an empty string if it is not a date.
 */
function carejobs_hj_format_date( $listed ) {
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $listed ) ) {
		return '';
	}
	$timestamp = strtotime( $listed . ' 12:00:00 UTC' );
	if ( false === $timestamp ) {
		return '';
	}
	return wp_date( get_option( 'date_format' ), $timestamp, new DateTimeZone( 'UTC' ) );
}

/**
 * The list markup shared by the shortcode, the block and the widget.
 *
 * @param array<string,mixed> $settings   Output of carejobs_hj_normalize().
 * @param bool                $with_title Whether to print the title (the widget prints its own).
 * @return string HTML, escaped.
 */
function carejobs_hj_render_list( $settings, $with_title = true ) {
	static $instance = 0;
	++$instance;

	carejobs_hj_enqueue_style();

	$html = '';

	// People who can edit see what was ignored. Visitors never do.
	if ( ! empty( $settings['problems'] ) && current_user_can( 'edit_posts' ) ) {
		$html .= '<p class="carejobs-hj__notice">' . esc_html( implode( ' ', $settings['problems'] ) ) . '</p>';
	}

	$data = carejobs_hj_get_jobs( $settings['api'] );

	if ( false === $data ) {
		return '<div class="carejobs-hj carejobs-hj--error">' . $html .
			'<p class="carejobs-hj__message">' . esc_html__( 'Healthcare jobs could not be loaded right now.', 'carejobs-healthcare-jobs' ) . '</p></div>';
	}

	$heading_id = 'carejobs-hj-heading-' . $instance;
	$heading    = '';
	if ( $with_title && '' !== $settings['title'] ) {
		$heading = '<h3 class="carejobs-hj__heading" id="' . esc_attr( $heading_id ) . '">' . esc_html( $settings['title'] ) . '</h3>';
	}

	if ( empty( $data['jobs'] ) ) {
		return '<div class="carejobs-hj">' . $html . $heading .
			'<p class="carejobs-hj__message">' . esc_html__( 'No open healthcare jobs match these filters right now.', 'carejobs-healthcare-jobs' ) . '</p></div>';
	}

	$link_attrs = $settings['new_tab'] ? ' target="_blank" rel="noopener"' : '';

	$items = '';
	foreach ( $data['jobs'] as $job ) {
		$name = esc_html( $job['title'] );
		if ( '' !== $job['url'] ) {
			$hint = '';
			if ( $settings['new_tab'] ) {
				$hint = '<span class="carejobs-hj__sr"> ' . esc_html__( '(opens in a new tab)', 'carejobs-healthcare-jobs' ) . '</span>';
			}
			$name = '<a class="carejobs-hj__link" href="' . esc_url( $job['url'] ) . '"' . $link_attrs . '>' . $name . $hint . '</a>';
		}

		$meta = array();
		if ( '' !== $job['employer'] ) {
			$meta[] = '<li class="carejobs-hj__employer">' . esc_html( $job['employer'] ) . '</li>';
		}

		list( $pay, $note ) = carejobs_hj_split_pay( $job['pay'], $job['employer'] );
		if ( '' !== $pay ) {
			$pay_html = '<span class="carejobs-hj__pay-amount">' . esc_html( $pay ) . '</span>';
			if ( '' !== $note ) {
				$pay_html .= ' <span class="carejobs-hj__pay-note">(' . esc_html( $note ) . ')</span>';
			}
			$meta[] = '<li class="carejobs-hj__pay">' . $pay_html . '</li>';
		}

		if ( '' !== $job['location'] ) {
			$meta[] = '<li class="carejobs-hj__location">' . esc_html( $job['location'] ) . '</li>';
		}

		if ( ! empty( $job['schedule'] ) ) {
			$meta[] = '<li class="carejobs-hj__schedule">' . esc_html( implode( ', ', $job['schedule'] ) ) . '</li>';
		}

		$listed = carejobs_hj_format_date( $job['listed'] );
		if ( '' !== $listed ) {
			/* translators: %s: the date a job was listed. */
			$listed_text = sprintf( __( 'Listed %s', 'carejobs-healthcare-jobs' ), $listed );
			$meta[]      = '<li class="carejobs-hj__listed"><time datetime="' . esc_attr( $job['listed'] ) . '">' . esc_html( $listed_text ) . '</time></li>';
		}

		$items .= '<li class="carejobs-hj__item"><span class="carejobs-hj__name">' . $name . '</span>';
		if ( $meta ) {
			$items .= '<ul class="carejobs-hj__meta">' . implode( '', $meta ) . '</ul>';
		}
		$items .= '</li>';
	}

	$more_url = '' !== $data['more'] ? $data['more'] : 'https://carejobs.now/';
	if ( $data['total'] > 0 ) {
		/* translators: %s: number of open jobs. */
		$more_text = sprintf( _n( 'See all %s job on CareJobs.Now', 'See all %s jobs on CareJobs.Now', $data['total'], 'carejobs-healthcare-jobs' ), number_format_i18n( $data['total'] ) );
	} else {
		$more_text = __( 'See all jobs on CareJobs.Now', 'carejobs-healthcare-jobs' );
	}
	$more = '<p class="carejobs-hj__more"><a href="' . esc_url( $more_url ) . '"' . $link_attrs . '>' . esc_html( $more_text );
	if ( $settings['new_tab'] ) {
		$more .= '<span class="carejobs-hj__sr"> ' . esc_html__( '(opens in a new tab)', 'carejobs-healthcare-jobs' ) . '</span>';
	}
	$more .= '</a></p>';

	$labelled = '' !== $heading ? ' aria-labelledby="' . esc_attr( $heading_id ) . '"' : '';

	return '<div class="carejobs-hj">' . $html . $heading . '<ul class="carejobs-hj__list"' . $labelled . '>' . $items . '</ul>' . $more . '</div>';
}

/**
 * The [carejobs_jobs] shortcode.
 *
 * @param array<string,string>|string $atts Shortcode attributes.
 * @return string
 */
function carejobs_hj_shortcode( $atts ) {
	if ( is_feed() ) {
		return '';
	}
	$atts = shortcode_atts( carejobs_hj_defaults(), $atts, 'carejobs_jobs' );
	return carejobs_hj_render_list( carejobs_hj_normalize( $atts ), true );
}

/**
 * Server-side render of the block.
 *
 * @param array<string,mixed> $attributes Block attributes (camelCase for minPay and newTab).
 * @return string
 */
function carejobs_hj_render_block( $attributes ) {
	$attributes = is_array( $attributes ) ? $attributes : array();
	if ( isset( $attributes['minPay'] ) ) {
		$attributes['min_pay'] = $attributes['minPay'];
	}
	if ( isset( $attributes['newTab'] ) ) {
		$attributes['new_tab'] = $attributes['newTab'];
	}

	$html = carejobs_hj_render_list( carejobs_hj_normalize( $attributes ), true );

	// Lets the editor's own block settings (alignment, extra CSS class) apply.
	$wrapper = function_exists( 'get_block_wrapper_attributes' )
		? get_block_wrapper_attributes( array( 'class' => 'carejobs-hj-block' ) )
		: 'class="carejobs-hj-block"';

	// $wrapper is escaped by WordPress; $html is escaped where it is built.
	return '<div ' . $wrapper . '>' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
