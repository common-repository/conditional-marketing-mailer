<?php
if(!class_exists('WCMM_query_builder')){
    class WCMM_query_builder{
        private $id;
        public function __construct($id){
            $this->id = intval($id);
        }
        
        public function query(){
			$date_posted = get_the_date('Y-m-d',$this->id);
            $relations = get_post_meta($this->id,'relation',true);
            $post_status = get_post_meta($this->id,'statuses',true);
            $order_id_send = get_post_meta($this->id,'order_id_send');
            $args = array(
                'post_type' => 'shop_order',
                'post_status' =>$post_status,
				'date_query' => array(
					'after' => date('Y-m-d', strtotime('-1 day', strtotime($date_posted))) 
				),
                'meta_query' => array(
                    'relation' => 'AND',
                    $this->tags_select($relations),
                    $this->category_select($relations),
                    $this->products($relations),
                ),
            );
            if(!empty($order_id_send)){
                $args['post__not_in'] = $order_id_send;
            }
            
            $result = new WP_Query( $args );
            return $result;
        }
        public function category_select($relations){
            $cats = get_post_meta($this->id,'category_select',true);

            if(!empty($cats)){
                $cat_array = array();
                $cat_array['relation'] = $relations['Category'];
                foreach ($cats as $cat){
                    $cat_array[] = array(
                        'key'     => '_product_cat_id_',
                        'value'   => $cat,
                        'compare' => '=',
                    );
                }
                return $cat_array;
            }
            return;
        }
        public function tags_select($relations){
            $tags = get_post_meta($this->id,'tags_select',true);
            if(!empty($tags)){
                $tags_array = array();
                $tags_array['relation'] = $relations['Tag'];
                foreach ($tags as $tag){
                    $tags_array[] = array(
                        'key'     => '_product_tag_id_',
                        'value'   => $tag,
                        'compare' => '=',
                    );
                }
                return $tags_array;
            }
            return;
        }

        public function products($relations){
            $products = get_post_meta($this->id,'products',true);
            if(!empty($products)){
                $products_array = array();
                $products_array['relation'] = $relations['Product_in_order'];
                foreach ($products as $product){
                    $products_array[] = array(
                        'key'     => '_product_id_',
                        'value'   => $product,
                        'compare' => '=',
                    );
                }
                return $products_array;
            }
            return;

        }
    }
}