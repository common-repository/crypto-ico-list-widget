<?php
// Register and load the widget
function cilw_load_widget() {
    register_widget( 'cilw_widget' );
}

add_action( 'widgets_init', 'cilw_load_widget' );
 
// Creating the widget 
class cilw_widget extends WP_Widget {
 
	//this function registers widget with wordpress 
	function __construct() {
		parent::__construct(
		 
		// Base ID of your widget
		'cilw_widget', 
		 
		// Widget name will appear in UI
		__('Crypto ICO List Widget', 'cilw'), 
		 
		// Widget description
		array( 'description' => __( 'Crypto ICO List Widget', 'cilw' ), ) 
		);
	}
	 
	 
	// Creating widget front-end
	 
	public function widget( $args, $instance ) {
		wp_enqueue_style( 'cilw-custom-css');	// enqueue style for widget
		$crypto_ico_data=$instance['crypto_ico_data'];	
		$title = apply_filters( 'widget_title', $instance['title'] );

		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) )
		echo $args['before_title'] . $title . $args['after_title'];
		
	
		if(isset($crypto_ico_data) && !empty($crypto_ico_data)){
	
			if($crypto_ico_data=="upcoming"){

				$ico_upcoming_data = cilw_ico_data($ico='upcoming');
				if( is_array($ico_upcoming_data)) {
				
				echo '<div class="cilw-widget" id="'.$crypto_ico_data.'" >
					   <table class="cilw_table cilw-container table id="'.$crypto_ico_data.'">
					   <thead>
							<tr>
							<th>'.__('Upcoming ICOs','cilw').'</th>
							<th>'.__('Start Date','cilw').'</th>
							</tr>
					   </thead>
					   <tbody>';
				$ico_upcoming_data1=array_slice($ico_upcoming_data,0,10);
				foreach($ico_upcoming_data1 as $ico_upcoming){
				
				$up_ico_logo=cilw_coin_logo( $ico_upcoming['ico_id']);
				
				 $ico_start_time='';
					 $ico_start_date='';
				 if($ico_upcoming['start_time']!="TBA"){
				 $ico_start_time=cilw_formated_date($ico_upcoming['start_time']);
				 $ico_start_date =date("F d, Y",$ico_upcoming['start_time']);
				}
				
				if($ico_upcoming['platform']=='null'){
						$ico_upcoming['platform']='n/a';
					}
				
				$upcoming_html='<tr id="cilw-coin-id">
					   <td>
					   <div class="cilw-logo">'.$up_ico_logo.'</div>
					   <div class="cilw-detail"><span class="cilw-name">'.$ico_upcoming['name'].'</span>
					   <span class="cilw-symbol">('.$ico_upcoming['symbol'].')</span>
					   <div class="cilw-platform-n">'.__('Platform','cilw').': '.$ico_upcoming['platform'].'</div> 						   
					   </div>
					   </td>
					   
					   <td>
					   <div class="cilw-time"><div class="cilw-start-dt">'.$ico_start_date.'</div>
					   <div class="cilw-start-time">'.__('in ','cilw').''. $ico_start_time.'</div>
					   </div>
					   </td>
					   </tr>';

				echo $upcoming_html;
				}
				//echo '</tbody></table></div>';	
				}
			}
			
			else if($crypto_ico_data=="live"){
				$ico_live_data = cilw_ico_data($ico='ongoing');

				if( is_array($ico_live_data)){
				
				echo '<div class="cilw-widget" id="'.$crypto_ico_data.'" >
					   <table class="cilw_table cilw-container table id="'.$crypto_ico_data.'">
					   <thead>
							<tr>
							<th>'.__('Active ICOs','cilw').'</th>
							<th>'.__('End Date','cilw').'</th>
							</tr>
					   </thead>
					   <tbody>';
				$ico_live_data1=array_slice($ico_live_data,0,10);
				foreach($ico_live_data1 as $ico_live){
				
				$live_ico_logo=cilw_coin_logo( $ico_live['ico_id']);
				
				$ico_end_time='';
				$ico_end_date='';
					if($ico_live['end_time']!="TBA"){
				 $ico_end_time=cilw_formated_date($ico_live['end_time']);
				 $ico_end_date =date("F d, Y",$ico_live['end_time']);
				}
				
				if($ico_live['platform']=='null'){
						$ico_live['platform']='n/a';
					}
				
				$live_html='<tr id="cilw-coin-id">
					   <td>
					   <div class="cilw-logo">'.$live_ico_logo.'</div>
					   <div class="cilw-detail"><span class="cilw-name">'.$ico_live['name'].'</span>
					   <span class="cilw-symbol">('.$ico_live['symbol'].')</span> 
					   <div class="cilw-platform-n">'.__('Platform','cilw').': '.$ico_live['platform'].'</div> 						   
					   </div>
					   </td>
					   
					   <td><div class="cilw-time"><div class="cilw-end-dt">'.$ico_end_date.'</div>';
					   if($ico_live['end_time']!="TBA"){
					   $live_html .='<div class="cilw-end-time">'.__('in ','cilw').' '. $ico_end_time.'</div>';
					   }
					   $live_html .='</div></td>
					   </tr>';

				echo $live_html;

				}
				}
			}
			
			else{
				$ico_past_data = cilw_ico_data($ico='past');
				
				if( is_array($ico_past_data)) {
				
				echo '<div class="cilw-widget" id="'.$crypto_ico_data.'" >
					   <table class="cilw_table cilw-container table id="'.$crypto_ico_data.'">
					   <thead>
							<tr>

							<th>'.__('Past ICOs','cilw').'</th>
							<th>'.__('Closed','cilw').'</th>
							
							</tr>
					   </thead>
					   <tbody>';

				$ico_past_data1=array_slice($ico_past_data,0,10);
				foreach($ico_past_data1 as $ico_past){
				
				$fn_ico_logo=cilw_coin_logo( $ico_past['ico_id']);
				
				$ico_end_time='';
				$ico_end_date='';
				if($ico_past['end_time']!="TBA"){
				 $ico_end_time=cilw_formated_date($ico_past['end_time']);
				 $ico_end_date =date("F d, Y",$ico_past['end_time']);
				}
				else{
					$ico_end_date='TBA';
				}
				
				if($ico_past['platform']=='null'){
						$ico_past['platform']='n/a';
					}
				
				$past_html='<tr id="cilw-coin-id">
					   <td>
					   <div class="cilw-logo">'.$fn_ico_logo.'</div>
					   <div class="cilw-detail"><span class="cilw-name">'.$ico_past['name'].'</span>
					   <span class="cilw-symbol">('.$ico_past['symbol'].')</span>
					   <div class="cilw-platform-n">'.__('Platform','cilw').': '.$ico_past['platform'].'</div>					   
					   </div>
					    						   
					   </td>
					   <td><div class="cilw-time"><div class="cilw-end-dt">'.$ico_end_date.'</div><div class="cilw-end-time">';
				if($ico_past['end_time']!="TBA"){
				$past_html .=''. $ico_end_time.' '.__('ago ','cilw').' </div></div></td>';	
				}
				$past_html .='</tr>';

				echo $past_html;

				}
				}
			}

			echo '</tbody></table></div>';	
		}

		echo $args['after_widget'];
	}
			 
	// Widget Backend 
	public function form( $instance ) {

		if(!isset($instance['crypto_ico_data']) || empty($instance['crypto_ico_data'])) { 
		$instance['crypto_ico_data'] = "upcoming"; }
		else {
		$crypto_ico_data = $instance['crypto_ico_data'];	
		}

		if ( isset( $instance[ 'title' ] ) ) {
		$title = $instance[ 'title' ];
		}
		else {
		$title = __( 'Crypto ICO List Widget', 'cilw' );
		}
		// Widget admin form
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		   
		<p>
			<label for="<?php echo $this->get_field_id( 'crypto_ico_data' ); ?>"> View ICO:</label>
			<select style="width:70%" id="<?php echo $this->get_field_id( 'crypto_ico_data' ); ?>" name="<?php echo $this->get_field_name( 'crypto_ico_data' ); ?>" >
		
	    <option <?php selected($instance['crypto_ico_data'],'upcoming') ?> value="upcoming">Upcoming</option>
		<option <?php selected($instance['crypto_ico_data'],'live') ?> value="live">Active</option>
		<option <?php selected($instance['crypto_ico_data'],'past') ?> value="past">Past</option>	

			</select>
		</p>  
	
		<?php 
	}
		 
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance[ 'crypto_ico_data' ] = strip_tags( $new_instance[ 'crypto_ico_data' ] );

		return $instance;
	}
} // Class wpb_widget ends here