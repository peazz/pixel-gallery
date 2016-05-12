<?php
/*
	Plugin Name: Pixel Gallery
	Plugin URI:  http://www.pixelbin.com
	Description: Extended Gallery for PixelBin Themes
	Version:     1.0.0
	Author:      Andy Cresswell
	Domain Path: /languages
	Text Domain: pixel-gallery
*/

class Pixel_Galleries {

	/**
	 * Init our class
	 *
	 * @return null
	 */
	public static function init() {

		self::add_filters();
		self::add_actions();

		/**
		 * Add Custom Image Sizes
		 * */
		if ( function_exists( 'add_theme_support' ) ) {
			// thumbnail and big sizes
			add_image_size( 'gallery-large', 804, '', true );
			add_image_size( 'gallery-thumbnail', 201, 113, array( 'center', 'center' ) );
		}

	}

	static function add_filters() {
		add_filter( 'post_gallery', array( __CLASS__, 'improved_post_gallery' ), 10, 2 );
	}

	/**
	 * Init WordPRess Actions
	 */
	static function add_actions() {
		add_action( 'print_media_templates', array( __CLASS__, 'output_custom_gallery_options' ), 1 );
	}

	/**
	 * Add Custom Options to Gallery creator
	 */
	static function output_custom_gallery_options() { ?>

			<script type="text/html" id="tmpl-custom-gallery-setting">

			<label class="setting">
		        <span><?php _e( 'Gallery Title' ); ?></span>
		        <input type="text" value="" data-setting="bj_title" style="float:left;">
		    </label>

		    <label class="setting">
		      <span><?php _e( 'Gallery Style' ); ?></span>
		      <select data-setting="bj_style">
		        <option value="slider" selected><?php _e( 'Slideshow Style' ); ?></option>
		        <option value="masonry"> <?php _e( 'Masonry Block Style' ); ?> </option>
		      </select>
		    </label>

		</script>

		<script>

		    jQuery(document).ready(function()
		    {
		        _.extend(wp.media.gallery.defaults, {
			        bj_title: '',
			        bj_style: ''
			    });

		        wp.media.view.Settings.Gallery = wp.media.view.Settings.Gallery.extend({
		        template: function(view){
		          return wp.media.template('gallery-settings')(view)
		               + wp.media.template('custom-gallery-setting')(view);
		        }
		        });

		    });

		</script>

		<?php
	}

	/**
	 * Gallery Output Over-ride
	 *
	 * @param [type]  $output [description]
	 * @param [type]  $attr   [description]
	 * @return [type]         [description]
	 */
	static function improved_post_gallery( $output, $attr ) {

		global $post, $wp_locale;

		static $instance = 0;
		$instance++;

		add_action( 'wp_footer', array( __CLASS__, 'load_scripts' ), 1 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_styles' ), 1 );

		// We're trusting author input, so let's at least make sure it looks like a valid orderby statement
		if ( isset( $attr['orderby'] ) ) {
			$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
			if ( !$attr['orderby'] )
				unset( $attr['orderby'] );
		}

		extract( shortcode_atts( array(
					'order'      => 'ASC',
					'orderby'    => 'menu_order ID',
					'id'         => $post->ID,
					'itemtag'    => 'figure',
					'icontag'    => 'div',
					'captiontag' => 'figure',
					'columns'    => 3,
					'size'       => '',
					'include'    => '',
					'exclude'    => ''
				), $attr ) );

		$id = intval( $id );
		if ( 'RAND' == $order )
			$orderby = 'none';

		if ( !empty( $include ) ) {
			$include = preg_replace( '/[^0-9,]+/', '', $include );
			$_attachments = get_posts( array( 'include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );

			$attachments = array();
			foreach ( $_attachments as $key => $val ) {
				$attachments[$val->ID] = $_attachments[$key];
			}
		} elseif ( !empty( $exclude ) ) {
			$exclude = preg_replace( '/[^0-9,]+/', '', $exclude );
			$attachments = get_children( array( 'post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );
		} else {
			$attachments = get_children( array( 'post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );
		}

		if ( empty( $attachments ) )
			return '';

		if ( is_feed() ) {
			$output = "\n";
			foreach ( $attachments as $att_id => $attachment )
				$output .= wp_get_attachment_link( $att_id, $size, true ) . "\n";
			return $output;
		}

		$itemtag = tag_escape( $itemtag );
		$captiontag = tag_escape( $captiontag );
		$columns = intval( $columns );
		$itemwidth = $columns > 0 ? floor( 100/$columns ) : 100;
		$float = is_rtl() ? 'right' : 'left';

		$selector = "gallery-{$instance}";


		/** 
		 *  If User Wants Masonry
		 */
		if( isset($attr['bj_style']) && $attr['bj_style'] === 'masonry'){
			// Start Output
			$output = apply_filters( 'gallery_style', "
			        <div id='$selector' class='bj-masonry-gallery galleryid-{$id}' itemscope itemtype='http://schema.org/ImageGallery'>" );
		} else {

			// Start Output
			$output = apply_filters( 'gallery_style', "
			        <div id='$selector' class='gallery bj-gallery galleryid-{$id}' itemscope itemtype='http://schema.org/ImageGallery'>" );

		}

		


		/** 
		 *  If Gallery Title Added
		 */
		if ( isset( $attr['bj_title'] ) ) {

			$output .= '<div><strong class="title">' . $attr['bj_title'] . '</strong></div>';
		}

		
		/**
		 *  Masonry Output
		 */
		if( isset($attr['bj_style']) && $attr['bj_style'] === 'masonry'){ 



				$output .= '<div class="masonry-gallery row">';
				$output .= '<div class="sizer col-xs-12 col-sm-6 col-md-4"></div>';

				foreach ( $attachments as $id => $attachment ) {

					$link = wp_get_attachment_image_src( $id, 'gallery-large',  false );

					$output .= '<figure class="masonry-item col-xs-12 col-sm-6 col-md-4" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">';
				
					$output .= '<img class="lazyload" itemprop="thumbnail"  src="' . $link[0] . '" width="'. $link[1] .'" alt="' . $attachment->post_title . '">
				                <a itemprop="contentUrl" href="' . $attachment->guid . '" data-rel="lightcase">
				                	<i class="fa fa-link"></i>
				                </a>
				                    <figcaption itemprop="caption description" class="slide-caption">
				                        ' . $attachment->post_excerpt . '
				                    </figcaption>
				            </figure>';

				} // foreach
				$output .= '</div>';

			$output .= '</div>'; // container end
			return $output;

		/**
		 *  Slider Output
		 */
		} else {

			$output .= '<div class="slide-inner">';
			$i = 0;
			foreach ( $attachments as $id => $attachment ) {
				$i++;

				$link = wp_get_attachment_image_src( $id, 'gallery-large',  false );


				if ( $i === 1 ) :
					$output .= '<figure class="slide active slide_'. $i .'" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">';
				else :
					$output .= '<figure class="slide slide_'. $i .'" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">';
				endif;
				$output .= '<a itemprop="contentUrl" href="' . $attachment->guid . '" data-rel="lightcase">
									                        <img class="lazyload" itemprop="thumbnail"  src="' . $link[0] . '" width="'. $link[1] .'" alt="' . $attachment->post_title . '">
									                    </a>
									                    <figcaption itemprop="caption description" class="slide-caption">
									                        ' . $attachment->post_excerpt . '
									                    </figcaption>
									            </figure>';


			} // foreach
			$output .= '</div>';


			$output .= '<ul class="gallery-selector">';

			$i = 0;
			foreach ( $attachments as $id => $attachment ) {
				$i++;

				$link = wp_get_attachment_image_src( $id, 'gallery-thumbnail',  false );

				if ( $i === 1 ) :
					// use jquery / javascript -> this.parent.parent.find->slide_$i
					$output .= '<li class="active" data-slide="slide_'. $i .'"><img src="' . $link[0] . '" width="'. $link[1] .'" alt="' . $attachment->post_title . '"></li>';
				else :
					// use jquery / javascript -> this.parent.parent.find->slide_$i
					$output .= '<li data-slide="slide_'. $i .'"><img src="' . $link[0] . '" width="'. $link[1] .'" alt="' . $attachment->post_title . '"></li>';
				endif;

			} // foreach

			$output .= '</ul>';

			$output .= '</div>';

			return $output;

		}

		
	}

	/**
	 * Load Gallery Scripts
	 *
	 * @return [type] [description]
	 */
	static function load_scripts() {

		if(is_single() || is_page()){
			
			wp_register_script( 'lightbox', plugin_dir_url( '', __FILE__ ) . 'bj-plugins/pixel-gallery/assets/js/lightcase.js', array(), '1.0.0', true );
			wp_enqueue_script( 'lightbox' ); // Enqueue it!

			wp_register_script( 'post-gallery', plugin_dir_url( '', __FILE__ ) . 'bj-plugins/pixel-gallery/assets/js/post-gallery.js', array(), '1.0.0', true );
			wp_enqueue_script( 'post-gallery' ); // Enqueue it!

		}
	}

	/**
	 * Load Galery Styles
	 *
	 * @return [type] [description]
	 */
	static function load_styles() {

		if(is_single() || is_page()){

			wp_register_style( 'bj-lightbox', plugin_dir_url( '', __FILE__ ) . 'bj-plugins/pixel-gallery/assets/css/lightcase.css', array(), '1.0', 'all' );
			wp_register_style( 'bj-masonry-gallery', plugin_dir_url( '', __FILE__ ) . 'bj-plugins/pixel-gallery/assets/css/masonry-gallery.css', array(), '1.0', 'all' );

			wp_enqueue_style( 'bj-lightbox' ); // Enqueue it!
			wp_enqueue_style( 'bj-masonry-gallery' ); // Enqueue it!

		}

	}
}

Pixel_Galleries::init();
