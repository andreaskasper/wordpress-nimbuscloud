<?php
/**
 * Plugin Name: goo1 Nimbuscloud Plugin
 * Plugin URI: https://github.com/andreaskasper/wordpress-nimbuscloud
 * Description: Connect Nimbuscloud to your Wordpress
 * Author: Andreas Kasper
 * Version: 0.1.5
 * Author URI: https://github.com/andreaskasper/
 * Network: True
 * Text Domain: goo1-nimbuscloud
 */

add_action('admin_init', function() {
    register_setting( 'general', 'goo1_nimbuscloud_apikey', array(
        'show_in_rest' => false,
        'type'         => 'string',
        'default'      => "",
    ));
    register_setting( 'general', 'goo1_nimbuscloud_apiurl', array(
        'show_in_rest' => false,
        'type'         => 'string',
        'default'      => "",
    ));
    add_settings_field(
        'goo1_nimbuscloud_apikey',
        'Nimbuscloud APIkey',
        function() {
            echo('<INPUT type="text" class="regular-text" name="goo1_nimbuscloud_apikey" value="'.esc_attr(get_option("goo1_nimbuscloud_apikey","")).'" PATTERN="[0-9a-f]+"/>');
        },
        'general',
        'default',
        array( 'label_for' => 'myprefix_setting-id' )
    );
    add_settings_field(
        'goo1_nimbuscloud_apiurl',
        'Nimbuscloud URL Host',
        function() {
            echo('<INPUT type="text" class="regular-text" name="goo1_nimbuscloud_apiurl" value="'.esc_attr(get_option("goo1_nimbuscloud_apiurl","")).'" PATTERN="[A-Za-z0-9-\.]+"/>');
        },
        'general',
        'default',
        array( 'label_for' => 'myprefix_setting-id' )
    );
});

add_filter( 'woocommerce_product_data_tabs', function( $product_data_tabs ) {
	$product_data_tabs['vimeo'] = array(
		'label' => __('NimbusCloud', 'goo1-nimbuscloud'),
		'target' => 'wc_nimbuscloud_id',
        'class'  => array('show_if_virtual'),
	);
	return $product_data_tabs;
});

add_action( 'woocommerce_product_data_panels', function() {
    global $post_id;
	echo('<div id="wc_nimbuscloud_id" class="panel woocommerce_options_panel">');
    $arr = get_post_meta($post_id);
        
    $arr2 = array("" => "Wähle einen Kurs");
    $rows = goo1_nimbuscloud_getcourses();
    foreach ($rows["content"]["courses"] as $row) {
        $arr2[$row["courseId"]] = $row["displayName"]." [".$row["courseId"]."]";
    }

    woocommerce_wp_select(array(
        'id'            => 'wc_nimbuscloud_course_id',
        'wrapper_class' => 'show_if_simple',
        "desc_tip" => true,
        'label'         => __('Kurs:', 'goo1-nimbuscloud'),
        'description'   => __('abc', 'goo1-nimbuscloud'),
        'options'       => $arr2,
        'value'         => $arr["wc_nimbuscloud_course_id"][0] ?? ""
    ));

    if (!empty($arr["wc_nimbuscloud_course_id"][0])) {

    $rows = goo1_nimbuscloud_getcourse($arr["wc_nimbuscloud_course_id"][0]);
    //print_r($rows);
    $arr2 = array("" => "Wähle einen OnlineKurs");
    foreach ($rows["content"]["courses"] as $row) {
        $arr2[$row["onlineCourseId"]] = $row["displayName"]." [".$row["onlineCourseId"]."]";
    }

    woocommerce_wp_select(array(
        'id'            => 'wc_nimbuscloud_courseonline_id',
        'wrapper_class' => 'show_if_simple',
        "desc_tip" => true,
        'label'         => __('Online Kurs:', 'goo1-nimbuscloud'),
        'description'   => __('abc', 'goo1-nimbuscloud'),
        'options'       => $arr2,
        'value'         => $arr["wc_nimbuscloud_courseonline_id"][0] ?? ""
    ));
    
    $arr2 = array("" => "Wähle einen Starttermin");
    $arr2[$rows["content"]["courses"][0]["startEventId"]] = "Nächster Termin [".$rows["content"]["courses"][0]["startEventId"]."]";
    foreach ($rows["content"]["courses"][0]["events"] as $row) {
        $arr2[$row["id"]] = date("D d.m.Y H:i",$row["start_time"])."Uhr [".$row["id"]."]";
    }
    $arr2[$rows["content"]["courses"][0]["startEventId"]] = "Nächster Termin [".$rows["content"]["courses"][0]["startEventId"]."]";
    

    woocommerce_wp_select(array(
        'id'            => 'wc_nimbuscloud_firstevent_id',
        'wrapper_class' => 'show_if_simple',
        "desc_tip" => true,
        'label'         => __('Starttermin ID', 'goo1-nimbuscloud'),
        'description'   => __('abc', 'goo1-nimbuscloud'),
        'options'       => $arr2,
        'value'         => $arr["wc_nimbuscloud_firstevent_id"][0] ?? ""
    ));

    }
        
    
	echo('</div>');
});

// Save tab settings
add_action('woocommerce_process_product_meta', function($post_id) {
    $tagPrefix = 'wc_akyoutube_';
	if (isset($_POST['wc_nimbuscloud_course_id'])) update_post_meta($post_id, "wc_nimbuscloud_course_id", $_POST['wc_nimbuscloud_course_id'] ?? "");
	if (isset($_POST['wc_nimbuscloud_courseonline_id'])) update_post_meta($post_id, "wc_nimbuscloud_courseonline_id", $_POST['wc_nimbuscloud_courseonline_id'] ?? "");
    if (isset($_POST['wc_nimbuscloud_firstevent_id'])) update_post_meta($post_id, "wc_nimbuscloud_firstevent_id", $_POST['wc_nimbuscloud_firstevent_id'] ?? "");
});

add_action( 'woocommerce_payment_complete', 'goo1_nimbuscloud_payment_complete' );
function goo1_nimbuscloud_payment_complete( $order_id ){
    $order = wc_get_order( $order_id );
    $order->add_order_note("Zahlung abgeschlossen übertrage Daten an die Nimbuscloud wenn möglich.");
    $user = $order->get_user();
    if( $user ){
        // do something with the user
    }

    $items = $order->get_items();
    foreach ( $items as $item ) {
        $arr = get_post_meta($item->get_product_id());
        if (empty($arr["wc_nimbuscloud_course_id"][0]) AND empty($arr["wc_nimbuscloud_courseonline_id"][0])) continue;
        
        $item_meta_data = $item->get_meta_data();
        $anmerkungen = "Anmeldung über Webseite ".$_SERVER["HTTP_HOST"].", WooCommerce Order-ID: ".$order_id."   ".json_encode(get_post_meta($order_id))."   ".json_encode($item_meta_data);

        $w = array();
        $w["apikey"] = get_option("goo1_nimbuscloud_apikey","");
        $w["customerCity"] = $order->get_billing_city();
        $w["customerFirstname"] = $order->get_billing_first_name();
        $w["customerGender"] = "m";
        $w["customerPhone"] = $order->get_billing_phone();
        $w["customerStreet"] = $order->get_billing_address_1();
        $w["customerSurname"] = $order->get_billing_last_name();
        $w["customerZIP"] = $order->get_billing_postcode();
        //$w["coupon-id"] = null;
        $w["course-id"] = $arr["wc_nimbuscloud_course_id"][0];
        $w["course-onlineid"] = $arr["wc_nimbuscloud_courseonline_id"][0];
        //$w["customerAccountOwner"] = null;
        //$w["customerBic"] = null;
        //$w["customerBirthday"] = null;
        //$w["customerIban"] = null;
        $w["customerMail"] = $order->get_billing_email();
        $w["customerMessage"] = $anmerkungen;
        //$w["customerMobile"] = null;
        $w["firstEvent"] = $arr["wc_nimbuscloud_firstevent_id"][0];
        //$w["newsletter"] = null;
        //$w["partnerAccountOwner"] = null;
        //$w["partnerBic"] = null;
        //$w["partnerBirthday"] = null;
        //$w["partnerCity"] = null;
        //$w["partnerFirstname"] = null;
        //$w["partnerGender"] = null;
        //$w["partnerIban"] = null;
        //$w["partnerMail"] = null;
        //$w["partnerMobile"] = null;
        //$w["partnerPhone"] = null;
        //$w["partnerStreet"] = null;
        //$w["partnerSurname"] = null;
        //$w["partnerZIP"] = null;
        //$w["payment-id"] = date("YmdHis");
        $w["paymentMethod"] = "bank";
        //$w["perform-registration"] = false; //Zahlung durchgeführt
        //$w["registration-legalGuardian"] = false;
        $w["typeOfRegistration"] = "course-".$w["course-id"];
        $w["typeOfRegistrationText"] = "---";

        $payload = http_build_query($w);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://'.get_option("goo1_nimbuscloud_apiurl","").'/api/json/v1/online-registration/register',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $json = json_decode($response, true);
        switch ($json["statuscode"] ?? "") {
            case 400:
            case 404:
            case 405:
                die('<pre>'.json_encode($json, JSON_PRETTY_PRINT).'</pre>');
                print_r($w);
                throw new \Exception("Nimbuscloud gibt einen Fehler 400 zurück. ".$json["status"]."  ".$json["content"]["message"]);
            default:
        }
        $order->add_order_note(json_encode($json, JSON_PRETTY_PRINT));
    }
}

function goo1_nimbuscloud_getcourses() : Array {
    $payload = http_build_query(array("apikey" => get_option("goo1_nimbuscloud_apikey","")));
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://'.get_option("goo1_nimbuscloud_apiurl","").'/api/json/v1/online-registration/courses',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded'
        ),
    ));


    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response, true);
}

function goo1_nimbuscloud_getcourse(int $id) : Array {
    $payload = http_build_query(array("apikey" => get_option("goo1_nimbuscloud_apikey",""), "id" => $id));
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://'.get_option("goo1_nimbuscloud_apiurl","").'/api/json/v1/online-registration/course',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded'
        ),
    ));


    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response, true);
}

/*if (!empty($_GET["test"])) {
    add_action( 'woocommerce_after_register_post_type',  function() {
        goo1_nimbuscloud_payment_complete(103);
        exit(1);
    });
}*/

if (!class_exists("Puc_v4_Factory")) {
	require_once(__DIR__."/plugin-update-checker/plugin-update-checker.php");
}
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    "https://raw.githubusercontent.com/andreaskasper/wordpress-nimbuscloud/main/dist/updater.json",
    __FILE__, //Full path to the main plugin file or functions.php.
    "goo1-nimbuscloud"
);