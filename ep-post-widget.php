<?php
/*
Plugin Name: EP Post Widget
Plugin URI: http://www.earthpeople.se
Description: Display posts from one or more categories, with our without date and content text.
Author: Mattias Hedman, Earth People AB
Version: 0.2.1
Author URI: http://www.earthpeople.se
*/

add_action('widgets_init','load_epPostWidget');
add_action('wp_head','epPostWidgetCss');

function load_epPostWidget() {
	register_widget('epPostWidget');
}

function epPostWidgetCss() {
	echo '<link href="'.plugins_url('style.css', __FILE__).'" rel="stylesheet" />';
}

function epPostWidget_excerpt($excerpt_length = 10,$excerptEnd) {
	global $post;
	if ( '' == $text ) {
		$text = get_the_content('');
		$text = apply_filters('the_content', $text);
		$text = str_replace(']]>', ']]&gt;', $text);
		$text = preg_replace('@<script[^>]*?>.*?</script>@si', '', $text);
		$text = strip_tags($text, '<p>');
		$words = explode(' ', $text, $excerpt_length + 1);
		if (count($words)> $excerpt_length) {
			array_pop($words);
			# Style the end of the excerpt here.
			array_push($words, $excerptEnd.'</p>');
			$text = implode(' ', $words);
		}
	}
	echo $text;
}

class epPostWidget extends WP_Widget {
	function epPostWidget() {
		//Settings
		$widget_ops = array('classname'=>'eppostwidget','description'=>__('Display posts from one or more categorys','eppostwidget'));
		
		//Controll settings
		$control_ops = array('id_base' => 'postwidget');
		
		//Create widget
		$this->WP_Widget('postwidget',__('EP Post Widget'),$widget_ops,$control_ops);
		
	}
	
	function widget($args,$instance) {
		extract($args);
		
		//User selected settings
		$title 			= $instance['title'];
		$cat 			= $instance['cat'];
		$showNum 		= $instance['showNum'];
		$date_format 	= $instance['date_format'];
		$excerptLength 	= $instance['excerptlength'];
		$excerptEnd 	= $instance['excerptend'];
		$blogurl 		= $instance['blogurl'];
		$theme 			= $instance['theme'];
		
		echo $before_widget;
		
		if($title) echo $before_title . __($title) . $after_title;
		
		query_posts('cat='.$cat.'&posts_per_page='.$showNum);
		?>
		
		<ul class="eppostwidget-list <?php echo $theme; ?>">
			<?php while(have_posts()) : the_post(); ?>
				<li class="eppostwidget-item" id="eppostwidget-<?php the_ID(); ?>">
					<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">
						<?php if($date_format) : ?>
							<div class="eppostwidget-date">
								<?php the_time($date_format); ?>
							</div>
						<?php endif; ?>
						<strong><?php the_title(); ?></strong>
						<?php if($excerptLength > 0) epPostWidget_excerpt($excerptLength,$excerptEnd); ?>
					</a>
				</li>
			<?php endwhile; ?>
		</ul>
		
		<a href="<?php echo ($blogurl == '') ? get_bloginfo('siteurl') : $blogurl; ?>" class="link-btn <?php echo $theme; ?>"><?php echo __('Read more'); ?></a>
		
		<?php
		echo $after_widget;

		wp_reset_query();
	}
	
	function update($new_instance,$old_instance) {
		$instance = $old_instance;
		
		$instance['title'] 			= strip_tags($new_instance['title']);
		$instance['cat'] 			= $new_instance['cat'];
		$instance['showNum'] 		= $new_instance['showNum'];
		$instance['date_format'] 	= $new_instance['date_format'];
		$instance['excerptlength'] 	= $new_instance['excerptlength'];
		$instance['excerptend'] 	= $new_instance['excerptend'];
		$instance['blogurl'] 		= $new_instance['blogurl'];
		$instance['theme']			= $new_instance['theme'];
		
		return $instance;
	}

	function form($instance) {
		$default = array(
			'title'			=>'',
			'cat'			=>'1',
			'showNum'		=>'5',
			'post_option' 	=>'all',
			'date_format'	=>'Y-m-d H:i:s',
			'excerptlength'	=>'20',
			'excerptend'	=>'...',
			'blogurl' 		=> '',
			'theme' 		=> 'light'
		);
		$instance = wp_parse_args((array)$instance,$default);
	?>
		<!-- TITLE -->
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title:'); ?></label>
			<br />
			<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
		</p>
		
		<!-- CAT ID -->
		<p>
			<label for="<?php echo $this->get_field_id('cat'); ?>"><?php echo __('Category id:'); ?></label>
			<br />
			<input type="text" id="<?php echo $this->get_field_id('cat'); ?>" name="<?php echo $this->get_field_name('cat'); ?>" value="<?php echo $instance['cat']; ?>" class="widefat" />
			<br /><em>For multiple categories, separate with comma. An 0 for all categories.</em>
		</p>
		
		<!-- SHOW NUM -->
		<p>
			<label for="<?php echo $this->get_field_id('showNum'); ?>"><?php echo __('# posts to display:'); ?></label>
			<br />
			<input type="text" id="<?php echo $this->get_field_id('showNum'); ?>" name="<?php echo $this->get_field_name('showNum'); ?>" value="<?php echo $instance['showNum']; ?>" class="widefat" />
		</p>
		
		<!-- DATE FORMAT -->
		<p>
			<label for="<?php echo $this->get_field_id('date_format'); ?>"><?php echo __('Date format:'); ?></label>
			<br />
			<input type="text" id="<?php echo $this->get_field_id('date_format'); ?>" name="<?php echo $this->get_field_name('date_format'); ?>" value="<?php echo $instance['date_format']; ?>" class="widefat" />
			<br/><em>Use <a href="http://php.net/manual/en/function.date.php" target="_blank">PHP date</a> format variables, leave empty to hide post date</em>
		</p>
		
		<!-- EXCERPT LEGTH -->
		<p>
			<label for="<?php echo $this->get_field_id('excerptlength'); ?>"><?php echo __('Text excerpt length:'); ?></label>
			<br />
			<input type="text" id="<?php echo $this->get_field_id('excerptlength'); ?>" name="<?php echo $this->get_field_name('excerptlength'); ?>" value="<?php echo $instance['excerptlength']; ?>" class="widefat" />
			<br/><em>If 0, no text will be displayed, only title</em>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('excerptend'); ?>"><?php echo __('Text excerpt ending:'); ?></label>
			<br />
			<input type="text" id="<?php echo $this->get_field_id('excerptend'); ?>" name="<?php echo $this->get_field_name('excerptend'); ?>" value="<?php echo $instance['excerptend']; ?>" class="widefat" />
		</p>
		
		<!-- BLOG URL -->
		<p>
			<label for="<?php echo $this->get_field_id('blogurl'); ?>"><?php echo __('Blog url:'); ?></label>
			<br />
			<input type="text" id="<?php echo $this->get_field_id('blogurl'); ?>" name="<?php echo $this->get_field_name('blogurl'); ?>" value="<?php echo $instance['blogurl']; ?>" class="widefat" />
			<br/><em>If leaved empty, it assume your frontpage is the blog page.</em>
		</p>
		
		<!-- THEME -->
		<p>
			<label for="<?php echo $this->get_field_id('theme'); ?>"><?php echo __('Theme:'); ?></label>
			<br />
			<select name="<?php echo $this->get_field_name('theme'); ?>" class="widefat">
				<?php if($instance['theme'] == 'light') : ?>
					<option selected value="light">Light</option>
					<option value="dark">Dark</option>
				<?php elseif($instance['theme'] == 'dark') : ?>
					<option value="light">Light</option>
					<option selected value="dark">Dark</option>
				<?php endif; ?>
			</select>
		</p>
	<?php
	}
}
?>