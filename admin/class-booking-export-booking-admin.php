<?php

/**
 * Booking Class
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Booking_Export
 * @subpackage Booking_Export/admin
 */

/**
 * Booking Class
 *
 * @package    Booking_Export
 * @subpackage Booking_Export/admin
 * @author     Your Name <email@example.com>
 */
class Booking_Class_Admin {

	public static function get_booking_by_period($start_date, $end_date) {
		global $wpdb;

		$results = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}booking WHERE is_new = %d", 0 ) );

		return $results;
	}
}
