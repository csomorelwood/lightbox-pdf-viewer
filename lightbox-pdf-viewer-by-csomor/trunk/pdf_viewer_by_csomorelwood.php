<?php
/**
* Plugin Name: Lightbox PDF viewer by Csömör
* Plugin URI: https://vassgergo.me
* Description: The plugin allows you to add pdf lightboxes to your site via shortcode.
* Version: 1.0
* Author: Csömör
* Author URI: https://profiles.wordpress.org/csomorelwood/
**/

function register_the_magical_styles_for_lightbox_pdf_viewer_by_csomorelwood() {
  wp_register_style('pdfviewrstyles', plugins_url('assets/css/style.css',__FILE__ ));
  wp_enqueue_style('pdfviewrstyles');
}

add_action( 'init','register_the_magical_styles_for_lightbox_pdf_viewer_by_csomorelwood');

function register_the_magical_scripts_for_lightbox_pdf_viewer_by_csomorelwood() {
  wp_register_script('PDFJS', plugin_dir_url( __FILE__ ) . '/assets/js/pdf.min.js' );
  wp_enqueue_script('PDFJS');

  wp_register_script('lightbox_pdf_viewer_script', plugin_dir_url( __FILE__ ) . '/assets/js/lightbox_pdf_viewer.js' );
  wp_enqueue_script( 'lightbox_pdf_viewer_script');
  $plugin_url = array( 
    'expand_url' => plugin_dir_url( __FILE__ ) . '/assets/images/expand.png',
    'zoomin_url' => plugin_dir_url( __FILE__ ) . '/assets/images/zoom-in.png',
    'zoomout_url' => plugin_dir_url( __FILE__ ) . '/assets/images/zoom-out.png'
  );
  wp_localize_script( 'lightbox_pdf_viewer_script', 'plugin_url', $plugin_url );
 }
add_action('init', 'register_the_magical_scripts_for_lightbox_pdf_viewer_by_csomorelwood');

function lightbox_pdf_viewer_register_settings() {
  register_setting( 'lightbox_pdf_viewer_options_group', 'controls');
}
add_action( 'admin_init', 'lightbox_pdf_viewer_register_settings' );

function lightbox_pdf_viewer_register_options_page() {
  add_options_page('Lightbox PDF viewer', 'Lightbox PDF viewer by Csömör', 'manage_options', 'lightbox_pdf_viewer', 'lightbox_pdf_viewer_options_page');
}
add_action('admin_menu', 'lightbox_pdf_viewer_register_options_page');

// lightbox_pdf
define ('LIGHTBOX_PDF_TYPE_GENERATED_BY_CSOMOR', 'lightbox_pdf_post_type_generated_by_the_god_himself');
add_action( 'init', 'add_lightbox_pdf_post_type_for_lightbox_pdf_viewer_plugin_by_csomorelwood', 0 );
function add_lightbox_pdf_post_type_for_lightbox_pdf_viewer_plugin_by_csomorelwood(){
	$args = array(
		'public' => true,
		'label'  => __('LightBox PDF','lbpdf'),
		'supports' => array( 'title', 'thumbnail', 'editor', 'post-formats', 'excerpt' ),
		'has_archive' => true,
		'menu_icon' => 'dashicons-media-document',
		'with_front' => true,
		'show_in_rest' => true,
		'taxonomies' => array( 'category', 'post_tag' ),
		'hierarchical' => false
	);
	register_post_type( LIGHTBOX_PDF_TYPE_GENERATED_BY_CSOMOR, $args );
}

/*
**
**  PDF attachment to custom post type
**
*/
add_action("admin_init", "init_lightbox_pdf_file_selector_by_csomorelwood");
add_action('save_post', 'save_lightbox_pdf_file_by_csomorelwood');
function init_lightbox_pdf_file_selector_by_csomorelwood(){
  add_meta_box("selected_lightbox_pdf", "PDF Document", "lightbox_pdf_file_selector_by_csomorelwood", LIGHTBOX_PDF_TYPE_GENERATED_BY_CSOMOR, "normal", "low");
}
function lightbox_pdf_file_selector_by_csomorelwood(){
  global $post;
  $custom  = get_post_custom($post->ID);
  $selected    = $custom["_selected_pdf"][0];
  $count   = 0;
  echo '<div class="selected_header">';
  $query_pdf_args = array(
    'post_type' => 'attachment',
    'post_mime_type' =>'application/pdf',
    'post_status' => 'inherit',
    'posts_per_page' => -1,
  );
  $query_pdf = new WP_Query( $query_pdf_args );
  $pdf = array();
  echo '<select name="selected">';
  echo '<option class="pdf_select">Select a PDF file</option>';
  foreach ( $query_pdf->posts as $file) {
    if($selected == $pdf[]= $file->guid){
      echo '<option value="'.$pdf[]= esc_attr($file->guid).'" selected="true">'.$pdf[]= esc_html($file->guid).'</option>';
    } else{
      echo '<option value="'.$pdf[]= esc_attr($file->guid).'">'.$pdf[]= esc_html($file->guid).'</option>';
    }
    $count++;
  }
  echo '</select><br /></div>';
  echo '<p>Selecting a pdf file from the above list to attach to this post.</p>';
  echo '<div class="pdf_count"><span>Files:</span> <b>'.esc_html($count).'</b></div>';
}
function save_lightbox_pdf_file_by_csomorelwood(){
  global $post;
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){ return $post->ID; }
  update_post_meta($post->ID, "_selected_pdf", esc_url($_POST["selected"]));
}
function lightbox_pdf_viewer_draw_pdf($id, $option, $audio, $class){
  if($option == "thumbnail"){
    echo '<div class="lightbox_pdf-card">';
      echo '<a href="javascript: openLightBoxPDFView(\'' . esc_attr(get_post_meta( $id, "_selected_pdf", TRUE )) . '\', \'' . ($audio ? esc_attr(get_post_meta( $id, "_selected_mp3", TRUE )) : "") . '\');" class="open-lbpdf ' . ($class ? esc_attr($class) : '') . '">';
        echo '<div class="rounded-box">';
          echo '<img src="' . get_the_post_thumbnail_url($id) . '" alt="pdf-thumbnail-' . esc_attr($id) . '">';
          echo '<div class="pdf-shadow"><h5>' . get_the_title($id) . '</h5><p>' . __('Open', 'lbpdf') . '</p></div>';
        echo '</div>';
      echo '</a>';
    echo '</div>';
  } else{
    echo '<a href="javascript: openLightBoxPDFView(\'' . esc_attr(get_post_meta( $id, "_selected_pdf", TRUE )) . '\', \'' . ($audio ? esc_attr(get_post_meta( $id, "_selected_mp3", TRUE )) : "") . '\');" class="open-lbpdf ' . ($class ? esc_attr($class) : '') . '">';
      echo __('View', 'lbpdf');
    echo '</a>';
  }
}

/*
**
**  MP3 attachment to custom post type
**
*/

add_action("admin_init", "init_lightbox_mp3_file_selector_by_csomorelwood");
add_action('save_post', 'save_lightbox_mp3_file_by_csomorelwood');
function init_lightbox_mp3_file_selector_by_csomorelwood(){
  add_meta_box("selected_lightbox_mp3", "MP3 File", "lightbox_mp3_file_selector_by_csomorelwood", LIGHTBOX_PDF_TYPE_GENERATED_BY_CSOMOR, "normal", "low");
}
function lightbox_mp3_file_selector_by_csomorelwood(){
  global $post;
  $custom = get_post_custom($post->ID);
  $selected = $custom["_selected_mp3"][0];
  $count = 0;
  echo '<div class="selected_header">';
  $query_mp3_args = array(
    'post_type' => 'attachment',
    'post_mime_type' =>'audio/mpeg',
    'post_status' => 'inherit',
    'posts_per_page' => -1,
  );
  $query_mp3 = new WP_Query( $query_mp3_args );
  $mp3 = array();
  echo '<select name="selected-audio">';
  echo '<option class="mp3_select">Select an MP3 file</option>';
  foreach ( $query_mp3->posts as $file) {
    if($selected == $mp3[]= $file->guid){
      echo '<option value="'. $mp3[]= esc_attr($file->guid) . '" selected="true">' . $mp3[]= esc_html($file->guid) . '</option>';
    } else{
      echo '<option value="' . $mp3[]= esc_attr($file->guid) . '">' . $mp3[]= esc_html($file->guid) . '</option>';
    }
    $count++;
  }
  echo '</select><br /></div>';
  echo '<p>Selecting a mp3 file from the above list to attach to this post.</p>';
  echo '<div class="mp3_count"><span>Files:</span> <b>' . esc_html($count) . '</b></div>';
}
function save_lightbox_mp3_file_by_csomorelwood(){
  global $post;
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){ return $post->ID; }
  update_post_meta($post->ID, "_selected_mp3", esc_url($_POST["selected-audio"]));
}

/*
**
**  Set up Options page
**
*/
function lightbox_pdf_viewer_options_page(){ ?>
  <div>
    <h1>Lightbox PDF viewer by Csömör</h1>
    <h2>Thanks for downloading my plugin! :)</h2>
    <h3>If you like it, you can donate me on paypal -----> <a href="https://paypal.me/csomorelwood">Donate!</a></h3>
    

    <h3>Usage:</h3>
    <p>
      There is a new post type that appeared on the menubar, the LightBox PDF.
      Add a new, then you can set title, content, thumbnail, a pdf file, and an mp3 file for every created LightBox PDF.
      Then you can add the pdf-s via shortcode, wherever you need :)
    </p>
    <p>
      The shortcode looks like this: <strong>[lbpdfviewr id={ID of the LightBox PDF you want} type={button / thumbnail} audio={true / false} class="{custom css class}"]</strong>
    </p>
  </div>
<?php } 


// function that runs when shortcode is called
function lightbox_pdf_viewer_shortcode_callback($atts = []) {
  lightbox_pdf_viewer_draw_pdf($atts['id'], $atts['type'], $atts['audio'], $atts['class']);
} 
// register shortcode
add_shortcode('lbpdfviewr', 'lightbox_pdf_viewer_shortcode_callback'); 
?>
