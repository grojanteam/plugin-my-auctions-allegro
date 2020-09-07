<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Hook_Media
{

    public function __construct()
    {        
//         add_action( 'save_post', [$this,'thumbnail_url_field_save'], 10, 2 );
//         add_filter( 'post_thumbnail_html', [$this,'thumbnail_external_replace'], 10, PHP_INT_MAX );
    }
    
    public function thumbnail_external_replace($html, $post_id)
    {
//         $url = get_post_meta($post_id, '_thumbnail_ext_url', TRUE);
//         if (empty($url) || ! url_is_image($url)) {
//             return $html;
//         }
//         $alt = get_post_field('post_title', $post_id) . ' ' . __('thumbnail', 'txtdomain');
//         $attr = array(
//             'alt' => $alt
//         );
//         $attr = apply_filters('wp_get_attachment_image_attributes', $attr, NULL);
//         $attr = array_map('esc_attr', $attr);
//         $html = sprintf('<img src="%s"', esc_url($url));
//         foreach ($attr as $name => $value) {
//             $html .= " $name=" . '"' . $value . '"';
//         }
//         $html .= ' />';
//         return $html;
    }

    public function thumbnail_url_field_save($pid, $post)
    {
//         $cap = $post->post_type === 'product' ? 'edit_page' : 'edit_post';
//         if (! current_user_can($cap, $pid) || ! post_type_supports($post->post_type, 'thumbnail') || defined('DOING_AUTOSAVE')) {
//             return;
//         }
//         $action = 'thumbnail_ext_url_' . $pid . get_current_blog_id();
//         $nonce = filter_input(INPUT_POST, 'thumbnail_ext_url_nonce', FILTER_SANITIZE_STRING);
//         $url = filter_input(INPUT_POST, 'thumbnail_ext_url', FILTER_VALIDATE_URL);
//         if (empty($nonce) || ! wp_verify_nonce($nonce, $action) || (! empty($url) && ! url_is_image($url))) {
//             return;
//         }
//         if (! empty($url)) {
//             update_post_meta($pid, '_thumbnail_ext_url', esc_url($url));
//             if (! get_post_meta($pid, '_thumbnail_id', TRUE)) {
//                 update_post_meta($pid, '_thumbnail_id', 'by_url');
//             }
//         } elseif (get_post_meta($pid, '_thumbnail_ext_url', TRUE)) {
//             delete_post_meta($pid, '_thumbnail_ext_url');
//             if (get_post_meta($pid, '_thumbnail_id', TRUE) === 'by_url') {
//                 delete_post_meta($pid, '_thumbnail_id');
//             }
//         }
    }
}

?>