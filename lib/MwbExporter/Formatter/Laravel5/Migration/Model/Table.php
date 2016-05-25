<?php

/*
 * The MIT License
 *
 * Copyright (c) 2016 Mateus Vitali <mateus.c.vitali@gmail.com>
 * Copyright (c) 2012-2014 Toha <tohenk@yahoo.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace MwbExporter\Formatter\Laravel5\Migration\Model;

use MwbExporter\Model\Table as BaseTable;
use MwbExporter\Formatter\Laravel5\Migration\Formatter;
use MwbExporter\Writer\WriterInterface;
use MwbExporter\Helper\Comment;

class Table extends BaseTable
{

    public function getTablePrefix()
    {
        return $this->translateVars($this->getConfig()->get(Formatter::CFG_TABLE_PREFIX));
    }

    public function getTableSuffix()
    {
        return $this->translateVars($this->getConfig()->get(Formatter::CFG_TABLE_PREFIX));
    }

    public function getParentTable()
    {
        return $this->translateVars($this->getConfig()->get(Formatter::CFG_PARENT_TABLE));
    }

    public function writeTable(WriterInterface $writer)
    {
        if (!$this->isExternal()) {
            // $this->getModelName() return singular form with correct camel case
            // $this->getRawTableName() return original form with no camel case
            $writer
                ->open($this->getTableFileName())
                ->write('<?php')
                ->write('')
                ->write('use Illuminate\Database\Schema\Blueprint;')
                ->write('use Illuminate\Database\Migrations\Migration;')
                ->write('')
                ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                    if ($_this->getConfig()->get(Formatter::CFG_ADD_COMMENT)) {
                        $writer
                            ->write($_this->getFormatter()->getComment(Comment::FORMAT_PHP))
                            ->write('')
                        ;
                    }
                })
                ->write('class ' . $this->getTablePrefix() . $this->beautify($this->getRawTableName()) . $this->getTableSuffix() . ' extends '. $this->getParentTable())
                ->write('{')
                ->indent()
                    ->write('')
                    ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                        $_this->writeUp($writer);
                    })
                    ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                        $_this->writeDown($writer);
                    })
                ->outdent()
                ->write('}')
                ->write('')
                ->close()
            ;

            return self::WRITE_OK;
        }

        return self::WRITE_EXTERNAL;
    }

    public function writeUp(WriterInterface $writer)
    {
        $writer
            ->write('/**')
            ->write(' * Run the migrations.')
            ->write(' *')
            ->write(' * @return void')
            ->write(' */')
            ->write('public function up()')
            ->write('{')
            ->indent()
                ->write('Schema::create(\''. $this->getRawTableName() . '\', function(Blueprint $table)')
                ->write('{')
                ->indent()
                    ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                        $_this->writeColumns($writer);
                    })
                    ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                        $_this->writeForeignKeys($writer);
                    })
                    ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                        if ($_this->getConfig()->get(Formatter::CFG_GENERATE_TIMESTAPMS)) {
                            $writer->write('$table->timestamps();');
                        }
                    })
                ->outdent()
                ->write('});')
            ->outdent()
            ->write('}')
            ->write('')
        ;

        return $this;
    }

    public function writeColumns(WriterInterface $writer)
    {
        $writer
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                if (count($_this->getColumns())) {
                    foreach ($_this->getColumns() as $column) {
                        $line = '$table->';
                        $type = $this->getFormatter()->getDatatypeConverter()->getType($column);

                        if ($column->isPrimary()) {
                            if($type == 'bigInteger') {
                                $writer->write('$table->bigIncrements(\'' . $column->getColumnName() . '\');');
                            } else {
                                $writer->write('$table->increments(\'' . $column->getColumnName() . '\');');
                            }
                            continue;
                        }

                        /*
                         * TODO: 'isUnique' it is not provided by BaseTable and it is necessary to check for size in columns, like decimal(12,5).
                         */

                        $line .= $type . '(\'' . $column->getColumnName() . '\')';

                        if (!$column->isNotNull()) {
                            $line .= '->nullable()';
                        }

                        if ($column->isUnsigned()) {
                            $line .= '->unsigned()';
                        }

                        $line .= ';';

                        $writer->write($line);
                    }
                } 
            })
        ;

        return $this;
    }

    public function writeForeignKeys(WriterInterface $writer) 
    {
        $writer
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                if (count($_this->getForeignKeys())) {
                    foreach ($_this->getForeignKeys() as $foreignKey) {
                        $foreignKey->write($writer);
                    }
                }
            })
        ;

        return $this;        
    }

    public function writeDown(WriterInterface $writer)
    {
        $writer
            ->write('/**')
            ->write(' * Reverse the migrations.')
            ->write(' *')
            ->write(' * @return void')
            ->write(' */')
            ->write('public function down()')
            ->write('{')
            ->indent()
                ->write('Schema::drop(\''. $this->getRawTableName() . '\');')
            ->outdent()
            ->write('}')
            ->write('')
        ;

        return $this;
    }
}