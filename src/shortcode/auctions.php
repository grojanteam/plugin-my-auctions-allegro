<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Shortcode_Auctions
{

    public function execute($attributes = [])
    {
        $profileId = $attributes['id'];
        $countOfAuctions = isset($attributes['count']) ? $attributes['count'] : 25;
        $title = isset($attributes['title']) ? $attributes['title'] : '';
        $showPrice = isset($attributes['show_price']) ? (bool) $attributes['show_price'] : false;
        $showTime = isset($attributes['show_time']) ? (bool) $attributes['show_time'] : false;
        $imageWidth = isset($attributes['image_width']) ? $attributes['image_width'] : 200;
        $imageHeight = isset($attributes['image_height']) ? $attributes['image_height'] : 150;
        $template = isset($attributes['template']) ? $attributes['template'] : 'default';
        
        add_action('wp_footer', [$this,'addStylesAndScripts']);
        
        $profileModel = GJMAA::getModel('profiles');
        $profileModel->load($profileId);

        $auctions = [];

        if ($profileModel->getId()) {

            $auctionsModel = GJMAA::getModel('auctions');

            $sortOrder = 'auction_sort_order ASC';

            $filters = [
                'WHERE' => sprintf('auction_profile_id = %d AND auction_status = \'%s\' AND (auction_time > now() OR auction_time IS NULL)', $profileId, 'ACTIVE'),
                'ORDER BY' => $sortOrder,
                'LIMIT' => $countOfAuctions,
                'OFFSET' => 0
            ];

            $auctions = $auctionsModel->getAllBySearch($filters);

            foreach ($auctions as $auction) {
                $auctionsModel->collect($auction['auction_id'], $auction['auction_profile_id'], 'visits');
            }
        }

        $renderAttributes = [
            'title' => $title,
            'profile_id' => $profileId,
            'show_price' => $showPrice,
            'show_time' => $showTime,
            'image_width' => $imageWidth,
            'image_height' => $imageHeight,
            'auctions' => $auctions
        ];

        return $this->render($renderAttributes,$template);
    }

    public function render($attributes,$template = 'default')
    {
        ob_start();
        GJMAA::getView('shortcode_'.$template.'.phtml', 'front', $attributes);
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    public function addStylesAndScripts()
    {
        $style = 'allegro-shortcode-style';
        if ((! wp_style_is($style, 'queue')) && (! wp_style_is($style, 'done'))) {
            wp_enqueue_style($style, GJMAA_URL . 'assets/css/front/allegro-shortcode.min.css');
        }
        
        $lazyLoadScript = 'allegro-lazyload-script';
        if ((! wp_script_is($lazyLoadScript, 'queue')) && (! wp_script_is($lazyLoadScript, 'done'))) {
            wp_enqueue_script($lazyLoadScript, GJMAA_URL . 'assets/js/front/lozad.min.js');
        }
        
        $jsClickAuction = 'allegro-collect-click';
        if ((! wp_script_is($jsClickAuction, 'queue')) && (! wp_script_is($jsClickAuction, 'done'))) {
            wp_enqueue_script($jsClickAuction, GJMAA_URL . 'assets/js/front/collectClick.js', array(
                'jquery'
            ));
            
            wp_localize_script($jsClickAuction, 'gjmaa_ajax_url', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('collect_click')
            ));
        }
    }
}

?>