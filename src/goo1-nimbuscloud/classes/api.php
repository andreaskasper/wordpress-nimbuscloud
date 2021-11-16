<?php

namespace plugins\goo1\nimbuscloud;

class api {

  /*
   * API Beschreibung Nimbuscloud
   * https://help.nimbuscloud.at/?route=/pages/api
   */

  public static function get_courses() : Array { return self::requestPOST('/api/json/v1/online-registration/courses', array()); }
  public static function get_course(int $id) : Array { return self::requestPOST('/api/json/v1/online-registration/course', array("id" => $id)); }
  public static function get_first_onlinecourse_id(int $id) : int { $json = self::get_course($id); return $json["content"]["courses"][0]["onlineCourseId"]; }
  public static function get_first_starttermin_id(int $id) : int { $json = self::get_course($id); return $json["content"]["courses"][0]["startEventId"]; }
	
  public static function requestPOST(string $path, Array $data) : Array {
    $data["apikey"] = get_option("goo1_nimbuscloud_apikey","");
    $payload = http_build_query($data);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://'.get_option("goo1_nimbuscloud_apiurl","").$path,
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
            throw new \Exception("Nimbuscloud gibt einen Fehler ".$json["statuscode"]." zurück. ".$json["status"]."  ".$json["content"]["message"]);
        default:
    }

    return $json;
  }
}