<?php
/**
 * Table Name: cilw_icos
 * This file is responsible for all database realted functionality for a specific table.
 */
class cilw_list_table {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function __construct()
	{

		global $wpdb;

		$this->table_name = $wpdb->base_prefix . 'cilw_icos';
		$this->primary_key = 'id';
		$this->version = '1.0';

	}

	/**
	 * 
	 */
	public function cilw_get_ico_by_status( $status, $init, $limit ){
		global $wpdb;

		$query;	$orderby;

		switch( $status ){
			case 'ongoing':
				$query = "SELECT * FROM ".$this->table_name." WHERE status = '".$status."' ORDER BY end_time ASC, start_time ASC LIMIT ".$init.",".$limit;
			break;
			case "upcoming":
				$query = "SELECT * FROM ".$this->table_name." WHERE status = '".$status."' ORDER BY start_time ASC LIMIT ".$init.",".$limit;
			break;
			case "past":
				$query = "SELECT * FROM ".$this->table_name." WHERE status = '".$status."' ORDER BY end_time DESC LIMIT ".$init.",".$limit ;
		}

		$result = $wpdb->get_results( $query );
		
		return $result;
	}
	/**
	 * Get columns and formats
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function cilw_get_columns()
	{
		return array(
			'id' => '%d',
			'ico_id' => '%s',
			'name' => '%s',
			'symbol' => '%s',
			'status' => '%s',
			'small_desc' => '%s',
			'start_time' => '%s',
			'end_time' => '%s',
			'rating' => '%f',
			'country' => '%s',
			'platform' => '%s',
			'type' => '%s',
			'timestamp' => '%s',
			'featured' => '%s',
		);
	}

	function cilw_insert($icos_data){
		if(is_array($icos_data) && count($icos_data)>1){
		
		return $this->cilw_wp_insert_rows($icos_data,$this->table_name,true,'ico_id');
		}
	} 

	/**
	 * Retrieve orders from the database
	 *
	 * @access  public
	 * @since   1.0
	 * @param   array $args
	 * @param   bool  $count  Return only the total number of results found (optional)
	 */
	public function cilw_get_icos($args = array(), $count = false)
	{

		global $wpdb;

		$defaults = array(
			'limit' => 25,
			'offset' => 0,
			'id' =>'',
			'ico_id'=>'',
			'name'=>'',
			'status' => '',
			'orderby' => 'id',
			'order' => 'ASC',
		);

		$args = wp_parse_args($args, $defaults);

		if ($args['limit'] < 1) {
			$args['limit'] = 999999999999;
		}

		$where = '';

	// specific referrals
		if (!empty($args['id'])) {

			if (is_array($args['id'])) {
				$order_ids = implode(',', $args['id']);
			} else {
				$order_ids = intval($args['id']);
			}

			$where .= "WHERE `id` IN( {$order_ids} ) ";

		}

		if (!empty($args['status'])) {

			if (empty($where)) {
				$where .= " WHERE";
			} else {
				$where .= " AND";
			}

			if (is_array($args['status'])) {
				$where .= " `status` IN('" . implode("','", $args['status']) . "') ";
			} else {
				$where .= " `status` = '" . $args['status'] . "' ";
			}

		}

		if (!empty($args['ico_id'])) {

			if (empty($where)) {
				$where .= " WHERE";
			} else {
				$where .= " AND";
			}

			if (is_array($args['ico_id'])) {
				$where .= " `ico_id` IN('" . implode("','", $args['ico_id']) . "') ";
			} else {
				$where .= " `ico_id` = '" . $args['ico_id'] . "' ";
			}

		}


		$args['orderby'] = !array_key_exists($args['orderby'], $this->cilw_get_columns()) ? $this->primary_key : $args['orderby'];

		if ('total' === $args['orderby']) {
			$args['orderby'] = 'total+0';
		} else if ('subtotal' === $args['orderby']) {
			$args['orderby'] = 'subtotal+0';
		}

		$cache_key = (true === $count) ? md5('cilw_icos_count' . serialize($args)) : md5('cilw_icos_' . serialize($args));

		$results = wp_cache_get($cache_key, 'icos');

		if (false === $results) {

			if (true === $count) {

				$results = absint($wpdb->get_var("SELECT COUNT({$this->primary_key}) FROM {$this->table_name} {$where};"));

			} else {

				$results = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM {$this->table_name} {$where} ORDER BY {$args['orderby']} {$args['order']} LIMIT %d, %d;",
						absint($args['offset']),
						absint($args['limit'])
					)
				);

			}

			wp_cache_set($cache_key, $results, 'icos', 3600);

		}

		return $results;

	}
	
	/**
	 *  A method for inserting multiple rows into the specified table
	 *  Updated to include the ability to Update existing rows by primary key
	 *  
	 *  Usage Example for insert: 
	 *
	 *  $insert_arrays = array();
	 *  foreach($assets as $asset) {
	 *  $time = current_time( 'mysql' );
	 *  $insert_arrays[] = array(
	 *  'type' => "multiple_row_insert",
	 *  'status' => 1,
	 *  'name'=>$asset,
	 *  'added_date' => $time,
	 *  'last_update' => $time);
	 *
	 *  }
	 *
	 *
	 *  cilw_wp_insert_rows($insert_arrays, $wpdb->tablename);
	 *
	 *  Usage Example for update:
	 *
	 *  cilw_wp_insert_rows($insert_arrays, $wpdb->tablename, true, "primary_column");
	 *
	 *
	 * @param array $row_arrays
	 * @param string $wp_table_name
	 * @param boolean $update
	 * @param string $primary_key
	 * @return false|int
	 *
	 */
function cilw_wp_insert_rows($row_arrays = array(), $wp_table_name, $update = false, $primary_key = null) {
	global $wpdb;
	$wp_table_name = esc_sql($wp_table_name);
	// Setup arrays for Actual Values, and Placeholders
	$values        = array();
	$place_holders = array();
	$query         = "";
	$query_columns = "";

	$floatCols=array( 'rating' );
	$query .= "INSERT INTO `{$wp_table_name}` (";
	foreach ($row_arrays as $count => $row_array) {
		foreach ($row_array as $key => $value) {
			if ($count == 0) {
				if ($query_columns) {
					$query_columns .= ", " . $key . "";
				} else {
					$query_columns .= "" . $key . "";
				}
			}
			
			$values[] = $value;
			
			$symbol = "%s";
			if (is_numeric($value)) {
						$symbol = "%d";
				}
		
			if(in_array( $key,$floatCols)){
				$symbol = "%f";
			}
			if (isset($place_holders[$count])) {
				$place_holders[$count] .= ", '$symbol'";
			} else {
				$place_holders[$count] = "( '$symbol'";
			}
		}
		// mind closing the GAP
		$place_holders[$count] .= ")";
	}
	
	$query .= " $query_columns ) VALUES ";
	
	$query .= implode(', ', $place_holders);
	
	if ($update) {
		$update = " ON DUPLICATE KEY UPDATE $primary_key=VALUES( $primary_key ),";
		$cnt    = 0;
		foreach ($row_arrays[0] as $key => $value) {
			if ($cnt == 0) {
				$update .= "$key=VALUES($key)";
				$cnt = 1;
			} else {
				$update .= ", $key=VALUES($key)";
			}
		}
		$query .= $update;
	}

	$sql = $wpdb->prepare($query, $values);
	
	if ($wpdb->query($sql)) {
		return true;
	} else {
		return false;
	}
}

	/**
	 * Return the number of results found for a given query
	 *
	 * @param  array  $args
	 * @return int
	 */
	public function count($args = array())
	{
		return $this->cilw_get_icos($args, true);
	}

	/*
	|--------------------------------------------------------------------------
	|  create table for ICO main list
	|--------------------------------------------------------------------------
	*/
	public function cilw_create_ico_list_table()
	{

		global $wpdb;
		if( $wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") != $this->table_name ){
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			$sql = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				ico_id varchar(200) NOT NULL UNIQUE,
				name varchar(200) NOT NULL,
				symbol varchar(10),
				status varchar(10) NOT NULL,
				small_desc varchar(200),
				start_time varchar(20),
				end_time varchar(20),
				rating decimal(3,2),
				country varchar(50),
				platform varchar(20),
				type varchar(20),
				timestamp varchar(20),
				featured varchar(5),
				last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id)
			) CHARACTER SET utf8 COLLATE utf8_general_ci;";

			dbDelta($sql);
			update_option($this->table_name . '_db_version', $this->version);
		}

	}

	/**
	 * Drop database table(s)
	 */
	public function cilw_drop_table(){
		global $wpdb;

		// drop all tables
		$wpdb->query("DROP TABLE IF EXISTS " . $this->table_name);

	}
}