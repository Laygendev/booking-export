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

<div class="sn-form">
  <?php
  foreach ($resources as $key => $resource):
    ?>
    <div data-number="<?php echo $key; ?>" class="sn-flex bloc-form bloc-form-to-duplicate">
      <span class="sn-w-10">Resource #<span class="key"><?php echo ($key + 1); ?></span></span>

      <div class="sn-w-100 sn-flex sn-flex-column">
        <div class="sn-w-100 sn-flex sn-form-group">
          <label class="sn-w-10" for="resource_id_<?php echo $key; ?>">Resource ID</label>
          <select class="sn-w-100" id="resource_id_<?php echo $key; ?>" name="resources[<?php echo $key; ?>][id]">
            <?php
            foreach ($resources_def as $resource_def):
              ?>
              <option <?php selected($resource_def['id'], $resource['id']); ?> value="<?php echo $resource_def['id']; ?>"><?php echo $resource_def['title']; ?></option>
              <?php
            endforeach;
            ?>
          </select>
        </div>

        <div class="sn-w-100 sn-flex sn-form-group">
          <label class="sn-w-10"  for="resource_price_<?php echo $key; ?>">Comission (€)</label>
          <input class="sn-w-100" id="resource_price_<?php echo $key; ?>" type="number" name="resources[<?php echo $key; ?>][price_comission]" value="<?php echo $resource["price_comission"]; ?>" />
        </div>
      </div>
      
      <span class="sn-w-10 sn-text-center"><a class="button button-danger delete-resource"><span class="dashicons dashicons-trash"></span></a></span>
    </div>
    <?php
  endforeach;
  ?>
</div>

<div class="sn-text-right">
  <hr />
  <a class="button button-primary add-resource">Ajouter une ressource</a>
</div>