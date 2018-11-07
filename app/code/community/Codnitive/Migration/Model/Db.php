<?php
/**
 * CODNITIVE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE_EULA.html.
 * It is also available through the world-wide-web at this URL:
 * http://www.codnitive.com/en/terms-of-service-softwares/
 * http://www.codnitive.com/fa/terms-of-service-softwares/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @category   Codnitive
 * @package    Codnitive_Migration
 * @author     Hassan Barza <support@codnitive.com>
 * @copyright  Copyright (c) 2012 CODNITIVE Co. (http://www.codnitive.com)
 * @license    http://www.codnitive.com/en/terms-of-service-softwares/ End User License Agreement (EULA 1.0)
 */

abstract class Codnitive_Migration_Model_Db extends Codnitive_Db
{
    protected $_dbConfig;
    protected $_query = '';
    
    protected $_utf8Collate = array(
        'CHAR', 'VARCHAR', 'TINYTEXT', 'TEXT', 
        'MEDIUMTEXT', 'LONGTEXT', 'ENUM', 'SET'
    );
    
    protected $_reservedNames = array(
        'foreign_key', 'unique_key'
    );

    public function __construct($config = array())
    {
        parent::__construct($this->_dbConfig);
    }
    
    public function setQuery($sql)
    {
        if (is_string($sql)) {
            $this->_query = $sql;
        }
        else {
            throw new Codnitive_Migration_Model_Db_Exception("Codnitive_Migration_Model_Db::setQuery(): SQL query is not valid, it must be string.");
        }
        return $this;
    }
    
    public function resetQurey()
    {
        $this->setQuery('');
        return $this;
    }

    public function getQuery()
    {
        return $this->_query;
    }
    
    public function quTableCreate($tables, $ifNotExists = true, $autoRun = true)
    {
        $result = array();
        if (is_array($tables)) {
            foreach ($tables as $tableName => $tableCols) {
                $sql = 'CREATE TABLE ';
                $sql .= ($ifNotExists ? 'IF NOT EXISTS ' : '');
                
                if (is_array($tableCols)) {
                    $col = '';
                    $engine = '';
                    
                    foreach ($tableCols as $colName => $colDefs) {
                        if (is_array($colDefs) && !in_array($colName, $this->_reservedNames)) {
                            $col .= $colName . ' ';
                            
                            if (isset($colDefs['type']) && !isset($colDefs['collate'])) {
                                if (in_array(strtoupper($colDefs['type']), $this->_utf8Collate)) {
                                    $colDefs['collate'] = 'utf8_unicode_ci';
                                }
                            }
                            
                            if (isset($colDefs['type']) && is_string($colDefs['type'])) {
                                $col .= strtoupper($colDefs['type']) . ' ';
                            }
                            
                            if (isset($colDefs['length']) && is_numeric($colDefs['length'])) {
                                if (isset($colDefs['type']) && strtoupper($colDefs['type']) != 'TIMESTAMP') {
                                    $col = rtrim($col, ' ');
                                    $col .= '(' . $colDefs['length'] . ') ';
                                }
                            }
                            
                            if (isset($colDefs['attribute']) && is_string($colDefs['attribute'])) {
                                $col .= strtoupper($colDefs['attribute']) . ' ';
                            }
                            
                            if (isset($colDefs['character_set']) && is_string($colDefs['character_set'])) {
                                $col .= 'CHARACTER SET ' . $colDefs['character_set'] . ' ';
                            }
                            
                            if (isset($colDefs['collate']) && is_string($colDefs['collate'])) {
                                if (!isset($colDefs['character_set'])) {
                                    $charSet = explode('_', $colDefs['collate']);
                                    $col .= 'CHARACTER SET ' . $charSet[0] . ' COLLATE ' . $colDefs['collate'] . ' ';
                                }
                                else {
                                    $col .= 'COLLATE ' . $colDefs['collate'] . ' ';
                                }
                            }
                            
                            if (isset($colDefs['null']) && is_bool($colDefs['null'])) {
                                $col .= $colDefs['null'] ? 'NULL ' : 'NOT NULL ';
                            }
                            
                            if (isset($colDefs['default']) && is_string($colDefs['default'])) {
                                if (!isset($colDefs['null']) || (isset($colDefs['null']) && !$colDefs['null'])) {
                                    if (isset($colDefs['type']) && strtoupper($colDefs['type']) == 'TIMESTAMP') {
                                        $col .= 'DEFAULT CURRENT_TIMESTAMP ';
                                    }
                                    else {
                                        $col .= "DEFAULT '" . $colDefs['default'] . "' ";
                                    }
                                }
                            }
                            
                            if (isset($colDefs['auto_increment']) && is_bool($colDefs['auto_increment'])) {
                                $col .= 'AUTO_INCREMENT ';
                            }
                            
                            if (isset($colDefs['comment']) && is_string($colDefs['comment'])) {
                                $col .= "COMMENT '" . $colDefs['comment'] . "' ";
                            }
                            
                            $col = rtrim($col, ' ');
                            $col .= ', ';
                        }
                        else if (isset($tableCols['unique_key']) && is_array($tableCols['unique_key'])) {
                            foreach ($tableCols['unique_key'] as $key) {
                                $col .= 'UNIQUE KEY (' . $key . '), ';
                            }
                            unset($tableCols['unique_key']);
                        }
                        else if (isset($tableCols['index']) && is_string($tableCols['index'])) {
                            $col .= 'INDEX (' . $tableCols['index'] . '), ';
                            unset($tableCols['index']);
                        }
                        else if (isset($tableCols['fulltext']) && is_string($tableCols['fulltext'])) {
                            $col .= 'FULLTEXT (' . $tableCols['fulltext'] . '), ';
                            unset($tableCols['fulltext']);
                        }
                        else if (isset($tableCols['primary_key']) && is_string($tableCols['primary_key'])) {
                            $col .= 'PRIMARY KEY (' . $tableCols['primary_key'] . '), ';
                            unset($tableCols['primary_key']);
                        }
                        else if (isset ($tableCols['foreign_key']) && is_array($tableCols['foreign_key'])) {
                            foreach ($tableCols['foreign_key'] as $table => $column) {
                                $col .= 'FOREIGN KEY ('. $column .') REFERENCES '. $table .'('. $column .'), ';
                            }
                            unset($tableCols['foreign_key']);
                        }
                        else if (isset ($tableCols['custom']) && is_string($tableCols['custom'])) {
                            $col .= $tableCols['custom'] . ' ';
                            unset($tableCols['custom']);
                        }
                        else if (isset($tableCols['engine']) && is_string($tableCols['engine'])) {
                            $engine = ' ENGINE=' . $tableCols['engine'];
                            unset($tableCols['engine']);
                        }
                        else {
                            throw new Codnitive_Migration_Model_Db_Exception("Codnitive_Migration_Model_Db::quTableCreat(): CREATE TABLE column definition is not valid, it must be array of definitions.");
                        }
                    }
                }
                else {
                    throw new Codnitive_Migration_Model_Db_Exception("Codnitive_Migration_Model_Db::quTableCreat(): CREATE TABLE columns is not valid, it must be array of columns.");
                }
                
                $sql .= $tableName . ' (' . rtrim($col, ', ') . ')' . $engine;
                if (true === $autoRun) {
                    $this->query($sql);
                }
                $result[$tableName] = $sql;
            }
        }
        else {
            throw new Codnitive_Migration_Model_Db_Exception("Codnitive_Migration_Model_Db::quTableCreat(): CREATE TABLE is not valid, it must be array of tables.");
        }
        
        return $result;
    }
    
    public function quInsert($table, array $bind)
    {
        foreach ($bind as $value) {
            if (is_array($value)) {
                $this->insert($table, $value);
            }
            else {
                $this->insert($table, $bind);
            }
        }
        return $this;
    }
    
    public function quSelect($table, $columns = '*')
    {
        $cols = '';
        
        if (!is_string($table)) {
            throw new Codnitive_Migration_Model_Db_Exception("Codnitive_Migration_Model_Db::quSelect(): FROM table_name is not valid, it must be string.");
        }
        
        if (is_array($columns)) {
            foreach ($columns as $col) {
                $cols .= $col . ', ';
            }
            $cols = rtrim($cols, ', ');
        }
        else if (is_string($columns)) {
            $cols = $columns;
        }
        else {
            throw new Codnitive_Migration_Model_Db_Exception("Codnitive_Migration_Model_Db::quSelect(): SELECT column(s) is not valid, column name must be string or array of columns names.");
        }
        
        $sql = "SELECT $cols FROM $table";
        $this->_query = $sql;
        return $this;
    }
    
    public function quUpdate(array $updates)
    {
        if (is_array($updates)) {
            foreach ($updates as $table => $bind) {
                $where = trim($this->resetQurey()
                        ->quWhere($bind['$where'])->getQuery(), ' WHERE ');
                unset($bind['$where']);
                $result[$table] = $this->update($table, $bind, $where);
            }
        }
        else {
            throw new Codnitive_Migration_Model_Db_Exception("Codnitive_Migration_Model_Db::quUdate(): UPDATE is not valid, it must be an array of tables and column to update.");
        }
        
        return $result;
    }
   
    public function quDelete(array $tables)
    {
        if (is_array($tables)) {
            foreach($tables as $table => $where) {
                $whe = trim($this->resetQurey()->quWhere($where)
                        ->getQuery(), ' WHERE '); 
                $result[$table] = $this->delete($table, $whe);
            }
        }
        else {
            throw new Codnitive_Migration_Model_Db_Exception("Codnitive_Migration_Model_Db::quDelete(): DELETE FROM is not valid, it must be an array of tables and rows to delete.");
        }
        
        return $result;
    }
    
    public function quWhere($wheres, $sql = null)
    {
        $str = '';
        
        if (null === $sql) {
            $sql = $this->getQuery();
        }
        
        if (is_array($wheres)) {
            foreach ($wheres as $where) {
                if (is_string($where)) {
                    $str .= $where;
                }
                else if (is_array($where)) {
                    if (isset($where[3])) {
                        $str .= ' ' . strtoupper($where[3]) . ' ';
                    }
                    $str .=  $where[0] . $where[1] . $where[2];
                }
            }
        }
        else if (is_string($wheres)) {
            $str = $wheres;
        }
        else {
            throw new Codnitive_Migration_Model_Db_Exception("Codnitive_Migration_Model_Db::quWhere(): WHERE is not valid, it must be string or array of WHEREs array.");
        }

        $str = " WHERE $str";
        $sql .= $str;
        $this->_query = $sql;
        return $this;
    }

    public function quCount($table, $columns = '*')
    {
        $cols = '';
        
        if (!is_string($table)) {
            throw new Codnitive_Migration_Model_Db_Exception("Codnitive_Migration_Model_Db::quCount(): FROM table_name is not valid, it must be string.");
        }
        
        if (is_array($columns)) {
            foreach ($columns as $col) {
                $cols .= $col . ', ';
            }
            $cols = rtrim($cols, ', ');
        }
        else if (is_string($columns)) {
            $cols = $columns;
        }
        else {
            throw new Codnitive_Migration_Model_Db_Exception("Codnitive_Migration_Model_Db::quCount(): SELECT COUNT(column(s)) is not valid, column name must be string or array of columns names.");
        }
        
        $sql = "SELECT COUNT($cols) FROM $table";
        $this->_query = $sql;
        return $this;
    }

    public function quOrder($columns, $sc = 'ASC', $sql = null)
    {
        $cols = '';
        
        if (null === $sql) {
            $sql = $this->getQuery();
        }
        
        if (is_array($columns)) {
            foreach ($columns as $col) {
                $cols .= $col . ', ';
            }
            $cols = rtrim($cols, ', ');
        }
        else if (is_string($columns)) {
            $cols = $columns;
        }
        else {
            throw new Codnitive_Migration_Model_Db_Exception("Codnitive_Migration_Model_Db::quOrder(): ORDER BY column(s) is not valid, column name must be string or array of columns names.");
        }

        $sql .= " ORDER BY $cols $sc";        
        $this->_query = $sql;
        return $this;
    }
    
    public function quLimit($count, $offset = 0, $sql = null)
    {
        if (null === $sql) {
            $sql = $this->getQuery();
        }
        
        $sql = $this->limit($sql, $count, $offset);
        
        $this->_query = $sql;
        return $this;
    }
    
    public function fetchObj($sql, $bind = array())
    {
        $this->setFetchMode(Zend_Db::FETCH_OBJ);
        return $this->fetchAll($sql, $bind);
    }
    
    public function fetchNum($sql, $bind = array())
    {
        $this->setFetchMode(Zend_Db::FETCH_NUM);
        return $this->fetchAll($sql, $bind);
    }
    
    public function quote($value, $type = null)
    {
        return trim(parent::quote($value, $type), '\'');
    }
    
}