# README

This is an exporter to convert [MySQL Workbench](http://www.mysql.com/products/workbench/) Models (\*.mwb) to Laravel Framework 5 Model and Migration Schema.

## Prerequisites

  * PHP 5.4+
  * Composer to install the dependencies

## Installation

```
php composer.phar require --dev vitalibr/laravel5-exporter
```

This will install the exporter and also require [mysql-workbench-schema-exporter](https://github.com/mysql-workbench-schema-exporter/mysql-workbench-schema-exporter).

You then can invoke the CLI script using `vendor/bin/mysql-workbench-schema-export`.

## Formatter Setup Options

Additionally to the [common options](https://github.com/mysql-workbench-schema-exporter/mysql-workbench-schema-exporter#configuring-mysql-workbench-schema-exporter) of mysql-workbench-schema-exporter these options are supported:

### Laravel Model

#### Setup Options

  * `namespace`

    Namespace for generated class.

    Default is `App\Models`.

  * `parentTable`

    Ancestor class, the class to extend for generated class.

    Default is `Model`.

  * `generateFillable`

    Generate variable fillable with all columns.

    Default is `false`.

### Laravel Migration

#### Setup Options

  * `tablePrefix`

    Table prefix for generated class.

    Default is `Create`.

  * `tableSuffix`

    Table suffix for generated class.

    Default is `Table`.

  * `parentTable`

    See above.

    Default is `Migration`.

  * `generateTimestamps`

    Generate `created_at` and `updated_at` columns to all Tables.

    Default is `false`.

## Command Line Interface (CLI)

See documentation for [mysql-workbench-schema-exporter](https://github.com/mysql-workbench-schema-exporter/mysql-workbench-schema-exporter#command-line-interface-cli)

## Links

  * [MySQL Workbench](http://wb.mysql.com/)
  * [Laravel Project](https://laravel.com/)
