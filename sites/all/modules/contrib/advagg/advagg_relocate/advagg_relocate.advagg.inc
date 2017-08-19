<?php

/**
 * @file
 * Advanced aggregation relocate module.
 */

/**
 * @addtogroup advagg_hooks
 * @{
 */

/**
 * Implements hook_advagg_get_css_aggregate_contents_alter().
 */
function advagg_relocate_advagg_get_css_aggregate_contents_alter(&$data, $files, $aggregate_settings) {
  // Set variables if needed.
  if (!isset($aggregate_settings['variables']['advagg_relocate_css_inline_import'])) {
    $aggregate_settings['variables']['advagg_relocate_css_inline_import'] = variable_get('advagg_relocate_css_inline_import', ADVAGG_RELOCATE_CSS_INLINE_IMPORT);
  }

  // Do nothing if this is disabled.
  if (!$aggregate_settings['variables']['advagg_relocate_css_inline_import']) {
    return;
  }

  if (strpos($data, '@import') !== FALSE) {
    // Set values that will be used when preg_replace_callback is ran.
    _advagg_relocate_callback(array(), $files, $aggregate_settings);

    // Replace external import statements with the contents of them.
    $data = preg_replace_callback('%@import\s*+(?:url\(\s*+)?+[\'"]?+((?:http:\/\/|https:\/\/|\/\/)(?:[^\'"()\s]++))[\'"]?+\s*+\)?+\s*+;%i', '_advagg_relocate_callback', $data);
  }
}

/**
 * @} End of "addtogroup advagg_hooks".
 */

/**
 * Gets external CSS files and puts the contents of it in the aggregate.
 *
 * @param array $matches
 *   Array of matched items from preg_replace_callback().
 * @param array $files
 *   List of files with the media type.
 * @param array $aggregate_settings
 *   Array of settings.
 *
 * @return string
 *   Contents of the import statement.
 */
function _advagg_relocate_callback(array $matches = array(), array $files = array(), array $aggregate_settings = array()) {
  // Store values for preg_replace_callback callback.
  $_args = &drupal_static(__FUNCTION__, array());
  if (!empty($files)) {
    $_args['files'] = $files;
  }
  if (!empty($aggregate_settings)) {
    $_args['aggregate_settings'] = $aggregate_settings;
  }
  // Short circuit if no matches were passed in.
  if (empty($matches)) {
    return '';
  }

  // Bail if not matched.
  if (empty($matches[1])) {
    return $matches[0];
  }

  // Check URL.
  if (!advagg_relocate_check_domain_of_font_url($matches[1], $_args['aggregate_settings'])) {
    return $matches[0];
  }

  // Check per file settings.
  if (!isset($_args['aggregate_settings']['variables']['advagg_relocate_css_file_settings'])) {
    $_args['aggregate_settings']['variables']['advagg_relocate_css_file_settings'] = variable_get('advagg_relocate_css_file_settings', array());
  }
  foreach ($_args['files'] as $filename => $values) {
    $form_api_filename = str_replace(array('/', '.'), array('__', '--'), $filename);
    if (isset($_args['aggregate_settings']['variables']['advagg_relocate_css_file_settings'][$form_api_filename])) {
      foreach ($_args['aggregate_settings']['variables']['advagg_relocate_css_file_settings'][$form_api_filename] as $key => $value) {
        if ($key !== $value && $key === $matches[1]) {
          // Do not replace.
          return $matches[0];
        }
      }
    }
  }

  $font_faces = advagg_relocate_get_remote_font_data($matches[1], $_args['aggregate_settings']);
  return advagg_relocate_font_face_parser($font_faces);
}

/**
 * Given an array of font data output a new CSS string.
 *
 * @param array $font_faces
 *   Array of font data.
 *
 * @return string
 *   String of CSS font data.
 */
function advagg_relocate_font_face_parser(array $font_faces) {
  $new_css = '';
  foreach ($font_faces as $values => $src) {
    $output = '';
    $output .= str_replace('; ', ";\n", $values);
    if (isset($src['eot'])) {
      $output .= "src: {$src['eot']};\n";
    }
    $output .= 'src:';
    foreach ($src as $key => $location) {
      if (is_numeric($key)) {
        $output .= "$location,";
      }
    }
    if (isset($src['eot'])) {
      $src['eot'] = str_replace('.eot', '.eot?#iefix', $src['eot']);
      $output .= "{$src['eot']} format('embedded-opentype'),";
    }
    if (isset($src['woff2'])) {
      $output .= "{$src['woff2']},";
    }
    if (isset($src['woff'])) {
      $output .= "{$src['woff']},";
    }
    if (isset($src['ttf'])) {
      $output .= "{$src['ttf']},";
    }
    if (isset($src['svg'])) {
      $output .= "{$src['svg']},";
    }
    $output = str_replace(array('),l', '),u'), array("),\nl", "),\nu"), trim($output, ',') . ';');
    $new_css .= "@font-face {\n$output\n}\n";
  }

  return $new_css;
}

/**
 * Gets external CSS and JS files; caches and returns response.
 *
 * @param string $urls
 *   URLs to get.
 * @param string $type
 *   Will be css or js.
 * @param array $options
 *   Array of settings for the http request.
 * @param bool $force_check
 *   TRUE if you want to force check the external source.
 *
 * @return array
 *   Array of http responses.
 */
function advagg_relocate_get_remote_data($urls, $type, array $options = array(), $force_check = FALSE) {
  // Set arguments for drupal_http_request().
  $options += array(
    'headers' => array(
      'Accept-Encoding' => 'gzip, deflate',
      'Connection' => 'close',
      'Referer' => $GLOBALS['base_root'] . request_uri(),
    ),
    'timeout' => 8,
    'version' => '1.0',
  );
  if (function_exists('brotli_uncompress')) {
    $options['headers']['Accept-Encoding'] .= ', br';
  }

  // Build CID.
  $cids = array();
  foreach ($urls as $k => $v) {
    $cids["advagg_relocate_{$type}_external:{$k}"] = "advagg_relocate_{$type}_external:{$k}";
  }

  // Try local cache.
  $return = array();
  $responses = array();
  $cached_data = cache_get_multiple($cids, 'cache_advagg_info');
  $cached_data = array_merge($cids, $cached_data);
  $url_to_cid = array();
  foreach ($cached_data as $cid => $cache) {
    // Reset headers if needed.
    if (isset($options['headers']['If-None-Match'])) {
      unset($options['headers']['If-None-Match']);
    }
    if (isset($options['headers']['If-Modified-Since'])) {
      unset($options['headers']['If-Modified-Since']);
    }

    // Use cached data or setup for 304.
    if (!empty($cache->data)) {
      if ($cache->expire >= REQUEST_TIME && isset($cache->data->url) && !$force_check) {
        $return[$cache->data->url] = $cache->data;
        continue;
      }
      else {
        // Set header for 304 response.
        if (isset($cached_data->data->headers['etag'])) {
          $options['headers']['If-None-Match'] = $cached_data->data->headers['etag'];
        }
        if (isset($cached_data->created)) {
          $options['headers']['If-Modified-Since'] = gmdate('D, d M Y H:i:s T', $cached_data->created);
        }
      }
    }

    // Set cid, filename and get url.
    $options['cid'] = $cid;
    $options['filename'] = substr($cid, 26 + strlen($type));
    $url = advagg_force_https_path($urls[$options['filename']]);
    $url_to_cid[$url] = $cid;

    // Get data.
    if (module_exists('httprl')) {
      httprl_request($url, $options);
    }
    else {
      $responses[$url] = drupal_http_request($url, $options);
    }
  }
  if (module_exists('httprl')) {
    $responses = httprl_send_request();
  }
  if (empty($responses)) {
    return $return;
  }

  // Try failures again.
  foreach ($responses as $key => $response) {
    if ($response->code == 0) {
      if (isset($response->options)) {
        $options = $response->options;
      }
      $responses[$key] = drupal_http_request($key, $options);
    }
  }

  // Process remote data.
  foreach ($responses as $url => $response) {
    // No url is a no go.
    if (empty($response->url)) {
      $response->url = $url;
    }
    if (isset($response->options['cid'])) {
      $cid = $response->options['cid'];
    }
    elseif (isset($url_to_cid[$response->url])) {
      $cid = $url_to_cid[$response->url];
    }
    else {
      // Can't match up url to the cid.
      continue;
    }
    // Update object.
    if (!isset($response->options['filename'])) {
      $response->options['filename'] = substr($cid, 26 + strlen($type));
    }
    if (!isset($response->options['cid'])) {
      $response->options['cid'] = $cid;
    }

    advagg_relocate_process_http_request($response, $type);

    if ($response->code == 304 && !empty($cached_data->data)) {
      // Update cache expire time.
      cache_set($cid, $cached_data->data, 'cache_advagg_info', REQUEST_TIME + $response->ttl);

      // Return cached data.
      $return[$cached_data->data->url] = $cached_data->data;
    }

    // Skip if not a 200.
    if ($response->code != 200
      && $response->code != 201
      && $response->code != 202
      && $response->code != 206
    ) {
      continue;
    }
    if (empty($response->data)) {
      continue;
    }

    $response->local_cache = FALSE;
    // Save data to the cache.
    if (!empty($response->data)) {
      $response->hash = drupal_hash_base64($response->data);
      $response->local_cache = TRUE;
      cache_set($cid, $response, 'cache_advagg_info', REQUEST_TIME + $response->ttl);
      $response->local_cache = FALSE;
    }
    $return[$response->url] = $response;
  }
  return $return;
}

/**
 * Get the TTL and decompress the data.
 *
 * @param object $response
 *   Response from http request.
 * @param string $type
 *   Can be css, js, or font.
 */
function advagg_relocate_process_http_request(&$response, $type) {
  // Get ttl from response.
  $ttl = 0;
  $now = REQUEST_TIME;
  if (isset($response->headers['expires'])) {
    $expires = strtotime($response->headers['expires']);
    if (isset($response->headers['date'])) {
      $now = max($now, strtotime($response->headers['date']));
    }
    $ttl = max($ttl, $expires - $now);
  }
  if (isset($response->headers['cache-control'])) {
    $cache_control_array = advagg_relocate_parse_cache_control($response->headers['cache-control']);
    if (isset($cache_control_array['max-age']) && is_numeric($cache_control_array['max-age'])) {
      $ttl = max($ttl, $cache_control_array['max-age']);
    }
    if (isset($cache_control_array['s-maxage']) && is_numeric($cache_control_array['s-maxage'])) {
      $ttl = max($ttl, $cache_control_array['s-maxage']);
    }
  }
  $response->ttl = $ttl;

  // Uncompress.
  if (!empty($response->headers['content-encoding'])
    && !empty($response->data)
    && !isset($response->chunk_size)
    && ($response->headers['content-encoding'] === 'gzip'
      || $response->headers['content-encoding'] === 'deflate'
      || $response->headers['content-encoding'] === 'br'
  )) {
    // Do the first level of decoding if not already done.
    if ($response->headers['content-encoding'] === 'gzip') {
      $response->data = @gzinflate(substr($response->data, 10));
    }
    elseif ($response->headers['content-encoding'] === 'deflate') {
      $response->data = @gzinflate($response->data);
    }
    elseif ($response->headers['content-encoding'] === 'br' && is_callable('brotli_uncompress')) {
      $response->data = @brotli_uncompress($response->data);
    }
  }

  // If a BOM is found, convert the string to UTF-8.
  $encoding = advagg_get_encoding_from_bom($response->data);
  if (!empty($encoding)) {
    $response->data = advagg_convert_to_utf8($response->data, $encoding);
  }

  drupal_alter('advagg_relocate_process_http_request', $response, $type);
}

/**
 * Implements hook_advagg_relocate_process_http_request_alter().
 */
function advagg_relocate_advagg_relocate_process_http_request_alter(&$response, $type) {
  if ($type === 'js') {
    if (strpos($response->url, 'connect.facebook.net/en_US/fbevents.js') !== FALSE) {
      $baseURL = 'https://connect.facebook.net';
      // Fix loader so it works if not loaded from connect.facebook.net.
      $response->data = str_replace("function xb(){var ac=null,bc=null,cc=rb.getElementsByTagName('script');", "function xb(){var ac='$baseURL',cc=rb.getElementsByTagName('script'),bc=cc[0];", $response->data);

      // Get Facebook IDs.
      $fb_ids = array_filter(array_map('trim', explode("\n", variable_get('advagg_relocate_js_fbevents_local_ids', ADVAGG_RELOCATE_JS_FBEVENTS_LOCAL_IDS))));
      if (empty($fb_ids)) {
        return;
      }

      // Get Facebook Version.
      $matches = array();
      preg_match('/fbq.version\s*=\s*[\'"]([\.\d]+)[\'"]/', $response->data, $matches);
      if (empty($matches[1])) {
        return;
      }
      $version = $matches[1];

      // Update local copies of the /signals/config/ js.
      $js = array();
      foreach ($fb_ids as $fb_id) {
        $url = "$baseURL/signals/config/$fb_id?v=$version";
        $js[$url]['data'] = $url;
        $js[$url]['type'] = 'external';
        $js[$url]['#fbid'] = $fb_id;
      }
      if (!empty($js)) {
        advagg_relocate_js_post_alter($js, TRUE);
      }
      // Get a list of the local copies for this version.
      $local_copies = array();
      foreach ($js as $values) {
        if ($values['type'] === 'file') {
          // Create an aggregate just for this file.
          $values += drupal_js_defaults($values);
          $elements = array($values);
          $groups = advagg_group_js($elements);
          _advagg_aggregate_js($groups);
          if (isset($groups[0]['data'])) {
            $local_copies[$values['#fbid']] = advagg_file_create_url($groups[0]['data']);
          }
        }
      }
      if (empty($local_copies)) {
        return;
      }

      // Add the local copies to the js file.
      $local_copies = json_encode($local_copies);
      $response->data = str_replace("return {baseURL:ac,scriptElement:bc}", "return {baseURL:ac,scriptElement:bc,localCopies:$local_copies}", $response->data);
      // Change logic so it'll use the local copy if it exists.
      $response->data = str_replace("var yc=xc.baseURL+'/signals/config/'+vc+'?v='+wc,zc=rb.createElement('script');zc.src=yc;", "var yc=xc.baseURL+'/signals/config/'+vc+'?v='+wc,zc=rb.createElement('script');if (xc.localCopies && xc.localCopies[vc]) {yc = xc.localCopies[vc];}zc.src=yc;", $response->data);
      // Change logic so it'll use the local copy if it exists.
      $response->data = str_replace("if(sb.baseURL&&sb.scriptElement){var tb=sb.baseURL+'/signals/plugins/'+qb+'.js?v='+g.version;", "if(sb.baseURL&&sb.scriptElement){var tb=sb.baseURL+'/signals/plugins/'+qb+'.js?v='+g.version;if (sb.localCopies && sb.localCopies[qb]) {yc = sb.localCopies[qb];}", $response->data);
    }
  }
}

/**
 * Gets external CSS files; caches it and returns css font rules.
 *
 * @param string $url
 *   URL of the CSS file to import.
 * @param array $aggregate_settings
 *   Array of settings.
 *
 * @return array
 *   Array of font data.
 */
function advagg_relocate_get_remote_font_data($url, array $aggregate_settings) {
  // Set settings if needed.
  if (!isset($aggregate_settings['variables']['advagg_relocate_css_inline_import_browsers'])) {
    $defaults = array(
      'woff2' => 'woff2',
      'woff' => 'woff',
      'ttf' => 'ttf',
    );
    $aggregate_settings['variables']['advagg_relocate_css_inline_import_browsers'] = variable_get('advagg_relocate_css_inline_import_browsers', $defaults);
  }

  // Set arguments for drupal_http_request().
  $options = array(
    'headers' => array(
      'Accept-Encoding' => 'gzip, deflate',
      'Connection' => 'close',
      'Referer' => $GLOBALS['base_root'] . request_uri(),
    ),
    'timeout' => 8,
    'version' => '1.0',
  );
  if (function_exists('brotli_uncompress')) {
    $options['headers']['Accept-Encoding'] .= ', br';
  }

  // Build CID.
  $aggregate_settings['variables']['advagg_relocate_css_inline_import_browsers'] = array_filter($aggregate_settings['variables']['advagg_relocate_css_inline_import_browsers']);
  $fonts = implode(',', $aggregate_settings['variables']['advagg_relocate_css_inline_import_browsers']);
  $cid = "advagg_relocate_css_inline_import:$fonts:$url";

  // Try local cache.
  $cached_data = cache_get($cid, 'cache_advagg_info');
  if (!empty($cached_data->data[0])) {
    if ($cached_data->expire >= REQUEST_TIME) {
      return $cached_data->data[0];
    }
    else {
      // Set header for 304 response.
      // $options['headers']['If-None-Match'] = $response->headers['etag'];.
      $options['headers']['If-Modified-Since'] = gmdate('D, d M Y H:i:s T', $cached_data->created);
    }
  }

  // Get external data.
  $responses = array();
  if (module_exists('httprl')) {
    // Get ttf.
    if (!empty($aggregate_settings['variables']['advagg_relocate_css_inline_import_browsers']['ttf'])) {
      $options['#font-type'] = 'ttf';
      httprl_request($url . '#ttf', $options);
    }

    // Get eot.
    if (!empty($aggregate_settings['variables']['advagg_relocate_css_inline_import_browsers']['eot'])) {
      $options['#font-type'] = 'eot';
      $options['headers']['User-Agent'] = 'Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 6.0; en-US)';
      httprl_request($url . '#eot', $options);
    }

    // Get svg.
    if (!empty($aggregate_settings['variables']['advagg_relocate_css_inline_import_browsers']['svg'])) {
      $options['#font-type'] = 'svg';
      $options['headers']['User-Agent'] = 'Mozilla/5.0 (iPad; U; CPU OS 3_2_2 like Mac OS X; nl-nl) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B500 Safari/531.21.10';
      httprl_request($url . '#svg', $options);
    }

    // Get woff.
    if (!empty($aggregate_settings['variables']['advagg_relocate_css_inline_import_browsers']['woff'])) {
      $options['#font-type'] = 'woff';
      $options['headers']['User-Agent'] = 'Mozilla/5.0 (Windows; U; MSIE 9.0; Windows NT 9.0; en-US)';
      httprl_request($url . '#woff', $options);
    }

    // Get woff2.
    if (!empty($aggregate_settings['variables']['advagg_relocate_css_inline_import_browsers']['woff2'])) {
      $options['#font-type'] = 'woff2';
      $options['headers']['User-Agent'] = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1';
      httprl_request($url . '#woff2', $options);
    }

    $responses = httprl_send_request();
  }
  else {
    // Get ttf.
    if (!empty($aggregate_settings['variables']['advagg_relocate_css_inline_import_browsers']['ttf'])) {
      $options['#font-type'] = 'ttf';
      $responses['ttf'] = drupal_http_request($url . '#ttf', $options);
    }

    // Get eot.
    if (!empty($aggregate_settings['variables']['advagg_relocate_css_inline_import_browsers']['eot'])) {
      $options['#font-type'] = 'eot';
      $options['headers']['User-Agent'] = 'Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 6.0; en-US)';
      $responses['eot'] = drupal_http_request($url . '#eot', $options);
    }

    // Get svg.
    if (!empty($aggregate_settings['variables']['advagg_relocate_css_inline_import_browsers']['svg'])) {
      $options['#font-type'] = 'svg';
      $options['headers']['User-Agent'] = 'Mozilla/5.0 (iPad; U; CPU OS 3_2_2 like Mac OS X; nl-nl) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B500 Safari/531.21.10';
      $responses['svg'] = drupal_http_request($url . '#svg', $options);
    }

    // Get woff.
    if (!empty($aggregate_settings['variables']['advagg_relocate_css_inline_import_browsers']['woff'])) {
      $options['#font-type'] = 'woff';
      $options['headers']['User-Agent'] = 'Mozilla/5.0 (Windows; U; MSIE 9.0; Windows NT 9.0; en-US)';
      $responses['woff'] = drupal_http_request($url . '#woff', $options);
    }

    // Get woff2.
    if (!empty($aggregate_settings['variables']['advagg_relocate_css_inline_import_browsers']['woff2'])) {
      $options['#font-type'] = 'woff2';
      $options['headers']['User-Agent'] = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1';
      $responses['woff2'] = drupal_http_request($url . '#woff2', $options);
    }
  }

  // Try failures again.
  foreach ($responses as $key => $response) {
    if ($response->code == 0) {
      $responses[$key] = drupal_http_request($response->url, $response->options);
    }
  }

  // Parse data.
  $font_faces = array();
  $ttl = 0;
  foreach ($responses as $key => $response) {
    if ($response->code == 304 && !empty($cached_data->data[0])) {
      // This might need to be better handled in the future.
      return $cached_data->data[0];
    }

    // Set the font type if not set.
    if (empty($response->options['#font-type'])) {
      if (!is_numeric($key)) {
        $response->options['#font-type'] = $key;
      }
      else {
        continue;
      }
    }

    if ($response->code != 200
      && $response->code != 201
      && $response->code != 202
      && $response->code != 206
    ) {
      return FALSE;
    }
    if (empty($response->data)) {
      return FALSE;
    }

    advagg_relocate_process_http_request($response, 'font');
    $ttl = max($ttl, $response->ttl);

    // Parse the CSS.
    $font_face = advagg_relocate_parse_css_font_face(
      $response->data,
      array('font-family', 'font-style', 'font-weight', 'src'),
      $response->options['#font-type']
    );

    // Format into a better data structure and combine.
    foreach ($font_face as $k => $values) {
      if (!isset($font_faces[$k])) {
        $font_faces[$k] = $font_face[$k];
        continue;
      }

      foreach ($values as $index => $value) {
        if (!in_array($value, $font_faces[$k])) {
          if ($index === $response->options['#font-type']) {
            $font_faces[$k][$index] = $values[$index];
          }
          else {
            $font_faces[$k][] = $values[$index];
          }
        }
      }
    }
  }

  // Save data to the cache.
  if (!empty($font_faces)) {
    cache_set($cid, array($font_faces, $responses), 'cache_advagg_info', REQUEST_TIME + $ttl);
  }
  return $font_faces;
}

/**
 * Parse the cache-control string into a key value array.
 *
 * @param string $cache_control
 *   The cache-control string.
 *
 * @return array
 *   Returns a key value array.
 */
function advagg_relocate_parse_cache_control($cache_control) {
  $cache_control_array = explode(',', $cache_control);
  $cache_control_array = array_map('trim', $cache_control_array);
  $cache_control_parsed = array();
  foreach ($cache_control_array as $value) {
    if (strpos($value, '=') !== FALSE) {
      $temp = array();
      parse_str($value, $temp);
      $cache_control_parsed += $temp;
    }
    else {
      $cache_control_parsed[$value] = TRUE;
    }
  }
  return $cache_control_parsed;
}

/**
 * Parse the font family string into a structured array.
 *
 * @param string $css_string
 *   The raw css string.
 * @param array $properties
 *   The css properties to get.
 * @param string $type
 *   The type of font file.
 *
 * @return array
 *   Returns a key value array.
 */
function advagg_relocate_parse_css_font_face($css_string, array $properties, $type) {
  // Get the CSS that contains a font-family rule.
  $length = strlen($css_string);
  $property_position = 0;
  $lower = strtolower($css_string);

  $attributes = array();
  foreach ($properties as $property) {
    while (($property_position = strpos($lower, $property, $property_position)) !== FALSE) {
      // Find the start of the values for the property.
      $start_of_values = strpos($css_string, ':', $property_position);
      // Get the property at this location of the css.
      $property_in_loop = trim(substr($css_string, $property_position, ($start_of_values - $property_position)));

      // Make sure this property is one of the ones we're looking for.
      if ($property_in_loop !== $property) {
        $property_position += strlen($property);
        continue;
      }

      // Get position of last closing bracket plus 1 (start of this section).
      $start = strrpos($css_string, '}', -($length - $property_position));
      if ($start === FALSE) {
        // Property is in the first selector and a declaration block (full rule
        // set).
        $start = 0;
      }
      else {
        // Add one to start after the }.
        $start++;
      }

      // Get closing bracket (end of this section).
      $end = strpos($css_string, '}', $property_position);
      if ($end === FALSE) {
        // The end is the end of this file.
        $end = $length;
      }

      // Get closing ; in order to get end of the declaration of the property.
      $declaration_end_a = strpos($css_string, ';', $property_position);
      $declaration_end_b = strpos($css_string, '}', $property_position);
      if ($declaration_end_a === FALSE) {
        $declaration_end = $declaration_end_b;
      }
      else {
        $declaration_end = min($declaration_end_a, $declaration_end_b);
      }
      if ($declaration_end > $end) {
        $declaration_end = $end;
      }
      // Add one in order to capture the } when we ge the full rule set.
      $end++;
      // Advance position for the next run of the while loop.
      $property_position = $end;

      // Get values assigned to this property.
      $values_string = substr($css_string, $start_of_values + 1, $declaration_end - ($start_of_values + 1));
      // Parse values string into an array of values.
      $values_array = explode(',', $values_string);
      $values_array = array_map('trim', $values_array);

      foreach ($values_array as $key => $value) {
        if (stripos($value, "'$type'") !== FALSE
          || stripos($value, ".$type") !== FALSE
        ) {
          unset($values_array[$key]);
          $values_array[$type] = $value;
        }
      }
      $attributes[$property][] = $values_array;
    }
  }

  // Make sure src is the last one.
  $temp = $attributes['src'];
  unset($attributes['src']);
  $attributes['src'] = $temp;

  // Parse attributes into an output array.
  $temp = array();
  $output = array();
  foreach ($attributes as $property => $values) {
    foreach ($values as $key => $value) {
      if ($property !== 'src') {

        $value = implode(',', $value);
        if (!isset($temp[$key])) {
          $temp[$key] = '';
        }
        $temp[$key] .= "$property: $value; ";
      }
      else {
        $output[$temp[$key]] = $value;
      }
    }
  }

  return $output;
}

/**
 * Parse the font family string into a structured array.
 *
 * @param string $filename
 *   The filename to save.
 * @param string $data
 *   The data to save to the file.
 * @param bool $local_cache
 *   TRUE if the data came from the cache bin.
 * @param string $hash
 *   Contents hash; if different resave data.
 *
 * @return array
 *   Returns an array of errors that might have happened.
 */
function _advagg_relocate_save_remote_asset($filename, $data, $local_cache, $hash) {
  // Save remote data.
  $errors = array();
  $saved = FALSE;
  $dir = variable_get('advagg_relocate_directory', ADVAGG_RELOCATE_DIRECTORY);
  $full_filename = $dir . $filename;
  $local_hash = '';
  if (!is_readable($full_filename)) {
    $errors = advagg_save_data($full_filename, $data);
    $saved = TRUE;
  }
  elseif (empty($local_cache)) {
    $file_contents = @advagg_file_get_contents($full_filename);
    if (!empty($file_contents)) {
      $local_hash = drupal_hash_base64($file_contents);
    }
    if ($hash !== $local_hash) {
      $errors = advagg_save_data($full_filename, $data, TRUE);
      $saved = TRUE;
    }
  }
  return array($full_filename, $errors, $saved);
}
