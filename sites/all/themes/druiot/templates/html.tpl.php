<?php
/**
 * @file
 * Basic html structure of a single Drupal page.
 */
?><!DOCTYPE html>
<html<?php print $html_attributes; ?><?php print $rdf_namespaces; ?>>
<head>
  <title><?php print $head_title; ?></title>
  <?php print $head; ?>
  <meta http-equiv="cleartype" content="on">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <?php print $styles; ?>
  <script type="text/javascript" src="http://dev.dru.io:35729/livereload.js?snipver=1"></script>
  <?php print $scripts; ?>
</head>
<body class="<?php print $classes; ?>" <?php print $attributes; ?>>
  <?php print $page_top; ?>
  <?php print $page; ?>
  <?php print $page_bottom; ?>
</body>
</html>
