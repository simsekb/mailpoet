<?php
namespace MailPoet\Services\Bridge;

if(!defined('ABSPATH')) exit;

class API {
  const SENDING_STATUS_OK = 'ok';
  const SENDING_STATUS_CONNECTION_ERROR = 'connection_error';
  const SENDING_STATUS_SEND_ERROR = 'send_error';

  const RESPONSE_CODE_KEY_INVALID = 401;

  private $api_key;

  public $url_me = 'https://bridge.mailpoet.com/api/v0/me';
  public $url_messages = 'https://bridge.mailpoet.com/api/v0/messages';
  public $url_bounces = 'https://bridge.mailpoet.com/api/v0/bounces/search';

  function __construct($api_key) {
    $this->setKey($api_key);
  }

  function checkKey() {
    $result = wp_remote_post(
      $this->url_me,
      $this->request(array('site' => home_url()))
    );

    $code = wp_remote_retrieve_response_code($result);
    switch($code) {
      case 200:
      case 402:
        $body = json_decode(wp_remote_retrieve_body($result), true);
        break;
      case 401:
        $body = wp_remote_retrieve_body($result);
        break;
      default:
        $body = null;
        break;
    }

    return array('code' => $code, 'data' => $body);
  }

  function sendMessages($message_body) {
    $result = wp_remote_post(
      $this->url_messages,
      $this->request($message_body)
    );
    if(is_wp_error($result)) {
      return array(
        'status' => self::SENDING_STATUS_CONNECTION_ERROR,
        'message' => $result->get_error_message()
      );
    }
    $response_code = wp_remote_retrieve_response_code($result);
    if($response_code !== 201) {
      $response = (wp_remote_retrieve_body($result)) ?
        wp_remote_retrieve_body($result) :
        wp_remote_retrieve_response_message($result);
      return array(
        'status' => self::SENDING_STATUS_SEND_ERROR,
        'message' => $response,
        'code' => $response_code
      );
    }
    return array('status' => self::SENDING_STATUS_OK);
  }

  function checkBounces(array $emails) {
    $result = wp_remote_post(
      $this->url,
      $this->request($emails)
    );
    if(wp_remote_retrieve_response_code($result) === 200) {
      return json_decode(wp_remote_retrieve_body($result), true);
    }
    return false;
  }

  function setKey($api_key) {
    $this->api_key = $api_key;
  }

  function getKey() {
    return $this->api_key;
  }

  private function auth() {
    return 'Basic ' . base64_encode('api:' . $this->api_key);
  }

  private function request($body) {
    return array(
      'timeout' => 10,
      'httpversion' => '1.0',
      'method' => 'POST',
      'headers' => array(
        'Content-Type' => 'application/json',
        'Authorization' => $this->auth()
      ),
      'body' => json_encode($body)
    );
  }
}
