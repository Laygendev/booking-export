<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Booking_Export
 * @subpackage Booking_Export/admin/partials
 */
?>

<table class="table table-datatable table-striped table-bordered" style="width:100%">
    <thead>
        <tr>
            <th>Propriétée</th>
            <th>Propriétaire</th>
            <th>Client</th>
            <th>Période</th>
            <th>Prix TTC</th>
            <th>Commission TTC</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($entries as $booking):
            ?>
            <tr>
                <td><?php echo $booking->title; ?></td>
                <td><?php echo $booking->post_title; ?></td>
                <td><?php echo $booking->form_data['name'] . ' ' . $booking->form_data['secondname']; ?></td>
                <td><?php echo $booking->period; ?></td>
                <td><?php echo $booking->cost ?>€</td>
                <td><?php echo $booking->comission . '€ (' . $booking->resource_data['price_comission'] . '%)'; ?></td>
            </tr>
            <?php
        endforeach;
        ?>
    </tbody>
    <tfoot>
    <tr>
            <th>Propriétée</th>
            <th>Propriétaire</th>
            <th>Client</th>
            <th>Période</th>
            <th>Prix</th>
            <th>Commission</th>
        </tr>
    </tfoot>
</table>

<a class="btn btn-primary" href="<?php echo admin_url('admin-post.php?action=export_by_period&_wpnonce=' . wp_create_nonce('export_by_period') . '&start_date=' . $start_date . '&end_date=' . $end_date); ?>">Exporter</a>
