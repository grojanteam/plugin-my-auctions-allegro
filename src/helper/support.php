<?php 
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Helper_Support {
    
    public function getPlaylist()
    {
        $videos = [
            'PLANzYTB7a8kgOKr5OXylnUvQDGPTulDkD' => [
                'name' => __('My auctions allegro',GJMAA_TEXT_DOMAIN) . ' / ' . __('WooCommerce Allegro PRO', GJMAA_TEXT_DOMAIN),
//                 'excerpt' => __('All videos about how to use plugins',GJMAA_TEXT_DOMAIN)
            ]
        ];
        
        return $videos;
    }
    
    public function generateHtmlPlaylist($playlistId, $width = 900, $height = 450) {
        return '<iframe width="'.$width.'" height="'.$height.'" src="https://www.youtube.com/embed/videoseries?list='.$playlistId.'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
    }
}
?>