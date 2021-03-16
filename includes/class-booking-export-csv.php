<?php

/**
 * Handle CSV
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Booking_Export
 * @subpackage Booking_Export/includes
 */

/**
 * Handle CSV
 *
 * This class defines all code necessary to generate CSV
 *
 * @since      1.0.0
 * @package    Booking_Export
 * @subpackage Booking_Export/includes
 * @author     Your Name <email@example.com>
 */
class Booking_Export_CSV {
  public static function generate($filename, $headers, $list) {
    header('Content-Disposition: attachment; filename="' . $filename . '";');
    header('Content-Type: application/csv; charset=UTF-8');

    $f = fopen('php://output', 'w');

    fputs($f, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
    fputcsv($f, $headers);

    foreach ($list as $fields) {
        fputcsv($f, $fields);
    }
  }
}
