<?php
add_action( 'woocommerce_product_after_variable_attributes', 'variation_settings_fields', 10, 3 );
add_action( 'woocommerce_save_product_variation', 'save_variation_settings_fields', 10, 2 );
add_filter( 'woocommerce_available_variation', 'load_variation_settings_fields' );

function variation_settings_fields( $loop, $variation_data, $variation ) {
    woocommerce_wp_text_input(
        array(
            'id'            => "my_text_field{$loop}",
            'name'          => "my_text_field[{$loop}]",
            'value'         => get_post_meta( $variation->ID, 'my_text_field', true ),
            'label'         => __( 'Some label', 'woocommerce' ),
            'desc_tip'      => true,
            'description'   => __( 'Some description.', 'woocommerce' ),
            'wrapper_class' => 'form-row form-row-full',
        )
    );
}

function save_variation_settings_fields( $variation_id, $loop ) {
    $text_field = $_POST['my_text_field'][ $loop ];

    if ( ! empty( $text_field ) ) {
        update_post_meta( $variation_id, 'my_text_field', esc_attr( $text_field ));
    }
}

function load_variation_settings_fields( $variation ) {     
    $variation['my_text_field'] = get_post_meta( $variation[ 'variation_id' ], 'my_text_field', true );

    return $variation;
}