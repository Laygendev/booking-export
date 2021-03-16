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

<table id="example" class="table table-datatable table-striped table-bordered" style="width:100%">
    <thead>
        <tr>
            <th>Propriétaire</th>
            <th>Montant TTC (Toutes propriétées incluses)</th>
            <th>Commission TTC (Toutes propriétées incluses)</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($entries as $owner):
            ?>
            <tr>
                <td><?php echo $owner['name']; ?></td>
                <td><?php echo $owner['amount']; ?>€</td>
                <td><?php echo $owner['comission']; ?>€</td>
                <td>
                    <a class="btn btn-primary" href="<?php echo admin_url('admin-post.php?action=export_by_owner_csv&_wpnonce=' . wp_create_nonce('export_by_owner_csv') . '&start_date=' . $start_date . '&end_date=' . $end_date . '&owner_id=' . $owner['id']); ?>">Exporter en CSV</a>
                    <a class="btn btn-primary" href="<?php echo admin_url('admin-post.php?action=export_by_owner_pdf&_wpnonce=' . wp_create_nonce('export_by_owner_pdf') . '&start_date=' . $start_date . '&end_date=' . $end_date . '&owner_id=' . $owner['id']); ?>">Exporter en PDF</a>
                </td>
            </tr>
            <?php
        endforeach;
        ?>
    </tbody>
    <tfoot>
        <tr>
            <th>Propriétaire</th>
            <th>Montant TTC (Toutes propriétées incluses)</th>
            <th>Commission TTC (Toutes propriétées incluses)</th>
            <th>Actions</th>
        </tr>
    </tfoot>
</table>
