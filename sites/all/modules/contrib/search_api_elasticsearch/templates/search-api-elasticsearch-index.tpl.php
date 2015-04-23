<?php

/**
 * @file
 */

?>

<h3><?php print $name; ?></h3>
<dl>
    <dt><?php print t('Status'); ?></dt>
    <dd>
      <?php print $enabled; ?>
    </dd>

    <dt><?php print t('Machine name'); ?></dt>
    <dd>
      <?php print $machine_name; ?>
    </dd>

    <dt><?php print t('Item type'); ?></dt>
    <dd>
      <?php print $item_type; ?>
    </dd>

    <?php if (!empty($description)) { ?>
      <dt><?php print t('Description'); ?></dt>
      <dd>
        <?php print $description; ?>
      </dd>
    <?php } ?>

    <?php if (!empty($server)) { ?>
      <dt><?php print t('Server'); ?></dt>
      <dd>
        <?php print $server; ?>
        <?php if (!empty($server_description)) { ?>
          <p class="description">'<?php $server_description; ?></p>
        <?php } ?>
      </dd>
    <?php } ?>

    <?php if (isset($read_only) && $read_only == FALSE) { ?>
      <dt><?php print t('Index options'); ?></dt>
      <dd>
        <dl>
          <dt><?php print $cron_limit; ?></dt>

          <?php if (isset($number_of_shards)) { ?>
            <dt><?php print $number_of_shards; ?></dt>
          <?php } ?>

          <?php if (isset($number_of_replicas)) { ?>
            <dt><?php print $number_of_replicas; ?></dt>
          <?php } ?>
        </dl>
      </dd>
    <?php } elseif (!empty($read_only)) { ?>
      <dt><?php print t('Read only'); ?></dt>
      <dd>
        <?php print $read_only; ?>
      </dd>
    <?php } ?>

    <dt><?php print t('Configuration status'); ?></dt>
    <dd>
      <?php print $configuration_status; ?>
    </dd>
</dl>
