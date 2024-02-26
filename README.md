[![phpunit](https://github.com/danilovl/translator-bundle/actions/workflows/phpunit.yml/badge.svg)](https://github.com/danilovl/translator-bundle/actions/workflows/phpunit.yml)
[![downloads](https://img.shields.io/packagist/dt/danilovl/translator-bundle)](https://packagist.org/packages/danilovl/translator-bundle)
[![latest Stable Version](https://img.shields.io/packagist/v/danilovl/translator-bundle)](https://packagist.org/packages/danilovl/translator-bundle)
[![license](https://img.shields.io/packagist/l/danilovl/translator-bundle)](https://packagist.org/packages/danilovl/translator-bundle)

# TranslatorBundle #

## About ##

Symfony bundle provides a simple way to manage system translations.

### Features

* Save YAML translations in a database table.
* Command to generate cached translations.
* Command to synchronize translations from YAML to the database table.
* Command to synchronize translations from the database table to YAML.
* EasyAdmin interface to manage translations.
* Automatically refresh cache when translations are changed in the admin panel.

### Requirements

* PHP 8.3 or higher
* Symfony 7.0 or higher
* MySQL
* EasyAdmin
* Only YAML translation file extensions and MySQL database are supported.

### 1. Installation

Install `danilovl/translator-bundle` package by Composer:

``` bash
$ composer require danilovl/translator-bundle
```
Add the `TranslatorBundle` to your application's bundles if it does not add automatically:

```php
<?php
// config/bundles.php

return [
    // ...
    Danilovl\TranslatorBundle\TranslatorBundle::class => ['all' => true]
];
```

### 2. Configuration

#### 2.1 Bundle configuration options

Create `danilovl_translator.yaml` in you `packages` folder.

```yaml
danilovl_translator:
  enabled: true
  enabledAutoAdminRefreshCache: true
  locale: [en, ru]
  domain: [messages,validators]
```

#### 2.2 Doctrine entity mapping

Add entity mapping to `doctrine.yaml` in `packages` folder.

```yaml
orm:
  auto_generate_proxy_classes: '%kernel.debug%'
  mappings:
    Danilovl\TranslatorBundle:
      is_bundle: false
      type: attribute
      dir: "%kernel.project_dir%/../vendor/danilovl/translator-bundle/src/Entity/"
      prefix: 'Danilovl\TranslatorBundle\Entity'

```

Then update you database schema by command.

```shell
php bin/console doctrine:schema:update --force
php bin/console doctrine:schema:update --dump-sql
```

#### 2.2 Easy admin

Add route to you project routes `routes.yaml`.

```yaml
_danilovl_translator_admin_dashboard:
  resource: "@TranslatorBundle/Resources/config/routing.yaml"
```
With prefix if url `/translator` exist in project.

```yaml
_danilovl_translator_admin_dashboard:
  prefix:   /danilovl
  resource: "@TranslatorBundle/Resources/config/routing.yaml"
```

The admin interface will be available at the URL `/translator/admin`.

### 3. Usage command

Parameters `locale`, `domain` is available in configuration `danilovl_translator`.

You cannot use parameters other than the ones provided.

#### 3.1 Generate translation

Generate cache translations.

```shell
php bin/console danilovl:translator:generate-translation
php bin/console danilovl:translator:generate-translation --locale=en
php bin/console danilovl:translator:generate-translation --locale=en --locale=ru
```

#### 3.2 Migrate translation to database

Delete all translations in the database and insert new ones.

Command has argument `strategy` with value `full|only-new|git-diff|database-diff`

Command has options `mode` with value `migrate|dump`, `locale`, `domain`.

By default `mode` is `migrate`, `dump` show you sql query.

```shell
php bin/console danilovl:translator:migration-to-database full

php bin/console danilovl:translator:migration-to-database full migrate
php bin/console danilovl:translator:migration-to-database full migrate --locale=en
php bin/console danilovl:translator:migration-to-database full --locale=en
php bin/console danilovl:translator:migration-to-database full --locale=en --domain=messages
php bin/console danilovl:translator:migration-to-database full --domain=messages

php bin/console danilovl:translator:migration-to-database full --mode=dump
php bin/console danilovl:translator:migration-to-database full --mode=dump --locale=en
php bin/console danilovl:translator:migration-to-database full --mode=dump --domain=messages
php bin/console danilovl:translator:migration-to-database full --mode=dump --locale=en --locale=ru --domain=messages
```

Insert only new translations that do not already exist in the database.

```shell
php bin/console danilovl:translator:migration-to-database only-new

php bin/console danilovl:translator:migration-to-database only-new --locale=en
php bin/console danilovl:translator:migration-to-database only-new --locale=en --locale=ru --domain=messages
php bin/console danilovl:translator:migration-to-database only-new --domain=messages

php bin/console danilovl:translator:migration-to-database only-new --mode=dump
php bin/console danilovl:translator:migration-to-database only-new --mode=dump --locale=en
php bin/console danilovl:translator:migration-to-database only-new --mode=dump --locale=en -domain=messages
```

Generate `update`,`delete`,`insert` sql query by git diff. Compare the current file with the last version.

```shell
php bin/console danilovl:translator:migration-to-database git-diff
 
php bin/console danilovl:translator:migration-to-database git-diff --locale=en
php bin/console danilovl:translator:migration-to-database git-diff --locale=en --domain=messages

php bin/console danilovl:translator:migration-to-database git-diff --mode=dump
php bin/console danilovl:translator:migration-to-database git-diff --locale=en
```

Generate `update`, `delete`, `insert` sql queries by comparing the current file with the database.

```shell
php bin/console danilovl:translator:migration-to-database database-diff
 
php bin/console danilovl:translator:migration-to-database database-diff --locale=en
php bin/console danilovl:translator:migration-to-database database-diff --locale=en --domain=messages

php bin/console danilovl:translator:migration-to-database database-diff --mode=dump
php bin/console danilovl:translator:migration-to-database database-diff --locale=en
```

#### 3.3 Migrate translation from database to file

Generate translation yaml file.

Command has option `strategy` with value `flatten|dotNested`, `mode` with value `migrate|dump`, `locale`, `domain`.

By default `strategy` is `flatten`.

By default `mode` is `migrate`, `dump` show you `yaml` content.

```shell
php bin/console danilovl:translator:migration-from-database
php bin/console danilovl:translator:migration-from-database --strategy=flatten
php bin/console danilovl:translator:migration-from-database --strategy=dotNested

php bin/console danilovl:translator:migration-from-database --strategy=flatten --locale=en
php bin/console danilovl:translator:migration-from-database --strategy=flatten --locale=en --locale=ru --domain=messages
php bin/console danilovl:translator:migration-from-database --strategy=flatten --domain=messages

php bin/console danilovl:translator:migration-from-database --strategy=flatten --mode=dump
php bin/console danilovl:translator:migration-from-database --strategy=dotNested --mode=dump --locale=en
php bin/console danilovl:translator:migration-from-database --strategy=dotNested --mode=dump --locale=en -domain=messages
```

#### 3.4 Change translation file format

Format `flatten`

```yaml
app.text.common.A: A
app.text.common.B: B
app.text.common.C: C
```
```shell
php bin/console danilovl:translator:transform-to-format messages.en.yaml flatten
```

Format `dotNested`

```yaml
app:
  text:
    common:
      A: A
      B: B
      C: C
```

```shell
php bin/console danilovl:translator:transform-to-format messages.en.yaml dotNested
```

## License

The TranslatorBundle is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
