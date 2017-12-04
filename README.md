# bitrix-phinx

# Как поставить

1. Ставим пакет через composer

```
composer require magnifico/bitrix-phinx:^0.1
```

2. Ставим симлинк с именем "magnifico.phinx" из директории bitrix'а на местоположение пакета, например:

```
cd /home/bitrix/www/bitrix/modules
ln -s ../../../vendor/magnifico/bitrix-phinx magnifico.phinx
```

3. Делаем то же самое для модуля "magnifico.console":
```
cd /home/bitrix/www/bitrix/modules
ln -s ../../../vendor/magnifico/bitrix-console magnifico.console
```

4. Устанавливаем оба модуля в админке битрикса

5. Создаем где-нибудь файл "manage.php":

```
<?php

# Определяем, где находится DOCUMENT_ROOT
$_SERVER['DOCUMENT_ROOT'] = '/home/bitrix/www';

# Включаем служебный скрипт из модуля magnifico.console
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/magnifico.console/manage.php';
```

6. Пользуемся

# Как работать с модулем

Никакого дополнительного конфигурирования (типа phinx.yml) - не требуется, все необходимые настройки считываются из ядра битрикса.

В отличие от оригинального phinx'а, у каждой команды первым обязательным аргументом добавлено имя модуля битрикса, к которому относятся миграции. Все остальные параметры остались без изменений.

Например, если наш модуль называется magnifico.site, команды будут выглядеть так:

```
# Создать миграцию
php manage.php phinx:create magnifico.site MigrationName

# Применение миграций
php manage.php phinx:migrate magnifico.site

# Откат миграций
php manage.php phinx:rollback magnifico.site

# Информация о миграциях
php manage.php phinx:status magnifico.site
```

Миграции при этом будут создаваться и искаться в директории "migrations", лежащей в директории соответствующего модуля, например:
```
# Если используется local
/home/bitrix/www/local/modules/magnifico.site/migrations

# Так тоже будет работать
/home/bitrix/www/bitrix/modules/magnifico.site/migrations
```

Также, в отличие от оригинального phinx'а, в базе данных будет создана не одна таблица, а по таблице на каждый отдельный модуль по паттерну "magnifico_phinx_migrations_of_{module_name}", например:
```
magnifico_phinx_migrations_of_magnifico_site
magnifico_phinx_migrations_of_atlaslib_feedback
```
