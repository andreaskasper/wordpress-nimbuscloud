<?php
add_action( 'woocommerce_product_after_variable_attributes', 'variation_settings_fields', 10, 3 );
add_action( 'woocommerce_save_product_variation', 'save_variation_settings_fields', 10, 2 );
add_filter( 'woocommerce_available_variation', 'load_variation_settings_fields' );

function variation_settings_fields( $loop, $variation_data, $variation ) {
    woocommerce_wp_text_input(
        array(
            'id'            => "wc_nimbuscloud_course_id{$loop}",
            'name'          => "wc_nimbuscloud_course_id[{$loop}]",
            'value'         => get_post_meta( $variation->ID, 'wc_nimbuscloud_course_id', true ),
            'label'         => __( 'Nimbus Kurs:', 'woocommerce' ),
            'desc_tip'      => true,
            'description'   => __( 'War zu faul hier was zu schreiben.', 'woocommerce' ),
            'wrapper_class' => 'form-row form-row-full',
        )
    );

    woocommerce_wp_text_input(
        array(
            'id'            => "wc_nimbuscloud_courseonline_id{$loop}",
            'name'          => "wc_nimbuscloud_courseonline_id[{$loop}]",
            'value'         => get_post_meta( $variation->ID, 'wc_nimbuscloud_courseonline_id', true ),
            'label'         => __( 'Nimbus Online Kurs:', 'woocommerce' ),
            'desc_tip'      => true,
            'description'   => __( 'War zu faul hier was zu schreiben.', 'woocommerce' ),
            'wrapper_class' => 'form-row form-row-full',
        )
    );

    woocommerce_wp_text_input(
        array(
            'id'            => "wc_nimbuscloud_firstevent_id{$loop}",
            'name'          => "wc_nimbuscloud_firstevent_id[{$loop}]",
            'value'         => get_post_meta( $variation->ID, 'wc_nimbuscloud_firstevent_id', true ),
            'label'         => __( 'Nimbus Starttermin ID:', 'woocommerce' ),
            'desc_tip'      => true,
            'description'   => __( 'War zu faul hier was zu schreiben.', 'woocommerce' ),
            'wrapper_class' => 'form-row form-row-full',
        )
    );
}

function save_variation_settings_fields( $variation_id, $loop ) {
    $text_field = $_POST['my_text_field'][ $loop ];

    if (isset($_POST['wc_nimbuscloud_course_id'])) update_post_meta($variation_id, "wc_nimbuscloud_course_id", $_POST['wc_nimbuscloud_course_id'][ $loop ] ?? "");
    if (isset($_POST['wc_nimbuscloud_courseonline_id'])) update_post_meta($variation_id, "wc_nimbuscloud_courseonline_id", $_POST['wc_nimbuscloud_courseonline_id'][ $loop ] ?? "");
    if (isset($_POST['wc_nimbuscloud_firstevent_id'])) update_post_meta($variation_id, "wc_nimbuscloud_firstevent_id", $_POST['wc_nimbuscloud_firstevent_id'][ $loop ] ?? "");

}

function load_variation_settings_fields( $variation ) {     
    $variation['wc_nimbuscloud_course_id'] = get_post_meta( $variation[ 'variation_id' ], 'wc_nimbuscloud_course_id', true );
    $variation['wc_nimbuscloud_courseonline_id'] = get_post_meta( $variation[ 'variation_id' ], 'wc_nimbuscloud_courseonline_id', true );
    $variation['wc_nimbuscloud_firstevent_id'] = get_post_meta( $variation[ 'variation_id' ], 'wc_nimbuscloud_firstevent_id', true );

    return $variation;
}