<?php
/**

	Plugin Name: Pixavid Random Pics
	Plugin URI: http://pixavid.com/plugins.php
	Description: Integrate pictures from pixavid.com into your wordpress site.
	Version: 0.5
	Date: 8th November 2009
	License: GPL
	Author: Martin Platt
	Author URI: http://pixavid.com

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */


// --------------------------------------------------------------------
// Add function to widgets_init that'll load our widget.
// --------------------------------------------------------------------

add_action( 'widgets_init', 'pixavid_load_widgets' );


// --------------------------------------------------------------------
// Register our widget.
// --------------------------------------------------------------------

function pixavid_load_widgets() {
	register_widget( 'PixavidRand' );
}


// --------------------------------------------------------------------
// Pixavid Widget class.
// This class handles everything that needs to be handled with the widget:
// the settings, form, display, and update.  Nice!
// --------------------------------------------------------------------

class PixavidRand extends WP_Widget {

// --------------------------------------------------------------------
// Widget setup
// --------------------------------------------------------------------

	function PixavidRand() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'pixrand', 'description' => __('Displays random pictures from Pixavid.', 'pixrand') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'pixavidrand-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'pixavidrand-widget', __('Pixavid Random Pics', 'pixrand'), $widget_ops, $control_ops );
	}


// --------------------------------------------------------------------
// How to display the widget on the screen.
// --------------------------------------------------------------------

	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$imgcount = $instance['imgcount'];

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title . '<br>';

    $this->pixavid_out($imgcount) ;

		/* After widget (defined by themes). */
		echo $after_widget;
	}


// --------------------------------------------------------------------
// pixavid_out - where the work is done
// --------------------------------------------------------------------

	function pixavid_out($imgcount) {
	  $command = '2001' ;

		if($imgcount < 0) {$imgcount = 1;}
		if($imgcount > 20) {$imgcount = 20;}

		$PVSERVER = 'http://www.pixavid.com/' ;

	  $myxml = '<comm-xml><CMD>' . $command . '</CMD><ID>100</ID><REQNUMBER>'. $imgcount .'</REQNUMBER><REQSIZE>THUMB</REQSIZE></comm-xml>' ;

	  $ch = curl_init($PVSERVER . 'pixavid-api.php');
	  curl_setopt ($ch, CURLOPT_POST, 1);
	  curl_setopt ($ch, CURLOPT_POSTFIELDS, "XML=". $myxml);
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	  $xml = curl_exec($ch);
	  curl_close ($ch);

	  // echo $xml ;

	  $doc = new DOMDocument();
	  $doc->loadXML($xml);

	  $i = 0;


		while ($doc->getElementsByTagName("IMAGEID")->item($i)->nodeValue != '') {
		  $imageid[$i] = $doc->getElementsByTagName("IMAGEID")->item($i)->nodeValue;
		  $url[$i] = $doc->getElementsByTagName("URL")->item($i)->nodeValue;
			$i++;
		}

	  for ($i=0; $i< $imgcount; $i++) {
	    $out = '<a href="' . $PVSERVER . 'l' . $imageid[$i] . '"><img border="0" src="' . $url[$i] . '" width="100" height="90"></a>' ;
		  echo $out . '&nbsp;';
	  }
	}

// --------------------------------------------------------------------
// update the wideget settings
// --------------------------------------------------------------------

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['imgcount'] = strip_tags( $new_instance['imgcount'] );

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => __('Pixavid - easy picture sharing', 'pixrand'), 'imgcount' => __('4', 'pixrand'));

		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>

		<!-- Image count: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'imgcount' ); ?>"><?php _e('Image Count:', 'pixrand'); ?></label>
			<input id="<?php echo $this->get_field_id( 'imgcount' ); ?>" name="<?php echo $this->get_field_name( 'imgcount' ); ?>" value="<?php echo $instance['imgcount']; ?>" style="width:100%;" />
		</p>

	<?php
	}
}

?>