<?php

namespace plugins\goo1\nimbuscloud;

class core {
	
  public static function init() {

    //self::activate_testmode();


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
              echo('<INPUT type="text" class="regular-text" name="goo1_nimbuscloud_apiurl" value="'.esc_attr(get_option("goo1_nimbuscloud_apiurl","")).'" PATTERN="[A-Za-z0-9-\.]+" PLACEHOLDER="example.nimbuscloud.at"/>');
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
      $rows = \plugins\goo1\nimbuscloud\api::get_courses();
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
  
      $rows = \plugins\goo1\nimbuscloud\api::get_course($arr["wc_nimbuscloud_course_id"][0]);
      //print_r($rows);
      $arr2 = array("" => "automatisch");
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
      
      $arr2 = array("" => "automatisch");
      foreach ($rows["content"]["courses"][0]["events"] as $row) {
          $arr2[$row["id"]] = date("D d.m.Y H:i",$row["start_time"])."Uhr [".$row["id"]."]";
      }
      $arr2[$rows["content"]["courses"][0]["startEventId"]] = "Nächster Termin: ".($arr2[$rows["content"]["courses"][0]["startEventId"]] ?? "");
      
  
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

 
  add_action( 'woocommerce_order_actions',  function( $actions) {
    $actions['wc_send_nimbuscloud'] = __( 'Sende an Nimbuscloud', 'goo1-nimbuscloud' );
    return $actions;
  } );
  
  
  add_action( 'woocommerce_order_action_wc_send_nimbuscloud', function( \WC_Order $order ) {
    self::hook_payment_complete($order->get_id());
  });
  
    add_action( 'woocommerce_payment_complete', array('\plugins\goo1\nimbuscloud\core', 'hook_payment_complete'));
    do_action("goo1_nimbuscloud_loaded");
  }

  public static function hook_payment_complete(int $order_id){
    $order = wc_get_order( $order_id );
    $order->add_order_note("Zahlung abgeschlossen übertrage Daten an die Nimbuscloud wenn möglich.");
    $user = $order->get_user();
    if( $user ){
        // do something with the user
    }

    $items = $order->get_items();
    foreach ( $items as $item ) {
        for ($anzahl = 0; $anzahl < max(1,$item->get_quantity()); $anzahl++) {
          $arr = get_post_meta($item->get_product_id());
          if (empty($arr["wc_nimbuscloud_course_id"][0]) AND empty($arr["wc_nimbuscloud_courseonline_id"][0])) continue;
          
          $item_meta_data = $item->get_meta_data();
          $anmerkungen = "Anmeldung über Webseite ".$_SERVER["HTTP_HOST"].PHP_EOL."WooCommerce Order-ID: ".$order_id.PHP_EOL;
          foreach ($item_meta_data as $a) {
            $a2 = $a->get_data();
            if (empty($a2["key"]) OR !isset($a2["value"])) continue;
            if (substr($a2["key"],0,1) == "_") continue;
            $anmerkungen .= $a2["key"].": ".$a2["value"].PHP_EOL;
          }
          $anmerkungen .= "----------".PHP_EOL.json_encode(get_post_meta($order_id));
          $anmerkungen = nl2br($anmerkungen);

          $w = array();
          $w["customerCity"] = $order->get_billing_city();
          $w["customerFirstname"] = $order->get_billing_first_name();
          $w["customerGender"] = "m";
          $w["customerPhone"] = $order->get_billing_phone();
          $w["customerStreet"] = $order->get_billing_address_1();
          $w["customerSurname"] = $order->get_billing_last_name();
          $w["customerZIP"] = $order->get_billing_postcode();
          //$w["coupon-id"] = null;
          $w["course-id"] = $arr["wc_nimbuscloud_course_id"][0];
          if (!empty($arr["wc_nimbuscloud_courseonline_id"][0])) $w["course-onlineid"] = $arr["wc_nimbuscloud_courseonline_id"][0];
          else $w["course-onlineid"] = \plugins\goo1\nimbuscloud\api::get_first_onlinecourse_id($w["course-id"]);
          //$w["customerAccountOwner"] = null;
          //$w["customerBic"] = null;
          //$w["customerBirthday"] = null;
          //$w["customerIban"] = null;
          $w["customerMail"] = $order->get_billing_email();
          $w["customerMessage"] = $anmerkungen;
          //$w["customerMobile"] = null;
          if (!empty($arr["wc_nimbuscloud_firstevent_id"][0])) $w["firstEvent"] = $arr["wc_nimbuscloud_firstevent_id"][0];
          else $w["firstEvent"] = \plugins\goo1\nimbuscloud\api::get_first_starttermin_id($w["course-id"]);
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

          $json = \plugins\goo1\nimbuscloud\api::requestPOST('/api/json/v1/online-registration/register', $w);

          $order->add_order_note(json_encode($json, JSON_PRETTY_PRINT));
      }
    }
}

  public static function activate_testmode() {
    if (!empty($_GET["testandi"])) {
      add_action( 'woocommerce_after_register_post_type',  function() {
        self::hook_payment_complete(103);
        die(PHP_EOL."fertig...".PHP_EOL);
        exit();
      });
    }
  }
}