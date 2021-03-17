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

		$start_date = Booking_Class_Admin::date_to_mysql($start_date) . ' 00:00:00';
		$end_date = Booking_Class_Admin::date_to_mysql($end_date) . ' 23:59:59';

		$prepare = $wpdb->prepare( "
			SELECT 
				b.booking_id,
				b.booking_type,
				b.form,
				b.cost,
				bt.title,
				b.pay_status,
				sbd.booking_date,
				p.post_title,
				pm.meta_value
			FROM {$wpdb->prefix}booking b
			RIGHT JOIN {$wpdb->prefix}bookingtypes bt ON b.booking_type=bt.booking_type_id
			RIGHT JOIN {$wpdb->prefix}postmeta pm ON pm.meta_key='resources'
			RIGHT JOIN {$wpdb->prefix}posts p ON p.id=pm.post_id,
			(
				SELECT bd.booking_id,
					bd.booking_date,
					MAX(bd.booking_date)
					FROM {$wpdb->prefix}bookingdates bd
					WHERE bd.approved = 1 AND
						bd.booking_date BETWEEN '%s' AND '%s'
					GROUP BY bd.booking_id
			) sbd
			WHERE b.is_new = 0
				AND b.pay_status != ''
				AND b.booking_id = sbd.booking_id
			", [$start_date, $end_date] );

		$results = $wpdb->get_results( $prepare );

		$outputs = [];

		foreach ($results as &$result) {
			$result = Booking_Class_Admin::get_reservation_detail($result);

			if ($result->to_delete) {
				continue;
			}

			$outputs[] = $result;
		}

		return $outputs;
	}

	public static function get_owner_by_period($start_date, $end_date, $owner_ids = []) {
		global $wpdb;

		$start_date = Booking_Class_Admin::date_to_mysql($start_date) . ' 00:00:00';
		$end_date = Booking_Class_Admin::date_to_mysql($end_date) . ' 23:59:59';

		$query = "
		SELECT 
			b.booking_id,
			b.booking_type,
			b.form,
			b.cost,
			bt.title,
			b.pay_status,
			sbd.booking_date,
			p.id,
			p.post_title,
			pm.meta_value
		FROM {$wpdb->prefix}booking b
		RIGHT JOIN {$wpdb->prefix}bookingtypes bt ON b.booking_type=bt.booking_type_id
		RIGHT JOIN {$wpdb->prefix}postmeta pm ON pm.meta_key='resources'
		RIGHT JOIN {$wpdb->prefix}posts p ON p.id=pm.post_id,
		(
			SELECT bd.booking_id,
				bd.booking_date,
				MAX(bd.booking_date)
				FROM {$wpdb->prefix}bookingdates bd
				WHERE bd.approved = 1 AND
					bd.booking_date BETWEEN '%s' AND '%s'
				GROUP BY bd.booking_id
		) sbd
		WHERE b.is_new = 0
			AND b.pay_status != ''
			AND b.booking_id = sbd.booking_id
		";

		if (!empty($owner_ids)) {
			$query .= ' AND (';
			foreach ($owner_ids as $id) {
				$query .= "p.id=$id OR ";
			}

			$query = substr($query, 0, -3);
			
			$query .= ")";
		}

		$prepare = $wpdb->prepare( $query, [$start_date, $end_date] );

		$results = $wpdb->get_results( $prepare );

		$outputs = [];

		foreach ($results as &$result) {
			$result = Booking_Class_Admin::get_reservation_detail($result);

			if ($result->to_delete) {
				continue;
			}

			if (empty($owner_ids) || ! empty($owner_ids) && in_array($result->id, $owner_ids)) {

				if ( !isset($outputs[$result->id]) ) {
					$outputs[$result->id] = [
						'id' => $result->id,
						'name' => $result->post_title,
						'reservations' => [],
						'amount' => 0,
						'comission' => 0,
					];
				}

				$outputs[$result->id]['reservations'][] = $result;
				$outputs[$result->id]['amount'] += $result->cost;
				$outputs[$result->id]['comission'] += $result->comission;
			}
		}

		/*if (! empty($owner_ids) && count($outputs) == 1) {
			return end($outputs);
		}*/

		return $outputs;
	}

	public static function get_booking_by_ids($ids) {
		global $wpdb;

		$query = "SELECT 
			b.booking_id,
			b.booking_type,
			b.form,
			b.cost,
			bt.title,
			b.pay_status,
			sbd.booking_date,
			p.post_title,
			pm.meta_value
		FROM {$wpdb->prefix}booking b
		RIGHT JOIN {$wpdb->prefix}bookingtypes bt ON b.booking_type=bt.booking_type_id
		RIGHT JOIN {$wpdb->prefix}postmeta pm ON pm.meta_key='resources'
		RIGHT JOIN {$wpdb->prefix}posts p ON p.id=pm.post_id,
		(
			SELECT bd.booking_id,
				bd.booking_date,
				MAX(bd.booking_date)
				FROM {$wpdb->prefix}bookingdates bd
				WHERE bd.approved = 1
				GROUP BY bd.booking_id
		) sbd
		WHERE b.is_new = 0
			AND b.pay_status != ''
			AND b.booking_id = sbd.booking_id AND (";

			
		foreach ($ids as $id) {
			$query .= "b.booking_id=$id OR ";
		}

		$query = substr($query, 0, -3);
		
		$query .= ")";

		$results = $wpdb->get_results( $query );

		foreach ($results as &$result) {
			$result = Booking_Class_Admin::get_reservation_detail($result);
		}

		return $results;
	}

	public static function get_reservation_detail($booking) {
		global $wpdb;

		$booking->meta_value = unserialize($booking->meta_value);

		$booking->resource_data = null;

		$booking->form_data = get_form_content($booking->form, $booking->booking_type, null, [
			'booking_id' => $booking->booking_id,
			'resource_title' => $booking->title,
		] );

		foreach ($booking->meta_value as $meta_value) {
			if ($meta_value['id'] == $booking->booking_type) {
				$booking->resource_data = $meta_value;
			}
		}

		$booking->to_delete = false;

		if ( $booking->resource_data == null ) {
			$booking->to_delete = true;
			return $booking;
		}

		$booking->dates = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bookingdates WHERE booking_id=" . $booking->booking_id . " AND approved=1" );

		$booking->comission = $booking->cost / 100 * $booking->resource_data['price_comission'];
		
		$booking->period = '';
		$booking->start_date = null;
		$booking->end_date = null;
		$booking->start_datetime = null;
		$booking->end_datetime = null;
		$booking->number_night = null;
		
		if (count($booking->dates) == 1) {
			$booking->start_datetime = new DateTime($booking->dates[0]->booking_date);
			$booking->end_datetime = new DateTime($booking->dates[0]->booking_date);

			$booking->period = date('d/m/Y', strtotime($booking->dates[0]->booking_date));
			$booking->start_date = date('d/m/Y', strtotime($booking->dates[0]->booking_date));
			$booking->end_date = date('d/m/Y', strtotime($booking->dates[0]->booking_date));

		} else {
			$first_entry = array_shift($booking->dates);
			$last_entry = end($booking->dates);

			$booking->start_datetime = new DateTime($first_entry->booking_date);
			$booking->end_datetime = new DateTime($last_entry->booking_date);

			$booking->period = date('d/m/Y', strtotime($first_entry->booking_date)) . ' au ' . date('d/m/Y', strtotime($last_entry->booking_date));
			$booking->start_date = date('d/m/Y', strtotime($first_entry->booking_date));
			$booking->end_date = date('d/m/Y', strtotime($last_entry->booking_date));
		}

		$interval = date_diff($booking->start_datetime, $booking->end_datetime);
		$booking->number_night = $interval->format('%a') + 1;

		unset ($booking->meta_value);
		unset ($booking->form);
		unset ($booking->dates);

		return $booking;
	}

	public static function date_to_mysql($date) {
		$tab_date = explode('/', $date);
		$date  = $tab_date[2].'-'.$tab_date[1].'-'.$tab_date[0];
		$date = date( 'Y-m-d', strtotime($date) );
		return $date;
	}
}
