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

namespace MwbExporter\Formatter\Laravel5\Model\Model;

use MwbExporter\Model\Table as BaseTable;
use MwbExporter\Formatter\Laravel5\Model\Formatter;
use MwbExporter\Writer\WriterInterface;
use MwbExporter\Helper\Comment;
use Doctrine\Common\Inflector\Inflector;

class Table extends BaseTable
{
    public function getNamespace()
    {
        return $this->translateVars($this->getConfig()->get(Formatter::CFG_NAMESPACE));
    }

    public function getParentTable()
    {
        return $this->translateVars($this->getConfig()->get(Formatter::CFG_PARENT_TABLE));
    }

    public function writeTable(WriterInterface $writer)
    {
        if (!$this->isExternal() && !$this->isManyToMany()) {
            // $this->getModelName() return singular form with correct camel case
            // $this->getRawTableName() return original form with no camel case
            $writer
                ->open($this->getTableFileName())
                ->write('<?php namespace ' . $this->getNamespace() . ';')
                ->write('')
                ->write('use Illuminate\Database\Eloquent\Model;')
                ->write('')
                ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                    if ($_this->getConfig()->get(Formatter::CFG_ADD_COMMENT)) {
                        $writer
                            ->write($_this->getFormatter()->getComment(Comment::FORMAT_PHP))
                            ->write('')
                        ;
                    }
                })
                ->write('class ' . $this->getModelName() . ' extends '. $this->getParentTable())
                ->write('{')
                ->indent()
                    ->write('/**')
                    ->write(' * The database table used by the model.')
                    ->write(' * ')
                    ->write(' * @var string')
                    ->write(' */')
                    ->write('protected $table = \''. $this->getRawTableName() .'\';')
                    ->write('')                 
                    ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                        if ($_this->getConfig()->get(Formatter::CFG_GENERATE_FILLABLE)) {
                            $_this->writeFillable($writer);
                        }
                    })
                    ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                        $_this->writeRelationships($writer);
                    })
                    ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                        $_this->writeReferences($writer);
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
    
    public function writeReferences(WriterInterface $writer) 
    {
        $writer
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                if (count($_this->getColumns())) {
                    // Get current column from this table
                    foreach ($_this->getColumns() as $column) {
                        // Get tables from the same schema
                        foreach ($this->getParent() as $table) {  
                            // Get foreignKeys from table
                            foreach ($table->getForeignKeys() as $foreignKey) {
                                // If current column is referenced by foreignKey
                                if(($_this->getRawTableName() == $foreignKey->getReferencedTable()->getRawTableName()) &&
                                    ($column->getColumnName() == $foreignKey->getForeign()->getColumnName()) &&
                                    (!$foreignKey->getOwningTable()->isManyToMany())) {
                                    // Comment                                        
                                    $writer->write('/**');
                                    $writer->write(' * Relationship with ' . $foreignKey->getOwningTable()->getModelName() . '.');
                                    $writer->write(' */'); 
                                    $writer->write('public function ' . Inflector::pluralize($foreignKey->getOwningTable()->getRawTableName()) . '()');            
                                    $writer->write('{');       
                                    $writer->indent();
                                    // One to Many
                                    if($foreignKey->isManyToOne()) {
                                        $writer->write('return $this->hasMany(\'' . $_this->getNamespace() . '\\' . $foreignKey->getOwningTable()->getModelName() . '\');');                      
                                    } 
                                    // One to One
                                    else {
                                        $writer->write('return $this->hasOne(\'' . $_this->getNamespace() . '\\' . $foreignKey->getOwningTable()->getModelName() . '\');');                                  
                                    }             
                                    $writer->outdent();
                                    $writer->write('}');   
                                    $writer->write('');      
                                }
                            }  
                        }
                    }
                }
            })
        ;

        return $this;
    }

    public function writeRelationships(WriterInterface $writer) 
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

    public function writeFillable(WriterInterface $writer)
    {
        /*
         * FIXME: identify which columns are FK and not add to the array fillable
         */
        $writer
            ->write('/**')
            ->write(' * The attributes that are mass assignable.')
            ->write(' * ')
            ->write(' * @var array')
            ->write(' */')   
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                if (count($_this->getColumns())) {
                    $content = '';
                    $columns = $_this->getColumns();
                    foreach ($columns as $column) {
                        $content .= '\'' . $column->getColumnName() . '\',';
                    }
                    $writer->write('protected $fillable = [' . substr($content, 0, -1) . '];');
                } 
            })
        ;

        return $this;
    }
}