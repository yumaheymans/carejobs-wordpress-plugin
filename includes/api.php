<?php
/**
 * Argument validation, the CareJobs.Now API request and its cache.
 *
 * @package CareJobs_Healthcare_Jobs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads a truthy value from a shortcode, widget or block attribute.
 *
 * @param mixed $value Raw value.
 * @return bool
 */
function carejobs_hj_bool( $value ) {
	if ( is_bool( $value ) ) {
		return $value;
	}
	if ( is_int( $value ) ) {
		return 1 === $value;
	}
	if ( is_string( $value ) ) {
		return in_array( strtolower( trim( $value ) ), array( '1', 'true', 'yes', 'on' ), true );
	}
	return false;
}

/**
 * Turns a state code or name into the two-letter code.
 *
 * @param string $value A state code ("TX") or name ("Texas"), any case.
 * @return string|false The code, an empty string for no state, false if not a US state.
 */
function carejobs_hj_state_code( $value ) {
	$value = trim( sanitize_text_field( $value ) );
	if ( '' === $value ) {
		return '';
	}
	$states = carejobs_hj_states();
	$upper  = strtoupper( $value );
	if ( isset( $states[ $upper ] ) ) {
		return $upper;
	}
	foreach ( $states as $code => $name ) {
		if ( 0 === strcasecmp( $name, $value ) ) {
			return $code;
		}
	}
	return false;
}

/**
 * Sanitizes the options of a shortcode, widget or block into API arguments.
 *
 * Every value is validated here and nowhere else: kind, state and sort against
 * allowlists, numbers with absint and clamped, text with sanitize_text_field.
 * Unknown values are dropped and reported in `problems` (shown only to people
 * who can edit posts).
 *
 * @param mixed $raw Options keyed like the shortcode attributes.
 * @return array{api:array<string,mixed>,title:string,new_tab:bool,problems:string[]}
 */
function carejobs_hj_normalize( $raw ) {
	$defaults = carejobs_hj_defaults();
	$raw      = is_array( $raw ) ? $raw : array();
	$raw      = array_merge( $defaults, array_intersect_key( $raw, $defaults ) );
	$api      = array();
	$problems = array();

	// Kind of job.
	$kinds = carejobs_hj_kinds();
	$kind  = sanitize_key( is_scalar( $raw['kind'] ) ? (string) $raw['kind'] : '' );
	if ( '' !== $kind ) {
		if ( isset( $kinds[ $kind ] ) ) {
			$api['kind'] = $kind;
		} else {
			/* translators: %s: the kind of job the site owner typed. */
			$problems[] = sprintf( __( '"%s" is not a valid kind of job, so it was ignored.', 'carejobs-healthcare-jobs' ), $kind );
		}
	}

	// State.
	$state_raw = is_scalar( $raw['state'] ) ? (string) $raw['state'] : '';
	$state     = carejobs_hj_state_code( $state_raw );
	if ( false === $state ) {
		/* translators: %s: the state the site owner typed. */
		$problems[] = sprintf( __( '"%s" is not a US state, so it was ignored.', 'carejobs-healthcare-jobs' ), sanitize_text_field( $state_raw ) );
	} elseif ( '' !== $state ) {
		$api['state'] = $state;
	}

	// Keywords.
	$query = sanitize_text_field( is_scalar( $raw['query'] ) ? (string) $raw['query'] : '' );
	$query = mb_substr( $query, 0, 100 );
	if ( '' !== $query ) {
		$api['query'] = $query;
	}

	// Number of jobs, 1 to 25.
	$limit = absint( is_scalar( $raw['limit'] ) ? $raw['limit'] : 0 );
	if ( $limit < 1 ) {
		$limit = 10;
	}
	$api['limit'] = min( $limit, 25 );

	// Sort order.
	$sorts = carejobs_hj_sorts();
	$sort  = sanitize_key( is_scalar( $raw['sort'] ) ? (string) $raw['sort'] : '' );
	if ( isset( $sorts[ $sort ] ) ) {
		$api['sort'] = $sort;
	} else {
		$api['sort'] = 'newest';
		if ( '' !== $sort ) {
			/* translators: %s: the sort order the site owner typed. */
			$problems[] = sprintf( __( '"%s" is not a valid sort order, so "newest" was used.', 'carejobs-healthcare-jobs' ), $sort );
		}
	}

	// Remote only.
	if ( carejobs_hj_bool( $raw['remote'] ) ) {
		$api['remote'] = true;
	}

	// Minimum hourly pay in US dollars, 1 to 1000.
	$min_pay = absint( is_scalar( $raw['min_pay'] ) ? $raw['min_pay'] : 0 );
	if ( $min_pay > 0 ) {
		$api['min_hourly_usd'] = min( $min_pay, 1000 );
	}

	$title = sanitize_text_field( is_scalar( $raw['title'] ) ? (string) $raw['title'] : '' );

	return array(
		'api'      => $api,
		'title'    => mb_substr( $title, 0, 120 ),
		'new_tab'  => carejobs_hj_bool( $raw['new_tab'] ),
		'problems' => $problems,
	);
}

/**
 * Keeps a link only if it is an https link on carejobs.now.
 *
 * @param mixed $url Address from the API.
 * @return string The address, or an empty string.
 */
function carejobs_hj_safe_url( $url ) {
	if ( ! is_string( $url ) ) {
		return '';
	}
	$url = esc_url_raw( $url, array( 'https' ) );
	if ( '' === $url ) {
		return '';
	}
	$host = wp_parse_url( $url, PHP_URL_HOST );
	if ( ! is_string( $host ) ) {
		return '';
	}
	$host = strtolower( $host );
	if ( 'carejobs.now' !== $host && '.carejobs.now' !== substr( $host, -13 ) ) {
		return '';
	}
	return $url;
}

/**
 * Reads a plain-text field of a job.
 *
 * @param array<string,mixed> $job Job from the API.
 * @param string              $key Field name.
 * @return string
 */
function carejobs_hj_job_text( $job, $key ) {
	if ( isset( $job[ $key ] ) && is_scalar( $job[ $key ] ) ) {
		return sanitize_text_field( (string) $job[ $key ] );
	}
	return '';
}

/**
 * Decodes the API answer: plain JSON, or a server-sent event carrying it.
 *
 * @param string $body Response body.
 * @return array<string,mixed>|null
 */
function carejobs_hj_decode_body( $body ) {
	$body = trim( (string) $body );
	if ( '' === $body ) {
		return null;
	}
	if ( '{' === $body[0] ) {
		$decoded = json_decode( $body, true );
		return is_array( $decoded ) ? $decoded : null;
	}
	foreach ( preg_split( '/\r\n|\r|\n/', $body ) as $line ) {
		if ( 0 === strpos( $line, 'data:' ) ) {
			$decoded = json_decode( trim( substr( $line, 5 ) ), true );
			if ( is_array( $decoded ) && isset( $decoded['result'] ) ) {
				return $decoded;
			}
		}
	}
	return null;
}

/**
 * Asks CareJobs.Now for jobs. No caching here.
 *
 * Sends only the filters the site owner chose. Nothing about visitors.
 *
 * @param array<string,mixed> $api_args Validated arguments from carejobs_hj_normalize().
 * @return array{total:int,jobs:array<int,array<string,mixed>>,more:string}|false False on any failure.
 */
function carejobs_hj_request( $api_args ) {
	$payload = array(
		'jsonrpc' => '2.0',
		'id'      => 1,
		'method'  => 'tools/call',
		'params'  => array(
			'name'      => 'search_healthcare_jobs',
			'arguments' => $api_args,
		),
	);

	$response = wp_remote_post(
		CAREJOBS_HJ_ENDPOINT,
		array(
			'timeout'     => 8,
			'redirection' => 2,
			'user-agent'  => 'CareJobsHealthcareJobs/' . CAREJOBS_HJ_VERSION . ' (WordPress plugin)',
			'headers'     => array(
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json, text/event-stream',
			),
			'body'        => wp_json_encode( $payload ),
		)
	);

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		return false;
	}

	$decoded = carejobs_hj_decode_body( wp_remote_retrieve_body( $response ) );
	if ( ! is_array( $decoded ) || empty( $decoded['result'] ) || ! is_array( $decoded['result'] ) ) {
		return false;
	}
	$result = $decoded['result'];
	if ( ! empty( $result['isError'] ) ) {
		return false;
	}
	$content = isset( $result['structuredContent'] ) ? $result['structuredContent'] : null;
	if ( ! is_array( $content ) || ! isset( $content['jobs'] ) || ! is_array( $content['jobs'] ) ) {
		return false;
	}

	$jobs = array();
	foreach ( array_slice( $content['jobs'], 0, 25 ) as $job ) {
		if ( ! is_array( $job ) ) {
			continue;
		}
		$title = carejobs_hj_job_text( $job, 'title' );
		if ( '' === $title ) {
			continue;
		}
		$schedule = array();
		if ( isset( $job['schedule'] ) && is_array( $job['schedule'] ) ) {
			foreach ( array_slice( $job['schedule'], 0, 3 ) as $shift ) {
				$shift = is_scalar( $shift ) ? sanitize_text_field( (string) $shift ) : '';
				if ( '' !== $shift ) {
					$schedule[] = $shift;
				}
			}
		}
		$jobs[] = array(
			'title'    => $title,
			'employer' => carejobs_hj_job_text( $job, 'employer' ),
			'pay'      => carejobs_hj_job_text( $job, 'pay' ),
			'schedule' => $schedule,
			'location' => carejobs_hj_job_text( $job, 'location' ),
			'listed'   => carejobs_hj_job_text( $job, 'listed' ),
			'url'      => carejobs_hj_safe_url( isset( $job['url'] ) ? $job['url'] : '' ),
		);
	}

	return array(
		'total' => isset( $content['total'] ) && is_numeric( $content['total'] ) ? absint( $content['total'] ) : count( $jobs ),
		'jobs'  => $jobs,
		'more'  => carejobs_hj_safe_url( isset( $content['more'] ) ? $content['more'] : '' ),
	);
}

/**
 * Jobs for a set of validated arguments, through a transient cache.
 *
 * A good answer is kept for an hour. A failure is remembered for five minutes,
 * so an outage does not make every page view wait for the timeout.
 *
 * @param array<string,mixed> $api_args Validated arguments from carejobs_hj_normalize().
 * @return array{total:int,jobs:array<int,array<string,mixed>>,more:string}|false False when the service could not be reached.
 */
function carejobs_hj_get_jobs( $api_args ) {
	ksort( $api_args );
	$key    = CAREJOBS_HJ_CACHE_PREFIX . md5( wp_json_encode( $api_args ) );
	$cached = get_transient( $key );

	if ( is_array( $cached ) ) {
		return $cached;
	}
	if ( 'error' === $cached ) {
		return false;
	}

	$data = carejobs_hj_request( $api_args );
	if ( false === $data ) {
		set_transient( $key, 'error', 5 * MINUTE_IN_SECONDS );
		return false;
	}

	set_transient( $key, $data, HOUR_IN_SECONDS );
	return $data;
}
