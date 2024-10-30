<?php
//CILW setting pannel
$ico_options = new_cmb2_box( array(
    'id'            => 'ico_settings',
    'title'         => __( 'Settings', 'cilw' ),
    'object_types'  => array( 'cilw', ), // Post type
    'context'       => 'normal',
    'priority'      => 'high',
    'show_names'    => true, // Show field names on the left

) );

$ico_pro_info = new_cmb2_box( array(
    'id'            => 'ico_pro_info',
    'title'         => __( 'Our Other Cryptocurrency Plugins', 'cilw' ),
    'object_types'  => array( 'cilw', ), // Post type
    'context'       => 'side',
    'priority'      => 'low',
    'show_names'    => true, // Show field names on the left

) );

/*  Create the options for metabox.  */

$ico_options->add_field( array(
    'name'      => __('Display ICOs','cilw' ),
    'desc'      => __('Select the ICOs you want to display.<br/>Note: All will be displayed on front-end if none selected.','cilw' ),
    'id'         => 'cilw_display_icos',
    'type'      => 'multicheck',
    'options'   => array (
                        'cilw_ongoing_icos'     =>  'Ongoing ICOs',
                        'cilw_upcoming_icos'    =>  'Upcoming ICOs',
                        'cilw_past_icos'        =>  'Past ICOs' 
                ),
    'default'   => cilw_cmb2_set_multicheck_default_for_new_post( true ),
    'select_all_button' =>  true,
    )
);
    $ico_options->add_field( array(
        'name' => __('ICO Platform?','cilw' ),
        'desc' => __('Select if you want to display ICO Platform?','cilw' ),
        'id'   => 'cilw_display_platform',
        'type' => 'checkbox',
        'default' => cilw_cmb2_set_checkbox_default_for_new_post( true ),
        ) );
        
    $ico_options->add_field( array(
    'name' => __('ICO Start Date?','cilw' ),
    'desc' => __('Select if you want to display Start Date ?','cilw' ),
    'id'   => 'cilw_start_date',
    'type' => 'checkbox',
    'default' => cilw_cmb2_set_checkbox_default_for_new_post( true ),
    
    ) );
    $ico_options->add_field( array(
    'name' => __('ICO End Date?','cilw' ),
    'desc' => __('Select if you want to display End Date?','cilw' ),
    'id'   => 'cilw_end_date',
    'type' => 'checkbox',
    'default' => cilw_cmb2_set_checkbox_default_for_new_post( true ),
    ) );
     $ico_options->add_field(array(
        'name' => '',
        'desc' => '
  <div class="cmc_pro">
  <H3>Crypto ICO List Widgets Pro - WordPress ICO Database Plugin</h3>
  <a target="_blank" href="https://codecanyon.net/item/crypto-ico-list-widgets-prowordpress-ico-database-plugin/22399693?irgwc=1&clickid=yzwwRT2Qu2hKXzRUmiS9Y0EyUkgQP1whcQh2w80&iradid=275988&irpid=1258464&iradtype=ONLINE_TRACKING_LINK&irmptype=mediapartner&mp_value1=&utm_campaign=af_impact_radius_1258464&utm_medium=affiliate&utm_source=impact_radius"><img style="max-width:100%;" src="https://res.cloudinary.com/coolplugins/image/upload/v1533116444/ico-plugin-files/ico-plugin-fetaures-11.png"></a>
  </div>',
        'type' => 'title',
        'id' => 'cmc_title',
    ));
    

  $ico_pro_info->add_field(array(
        'name' => '',
        'desc' => '
  <div class="cmc_pro">
  <a target="_blank" href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcryptocurrency-price-ticker-widget-pro-wordpress-plugin%2F21269050"><img style="max-width:100%;" src="'.CILW_URL.'/assets/images/buy-cryptowidgetpro.png"></a>
  </div>
    <div class="cmc_pro">
   <a target="_blank" href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcoin-market-cap-prices-wordpress-cryptocurrency-plugin%2F21429844"><img style="max-width:100%;" src="'.CILW_URL.'/assets/images/buy-coinmarketcap.png"></a>
   </div>
    <div class="cmc_pro">
   <a target="_blank" href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcryptocurrency-exchanges-list-pro-wordpress-plugin%2F22098669">
   <img style="max-width:100%;" src="'.CILW_URL.'/assets/images/buy-cryptoexchanges.png"></a> </div>',
        'type' => 'title',
        'id' => 'cmc_title',
    ));

function cilw_cmb2_set_checkbox_default_for_new_post( $default ) {
    //return;
    return isset( $_GET['post'] ) ? '' : ( $default ? (string) $default : '' );
}
function cilw_cmb2_set_multicheck_default_for_new_post( $default ) {
   if( !isset($_GET['post']) )
    {
      return array('cilw_ongoing_icos','cilw_upcoming_icos','cilw_past_icos');
    }
}
