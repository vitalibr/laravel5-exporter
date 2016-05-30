# Laravel 5.0 Exporter
[![Latest Stable Version](https://poser.pugx.org/vitalibr/laravel5-exporter/v/stable)](https://packagist.org/packages/vitalibr/laravel5-exporter) [![Total Downloads](https://poser.pugx.org/vitalibr/laravel5-exporter/downloads)](https://packagist.org/packages/vitalibr/laravel5-exporter) [![Latest Unstable Version](https://poser.pugx.org/vitalibr/laravel5-exporter/v/unstable)](https://packagist.org/packages/vitalibr/laravel5-exporter) [![License](https://poser.pugx.org/vitalibr/laravel5-exporter/license)](https://packagist.org/packages/vitalibr/laravel5-exporter)

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

## Examples (v3.0.2)

#### Workbench Schema
![alt tag](http://s33.postimg.org/xdhdnf7qn/model.png)

#### Model
![alt tag](http://s33.postimg.org/kalr45hin/models.png)

#### Migration
![alt tag](http://s33.postimg.org/muhdy952n/migrations.png)

## Links

  * [MySQL Workbench](http://wb.mysql.com/)
  * [Laravel Project](https://laravel.com/)
