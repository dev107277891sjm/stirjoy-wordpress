<?php



/**

||-> CPT - [tslr-gallery]

*/
function tslr_gallery_custom_post() {

    register_post_type('tslr-gallery', array(
                        'label' => __('TSLR Galley','themeslr'),
                        'description' => '',
                        'public' => true,
                        'show_ui' => true,
                        'show_in_menu' => true,
                        'capability_type' => 'post',
                        'map_meta_cap' => true,
                        'hierarchical' => false,
                        'rewrite' => array('slug' => 'tslr-gallery', 'with_front' => true),
                        'query_var' => true,
                        'menu_position' => '1',
                        'menu_icon' => 'dashicons-format-gallery',
                        'supports' => array('title','editor','thumbnail','author'),
                        'labels' => array (
                            'name' => __('Galleries','themeslr'),
                            'singular_name' => __('Gallery Item','themeslr'),
                            'menu_name' => __('TSLR Galleries','themeslr'),
                            'add_new' => __('Add Gallery Item','themeslr'),
                            'add_new_item' => __('Add New Gallery Item','themeslr'),
                            'edit' => __('Edit Item','themeslr'),
                            'edit_item' => __('Edit Gallery Item','themeslr'),
                            'new_item' => __('New Gallery Item','themeslr'),
                            'view' => __('View Gallery Item','themeslr'),
                            'view_item' => __('View Gallery Item','themeslr'),
                            'search_items' => __('Search Gallery Items','themeslr'),
                            'not_found' => __('No Gallery Item Found','themeslr'),
                            'not_found_in_trash' => __('No Gallery Item Found in Trash','themeslr'),
                            'parent' => __('Parent Gallery Item','themeslr'),
                            )
                        ) 
                    ); 
}
// add_action('init', 'tslr_gallery_custom_post');



/**

||-> TAX - [tslr-gallery-category]
||-> CPT - [tslr-gallery]

*/
function tslr_gallery_category_taxonomy() {
    
    $labels = array(
        'name'                       => _x( 'Categories', 'Taxonomy General Name', 'themeslr' ),
        'singular_name'              => _x( 'Gallery', 'Taxonomy Singular Name', 'themeslr' ),
        'menu_name'                  => __( 'Gallery Categories', 'themeslr' ),
        'all_items'                  => __( 'All Items', 'themeslr' ),
        'parent_item'                => __( 'Parent Item', 'themeslr' ),
        'parent_item_colon'          => __( 'Parent Item:', 'themeslr' ),
        'new_item_name'              => __( 'New Item Name', 'themeslr' ),
        'add_new_item'               => __( 'Add New Item', 'themeslr' ),
        'edit_item'                  => __( 'Edit Item', 'themeslr' ),
        'update_item'                => __( 'Update Item', 'themeslr' ),
        'view_item'                  => __( 'View Item', 'themeslr' ),
        'separate_items_with_commas' => __( 'Separate items with commas', 'themeslr' ),
        'add_or_remove_items'        => __( 'Add or remove items', 'themeslr' ),
        'choose_from_most_used'      => __( 'Choose from the most used', 'themeslr' ),
        'popular_items'              => __( 'Popular Items', 'themeslr' ),
        'search_items'               => __( 'Search Items', 'themeslr' ),
        'not_found'                  => __( 'Not Found', 'themeslr' ),
    );
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
    );
    register_taxonomy( 'tslr-gallery-category', array( 'tslr-gallery' ), $args );
}
// add_action( 'init', 'tslr_gallery_category_taxonomy' );




/**

||-> CPT - [testimonial]

*/
function tslr_testimonial_custom_post() {

    register_post_type('Testimonial', array(
                        'label' => __('Testimonials','themeslr'),
                        'description' => '',
                        'public' => true,
                        'show_ui' => true,
                        'show_in_menu' => true,
                        'capability_type' => 'post',
                        'map_meta_cap' => true,
                        'hierarchical' => false,
                        'rewrite' => array('slug' => 'testimonial', 'with_front' => true),
                        'query_var' => true,
                        'menu_position' => '1',
                        'menu_icon' => 'dashicons-format-status',
                        'supports' => array('title','editor','thumbnail','author','excerpt'),
                        'labels' => array (
                            'name' => __('Testimonials','themeslr'),
                            'singular_name' => __('Testimonial','themeslr'),
                            'menu_name' => __('TSLR Testimonials','themeslr'),
                            'add_new' => __('Add Testimonial','themeslr'),
                            'add_new_item' => __('Add New Testimonial','themeslr'),
                            'edit' => __('Edit','themeslr'),
                            'edit_item' => __('Edit Testimonial','themeslr'),
                            'new_item' => __('New Testimonial','themeslr'),
                            'view' => __('View Testimonial','themeslr'),
                            'view_item' => __('View Testimonial','themeslr'),
                            'search_items' => __('Search Testimonials','themeslr'),
                            'not_found' => __('No Testimonials Found','themeslr'),
                            'not_found_in_trash' => __('No Testimonials Found in Trash','themeslr'),
                            'parent' => __('Parent Testimonial','themeslr'),
                            )
                        ) 
                    ); 
}
add_action('init', 'tslr_testimonial_custom_post');






/**

||-> CPT - [portfolio]

*/
// function tslr_portfolio_custom_post() {

//     register_post_type('Portfolio', array(
//                         'label' => __('Portfolios','themeslr'),
//                         'description' => '',
//                         'public' => true,
//                         'show_ui' => true,
//                         'show_in_menu' => true,
//                         'capability_type' => 'post',
//                         'map_meta_cap' => true,
//                         'hierarchical' => false,
//                         'rewrite' => array('slug' => 'portfolio', 'with_front' => true),
//                         'query_var' => true,
//                         'menu_position' => '1',
//                         'menu_icon' => 'dashicons-portfolio',
//                         'supports' => array('title','editor','thumbnail','author','excerpt'),
//                         'labels' => array (
//                             'name' => __('Portfolios','themeslr'),
//                             'singular_name' => __('Portfolio','themeslr'),
//                             'menu_name' => __('TSLR Portfolios','themeslr'),
//                             'add_new' => __('Add Portfolio','themeslr'),
//                             'add_new_item' => __('Add New Portfolio','themeslr'),
//                             'edit' => __('Edit','themeslr'),
//                             'edit_item' => __('Edit Portfolio','themeslr'),
//                             'new_item' => __('New Portfolio','themeslr'),
//                             'view' => __('View Portfolio','themeslr'),
//                             'view_item' => __('View Portfolio','themeslr'),
//                             'search_items' => __('Search Portfolios','themeslr'),
//                             'not_found' => __('No Portfolios Found','themeslr'),
//                             'not_found_in_trash' => __('No Portfolios Found in Trash','themeslr'),
//                             'parent' => __('Parent Portfolio','themeslr'),
//                             )
//                         ) 
//                     ); 
// }
// add_action('init', 'tslr_portfolio_custom_post');



/**

||-> TAX - [services]
||-> CPT - [service]

*/
// function tslr_portfolio_taxonomy() {
    
//     $labels = array(
//         'name'                       => _x( 'Portfolios', 'Taxonomy General Name', 'themeslr' ),
//         'singular_name'              => _x( 'Portfolio', 'Taxonomy Singular Name', 'themeslr' ),
//         'menu_name'                  => __( 'Portfolio Categories', 'themeslr' ),
//         'all_items'                  => __( 'All Items', 'themeslr' ),
//         'parent_item'                => __( 'Parent Item', 'themeslr' ),
//         'parent_item_colon'          => __( 'Parent Item:', 'themeslr' ),
//         'new_item_name'              => __( 'New Item Name', 'themeslr' ),
//         'add_new_item'               => __( 'Add New Item', 'themeslr' ),
//         'edit_item'                  => __( 'Edit Item', 'themeslr' ),
//         'update_item'                => __( 'Update Item', 'themeslr' ),
//         'view_item'                  => __( 'View Item', 'themeslr' ),
//         'separate_items_with_commas' => __( 'Separate items with commas', 'themeslr' ),
//         'add_or_remove_items'        => __( 'Add or remove items', 'themeslr' ),
//         'choose_from_most_used'      => __( 'Choose from the most used', 'themeslr' ),
//         'popular_items'              => __( 'Popular Items', 'themeslr' ),
//         'search_items'               => __( 'Search Items', 'themeslr' ),
//         'not_found'                  => __( 'Not Found', 'themeslr' ),
//     );
//     $args = array(
//         'labels'                     => $labels,
//         'hierarchical'               => true,
//         'public'                     => true,
//         'show_ui'                    => true,
//         'show_admin_column'          => true,
//         'show_in_nav_menus'          => true,
//         'show_tagcloud'              => true,
//     );
//     register_taxonomy( 'portfolios', array( 'portfolio' ), $args );
// }
// add_action( 'init', 'tslr_portfolio_taxonomy' );



/**

||-> TAX - [portfolioskill]
||-> CPT - [portfolio]

*/
// function tslr_portfolio_tags_taxonomy() {
    
//     $labels = array(
//         'name'                       => _x( 'Skills', 'Taxonomy General Name', 'themeslr' ),
//         'singular_name'              => _x( 'Skill', 'Taxonomy Singular Name', 'themeslr' ),
//         'menu_name'                  => __( 'Portfolio Skills', 'themeslr' ),
//         'all_items'                  => __( 'All Items', 'themeslr' ),
//         'parent_item'                => __( 'Parent Item', 'themeslr' ),
//         'parent_item_colon'          => __( 'Parent Item:', 'themeslr' ),
//         'new_item_name'              => __( 'New Item Name', 'themeslr' ),
//         'add_new_item'               => __( 'Add New Item', 'themeslr' ),
//         'edit_item'                  => __( 'Edit Item', 'themeslr' ),
//         'update_item'                => __( 'Update Item', 'themeslr' ),
//         'view_item'                  => __( 'View Item', 'themeslr' ),
//         'separate_items_with_commas' => __( 'Separate items with commas', 'themeslr' ),
//         'add_or_remove_items'        => __( 'Add or remove items', 'themeslr' ),
//         'choose_from_most_used'      => __( 'Choose from the most used', 'themeslr' ),
//         'popular_items'              => __( 'Popular Items', 'themeslr' ),
//         'search_items'               => __( 'Search Items', 'themeslr' ),
//         'not_found'                  => __( 'Not Found', 'themeslr' ),
//     );
//     $args = array(
//         'labels'                     => $labels,
//         'hierarchical'               => false,
//         'public'                     => true,
//         'show_ui'                    => true,
//         'show_admin_column'          => true,
//         'show_in_nav_menus'          => true,
//         'show_tagcloud'              => true,
//     );
//     register_taxonomy( 'portfolioskill', array( 'portfolio' ), $args );
// }
// add_action( 'init', 'tslr_portfolio_tags_taxonomy' );






/**

||-> CPT - [client]

*/
function tslr_client_custom_post() {

    register_post_type('Clients', array(
                        'label' => __('Clients','themeslr'),
                        'description' => '',
                        'public' => true,
                        'show_ui' => true,
                        'show_in_menu' => true,
                        'capability_type' => 'post',
                        'map_meta_cap' => true,
                        'hierarchical' => false,
                        'rewrite' => array('slug' => 'client', 'with_front' => true),
                        'query_var' => true,
                        'menu_position' => '1',
                        'menu_icon' => 'dashicons-businessman',
                        'supports' => array('title','editor','thumbnail','author','excerpt'),
                        'labels' => array (
                            'name' => __('Clients','themeslr'),
                            'singular_name' => __('Client','themeslr'),
                            'menu_name' => __('TSLR Clients','themeslr'),
                            'add_new' => __('Add Client','themeslr'),
                            'add_new_item' => __('Add New Client','themeslr'),
                            'edit' => __('Edit','themeslr'),
                            'edit_item' => __('Edit Client','themeslr'),
                            'new_item' => __('New Client','themeslr'),
                            'view' => __('View Client','themeslr'),
                            'view_item' => __('View Client','themeslr'),
                            'search_items' => __('Search Clients','themeslr'),
                            'not_found' => __('No Clients Found','themeslr'),
                            'not_found_in_trash' => __('No Clients Found in Trash','themeslr'),
                            'parent' => __('Parent Client','themeslr'),
                            )
                        ) 
                    ); 
}
add_action('init', 'tslr_client_custom_post');





/**

||-> CPT - [member]

*/
// function tslr_members_custom_post() {

//     register_post_type('member', array(
//                         'label' => __('Members','themeslr'),
//                         'description' => '',
//                         'public' => true,
//                         'show_ui' => true,
//                         'show_in_menu' => true,
//                         'capability_type' => 'post',
//                         'map_meta_cap' => true,
//                         'hierarchical' => false,
//                         'rewrite' => array('slug' => 'member', 'with_front' => true),
//                         'query_var' => true,
//                         'menu_position' => '1',
//                         'menu_icon' => 'dashicons-businessman',
//                         'supports' => array('title','editor','thumbnail','author','excerpt'),
//                         'labels' => array (
//                             'name' => __('Members','themeslr'),
//                             'singular_name' => __('Member','themeslr'),
//                             'menu_name' => __('TSLR Members','themeslr'),
//                             'add_new' => __('Add Member','themeslr'),
//                             'add_new_item' => __('Add New Member','themeslr'),
//                             'edit' => __('Edit','themeslr'),
//                             'edit_item' => __('Edit Member','themeslr'),
//                             'new_item' => __('New Member','themeslr'),
//                             'view' => __('View Member','themeslr'),
//                             'view_item' => __('View Member','themeslr'),
//                             'search_items' => __('Search Members','themeslr'),
//                             'not_found' => __('No Members Found','themeslr'),
//                             'not_found_in_trash' => __('No Members Found in Trash','themeslr'),
//                             'parent' => __('Parent Member','themeslr'),
//                             )
//                         ) 
//                     ); 
// }
// add_action('init', 'tslr_members_custom_post');


// function tslr_member_taxonomy() {
    
//     $labels = array(
//         'name'                       => _x( 'Member Positions', 'Taxonomy General Name', 'themeslr' ),
//         'singular_name'              => _x( 'Member', 'Taxonomy Singular Name', 'themeslr' ),
//         'menu_name'                  => __( 'Member Positions', 'themeslr' ),
//         'all_items'                  => __( 'All Items', 'themeslr' ),
//         'parent_item'                => __( 'Parent Item', 'themeslr' ),
//         'parent_item_colon'          => __( 'Parent Item:', 'themeslr' ),
//         'new_item_name'              => __( 'New Item Name', 'themeslr' ),
//         'add_new_item'               => __( 'Add New Item', 'themeslr' ),
//         'edit_item'                  => __( 'Edit Item', 'themeslr' ),
//         'update_item'                => __( 'Update Item', 'themeslr' ),
//         'view_item'                  => __( 'View Item', 'themeslr' ),
//         'separate_items_with_commas' => __( 'Separate items with commas', 'themeslr' ),
//         'add_or_remove_items'        => __( 'Add or remove items', 'themeslr' ),
//         'choose_from_most_used'      => __( 'Choose from the most used', 'themeslr' ),
//         'popular_items'              => __( 'Popular Items', 'themeslr' ),
//         'search_items'               => __( 'Search Items', 'themeslr' ),
//         'not_found'                  => __( 'Not Found', 'themeslr' ),
//     );
//     $args = array(
//         'labels'                     => $labels,
//         'hierarchical'               => true,
//         'public'                     => true,
//         'show_ui'                    => true,
//         'show_admin_column'          => true,
//         'show_in_nav_menus'          => true,
//         'show_tagcloud'              => true,
//     );
//     register_taxonomy( 'members', array( 'member' ), $args );
// }
// add_action( 'init', 'tslr_member_taxonomy' );

?>