<?php 

if ( ! class_exists( 'thecrate_Walker_Comment' ) ) {
    /**
     * CUSTOM COMMENT WALKER
     * A custom walker for comments, based on the walker in Twenty Nineteen.
     */
    class thecrate_Walker_Comment extends Walker_Comment {

        /**
         * Outputs a comment in the HTML5 format.
         *
         * @see wp_list_comments()
         * @see https://developer.wordpress.org/reference/functions/get_comment_author_url/
         * @see https://developer.wordpress.org/reference/functions/get_comment_author/
         * @see https://developer.wordpress.org/reference/functions/get_avatar/
         * @see https://developer.wordpress.org/reference/functions/get_comment_reply_link/
         * @see https://developer.wordpress.org/reference/functions/get_edit_comment_link/
         *
         * @param WP_Comment $comment Comment to display.
         * @param int        $depth   Depth of the current comment.
         * @param array      $args    An array of arguments.
         */
        protected function html5_comment( $comment, $depth, $args ) {

            $tag = ( 'div' === $args['style'] ) ? 'div' : 'li';

            ?>
            <<?php echo esc_attr($tag); ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( $this->has_children ? 'parent' : '', $comment ); ?>>
                <article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
                    <div class="comment-meta">
                        <div class="comment-author vcard">
                            <?php
                            $comment_author_url = get_comment_author_url( $comment );
                            $comment_author     = get_comment_author( $comment );
                            $avatar             = get_avatar( $comment, $args['avatar_size'] );
                            if ( 0 !== $args['avatar_size'] ) {
                                if ( empty( $comment_author_url ) ) {
                                    echo get_avatar( $comment, $args['avatar_size'] );
                                } else {
                                    printf( '<a href="%s" rel="external nofollow" class="url">', $comment_author_url ); 
                                    echo get_avatar( $comment, $args['avatar_size'] );
                                }
                            }

                            printf(
                                '<span class="fn">%1$s</span><span class="screen-reader-text says">%2$s</span>',
                                esc_html( $comment_author ),
                                esc_html__( '', 'thecrate' )
                            );

                            if ( ! empty( $comment_author_url ) ) {
                                echo '</a>';
                            }

                            ?>
                            <a href="<?php echo esc_url( get_comment_link( $comment, $args ) ); ?>" class="comment-post-date">
                                <?php
                                /* Translators: 1 = comment date, 2 = comment time */
                                $comment_timestamp = sprintf( esc_html__( '%1$s at %2$s', 'thecrate' ), get_comment_date( '', $comment ), get_comment_time() );
                                ?>
                                <time datetime="<?php comment_time( 'c' ); ?>" title="<?php echo esc_attr( $comment_timestamp ); ?>">
                                     (<?php echo esc_html( $comment_timestamp ); ?>)
                                </time>
                            </a>

                        </div><!-- .comment-author -->
                    </div><!-- .comment-meta -->

                    <div class="comment-content entry-content">
                        <?php
                        comment_text();

                        if ( '0' === $comment->comment_approved ) {
                            ?>
                            <p class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'thecrate' ); ?></p>
                            <?php
                        }

                        ?>

                    </div><!-- .comment-content -->

                    <?php

                    $comment_reply_link = get_comment_reply_link(
                        array_merge(
                            $args,
                            array(
                                'add_below' => 'div-comment',
                                'depth'     => $depth,
                                'max_depth' => $args['max_depth'],
                                'before'    => '<span class="comment-reply">',
                                'after'     => '</span>',
                            )
                        )
                    );

                    $by_post_author = thecrate_is_comment_by_post_author( $comment );

                    if ( $comment_reply_link || $by_post_author ) {
                        ?>

                        <div class="comment-footer-meta">

                            <?php
                            if ( get_edit_comment_link() ) {
                                echo ' <a class="comment-edit-link" href="' . esc_url( get_edit_comment_link() ) . '">' . esc_html__( 'Edit', 'thecrate' ) . '</a> ';
                            }
                            
                            if ( $comment_reply_link ) {
                                echo wp_kses($comment_reply_link, 'link'); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Link is escaped in https://developer.wordpress.org/reference/functions/get_comment_reply_link/
                            }
                            ?>

                        </div>

                        <?php
                    }
                    ?>

                </article><!-- .comment-body -->

            <?php
        }
    }
}


/**
 * Comments
 */
/**
 * Check if the specified comment is written by the author of the post commented on.
 *
 * @param object $comment Comment data.
 *
 * @return bool
 */
function thecrate_is_comment_by_post_author( $comment = null ) {

    if ( is_object( $comment ) && $comment->user_id > 0 ) {

        $user = get_userdata( $comment->user_id );
        $post = get_post( $comment->comment_post_ID );

        if ( ! empty( $user ) && ! empty( $post ) ) {

            return $comment->user_id === $post->post_author;

        }
    }
    return false;

}