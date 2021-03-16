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

		foreach ($results as &$result) {
			$result->meta_value = unserialize($result->meta_value);

			$result->resource_data = null;

			$result->form_data = get_form_content($result->form, $result->booking_type, null, [
				'booking_id' => $result->booking_id,
				'resource_title' => $result->title,
			] );

			foreach ($result->meta_value as $meta_value) {
				if ($meta_value['id'] == $result->booking_type) {
					$result->resource_data = $meta_value;
				}
			}

			$result->dates = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bookingdates WHERE booking_id=" . $result->booking_id . " AND approved=1" );

			$result->comission = $result->cost / 100 * $result->resource_data['price_comission'];

			
			$result->period = '';
			
			if (count($result->dates) == 1) {
				$result->period = date('d/m/Y', strtotime($result->dates[0]->booking_date));
			} else {
				$first_entry = array_shift($result->dates);
				$last_entry = end($result->dates);

				$result->period = date('d/m/Y', strtotime($first_entry->booking_date)) . ' au ' . date('d/m/Y', strtotime($last_entry->booking_date));
			}

			unset ($result->meta_value);
			unset ($result->form);
			unset ($result->dates);
		}

		return $results;
	}

	public static function get_owner_by_period($start_date, $end_date, $owner_id = null) {
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
			", [$start_date, $end_date] );

		$results = $wpdb->get_results( $prepare );

		$outputs = [];

		foreach ($results as &$result) {
			$result->meta_value = unserialize($result->meta_value);

			$result->resource_data = null;

			$result->form_data = get_form_content($result->form, $result->booking_type, null, [
				'booking_id' => $result->booking_id,
				'resource_title' => $result->title,
			] );

			foreach ($result->meta_value as $meta_value) {
				if ($meta_value['id'] == $result->booking_type) {
					$result->resource_data = $meta_value;
				}
			}

			$result->dates = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bookingdates WHERE booking_id=" . $result->booking_id . " AND approved=1" );

			$result->comission = $result->cost / 100 * $result->resource_data['price_comission'];
			
			$result->period = '';
			
			if (count($result->dates) == 1) {
				$result->period = date('d/m/Y', strtotime($result->dates[0]->booking_date));
			} else {
				$first_entry = array_shift($result->dates);
				$last_entry = end($result->dates);

				$result->period = date('d/m/Y', strtotime($first_entry->booking_date)) . ' au ' . date('d/m/Y', strtotime($last_entry->booking_date));
			}

			unset ($result->meta_value);
			unset ($result->form);
			unset ($result->dates);

			if ($owner_id == null || ($owner_id != null && $owner_id == $result->id)) {

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

		if ($owner_id != null && count($outputs) == 1) {
			return end($outputs);
		}

		return $outputs;
	}

	public static function date_to_mysql($date) {
		$tab_date = explode('/', $date);
		$date  = $tab_date[2].'-'.$tab_date[1].'-'.$tab_date[0];
		$date = date( 'Y-m-d', strtotime($date) );
		return $date;
	}
}
