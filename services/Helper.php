<?php
class Helper {
  public static function baseUrl($path = '') {
    // Ganti 'emrest' sesuai nama folder proyek kamu di htdocs
    $base = '/emrest/';
    return $base . ltrim($path, '/');
  }
}
