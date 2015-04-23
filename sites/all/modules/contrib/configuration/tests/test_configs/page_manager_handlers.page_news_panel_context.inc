<?php
/**
 * @file
 * page_manager_handlers.page_news_panel_context.inc
 */

$api = '2.0.0';

$data = $handler = new stdClass();
$handler->disabled = FALSE; /* Edit this to true to make a default handler disabled initially */
$handler->api_version = 1;
$handler->name = 'page_news_panel_context';
$handler->task = 'page';
$handler->subtask = 'news';
$handler->handler = 'panel_context';
$handler->weight = 0;
$handler->conf = array(
  'title' => 'Main',
  'no_blocks' => 0,
  'pipeline' => 'standard',
  'body_classes_to_remove' => '',
  'body_classes_to_add' => '',
  'css_id' => '',
  'css' => '',
  'contexts' => array(
    0 => array(
      'identifier' => 'News Feature: Main Context',
      'keyword' => 'featured_context',
      'name' => 'view:news_feature-main_context',
      'id' => 1,
    ),
  ),
  'relationships' => array(),
);
$display = new panels_display();
$display->layout = 'one_off_news';
$display->layout_settings = array();
$display->panel_settings = array(
  'style_settings' => array(
    'default' => NULL,
    'middle' => NULL,
    'cards' => array(
      'per_row' => '3',
    ),
    'recent' => array(
      'border' => 'engraving',
      'background' => 'none',
      'accent' => array(
        'accent_before' => 0,
        'accent_after' => 0,
      ),
    ),
  ),
  'wrapper_settings' => array(
    'style' => 'default',
    'settings' => NULL,
  ),
  'middle' => array(
    'style' => 'naked',
  ),
  'cards' => array(
    'style' => 'multi_row_grid',
  ),
  'recent' => array(
    'style' => 'lol_wrapper',
  ),
);
$display->cache = array();
$display->title = 'News Home';
$display->content = array();
$display->panels = array();
  $pane = new stdClass();
  $pane->pid = 'new-70';
  $pane->panel = 'cards';
  $pane->type = 'views_row';
  $pane->subtype = 'views_row';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'rows' => array(
      1 => '1',
    ),
    'use_fields' => NULL,
    'fields' => NULL,
    'context' => 'context_view:news_feature-main_context_1',
    'override_title' => 0,
    'override_title_text' => '',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => array(
      'border' => 'engraving',
      'background' => 'none',
      'accent' => array(
        'accent_before' => 0,
        'accent_after' => 0,
      ),
    ),
    'style' => 'lol_wrapper',
  );
  $pane->css = array();
  $pane->extras = array();
  $pane->position = 0;
  $pane->locks = array();
  $display->content['new-70'] = $pane;
  $display->panels['cards'][0] = 'new-70';
  $pane = new stdClass();
  $pane->pid = 'new-71';
  $pane->panel = 'cards';
  $pane->type = 'views_row';
  $pane->subtype = 'views_row';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'rows' => array(
      2 => '2',
    ),
    'use_fields' => NULL,
    'fields' => NULL,
    'context' => 'context_view:news_feature-main_context_1',
    'override_title' => 0,
    'override_title_text' => '',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => array(
      'border' => 'engraving',
      'background' => 'none',
      'accent' => array(
        'accent_before' => 0,
        'accent_after' => 0,
      ),
    ),
    'style' => 'lol_wrapper',
  );
  $pane->css = array();
  $pane->extras = array();
  $pane->position = 1;
  $pane->locks = array();
  $display->content['new-71'] = $pane;
  $display->panels['cards'][1] = 'new-71';
  $pane = new stdClass();
  $pane->pid = 'new-72';
  $pane->panel = 'cards';
  $pane->type = 'views_row';
  $pane->subtype = 'views_row';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'rows' => array(
      3 => '3',
    ),
    'use_fields' => NULL,
    'fields' => NULL,
    'context' => 'context_view:news_feature-main_context_1',
    'override_title' => 0,
    'override_title_text' => '',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => array(
      'border' => 'engraving',
      'background' => 'none',
      'accent' => array(
        'accent_before' => 0,
        'accent_after' => 0,
      ),
    ),
    'style' => 'lol_wrapper',
  );
  $pane->css = array();
  $pane->extras = array();
  $pane->position = 2;
  $pane->locks = array();
  $display->content['new-72'] = $pane;
  $display->panels['cards'][2] = 'new-72';
  $pane = new stdClass();
  $pane->pid = 'new-73';
  $pane->panel = 'cards';
  $pane->type = 'views_row';
  $pane->subtype = 'views_row';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'rows' => array(
      4 => '4',
    ),
    'use_fields' => NULL,
    'fields' => NULL,
    'context' => 'context_view:news_feature-main_context_1',
    'override_title' => 0,
    'override_title_text' => '',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => array(
      'border' => 'engraving',
      'background' => 'none',
      'accent' => array(
        'accent_before' => 0,
        'accent_after' => 0,
      ),
    ),
    'style' => 'lol_wrapper',
  );
  $pane->css = array();
  $pane->extras = array();
  $pane->position = 3;
  $pane->locks = array();
  $display->content['new-73'] = $pane;
  $display->panels['cards'][3] = 'new-73';
  $pane = new stdClass();
  $pane->pid = 'new-74';
  $pane->panel = 'cards';
  $pane->type = 'views_row';
  $pane->subtype = 'views_row';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'rows' => array(
      5 => '5',
    ),
    'use_fields' => NULL,
    'fields' => NULL,
    'context' => 'context_view:news_feature-main_context_1',
    'override_title' => 0,
    'override_title_text' => '',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => array(
      'border' => 'engraving',
      'background' => 'none',
      'accent' => array(
        'accent_before' => 0,
        'accent_after' => 0,
      ),
    ),
    'style' => 'lol_wrapper',
  );
  $pane->css = array();
  $pane->extras = array();
  $pane->position = 4;
  $pane->locks = array();
  $display->content['new-74'] = $pane;
  $display->panels['cards'][4] = 'new-74';
  $pane = new stdClass();
  $pane->pid = 'new-75';
  $pane->panel = 'cards';
  $pane->type = 'views_row';
  $pane->subtype = 'views_row';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'rows' => array(
      6 => '6',
    ),
    'use_fields' => NULL,
    'fields' => NULL,
    'context' => 'context_view:news_feature-main_context_1',
    'override_title' => 0,
    'override_title_text' => '',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => array(
      'border' => 'engraving',
      'background' => 'none',
      'accent' => array(
        'accent_before' => 0,
        'accent_after' => 0,
      ),
    ),
    'style' => 'lol_wrapper',
  );
  $pane->css = array();
  $pane->extras = array();
  $pane->position = 5;
  $pane->locks = array();
  $display->content['new-75'] = $pane;
  $display->panels['cards'][5] = 'new-75';
  $pane = new stdClass();
  $pane->pid = 'new-76';
  $pane->panel = 'recent';
  $pane->type = 'views_panes';
  $pane->subtype = 'news_recent-teaser';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'items_per_page' => '1',
    'offset' => '0',
    'override_title' => 0,
    'override_title_text' => '',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => NULL,
    'style' => 'default',
  );
  $pane->css = array();
  $pane->extras = array();
  $pane->position = 0;
  $pane->locks = array();
  $display->content['new-76'] = $pane;
  $display->panels['recent'][0] = 'new-76';
  $pane = new stdClass();
  $pane->pid = 'new-77';
  $pane->panel = 'recent';
  $pane->type = 'views_panes';
  $pane->subtype = 'news_recent-teaser';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'items_per_page' => '1',
    'offset' => '1',
    'override_title' => 0,
    'override_title_text' => '',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => NULL,
    'style' => 'divider_before',
  );
  $pane->css = array();
  $pane->extras = array();
  $pane->position = 1;
  $pane->locks = array();
  $display->content['new-77'] = $pane;
  $display->panels['recent'][1] = 'new-77';
  $pane = new stdClass();
  $pane->pid = 'new-78';
  $pane->panel = 'recent';
  $pane->type = 'views_panes';
  $pane->subtype = 'news_recent-teaser';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'items_per_page' => '1',
    'offset' => '2',
    'override_title' => 0,
    'override_title_text' => '',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => NULL,
    'style' => 'divider_before',
  );
  $pane->css = array();
  $pane->extras = array();
  $pane->position = 2;
  $pane->locks = array();
  $display->content['new-78'] = $pane;
  $display->panels['recent'][2] = 'new-78';
$display->hide_title = PANELS_TITLE_FIXED;
$display->title_pane = '0';
$handler->conf['display'] = $display;


$dependencies = array(
  'views_view.news_recent' => 'views_view.news_recent',
);

$optional = array();

$modules = array(
  0 => 'ctools',
  1 => 'page_manager',
);
