<?php
/**
 * Allowlists: kinds of job, US states and sort orders.
 *
 * The kind slugs are the ones the CareJobs.Now API accepts (the `kind` enum of
 * its `search_healthcare_jobs` tool). Anything not listed here is never sent.
 *
 * @package CareJobs_Healthcare_Jobs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kinds of job: slug => label.
 *
 * @return array<string,string>
 */
function carejobs_hj_kinds() {
	return array(
		'nurses'              => __( 'Nurses', 'carejobs-healthcare-jobs' ),
		'nurse-practitioners' => __( 'NPs & PAs', 'carejobs-healthcare-jobs' ),
		'physicians'          => __( 'Doctors', 'carejobs-healthcare-jobs' ),
		'nursing-assistants'  => __( 'CNAs & PCTs', 'carejobs-healthcare-jobs' ),
		'caregivers'          => __( 'Caregivers', 'carejobs-healthcare-jobs' ),
		'therapy-rehab'       => __( 'Therapy & rehab', 'carejobs-healthcare-jobs' ),
		'mental-health'       => __( 'Mental health', 'carejobs-healthcare-jobs' ),
		'pharmacy'            => __( 'Pharmacy', 'carejobs-healthcare-jobs' ),
		'imaging'             => __( 'Imaging', 'carejobs-healthcare-jobs' ),
		'lab'                 => __( 'Lab', 'carejobs-healthcare-jobs' ),
		'surgical-techs'      => __( 'Surgical', 'carejobs-healthcare-jobs' ),
		'emts-paramedics'     => __( 'EMS', 'carejobs-healthcare-jobs' ),
		'medical-assistants'  => __( 'Medical assistants', 'carejobs-healthcare-jobs' ),
		'dental'              => __( 'Dental', 'carejobs-healthcare-jobs' ),
		'admin'               => __( 'Front desk & admin', 'carejobs-healthcare-jobs' ),
		'allied-health'       => __( 'Allied health', 'carejobs-healthcare-jobs' ),
		'ai-training'         => __( 'AI training', 'carejobs-healthcare-jobs' ),
	);
}

/**
 * US states and DC: two-letter code => name.
 *
 * The state names are proper nouns and are not translated.
 *
 * @return array<string,string>
 */
function carejobs_hj_states() {
	return array(
		'AL' => 'Alabama',
		'AK' => 'Alaska',
		'AZ' => 'Arizona',
		'AR' => 'Arkansas',
		'CA' => 'California',
		'CO' => 'Colorado',
		'CT' => 'Connecticut',
		'DE' => 'Delaware',
		'DC' => 'District of Columbia',
		'FL' => 'Florida',
		'GA' => 'Georgia',
		'HI' => 'Hawaii',
		'ID' => 'Idaho',
		'IL' => 'Illinois',
		'IN' => 'Indiana',
		'IA' => 'Iowa',
		'KS' => 'Kansas',
		'KY' => 'Kentucky',
		'LA' => 'Louisiana',
		'ME' => 'Maine',
		'MD' => 'Maryland',
		'MA' => 'Massachusetts',
		'MI' => 'Michigan',
		'MN' => 'Minnesota',
		'MS' => 'Mississippi',
		'MO' => 'Missouri',
		'MT' => 'Montana',
		'NE' => 'Nebraska',
		'NV' => 'Nevada',
		'NH' => 'New Hampshire',
		'NJ' => 'New Jersey',
		'NM' => 'New Mexico',
		'NY' => 'New York',
		'NC' => 'North Carolina',
		'ND' => 'North Dakota',
		'OH' => 'Ohio',
		'OK' => 'Oklahoma',
		'OR' => 'Oregon',
		'PA' => 'Pennsylvania',
		'RI' => 'Rhode Island',
		'SC' => 'South Carolina',
		'SD' => 'South Dakota',
		'TN' => 'Tennessee',
		'TX' => 'Texas',
		'UT' => 'Utah',
		'VT' => 'Vermont',
		'VA' => 'Virginia',
		'WA' => 'Washington',
		'WV' => 'West Virginia',
		'WI' => 'Wisconsin',
		'WY' => 'Wyoming',
	);
}

/**
 * Sort orders: value => label.
 *
 * @return array<string,string>
 */
function carejobs_hj_sorts() {
	return array(
		'newest'      => __( 'Newest first', 'carejobs-healthcare-jobs' ),
		'highest_pay' => __( 'Highest pay first', 'carejobs-healthcare-jobs' ),
		'mixed'       => __( 'Mixed employers', 'carejobs-healthcare-jobs' ),
	);
}

/**
 * Default option values, in the shortcode's own (snake_case) naming.
 *
 * @return array<string,string>
 */
function carejobs_hj_defaults() {
	return array(
		'kind'    => '',
		'state'   => '',
		'query'   => '',
		'limit'   => '10',
		'sort'    => 'newest',
		'remote'  => '',
		'min_pay' => '',
		'title'   => '',
		'new_tab' => '',
	);
}

/**
 * The lists the block editor needs, as value/label pairs.
 *
 * @return array<string,array<int,array<string,string>>>
 */
function carejobs_hj_editor_data() {
	$out = array(
		'kinds'  => array(),
		'states' => array(),
		'sorts'  => array(),
	);
	foreach ( carejobs_hj_kinds() as $value => $label ) {
		$out['kinds'][] = array(
			'value' => $value,
			'label' => $label,
		);
	}
	foreach ( carejobs_hj_states() as $value => $label ) {
		$out['states'][] = array(
			'value' => $value,
			'label' => $label,
		);
	}
	foreach ( carejobs_hj_sorts() as $value => $label ) {
		$out['sorts'][] = array(
			'value' => $value,
			'label' => $label,
		);
	}
	return $out;
}
