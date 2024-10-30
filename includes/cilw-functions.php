<?php 
/*
|--------------------------------------------------------------------------
| Fetching data from api and refresh every 2 hour
|--------------------------------------------------------------------------
 */
function cilw_get_api_data($status = 'ongoing')
{

    $cache_name = 'cilw-database-' . $status . '-icos'; // transient must be unique for every ico status type
    $DB = new cilw_list_table();
    $init = 0;
    $limit=25;
    $icos = array();
    $icos_data = array();
    $is_available = get_transient($cache_name);

    if (!isset($is_available) || $is_available == '') {
        $request = wp_remote_get('https://cryptomarketapi.com/api/icos/v1/list/' . $status . '/' . $init . '/' . $limit);
        if (is_wp_error($request)) {
            return false; // Bail early
        }
        $body = wp_remote_retrieve_body($request);
        $ico_data_list = json_decode($body);

        if ($ico_data_list) {

            foreach ($ico_data_list as $data) {

                $icos['ico_id'] = $data->id;
                $icos['name'] = $data->name;
                $icos['status'] = $status;
                $icos['small_desc'] = $data->small_desc;
                $icos['start_time'] = $data->start_time;
                $icos['end_time'] = $data->end_time;
                $icos['rating'] = $data->rating;
                $icos['country'] = $data->country;
                $icos['symbol'] = $data->symbol;
                $icos['platform'] = $data->platform;
                $icos['type'] = $data->type;
                $icos['timestamp'] = $data->timestamp;
                $icos['featured'] = $data->featured;

                $icos_data[] = $icos;

            }
            
                // Make sure table is already there in database
                $DB->cilw_create_ico_list_table();
            
            // Insert data or update if data already exists
            $result = $DB->cilw_insert($icos_data);

            // set transient so it won't run for the same data untile the transient expired
            set_transient($cache_name, date('H:s:i'), 5 * HOUR_IN_SECONDS);
        }
    }

}

/*
|--------------------------------------------------------------------------
|  Fetch icos from database on status basis
|--------------------------------------------------------------------------
 */
function cilw_ico_data($ico_status = 'ongoing')
{
	$limit=25; $init = 0;
    $DB = new cilw_list_table();
    $response = $DB->cilw_get_ico_by_status($ico_status, $init, $limit);

    $ico_data = cilw_objectToArray($response);

     if (!empty($ico_data)) {
        foreach ($ico_data as $coin) {
			$data[] = (array) $coin;
            
		}
		  return $data;
    }

}

/*
|--------------------------------------------------------------------------
|  Convert object into array
|--------------------------------------------------------------------------
 */
function cilw_objectToArray($d)
{
    if (is_object($d)) {
        // Gets the properties of the given object
        // with get_object_vars function
        $d = get_object_vars($d);
    }

    if (is_array($d)) {
        /*
         * Return array converted to object
         * Using __FUNCTION__ (Magic constant)
         * for recursive call
         */
        return array_map(__FUNCTION__, $d);
    } else {
        // Return array
        return $d;
    }
}
	
    // date formating
	function cilw_formated_date($datetime, $full = false) {
		if( $datetime == 'N/A' ){
			return '';
		}
	$now = new DateTime;	// to get current datetime stamp
	$time=	new DateTime;	// another datetime object required for making comparison

	$ago = $time->setTimestamp((int)$datetime);//new DateTime($datetime);
	$diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => __('year','cilw'),
        'm' => __('Month','cilw'),
        'w' => __('Week','cilw'),
        'd' => __('Day','cilw'),
        'h' => __('hour','cilw'),
        'i' => __('minute','cilw'),
        's' => __('second','cilw'),
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . '' : '';
	}

	
	
	//iCO's logos
	function cilw_coin_logo($coin_id)
	{
		$logo_html='';
		$coin_icon	= 'https://res.cloudinary.com/cryptoicolist/image/upload/ico-logo/' . $coin_id . '_ico.png';
		
		$defaultLogo=CILW_URL.'/assets/images/ico-icon.png';
		$logo_html='<img onerror="this.src=&quot;'.$defaultLogo.'&quot;" src="'.$coin_icon.'" alt="'.$coin_id.'">';
		
		return $logo_html;
    }