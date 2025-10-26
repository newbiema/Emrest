<?php
class Alert {
  public static function toast($icon, $message, $redirect = null) {
    ob_start(); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
      const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.addEventListener('mouseenter', Swal.stopTimer)
          toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
      });

      Toast.fire({
        icon: '<?= $icon ?>',
        title: '<?= addslashes($message) ?>'
      });

      <?php if ($redirect): ?>
        setTimeout(() => {
          window.location.href = '<?= $redirect ?>';
        }, 2600);
      <?php endif; ?>
    </script>
    <?php
    $contentFile = tempnam(sys_get_temp_dir(), 'content');
    file_put_contents($contentFile, ob_get_clean());
    include_once __DIR__ . '/../components/layout.php';
    exit;
  }
}
