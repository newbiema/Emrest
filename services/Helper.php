<?php
class Helper {
  public static function baseUrl($path = '') {
    $base = '/emrest/';
    return $base . ltrim($path, '/');
  }

  public static function statusBadge(string $status): string {
  $map = [
    'checkin'     => 'bg-gray-100 text-gray-700',
    'triase'      => 'bg-yellow-100 text-yellow-700',
    'dokter_done' => 'bg-blue-100 text-blue-700',
    'obat_siap'   => 'bg-indigo-100 text-indigo-700',
    'obat_ambil'  => 'bg-purple-100 text-purple-700',
    'lunas'       => 'bg-green-100 text-green-700',
    'batal'       => 'bg-red-100 text-red-700',
  ];
  $cls = $map[$status] ?? 'bg-gray-100 text-gray-700';
  return "<span class=\"px-2 py-1 rounded text-xs $cls\">".htmlspecialchars(str_replace('_',' ', ucfirst($status)))."</span>";
}

}
