<?php

class GjmaaServiceWoocommerce {

    protected $_isEnabled;

    public function isEnabled(){
        if(null === $this->_isEnabled) {
            if (function_exists('is_plugin_active')) {
                $this->_isEnabled = is_plugin_active('woocommerce/woocommerce.php');
            } else {
                include_once(ABSPATH . 'wp-admin/includes/plugin.php');
                $this->_isEnabled = is_plugin_active('woocommerce/woocommerce.php');
            }
        }
        return $this->_isEnabled;
    }

    public function saveProducts($auctionDetails){
        foreach($auctionDetails as $index => $auction){
            $this->addProduct($auction);
        }
    }

    public function addProduct($allegroProduct)
    {
        $categories = $allegroProduct->itemCats->item;
        $product = $allegroProduct->itemInfo;
        $post = $this->getProductIdOrCreate($allegroProduct);

        if($post['new']) {
            $post_id = $post['postId'];
            $media = $this->getProductImage($allegroProduct->itemImages->item);
            update_post_meta($post_id, '_visibility', 'visible');
            update_post_meta($post_id, '_stock_status', 'instock');
            update_post_meta($post_id, 'total_sales', '0');
            update_post_meta($post_id, '_downloadable', 'no');
            update_post_meta($post_id, '_virtual', 'no');
            update_post_meta($post_id, '_featured', 'no');
            update_post_meta($post_id, '_sku', (isset($allegroProduct->itEan) ? $allegroProduct->itEan : $product->itId));
            update_post_meta($post_id, '_price', ($product->itBuyNowActive ? $product->itBuyNowPrice : $product->itPrice));
            update_post_meta($post_id, '_manage_stock', 'yes');
            update_post_meta($post_id, '_backorders', 'no');
            update_post_meta($post_id, '_stock', $product->itQuantity);

            $this->assignCategories($categories,$post_id);
            $this->assignMediaProduct($product,$media,$post_id);
        }
    }

    public function getPriceData($auction){
        $cPrice = null;
        $prices = isset($auction->priceInfo) ? $auction->priceInfo->item : $auction->itemPrice;
        foreach($prices as $index => $price){
            if($price->priceType == 'buyNow' || $price->priceType == 1){
                $cPrice = (float)$price->priceValue;
                break;
            }
            else
                $cPrice = (float)$price->priceValue;
        }
        return $cPrice;
    }

    public function getProductImage($images){
        $images = is_array($images) ? $images : [$images];
        $media = [];
        foreach($images as $image){
            $media[$image->imageType] = $image->imageUrl;
        }

        return $media;
    }

    public function attach_image ($fileurl, $filealt, $type, $post_id)
    {
        $filename = str_replace(' ','_',$filealt); // Get the filename including extension from the $fileurl e.g. myimage.jp

        $destination = WP_CONTENT_DIR. '/uploads/woocommerce_uploads'; // Specify where we wish to upload the file, generally in the wp uploads directory
        if(!is_dir($destination)){
            mkdir($destination);
        }

        $destinationPath = $destination .'/'. $filename . '_'.$type.'.jpg';

        copy($fileurl,$destinationPath);

        $filetype = wp_check_filetype($destinationPath); // Get the mime type of the file

        $attachment = array( // Set up our images post data
            'guid'           => get_option('siteurl') . '/wp-content/uploads/woocommerce_uploads/'.$filename . '_'.$type.'jpg',
            'post_mime_type' => $filetype['type'],
            'post_title'     => $filename . '_'.$type.'.jpg',
            'post_author'    => 1,
            'post_content'   => ''
        );

        $attach_id = wp_insert_attachment( $attachment, $destinationPath, $post_id ); // Attach/upload image to the specified post id, think of this as adding a new post.

				if(!function_exists('wp_generate_attachment_metadata'))
				{
						include_once(ABSPATH . 'wp-admin/includes/image.php');
				}
        $attach_data = wp_generate_attachment_metadata( $attach_id, $destinationPath ); // Generate the necessary attachment data, filesize, height, width etc.

        wp_update_attachment_metadata( $attach_id, $attach_data ); // Add the above meta data data to our new image post

        add_post_meta($attach_id, '_wp_attachment_image_alt', $filealt); // Add the alt text to our new image post

        return $attach_id; // Return the images id to use in the below functions
    }


    public function getProductIdOrCreate($product){
        $postId = wc_get_product_id_by_sku(isset($product->itEan) ? $product->itEan : $product->itemInfo->itId);
        $new = false;
        if(0 === $postId){
            $postId = wp_insert_post( array(
                'post_title' => $product->itemInfo->itName,
                'post_content' => strip_tags($product->itemInfo->itDescripton),
                'post_status' => 'publish',
                'post_type' => "product",
            ) );
            $new = true;
        }

        return
            [
                'postId' => $postId,
                'new' => $new
            ];
    }

    public function assignCategories($categories,$product_id)
    {
        $categoriesId = [];
        foreach($categories as $category)
        {
            $term = wp_insert_term(
                $category->catName,
                'product_cat',
                [
                    'description'=> $category->catName . ' ('.$category->catId.')',
                    'slug' => strtolower(str_replace(' ','-',$category->catName)),
                    'parent' => $category->catLevel > 0 ? $categoriesId[$category->catLevel-1] : 0
                ]
            );

            if ( is_wp_error( $term ) ) {
                $term_id = $term->error_data['term_exists'] ? : null;
            } else {
                $term_id = $term['term_id'];
            }

            if(null!==$term_id) {
                $categoriesId[$category->catLevel] = $term_id;
            }
        }

        wp_set_object_terms( $product_id, $categoriesId, 'product_cat' );
    }

    public function assignMediaProduct($product,$media,$product_id){
        $attachment_ids = [];

        foreach($media as $type => $image){
            if($type === 3) {
                $attachment_ids[$type] = $this->attach_image($image, $product->itId, $type, $product_id);
            }
        }

        update_post_meta($product_id, '_thumbnail_id', $attachment_ids[3]);
    }
}