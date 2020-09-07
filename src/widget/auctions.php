<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Widget_Auctions extends WP_Widget
{

    function __construct()
    {
        parent::__construct(false, __('My auctions', GJMAA_TEXT_DOMAIN));
    }

    public function register()
    {
        register_widget('GJMAA_Widget_Auctions');
    }

    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;

        $fields = $this->getFieldsForWidget('update');
        $columns = array_keys($fields);

        foreach ($columns as $columnName) {
            $instance[$columnName] = strip_tags($new_instance[$columnName]);
        }

        return $instance;
    }

    public function getFieldsForWidget($type = 'widget')
    {
        $form = GJMAA::getHelper('widget_auctions');

        $fields = $form->getFieldsData();

        if ($type != 'widget') {
            return $fields;
        }

        foreach ($fields as $column => $schema) {
            $fields[$column]['name'] = $this->get_field_name($schema['name']);
        }

        return $fields;
    }

    function form($instance)
    {
        $auctionWidgetForm = GJMAA::getForm('widget_auctions');
        $auctionWidgetForm->prepareForm();

        $fieldsData = $this->getFieldsForWidget();

        $auctionWidgetForm->setFields($fieldsData);

        if ($instance) {
            $auctionWidgetForm->setValues($instance);
        }

        $auctionWidgetForm->generate(false, 'widget', $this);
        echo $auctionWidgetForm->toHtml();
    }

    // widget display
    function widget($args, $instance)
    {
        $title = isset($instance['title']) ? $instance['title'] : '';
        $template = isset($instance['template']) && !empty($instance['template']) ? $instance['template'] : 'default.phtml';
        $countOfAuctions = isset($instance['count_of_auctions']) ? $instance['count_of_auctions'] : 5;
        $showPrice = isset($instance['show_price']) ? $instance['show_price'] : false;
        $showTime = isset($instance['show_time']) ? $instance['show_time'] : false;
        $profileId = isset($instance['profile_id']) ? $instance['profile_id'] : null;
        $imageWidth = isset($instance['image_width']) ? $instance['image_width'] : 200;
        $imageHeight = isset($instance['image_height']) ? $instance['image_height'] : 150;

        
        $profileModel = GJMAA::getModel('profiles');
        $profileModel->load($profileId);
        
        $auctions = [];
        
        if($profileModel->getId()){
            add_action('wp_footer',[$this,'addStyleAndScripts']);
    
            $auctionsModel = GJMAA::getModel('auctions');
            
            $sort = 'auction_sort_order ASC';
    
            $filters = [
                'WHERE' => sprintf('auction_profile_id = %d AND auction_status = \'%s\' AND (auction_time > now() OR auction_time IS NULL)', $profileId, 'ACTIVE'),
                'ORDER BY' => $sort,
                'LIMIT' => $countOfAuctions,
                'OFFSET' => 0
            ];
    
            $auctions = $auctionsModel->getAllBySearch($filters);
        }
        
        foreach($auctions as $auction){
            $auctionsModel->collect($auction['auction_id'],$auction['auction_profile_id'],'visits');
        }

        GJMAA::getView('widgets_' . $template, 'front', [
            'title' => $title,
            'profile_id' => $profileId,
            'show_price' => $showPrice,
            'show_time' => $showTime,
            'image_width' => $imageWidth,
            'image_height' => $imageHeight,
            'auctions' => $auctions,
            'args' => $args
        ]);
    }
    
    public function addStyleAndScripts()
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