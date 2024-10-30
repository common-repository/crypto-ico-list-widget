<?php
class CILW_Shortcode{

	function __construct(){
	require_once(CILW_PATH.'/includes/cilw-functions.php');
	require_once(CILW_PATH.'/includes/cilw-widget.php');
	add_action( 'wp_enqueue_scripts', array( $this, 'cilw_register_scripts' ) );
	add_shortcode( 'crypto-ico-list-widget',array($this,'cilw_shortcode'));
	}	
	
	//Creating crypto ICO list widget front-end
	function cilw_shortcode($atts) {
	$atts = shortcode_atts( array(
		'id'  => '',
	), $atts, 'cilw' );
	
	wp_enqueue_style( 'cilw-bootstrap-css');
	wp_enqueue_style( 'cilw-custom-css');
	
	wp_enqueue_script( 'cilw-bootstrap-js' );
	wp_enqueue_script( 'cilw-js' );

	
	$post_id=$atts['id'];
	$post = get_post($post_id); 
	
	// Initialize CMB2
	$display_platform 		=	get_post_meta( $post_id, 'cilw_display_platform', true );
	$display_start_date 	=	get_post_meta( $post_id, 'cilw_start_date', true );
	$display_end_date 		=	get_post_meta( $post_id, 'cilw_end_date', true );
	$display_icos_type		=	get_post_meta( $post_id, 'cilw_display_icos', true );
	
	if( $display_icos_type==="" && !is_array($display_icos_type) ){
		$upcoming_enable	=	1;
		$ongoing_enable		=	1;
		$past_enable		=	1;
		$display_icos_type  = array(1,2,3);
	}else{
		$upcoming_enable	=	in_array( 'cilw_upcoming_icos', $display_icos_type );
		$ongoing_enable		=	in_array( 'cilw_ongoing_icos', $display_icos_type );
		$past_enable		=	in_array( 'cilw_past_icos', $display_icos_type );
	}
	$icos_enabled=count( $display_icos_type );
	$finished_ico_data='';
	$upcoming_ico_data='';
	$live_ico_data='';
	
	ob_start();  ?>
	
	<div class="cilw-container" id="<?php echo $post_id; ?>">

	<?php if( $icos_enabled > 1 ) { ?>
		<!-- Nav tabs -->
		<ul class="cilw-nav" role="tablist">
		<?php if( $ongoing_enable ){ ?>
		<li role="presentation" class="active"><a href="#cilw-live" aria-controls="cilw-live" role="tab" data-toggle="tab"><?php echo __("Active ICOs",'cilw'); ?></a></li>
		<?php } if( $upcoming_enable ){ ?>
		<li role="presentation" <?php if( !$ongoing_enable) {?> class="active" <?php } ?> ><a href="#cilw-upcoming" aria-controls="cilw-upcoming" role="tab" data-toggle="tab"><?php echo __("Upcoming ICOs",'cilw'); ?></a></li>
		<?php } if( $past_enable ){ ?>
		<li role="presentation"><a href="#cilw-finished" aria-controls="cilw-finished" role="tab" data-toggle="tab"><?php echo __("Past ICOs",'cilw'); ?></a></li>
		<?php } ?>
		</ul>
		<div class="ico-tab-content">
	<?php } // end of id-statement for TabMenu ?>
    <!-- Tab panes -->
    
        
	<?php if( $upcoming_enable ){ 

		if( ($icos_enabled>1 && !$ongoing_enable) || $icos_enabled==1 ){
	       ?><div role="tabpanel" class="ico-tab-pane show" id="cilw-upcoming"><?php
		}else if( $icos_enabled>1 && $ongoing_enable ){
			?><div role="tabpanel" class="ico-tab-pane" id="cilw-upcoming"><?php
		}
		
		$ico_upcoming_data=cilw_ico_data($ico='upcoming');
		
		if( is_array($ico_upcoming_data)) {
		
		?>           

		   <table id="cilw-upcoming-dt" class="cilw-table" cellspacing="0" width="100%">
                <thead>
				<tr>
				
				<th><?php _e( "Name", 'cilw' ); ?></th>
				
				<?php
				if($display_start_date==true){ ?>
				<th><?php _e( 'Start ', 'cilw' ); ?></th>
				<?php } ?>
				
				<?php if($display_end_date==true){ ?>
				<th><?php _e( 'End ', 'cilw' ); ?></th>
				<?php } ?>
				
				</tr>
				</thead>
                <tbody>
                <?php 
				
				foreach($ico_upcoming_data as $ico_upcoming){
					
				$up_ico_logo=cilw_coin_logo( $ico_upcoming['ico_id'] );
				$ico_start_time='';
				$ico_start_date='';
				 if($ico_upcoming['start_time']!="N/A"){
				 $ico_start_time=cilw_formated_date($ico_upcoming['start_time']);
				 $ico_start_date =date("F d, Y",$ico_upcoming['start_time']);
				}else{
					$ico_start_time ="TBA";
				}
				
				$ico_end_time='';
				$ico_end_date='';
				if($ico_upcoming['end_time']!="N/A"){
				 $ico_end_time=cilw_formated_date($ico_upcoming['end_time']);
				 $ico_end_date =date("F d, Y",$ico_upcoming['end_time']);
				}
				else{
					$ico_end_date='TBA';
				}
			
				if($ico_upcoming['platform']=='null'){
					$ico_upcoming['platform']='n/a';
				}
				
				$upcoming_ico_data.='<tr>';
				
				$upcoming_ico_data .='<td><div class="cilw-logo">'.$up_ico_logo.'</div>
				<div class="cilw-detail"><span class="cilw-name">'.$ico_upcoming['name'].'</span>
				<span class="cilw-symbol">('.$ico_upcoming['symbol'].')</span>';
				if($display_platform==true){
				$upcoming_ico_data .='<div class="cilw-platform-n">'.__('Platform','cilw').': '.$ico_upcoming['platform'].'</div>';
				}
				$upcoming_ico_data .='</div>
				</td>';
				
				if($display_start_date==true){
				$upcoming_ico_data .='<td><div class="cilw-time"><div class="cilw-start-dt">'.$ico_start_date.'</div><div class="cilw-start-time">'.__('in ','cilw').''. $ico_start_time.'</div></div></td>';	
				}

				if($display_end_date==true){
					$upcoming_ico_data .='<td><div class="cilw-time"><div class="cilw-end-dt">'.$ico_end_date.'</div>';
					if($ico_upcoming['end_time']!="N/A"){
					$upcoming_ico_data .='<div class="cilw-end-time">'.__('in ','cilw').' '. $ico_end_time.'</div>';	
					}
					$upcoming_ico_data .='</div></td>';
				}
				
				$upcoming_ico_data.='</tr>';
		
				}
				echo $upcoming_ico_data;
				
			?>
                </tbody>
            </table>
			<?php }
			else {
				echo __('something wrong with api','cilw');
			}	
		?>
        </div>
	<?php } // end of if-statement for display icos settings:upcoming ?>
		
	<?php if( $ongoing_enable ) { ?>
	<div role="tabpanel" class="ico-tab-pane show" id="cilw-live">
            <?php 
			$ico_live_data=cilw_ico_data($ico='ongoing');
			
			if( is_array($ico_live_data)) {
			
			?>
			
			<table id="cilw-live-dt" class="cilw-table" cellspacing="0" width="100%">
                <thead>
				<tr>
				
				<th><?php _e( "Name", 'cilw' ); ?></th>
				
				<?php
				if($display_start_date==true){ ?>
				<th><?php _e( 'Start ', 'cilw' ); ?></th>
				<?php } ?>
				
				<?php if($display_end_date==true){ ?>
				<th><?php _e( 'End ', 'cilw' ); ?></th>
				<?php } ?>
			
				</tr>
				</thead>
                <tbody>
			<?php 
					
				foreach($ico_live_data as $ico_live){		
				$live_ico_logo=cilw_coin_logo( $ico_live['ico_id']);
				
				$ico_end_time='';
				$ico_end_date='';
				if($ico_live['end_time']!="N/A"){
				 $ico_end_time=cilw_formated_date($ico_live['end_time']);
				 $ico_end_date =date("F d, Y",$ico_live['end_time']);
				}
				
				$ico_start_time='';
				$ico_start_date='';
				if($ico_live['start_time']!="N/A"){
				 $ico_start_time=cilw_formated_date($ico_live['start_time']);
				 $ico_start_date =date("F d, Y",$ico_live['start_time']);
				}else{
					$ico_start_time = "TBA";
				}
								
					
				if($ico_live['platform']=='null'){
						$ico_live['platform']='n/a';
					}
					
				$live_ico_data.='<tr>';
				
				$live_ico_data .='<td><div class="cilw-logo">'.$live_ico_logo.'</div>
				<div class="cilw-detail"><span class="cilw-name">'.$ico_live['name'].'</span>
				<span class="cilw-symbol">('.$ico_live['symbol'].')</span>';
				
				if($display_platform==true){
				$live_ico_data .='<div class="cilw-platform-n">'.__('Platform','cilw').': '.$ico_live['platform'].'</div>';
				}
				
				$live_ico_data .='</div>
				</td>';
					
				if($display_start_date==true){
					$live_ico_data .='<td><div class="cilw-time"><div class="cilw-start-dt">'.$ico_start_date.'</div>';	
					if($ico_live['start_time']!="N/A"){
						$live_ico_data .='<div class="cilw-start-time">'. $ico_start_time.' '.__('ago ','cilw').'</div>';
					}
					$live_ico_data .='</div></td>';
				}

				if($display_end_date==true){
					$live_ico_data .='<td><div class="cilw-time"><div class="cilw-end-dt">'.$ico_end_date.'</div>';	
					if($ico_live['end_time']!="N/A"){
						$live_ico_data .='<div class="cilw-end-time">'.__('in ','cilw').' '. $ico_end_time.'</div>';
					}
					$live_ico_data .='</div></td>';
				}
			
				
				$live_ico_data.='</tr>';
		
				}
				
			echo $live_ico_data;
		
			?>
                </tbody>
            </table>
			<?php }
			else {
				echo __('something wrong with api','cilw');
			}	
		?>
        </div>
	<?php } // end of if-statement for display ico settings:ongoing ?>

	<?php if( $past_enable ) { 
		
		if( $icos_enabled<2 ){ ?>
		<div role="tabpanel" class="ico-tab-pane show" id="cilw-upcoming">
		<?php }else{ ?>
				<div role="tabpanel" class="ico-tab-pane" id="cilw-finished">
		<?php 
				}

		$ico_finished_data=cilw_ico_data($ico='past');
		
		if( is_array($ico_finished_data)) {
		
				
		?>           

		   <table id="cilw-finished-dt" class="cilw-table" cellspacing="0" width="100%">
               <thead>
				<tr>
				
				<th><?php _e( "Name", 'cilw' ); ?></th>
				
				<?php
				if($display_start_date==true){ ?>
				<th><?php _e( 'Start ', 'cilw' ); ?></th>
				<?php } ?>
				
				<?php if($display_end_date==true){ ?>
				<th><?php _e( 'End ', 'cilw' ); ?></th>
				<?php } ?>
				
				</tr>
				</thead>
                <tbody>
                <?php 
				foreach($ico_finished_data as $ico_finished){
				$fn_ico_logo=cilw_coin_logo( $ico_finished['ico_id'] );
				 $ico_start_time='';
				 $ico_start_date='';
				 if($ico_finished['start_time']!="N/A"){
				 $ico_start_time=cilw_formated_date($ico_finished['start_time']);
				 $ico_start_date =date("F d, Y",$ico_finished['start_time']);
				}else{
					$ico_start_time = "TBA";
				}
				
				$ico_end_time='';
				$ico_end_date='';
				if($ico_finished['end_time']!="N/A"){
				 $ico_end_time=cilw_formated_date($ico_finished['end_time']);
				 $ico_end_date =date("F d, Y",$ico_finished['end_time']);
				}
				else{
					$ico_end_date='TBA';
				}
				if($ico_finished['platform']=='null'){
						$ico_finished['platform']='n/a';
					}
				
				$finished_ico_data.='<tr>';
				
				$finished_ico_data .='<td><div class="cilw-logo">'.$fn_ico_logo.'</div>
				<div class="cilw-detail"><span class="cilw-name">'.$ico_finished['name'].'</span>
				<span class="cilw-symbol">('.$ico_finished['symbol'].')</span>';
				if($display_platform==true){
				$finished_ico_data .='<div class="cilw-platform-n">'.__('Platform','cilw').': '.$ico_finished['platform'].'</div>';
				}
				$finished_ico_data .='</div>
				</td>';
				
				if($display_start_date==true){
					$finished_ico_data .='<td><div class="cilw-time"><div class="cilw-start-dt">'.$ico_start_date.'</div>';	
					if($ico_finished['start_time']!="N/A"){
						$finished_ico_data .='<div class="cilw-start-time">'. $ico_start_time.' '.__('ago ','cilw').'</div>';	
					}
					$finished_ico_data .='</div></td>';
				}
				

				if($display_end_date==true){
					$finished_ico_data .='<td><div class="cilw-time"><div class="cilw-end-dt">'.$ico_end_date.'</div>';
					if($ico_finished['end_time']!="N/A"){
						$finished_ico_data .='<div class="cilw-end-time">'. $ico_end_time.' '.__('ago ','cilw').' </div>';	
					}
					$finished_ico_data .='</div></td>';
				}
				
				$finished_ico_data.='</tr>';
		
				}
				echo $finished_ico_data;
				
			?>
                </tbody>
            </table>
			<?php }
			else {
				echo __('something wrong with api','cilw');
			}	
		?>
        </div>
	<?php	}	// end of if-statement for display-ico-settings:past ?>
		
</div>


<?php 
		$output_string = ob_get_contents();
			ob_end_clean();
			return $output_string;
		}
	
	
	/**
	 * Register scripts and styles
	 */
	 //add cdn and change js file functions
	public function cilw_register_scripts() {
		if ( ! is_admin() ) {
			if( ! wp_script_is( 'jquery', 'done' ) ){
                wp_enqueue_script( 'jquery' );
               }
	
	 //JS		  
	 wp_register_script( 'cilw-bootstrap-js',CILW_URL.'assets/js/bootstrap.min.js', array(), false, true);
	 wp_register_script( 'cilw-js', CILW_URL . 'assets/js/cilw.js', array( 'jquery'), false, true );		  
	 
	 //CSS	
	 wp_register_style( 'cilw-custom-css',CILW_URL.'assets/css/cilw-styles.css');
	 
		}
		
	}
	
}