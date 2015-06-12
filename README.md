# Dru.io - Drupal сообщество.

<img src="http://dru.io/sites/all/themes/druiot/logo.png" alt="Dru.io" style="width: 170px;" />

**Адрес сообщества:** <a href="http://dru.io" title="Русскоязычное сообщество Drupal">dru.io</a>

### Навигация по репозиторию:
- [Issues](https://github.com/Niklan/Dru.io/issues) - вопросы, предложения улучшения, запросы, обсуждения. Тут происходит обсуждение технической стороны проекта.
- [Список изменений в сообществе](https://github.com/Niklan/Dru.io/wiki/%D0%A1%D0%BF%D0%B8%D1%81%D0%BE%D0%BA-%D0%BE%D0%B1%D0%BD%D0%BE%D0%B2%D0%BB%D0%B5%D0%BD%D0%B8%D0%B9-2015).

## Описание

Здесь мы храним код нашего проекта: базу, ядро и модули.
Это место где мы обсуждаем и предлагаем новые идеи для проекта, развивая сообщество общими усилиями.

### Я не программист, но я хочу принять участие.

Добро пожаловать в [issues](https://github.com/Niklan/Dru.io/issues). Там вы сможете предложить новую идею, раздел для сайта, или указать на ошибки. Вы также можете принимать участие в обсуждениях и предложениях других участников Drupal-сообщества. Мы вместе принимаем решения.

### Я программист и хочу принять участие

При создании сообщества использовались следующие технологии: php, css, scss, js, html. Если вам знакомы все или часть из них, то вы сможете помочь и принять участие в разработке проекта. 


## Установка dev окружения

Актуальную версию базы данных можно скачать в [разделе релизов проекта](https://github.com/Niklan/Dru.io/releases)

Ниже приведена пошаговая инструкция для развертывания дистрибутива dru.io в собственной среде разработки. Команды указаны с расчетом на то, что выполняться они будут в корне каталога сайта.

~~~
git clone https://github.com/Niklan/Dru.io.git .
cd sites/default
cp default.settings.php settings.php
~~~

Далее, удобным для вас способ добавить в sites/default/settings.php следующий массив, заменив данные на свои.

~~~php
$databases = array (
  'default' => 
  array (
    'default' => 
    array (
      'database' => 'DATABASE_NAME',
      'username' => 'DATABASE_USERNAME',
      'password' => 'DATABASE_PASSWORD',
      'host' => 'localhost',
      'port' => '',
      'driver' => 'mysql',
      'prefix' => '',
    ),
  ),
);
~~~

Далее импортируем БД используя phpmyadmin или консоль:

~~~
mysql -u DATABASE_USERNAME -p
use DATABASE_NAME
source /path/to/dump.sql
~~~


*Создано [сообществом](https://github.com/Niklan/Dru.io/graphs/contributors), для сообщества.*
