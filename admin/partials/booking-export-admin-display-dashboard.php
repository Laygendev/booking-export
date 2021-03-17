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

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div id="wpbc-admin-page" class="wrap wpbc_page wpbc_dashboard">
  <h1 class="wpbc_header">
    <div class="wpbc_header_icon"></div>
    Tableau de bord
  </h1>

  <div class="wpbc_admin_message"></div>

  <div class="wpbc_admin_page">

    <div class="row mt-4">
    
        <div class="col-2">
            <ul class="nav sn-tabs flex-column">
                <li class="nav-item"><a class="nav-link <?php echo $tab == 'location' ? 'active': ''; ?>" href="<?php echo admin_url('admin.php?page=wpbc-dashboard&tab=location'); ?>">Propriétés</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $tab == 'owner' ? 'active': ''; ?>" href="<?php echo admin_url('admin.php?page=wpbc-dashboard&tab=owner'); ?>">Propriétaires</a></li>
            </ul>
        </div>

        <div class="col-10">
    
            <div class="row">
                <div class="col-12">
                    <form class="form-inline" action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                        <?php wp_nonce_field('filter_by_period'); ?>
                        <input type="hidden" name="action" value="filter_by_period" />
                        <input type="hidden" name="tab" value="<?php echo $tab; ?>" />


                        <div class="form-group">
                            <label for="start_date" class="mr-2">Du</label>
                            <input id="start_date" class="datepicker form-control mr-2" type="text" name="start_date" value="<?php echo $start_date; ?>" />
                        </div>

                        <div class="form-group">
                            <label for="end_date" class="mr-2">au</label>
                            <input id="end_date" class="datepicker form-control mr-2" type="text" name="end_date" value="<?php echo $end_date; ?>" />
                        </div>

                        <input class="btn btn-secondary" type="submit" value="Filtrer" />
                    </form>
                </div>
            </div>


            <div class="row mt-4">
                <div class="col-12 content">
                    <?php include $path . 'partials/booking-export-admin-display-dashboard-' . $tab . '.php'; ?>
                </div>
            </div>
        </div>
    </div>
  </div>
</div>