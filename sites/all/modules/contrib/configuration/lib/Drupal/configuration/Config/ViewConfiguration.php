<?php

/**
 * @file
 * Definition of Drupal\configuration\Config\ViewConfiguration.
 */

namespace Drupal\configuration\Config;

use Drupal\configuration\Config\CtoolsConfiguration;

class ViewConfiguration extends CtoolsConfiguration {

  /**
   * Overrides Drupal\configuration\Config\Configuration::isActive().
   */
  public static function isActive() {
    return module_exists('views');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponentHumanName().
   */
  static public function getComponentHumanName($component, $plural = FALSE) {
    return $plural ? t('Views') : t('View');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponent().
   */
  public function getComponent() {
    return 'views_view';
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::supportedComponents().
   */
  static public function supportedComponents() {
    return array('views_view');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::findRequiredModules().
   */
  public function findRequiredModules() {
    $this->addToModules('views');
    $view = $this->getData();

    // We get the module that creates the table for the view query.
    $schema = drupal_get_schema($view->base_table);
    $this->addToModules($schema['module']);

    foreach (views_object_types () as $type => $info) {
      foreach ($view->display as $display_id => $display) {
        // Views with a display provided by views_content module.
        if ($display->display_plugin == 'panel_pane') {
          $this->addToModules('views_content');
        }
        $view->set_display($display_id);
        foreach ($view->display_handler->get_handlers($type) as $handler_id => $handler) {
          if ($type == 'field') {
            if (!empty($handler->field_info) && !empty($handler->field_info['module'])) {
              $this->addToModules($handler->field_info['module']);
            }
          }
        }
      }
    }
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::alterDependencies().
   */
  public static function alterDependencies(Configuration $config) {
    static $cache;
    if (!isset($cache)) {
      $cache = array();
    }

    // Dependencies for Page Manager Handlers.
    if ($config->getComponent() == 'page_manager_handlers' && !$config->broken) {

      // This line seems to be inconsistent when executed from drush or browser.
      $config_data = $config->getData();

      // This alternative works more consistent althoug it's no so pretty.

      if (!isset($config_data->conf['display'])) {
        if (!isset($cache[$config->getUniqueId()])) {
          @eval(ctools_export_crud_export($config->getComponent(), $config_data));
          $cache[$config->getUniqueId()] = $handler;
        }
        else {
          $handler = $cache[$config->getUniqueId()];
        }
        $config_data = $handler;
      }

      foreach ($config_data->conf['display']->content as $object) {
        $type = $object->type;
        switch ($type) {
          case 'block':
            list($subtype, $id, ) = explode('-', $object->subtype);
            switch ($subtype) {
              // Display block from a view.
              case 'views':
                $config_id = 'views_view.' . $id;

                $view = ConfigurationManagement::createConfigurationInstance($config_id);
                $view->build();
                $config->addToDependencies($view);

                $config->addToDependencies($view);
                break;
            }
            break;
          // A view added directly.
          case 'views':
            $config_id = 'views_view.' . $object->subtype;

            $view = ConfigurationManagement::createConfigurationInstance($config_id);
            $view->build();
            $config->addToDependencies($view);
            break;
          // A view added using the Views content panes module.
          case 'views_panes':
            list($subtype, ) = explode('-', $object->subtype);
            $config_id = 'views_view.' . $subtype;

            $view = ConfigurationManagement::createConfigurationInstance($config_id);
            $view->build();
            $config->addToDependencies($view);
            break;
        }
      }
    }
  }
}
