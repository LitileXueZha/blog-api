<?php
  require_once('./response.php');

  class ErrorHander {
    public static $type = ['Error', 'Exception'];

    public static function init($tyoe) {
      return new Response();
    }
  }
