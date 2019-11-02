<?php
/**
 * Abstract class which has helper functions to get data from the database
 */
if ( !class_exists('GFDC_DB_ABSTRACT') )
{   
abstract class GFDC_DB_ABSTRACT
{
    /**
     * The current table name
     *
     * @var boolean
     */
    private $tableName = false;
	 /**
     * Constructor for the database class to inject the table name
     *
     * @param String $tableName - The current table name
     */
    public function __construct($tableName)
    {
		global $gfdc_wpdb;
        $this->tableName = $tableName;
		if( false  == $gfdc_wpdb )
		{
			gfdf_connect_db();
		}
    }
    /**
     * Insert data into the current data
     *
     * @param  array  $data - Data to enter into the database table
     *
     * @return InsertQuery Object
     */
    public function insert(array $data)
    {
        global $gfdc_wpdb;
        if(empty($data))
        {
            return false;
        }
        $gfdc_wpdb->insert($this->tableName, $data);
        return $gfdc_wpdb->insert_id;
    }
    /**
     * Get all from the selected table
     *
     * @param  String $orderBy - Order by column name
     *
     * @return Table result
     */
    public function get_all( $orderBy = NULL )
    {
        global $gfdc_wpdb;
        $sql = 'SELECT * FROM `'.$this->tableName.'`';
        if(!empty($orderBy))
        {
            $sql .= ' ORDER BY ' . $orderBy;
        }
        $all = $gfdc_wpdb->get_results($sql);
        return $all;
    }
    /**
     * Get a value by a condition
     *
     * @param  Array $conditionValue - A key value pair of the conditions you want to search on
     * @param  String $condition - A string value for the condition of the query default to equals
     *
     * @return Table result
     */
    public function get_by(array $conditionValue, $condition = '=')
    {
        global $gfdc_wpdb;
        $sql = 'SELECT * FROM `'.$this->tableName.'` WHERE ';
        foreach ($conditionValue as $field => $value) {
            switch(strtolower($condition))
            {
                case 'in':
                    if(!is_array($value))
                    {
                        throw new Exception("Values for IN query must be an array.", 1);
                    }
                    $sql .= $gfdc_wpdb->prepare('`%s` IN (%s)', $field, implode(',', $value));
                break;
                default:
                    $sql .= $gfdc_wpdb->prepare('`'.$field.'` '.$condition.' %s', $value);
                break;
            }
        }
        $result = $gfdc_wpdb->get_results($sql);
        return $result;
    }
    /**
     * Update a table record in the database
     *
     * @param  array  $data           - Array of data to be updated
     * @param  array  $conditionValue - Key value pair for the where clause of the query
     *
     * @return Updated object
     */
    public function update(array $data, array $conditionValue)
    {
        global $gfdc_wpdb;
        if(empty($data))
        {
            return false;
        }
        $updated = $gfdc_wpdb->update( $this->tableName, $data, $conditionValue);
        return $updated;
    }
    /**
     * Delete row on the database table
     *
     * @param  array  $conditionValue - Key value pair for the where clause of the query
     *
     * @return Int - Num rows deleted
     */
    public function delete(array $conditionValue)
    {
        global $gfdc_wpdb;
        $deleted = $gfdc_wpdb->delete( $this->tableName, $conditionValue );
        return $deleted;
    }
}
}
?>