<?php
/**
 * @file
 * Media watermark class.
 *
 * Class to process image watermarks.
 */

namespace Drupal\media_watermark\Watermark;

use Drupal\Component\Utility\Html;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\media_watermark\Entity\MediaWatermark;

/**
 * Custom watermark class.
 */
class Watermark {

  /**
   * Function to add watermark.
   *
   * @param object $main_img_obj
   *   image object to add watermark
   * @param object $watermark_img_obj
   *   watermark image object
   * @param MediaWatermark $watermark
   *   watermark position object
   *
   * @return mixed
   *   return resource of image with watermark already add
   */
  public static function addWatermark($main_img_obj, $watermark_img_obj, $watermark, $ext) {
    $main_img_obj_w = imagesx($main_img_obj);
    $main_img_obj_h = imagesy($main_img_obj);
    $watermark_img_obj_w = imagesx($watermark_img_obj);
    $watermark_img_obj_h = imagesy($watermark_img_obj);

    $vm = $watermark->getVerticalMargin();
    $hm = $watermark->getHorizontalMargin();
    switch ($watermark->getHorizontalPosition()) {
      case 'left':
        $margin_x = $hm;
        break;

      case 'middle':
        // Determine center position coordinates.
        $margin_x = floor(($main_img_obj_w / 2) - ($watermark_img_obj_w / 2)) + $hm;
        break;

      case 'right':
        $margin_x = $main_img_obj_w - $watermark_img_obj_w + $hm;
        break;

    }
    switch ($watermark->getVerticalPosition()) {
      case 'top':
        $margin_y = $vm;
        break;

      case 'center':
        $margin_y = floor(($main_img_obj_h / 2) - ($watermark_img_obj_h / 2)) + $hm;
        break;

      case 'bottom':
        $margin_y = $main_img_obj_h - $watermark_img_obj_h + $vm;
        break;

    }

    // Set the margins for stamp and get the height/width of the stamp image.
    $sx = imagesx($watermark_img_obj);
    $sy = imagesy($watermark_img_obj);
    imagecopy($main_img_obj, $watermark_img_obj, $margin_x, $margin_y, 0, 0, $sx, $sy);
    imagejpeg($main_img_obj);

    return $main_img_obj;
  }

  /**
   * Function to create image (.jpg, .jpeg, .png, .gif) file.
   *
   * Used in batch callback function and as before on file upload form.
   *
   * @param File $file
   *   Drupal file object
   *
   * @param MediaWatermark $watermark
   *   Watermark entity object.
   *
   * @param string $type
   *   Type of image operation
   */
  public static function createImage($file, $watermark, $type = 'add') {
    // Get file real path.
    $file_path = \Drupal::service('file_system')->realpath($file->getFileUri());

    // Get watermark file id.
    $fid = $watermark->getFid();
    if (!empty($fid) && is_array($fid)) {
      $fid = reset($fid);
    }
    else {
      // If none then do nothing.
      drupal_set_message(t("Watermark doesn't have file to be applied."));
      return;
    }

    // Watermark file object.
    $watermark_file = File::load($fid);
    // Get watermark file real path to get image extension.
    $watermark_filepath = \Drupal::service('file_system')->realpath($watermark_file->getFileUri());
    $watermark_extension = pathinfo($watermark_filepath, PATHINFO_EXTENSION);
    // Check uploaded image extension.
    $ext = pathinfo($file_path, PATHINFO_EXTENSION);
    // We need to use appropriate functions when save image.
    $ext = self::getFuncName($ext);
    if (!empty($ext)) {
      $func_name = 'imagecreatefrom' . self::getFuncName($ext);
      $img = $func_name($file_path);
      // Check if not empty image file and extension then proceed with adding watermark.
      if (!empty($img)) {
        // Copy orignal file to another folder before create watermark.
        $watermark_source = 'public://watermark_source/';
        file_prepare_directory($watermark_source, FILE_CREATE_DIRECTORY);
        $original_image = file_get_contents($file_path);
        $file_name = basename($file_path);
        if ($original_image) {
          $save_file = file_save_data($original_image, $watermark_source . "/" . $file_name, FILE_EXISTS_RENAME);
        }
        // Get watermark image.
        $get_watermark = 'imagecreatefrom' . $watermark_extension;
        $watermark_img = $get_watermark($watermark_filepath);
        ob_start();
        $im = self::addWatermark($img, $watermark_img, $watermark, $ext);
        $func_name = self::getFuncName($ext);
        $func_name = 'image' . $func_name;
        $func_name($im, $file_path);
        imagedestroy($im);
        ob_end_clean();
        // We need to call this function to flush image cache only for updated images.
        self::flushImageStylesCache($file->getFileUri());
      }
    }
    else {
      drupal_set_message(t('Unknown or unsupported image extension.'));
    }
  }


  /**
   * Helper to get function name suffix.
   *
   * @param string $ext
   *   drupal file extension
   *
   * @return mixed
   */
  public static function getFuncName($ext) {
    $func_name = '';

    if (!empty($ext) && is_string($ext)) {
      $ext = strtolower($ext);
      if ($ext == 'jpg' || $ext == 'jpeg') {
        $func_name = 'jpeg';
      }
      elseif ($ext == 'png') {
        $func_name = 'png';
      }
      elseif ($ext == 'gif') {
        $func_name = 'gif';
      }
    }

    return $func_name;
  }


  /**
   * Helper to flush image cache only for updated images.
   *
   * @param $file_uri
   *   file internal drupal path
   */
  public static function flushImageStylesCache($file_uri) {
    $styles = ImageStyle::loadMultiple();
    if (!empty($styles) && is_array($styles)) {
      foreach ($styles as $style) {
        if (method_exists($style, 'flush')) {
          $style->flush($file_uri);
        }
        else {
          // Log error about flushing image styles.
          $message = t('Method flush() is not available into ImageStyle class');
          \Drupal::logger('media_watermark')->error($message);
        }
      }
    }
  }

  /**
   * Batch worker function.
   *
   * Need to be in global visibility.
   *
   * @see media_watermark_batch_submit().
   *
   * @param $context
   */
  /**
   * @param File $file
   *   File entity object.
   * @param MediaWatermark $watermark
   *   MediaWatermark entity object.
   * @param $context
   *   Batch API context array.
   */
  public static function batchCreateImage($file, $watermark, &$context) {
    self::createImage($file, $watermark, 'edit');
    $context['results'][] = $file->id() . ' : ' . Html::escape($file->getFilename());
    // Optional message displayed under the progressbar.
    $context['message'] = t('Loading node "@title"', array('@title' => $file->getFilename()));
    $_SESSION['http_request_count']++;
  }


  /**
   * Perform tasks when a batch is complete.
   *
   * Callback for batch_set().
   *
   * @param bool $success
   *   A boolean indicating whether the batch operation successfully concluded.
   * @param int $results
   *   The results from the batch process.
   * @param array $operations
   *   The batch operations that remained unprocessed. Only relevant if $success
   *   is FALSE.
   *
   * @ingroup callbacks
   */
  public function batchFinished($success, $results, $operations) {
    // TODO find way to use batch finish function from class.
    if ($success) {
      $count = count($results);
      drupal_set_message(t('Added watermarks to @count images.',
        array( '@count' => $count )));
      drupal_set_message(t('Also has been flushed image styles generated for updated images.
    If images still seems to be same as before, please flush your browser cache.'));
    }
    else {
      // An error occurred.
      // $operations contains the operations that remained unprocessed.
      $error_operation = reset($operations);
      drupal_set_message(
        t('An error occurred while processing @operation with arguments : @args',
          array(
            '@operation' => $error_operation[0],
            '@args'      => print_r($error_operation[0], TRUE),
          )
        )
      );
    }
  }

}
