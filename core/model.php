<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Model
{

    private $dbVersion = '2.0.0';

    protected $_data;

    protected $columns;

    protected $defaultPK = 'id';

    protected $tableName;

    protected function getPrefix()
    {
        return $this->getWpdb()->prefix;
    }
    
    protected function getWpdb()
    {
        global $wpdb;
        
        return $wpdb;
    }

    public function getTable()
    {
        return $this->getPrefix() . $this->tableName;
    }

    public function tableExists($tableName)
    {
        return $this->getWpdb()->get_var("SHOW TABLES LIKE '" . $tableName . "'") == $tableName;
    }

    public function createTable($columns, $primaryKey = null)
    {
        $tableName = $this->getTable();
        if (! $this->tableExists($tableName)) {
            $sql = "CREATE TABLE IF NOT EXISTS " . $tableName . " (";
            foreach ($columns as $name => $attributes) {
                $sql .= $name . " " . implode(" ", $attributes['schema']) . ',';
            }
            $sql .= $primaryKey ? ' PRIMARY KEY (' . $primaryKey . ')' : '';
            $sql = rtrim($sql, ',');
            $sql .= ") CHARACTER SET utf8 COLLATE utf8_bin;";

            $this->getWpdb()->query($sql);
        }
    }

    public function install()
    {
        $this->createTable($this->getColumns(), $this->getDefaultPk());
    }

    public function uninstall()
    {
        $this->getWpdb()->query("DROP TABLE " . $this->getTable());
    }

    public function getData($key = null)
    {
        return $key ? (isset($this->_data[$key]) ? $this->_data[$key] : null) : $this->_data;
    }

    public function setData($key, $value = null)
    {
        $columns = $this->getColumns();
        
        if (! is_array($key)) {
            if(isset($columns[$key])){
                $this->_data[$key] = $value;
            }
        } else {
            foreach($key as $index => $value){
                if(isset($columns[$index])){
                    $this->_data[$index] = $value;
                }
            }
        }

        return $this;
    }

    public function unsetData()
    {
        $this->_data = [];
    }

    public function getColumn($name)
    {
        $columns = $this->getColumns();
        
        return $columns[$name];
    }

    public function addColumn($table, $name, $schema, $after = null)
    {
        $pattern = 'ALTER TABLE %s ADD %s %s';
        $query = sprintf($pattern, $table, $name, implode(' ', $schema));
        if ($after) {
            $pattern .= ' AFTER %s';
            $query = sprintf($pattern, $table, $name, implode(' ', $schema), $after);
        }

        $this->getWpdb()->query(
            $query
        );
    }
    
    public function existColumn($table, $name)
    {
        $this->getWpdb()->get_results(sprintf('SHOW COLUMNS FROM %s LIKE \'%s\'',$table,$name));
        
        return $this->getWpdb()->num_rows > 0 ? true : false;
    }

    public function removeColumn($table, $name)
    {
        $this->getWpdb()->query(sprintf('ALTER TABLE %s DROP COLUMN %s', $table, $name));
    }
    
    public function modifyColumn($table, $name, $schema)
    {
        $this->getWpdb()->query(sprintf('ALTER TABLE %s MODIFY %s %s', $table, $name, implode(' ',$schema)));
    }

    public function getColumns()
    {
        return apply_filters('gjmaa_get_columns_'.$this->getModel().'_filter',$this->columns);
    }

    public function setColumns($columns = [])
    {
        $this->columns = $columns;
        return $this;
    }

    public function migrate()
    {
        $data = $this->getData();
        $this->migrateData($data);
    }

    public function save()
    {
        $data = $this->getData();
        if (isset($data[$this->getDefaultPk()]) && $data[$this->getDefaultPk()] != 0) {
            $id = $data[$this->getDefaultPk()];
            unset($data[$this->getDefaultPk()]);
            $this->updateData($id, $data);
        } else {
            unset($data[$this->getDefaultPk()]);
            $this->insertData($data);
        }

        return $this;
    }

    public function delete()
    {
        if ($this->getData($this->getDefaultPk())) {
            $this->getWpdb()->query('DELETE FROM ' . $this->getTable() . ' WHERE ' . $this->getDefaultPk() . ' = ' . $this->getData($this->getDefaultPk()));
        }

        $this->unsetData();

        return $this;
    }

    public function getDefaultPk()
    {
        return $this->defaultPK;
    }

    public function setDefaultPk($key = 'id')
    {
        $this->defaultPK = $key;
    }

    public function insertData($data)
    {
        $this->getWpdb()->insert($this->getTable(), $data, $this->parseFormat($data));

        return $this->getWpdb()->insert_id ? $this->load($this->getWpdb()->insert_id) : $this->unsetData();
    }

    public function migrateData($data)
    {
        $this->getWpdb()->insert($this->getTable(), $data, $this->parseFormat($data));

        if (! $this->getWpdb()->last_error)
            return;

        $id = $data[$this->getDefaultPk()];
        unset($data[$this->getDefaultPk()]);

        $this->updateData($id, $data);
    }

    public function updateData($id, $data)
    {
        $whereData = [
            $this->getDefaultPk() => $id
        ];

        $this->getWpdb()->update($this->getTable(), $data, $whereData, $this->parseFormat($data), $this->parseFormat($whereData));

        return $this->load($id);
    }
    
    public function updateAttribute($id, $attribute, $value)
    {
        $this->updateData($id, [$attribute => $value]);
    }

    public function parseFormat($data)
    {
        $format = [];
        foreach ($data as $index => $value) {
            $column = $this->getColumn($index);
            $format[] = $column['format'];
        }

        return $format;
    }

    public function load($value, $column = null)
    {
        $columns = is_null($column) ? [
            $this->getDefaultPk()
        ] : (! is_array($column) ? [
            $column
        ] : $column);

        $values = ! is_array($value) ? [
            $value
        ] : $value;
        $where = [];

        foreach ($columns as $index => $column) {
            $where[] = sprintf('%s="%s"', $column, $values[$index]);
        }
        $sql = sprintf("SELECT * FROM %s WHERE %s", $this->getTable(), implode(' AND ', $where));

        $response = $this->getWpdb()->get_row($sql, ARRAY_A);

        if (! empty($response)) {
            foreach ($response as $key => $data) {
                $this->setData($key, $data);
            }
        } else {
            $this->unsetData();
        }

        return $this;
    }

    public function getAll()
    {
        $sql = "SELECT * FROM " . $this->getTable() . ' ORDER BY ' . $this->getDefaultPk() . ' ASC';
        return $this->getWpdb()->get_results($sql, ARRAY_A);
    }

    public function getAllIds()
    {
        $all = $this->getAll();

        $ids = [];

        foreach ($all as $entity) {
            $ids[] = $entity[$this->getDefaultPk()];
        }

        return $ids;
    }

    public function getCountAll()
    {
        $sql = "SELECT COUNT(*) FROM " . $this->getTable();
        return $this->getWpdb()->get_var($sql);
    }

    public function findIn($field, $array)
    {
        $query = 'SELECT * FROM ' . $this->getTable();
        if (is_array($array)) {
            $query .= " WHERE $field IN ('" . implode("','", $array) . "')";
        } else {
            $query .= " WHERE $field = '$array'";
        }

        return $this->getWpdb()->get_results($query, ARRAY_A);
    }

    public function getId()
    {
        return isset($this->_data[$this->defaultPK]) ? $this->_data[$this->defaultPK] : null;
    }

    public function update($currentVersion)
    {
        return false;
    }

    public function searchParseToWhere($search, $columns)
    {
        if (is_null($search) || $search === '' || (! is_numeric($search) && empty($search))) {
            return '';
        }

        $search = strtolower($search);
        $where = [];

        foreach ($columns as $columnName) {
            if (! isset($this->columns[$columnName])) {
                continue;
            }

            $format = $this->columns[$columnName]['format'];

            switch ($format) {
                case '%d':
                case '%f':
                    if (is_numeric($search)) {
                        $where[] = $columnName . ' = ' . $search;
                    }
                    break;
                case '%s':
                default:
                    $where[] = sprintf('LOWER(%s) LIKE \'%s\'',$columnName, '%' . $search . '%');
                    break;
            }
        }

        return empty($where) ? '' : 'WHERE (' . implode(' OR ', $where) .')';
    }

    public function getCountFilteredResult($search, $columns, $toFilter = [])
    {
        $where = $this->searchParseToWhere($search, $columns);
        
        $where = $this->parseFilters($where,$toFilter);

        $query = "SELECT COUNT(*) FROM " . $this->getTable() . " " . $where;

        return $this->getWpdb()->get_var($query);
    }
    
    public function parseFilters($where, $toFilter = [])
    {
        if(!empty($toFilter)) {
            $conditions = [];
            
            foreach($toFilter as $field => $value) {
                $columnData = $this->getColumn($field);
                
                switch($columnData['format']):
                    case '%d':
                    case '%f':
                        $conditions[] = $field . '=' . $value;
                        break;
                    case '%s':
                        $conditions[] = $field . '=\'' . $value . '\'';
                        break;
                endswitch;
            }
            
            $where .= empty($where) ? 'WHERE (' . implode(' AND ', $conditions) . ')' : ' AND ' . implode(' AND ', $conditions);
        }
        
        
        return $where;
    }

    public function getFilteredResult($page, $limit, $search, $columns, $sort, $toFilter = [])
    {
        $offset = $page * $limit - $limit;

        $where = $this->searchParseToWhere($search, $columns);
        
        $where = $this->parseFilters($where, $toFilter);

        $query = sprintf("SELECT * FROM %s %s %s LIMIT %d OFFSET %d", $this->getTable(), $where, $sort, $limit, $offset);

        return $this->getWpdb()->get_results($query, ARRAY_A);
    }
    
    public function getModel()
    {
        return $this->model;
    }

    public function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

	public function getRowBySearch($filters, $limit = 25)
	{
		$querySelect = "SELECT * FROM " . $this->getTable();
		$where = '';
		foreach ($filters as $schema => $value) {
			$where .= $schema . " " . $value . " ";
		}

		$querySelect .= " " . $where;

		$row = $this->getWpdb()->get_row($querySelect, ARRAY_A);

		return $row;
	}

	public function getAllBySearch($filters, $limit = 25)
	{
		$querySelect = "SELECT * FROM " . $this->getTable();
		$where = '';
		foreach ($filters as $schema => $value) {
			$where .= $schema . " " . $value . " ";
		}

		$querySelect .= " " . $where;

		$rows = $this->getWpdb()->get_results($querySelect, ARRAY_A);

		return $rows;
	}
}