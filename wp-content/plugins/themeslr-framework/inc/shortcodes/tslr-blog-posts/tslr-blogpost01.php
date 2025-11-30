<?php
if ( ! defined( 'ABSPATH' ) ) {
  die( '-1' );
}

/**
||-> Shortcode: Blog Posts
*/
function themeslr_shortcode_blogpost01($params, $content) {
    extract( shortcode_atts( 
        array(
            'number'              =>'',
            'columns'              =>'',
            'category'              => '',
        ), $params ) );

    // QUERY
    $args_blogposts = array(
      'posts_per_page'   => $number,
      'orderby'          => 'post_date',
      'order'            => 'DESC',
      'post_type'        => 'post',
      'post_status'      => 'publish' 
    );

    if (isset($category) && !empty($category)) {
      $args_blogposts['tax_query'] = array(
        array(
          'taxonomy' => 'category',
          'field' => 'slug',
          'terms' => $category
        )
      );
    }

    // CONTENT
    $html = '';
    $html .= '<div class="blog-posts tslr-blog-posts-shortcode row">';

    $blogposts = get_posts($args_blogposts);

    foreach ($blogposts as $blogpost) {

        #thumbnail
        $thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( $blogpost->ID ),'thecrate_post_pic700x450' );
        
        $content_post   = get_post($blogpost->ID);
        $content        = $content_post->post_content;
        $content        = apply_filters('the_content', $content);
        $content        = str_replace(']]>', ']]&gt;', $content);

        $post_col = '';
        if ($thumbnail_src) {
            $post_img = '<img class="blog_post_image" src="'. esc_url($thumbnail_src[0]) . '" alt="'.$blogpost->post_title.'" />';
        }else{
            $post_col = 'no-featured-image';
            $post_img = '';
        }

        $column = 'col-md-4';
        if (isset($columns) && $columns == 2) {
          $column = 'col-md-6';
        }elseif (isset($columns) && $columns == 3) {
          $column = 'col-md-4';
        }elseif (isset($columns) && $columns == 4) {
          $column = 'col-md-3';
        }elseif (isset($columns) && $columns == 1) {
          $column = 'col-md-12';
        }

        $html.='<div class="odd-post '.$column.'">
                  <article class="single-post grid-view">
                    <div class="blog_custom">

                      <!-- POST THUMBNAIL -->
                      <div class="post-thumbnail">
                          <a class="relative" href="'.get_permalink($blogpost->ID).'">'.$post_img.'
                            <span class="read-more-overlay">
                                <i class="fas fa-link"></i>
                            </span>
                          </a>
                      </div>

                      <!-- POST DETAILS -->
                      <div class="post-details '.$post_col.'">
                        <div class="post-category-comment-date">
                            <span class="post-date">
                                <a href="'.get_the_permalink().'">'.get_the_date(get_option('date_format'), $blogpost->ID).'</a>
                            </span> | 
                            <span class="post-tags">'. get_the_term_list( $blogpost->ID, 'category', '', ', ' ).'</span>
                        </div>

                        <h4 class="post-name">
                          <a class="text-center" href="'.get_permalink($blogpost->ID).'" title="'. $blogpost->post_title .'">'. $blogpost->post_title .'</a>
                        </h4>

                        <div class="post-excerpt">
                            '.strip_tags(themeslr_excerpt_limit($content, 12)).'
                        </div>
                      </div>
                    </div>
                  </article>
                </div>';
      }

    $html .= '</div>';
    return $html;
}
add_shortcode('blogpost01', 'themeslr_shortcode_blogpost01');

/**
||-> Map Shortcode vc_map();
*/
add_action( 'vc_before_init', 'themeslr_blogpost01_shortcode_map' );
function themeslr_blogpost01_shortcode_map() {
    $post_category_tax = get_terms('category');
    $post_category = array();
    if ($post_category_tax) {
      $post_category[esc_html__( '-Select a Category-', 'themeslr' )] = '';
      foreach ( $post_category_tax as $term ) {
         $post_category[$term->name] = $term->slug;
      }
    }

    vc_map( array(
     "name" => esc_attr__("ThemeSLR - Blog Posts", 'themeslr'),
     "base" => "blogpost01",
     "category" => esc_attr__('ThemeSLR', 'themeslr'),
     "icon" => "themeslr_shortcode",
     "params" => array(
        array(
          "group" => "Options",
           "type" => "dropdown",
           "holder" => "div",
           "class" => "",
           "heading" => esc_attr__("Blog Category (Optional)", "themeslr"),
           "param_name" => "category",
           "description" => esc_attr__("Please select blog category", "themeslr"),
           "std" => 'Default value',
           "value" => $post_category
        ),
        array(
          "group" => "Options",
          "type" => "textfield",
          "holder" => "div",
          "class" => "",
          "heading" => esc_attr__( "Number of posts to show", 'themeslr' ),
          "param_name" => "number",
          "value" => "",
          "description" => esc_attr__( "Enter number of blog post to show.", 'themeslr' )
        ),
        array(
          "group" => "Options",
          "type" => "dropdown",
          "heading" => esc_attr__("Posts per row", 'themeslr'),
          "param_name" => "columns",
          "std" => '',
          "holder" => "div",
          "class" => "",
          "description" => "",
          "value" => array(
            '1'   => '1',
            '2'   => '2',
            '3'   => '3',
            '4'   => '4'
            )
        ),
      )
  ));
}