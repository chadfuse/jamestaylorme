<?php
/*
Plugin Name: Simple Biz Twitter Widget
Plugin URI: http://simplebiz.graphicreaction.info/simple-biz-twitter-widget
Description: Creates a Simple Twitter widget for your sidebars. No passwords and is customisable! This widget can be used with any theme.
Version: 1.0
Author: Graphic Reaction
Author URI: http://graphicreaction.com
License: GPLv2
*/
?>
<?php
class SimpleBiz_Twitter_Widget extends WP_Widget {
	function __construct() {
		parent::__construct(false, $name = 'SimpleBiz Twitter Widget', array( 'description' => 'Simple Biz Twitter profile badge for your WordPress site that displays your Tweets.' ) );
	}

	function form( $instance ) {
	$screen_name = esc_attr( $instance['screen_name'] );
	$num_tweets = esc_attr( $instance['num_tweets'] );
	$shell_background_color = esc_attr( $instance['shell_background_color'] );
	$shell_text_color = esc_attr( $instance['shell_text_color'] );
	$tweet_background_color = esc_attr( $instance['tweet_background_color'] );
	$tweet_text_color = esc_attr( $instance['tweet_text_color'] );
	$links_color = esc_attr( $instance['links_color'] );
	?>
	<p>
		<label for="<?php echo $this->get_field_id( 'screen_name' ); ?>">Screen name:</label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'screen_name' ); ?>" name="<?php echo $this->get_field_name( 'screen_name' ); ?>" type="text" value="<?php echo $screen_name; ?>" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id( 'num_tweets' ); ?>">Number of Tweets:</label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'num_tweets' ); ?>" name="<?php echo $this->get_field_name( 'num_tweets' ); ?>" type="text" value="<?php echo $num_tweets; ?>" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id( 'shell_background_color' ); ?>">Shell Background Color:</label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'shell_background_color' ); ?>" name="<?php echo $this->get_field_name( 'shell_background_color' ); ?>" type="text" value="<?php echo $shell_background_color; ?>" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id( 'shell_text_color' ); ?>">Shell Text Color:</label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'shell_text_color' ); ?>" name="<?php echo $this->get_field_name( 'shell_text_color' ); ?>" type="text" value="<?php echo $shell_text_color; ?>" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id( 'tweet_background_color' ); ?>">Tweet Background Color:</label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'tweet_background_color' ); ?>" name="<?php echo $this->get_field_name( 'tweet_background_color' ); ?>" type="text" value="<?php echo $tweet_background_color; ?>" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id( 'tweet_text_color' ); ?>">Tweet Text Color:</label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'tweet_text_color' ); ?>" name="<?php echo $this->get_field_name( 'tweet_text_color' ); ?>" type="text" value="<?php echo $tweet_text_color; ?>" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id( 'links_color' ); ?>">Links Color:</label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'links_color' ); ?>" name="<?php echo $this->get_field_name( 'links_color' ); ?>" type="text" value="<?php echo $links_color; ?>" />
	</p>

	<?php
}
function widget( $args, $instance ) {
	echo $args['before_widget'];
	?>
	<script src="http://widgets.twimg.com/j/2/widget.js"></script>
	<script>
	new TWTR.Widget({
	  version: 2,
	  type: 'profile',
	  rpp: <?php echo $instance['num_tweets']; ?>,
	  interval: 6000,
	  width: 'auto',
	  height: 300,
	  theme: {
	    shell: {
	      background: '<?php echo $instance['shell_background_color']; ?>',
	      color: '<?php echo $instance['shell_text_color']; ?>'
	    },
	    tweets: {
	      background: '<?php echo $instance['tweet_background_color']; ?>',
	      color: '<?php echo $instance['tweet_text_color']; ?>',
	      links: '<?php echo $instance['links_color']; ?>'
	    }
	  },
	  features: {
	    scrollbar: false,
	    loop: false,
	    live: false,
	    hashtags: true,
	    timestamp: true,
	    avatars: false,
	    behavior: 'all'
	  }
	}).render().setUser('<?php echo $instance['screen_name']; ?>').start();
	</script>
	<?php
	echo $args['after_widget'];
}
};


add_action( 'widgets_init', create_function( '', 'return register_widget( "SimpleBiz_Twitter_Widget" );' ) );
?>