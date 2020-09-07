<?php 
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Helper_Widget_Auctions {
    public function getFieldsData(){
        return [
            'title' => [
                'id' => 'title',
                'type' => 'text',
                'name' => 'title',
                'label' => 'Title',
                'class' => 'widefat'
            ],
            'profile_id' => [
                'type' => 'select',
                'id' => 'profile_id',
                'name' => 'profile_id',
                'label' => 'Profile',
                'class' => 'widefat',
                'source' => 'profiles'
            ],
            'count_of_auctions' => [
                'type' => 'number',
                'id' => 'count_of_auctions',
                'name' => 'count_of_auctions',
                'label' => 'Count of auctions',
                'class' => 'widefat',
                'value' => 5
            ],
            'show_price' => [
                'type' => 'select',
                'id' => 'show_price',
                'name' => 'show_price',
                'label' => 'Show price',
                'class' => 'widefat',
                'source' => 'yesno'
            ],
            'show_time' => [
                'type' => 'select',
                'id' => 'show_time',
                'name' => 'show_time',
                'label' => 'Show time',
                'class' => 'widefat',
                'source' => 'yesno'
            ],
            'image_width' => [
                'type' => 'number',
                'id' => 'image_width',
                'name' => 'image_width',
                'label' => 'Image width',
                'class' => 'widefat',
            ],
            'image_height' => [
                'type' => 'number',
                'id' => 'image_height',
                'name' => 'image_height',
                'label' => 'Image height',
                'class' => 'widefat',
            ],
            'template' => [
                'type' => 'select',
                'id'  => 'template',
                'name' => 'template',
                'class' => 'widefat',
                'label' => 'Template',
                'source' => 'templates',
                'value' => 'default'
            ]
        ];
    }
}

?>