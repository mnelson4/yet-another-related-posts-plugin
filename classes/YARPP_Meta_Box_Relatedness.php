<?php

class YARPP_Meta_Box_Relatedness extends YARPP_Meta_Box {
    public function display() {
        global $yarpp;
        ?>
        <p><?php _e( 'YARPP limits the related posts list by (1) a maximum number and (2) a <em>match threshold</em>.', 'yarpp' ); ?> <span class='yarpp_help' data-help="<?php echo esc_attr( __( 'The higher the match threshold, the more restrictive, and you get less related posts overall. The default match threshold is 5. If you want to find an appropriate match threshhold, take a look at some post\'s related posts display and their scores. You can see what kinds of related posts are being picked up and with what kind of match scores, and determine an appropriate threshold for your site.', 'yarpp' ) ); ?>">&nbsp;</span></p>

        <?php
        $this->textbox( 'threshold', __( 'Match threshold:', 'yarpp' ) );
        $this->weight( 'title', __( "Titles: ", 'yarpp' ) );
        $this->weight( 'body', __( "Bodies: ", 'yarpp' ) );

        foreach ( $yarpp->get_taxonomies() as $taxonomy ) {
            $this->tax_weight( $taxonomy );
        }

        $this->checkbox( 'cross_relate', __( "Display results from all post types", 'yarpp' )." <span class='yarpp_help' data-help='" . esc_attr( __( "When \"display results from all post types\" is off, only posts will be displayed as related to a post, only pages will be displayed as related to a page, etc.", 'yarpp' ) ) . "'>&nbsp;</span>" );
        $this->checkbox( 'past_only', __( "Show only previous posts?", 'yarpp' ) );
    }
}