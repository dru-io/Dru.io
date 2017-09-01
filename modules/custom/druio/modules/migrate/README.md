# Dru.io — migration

Данный модуль отвечает за миграцию содержимого со старой версии сайта на 
Drupal 7. Перенос данных осуществляется при помощи Migrate API из ядра + Migrate
Tools модуля, для описания миграции в yml файле.

Миграции не только переносят данные, а также адаптируют их под новые форматы и
поля. Например у типа материала "Проект" в Drupal 7 используются taxonomy term
для обозначения статусов проекта, в Drupal 8 используется поле типа "текст 
(список)".

Данные берутся из второй БД. Она добавляется в **settings.php** Drupal 8 сайта в 
который производится миграция.

Примерно следующим образом:

```php
…
#
# if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
#   include $app_root . '/' . $site_path . '/settings.local.php';
# }

// Стандартная БД для Drupal 8 в который идет миграция.
$databases['default']['default'] = array (
  'database' => 'drupal',
  'username' => 'drupal',
  'password' => 'drupal',
  'prefix' => '',
  'host' => 'mariadb',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

// БД из Drupal 7, туда будут выполняться запросы на данные.
$databases['druio_old']['default'] = array (
  'database' => 'druio_old',
  'username' => 'drupal',
  'password' => 'drupal',
  'prefix' => '',
  'host' => 'mariadb',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
```

## Дерево зависимостей для миграций чтобы было проще ориентироваться

- [druio_user](config/install/migrate_plus.migration.user.yml)
  - [druio_file](config/install/migrate_plus.migration.file.yml)
    - [druio_event](config/install/migrate_plus.migration.node_event.yml)
    - [druio_order](config/install/migrate_plus.migration.node_order.yml)
  - [druio_node_post](config/install/migrate_plus.migration.node_post.yml)
  - [druio_node_project](config/install/migrate_plus.migration.node_project.yml)
- [druio_taxonomy_question_category](config/install/migrate_plus.migration.taxonomy_question_category.yml)
- [druio_taxonomy_city](config/install/migrate_plus.migration.taxonomy_city.yml)
