<?php
/**
 * @file
 * Contains \Drupal\media_watermark\Entity\MediaWatermark.
 */

namespace Drupal\media_watermark\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the media_watermark entity.
 *
 * @ConfigEntityType(
 *   id = "media_watermark",
 *   label = @Translation("Media Watermark"),
 *   handlers = {
 *     "list_builder" = "Drupal\media_watermark\Controller\MediaWatermarkListBuilder",
 *     "form" = {
 *       "add" = "Drupal\media_watermark\Form\MediaWatermarkForm",
 *       "edit" = "Drupal\media_watermark\Form\MediaWatermarkForm",
 *       "delete" = "Drupal\media_watermark\Form\MediaWatermarkDeleteForm"
 *     }
 *   },
 *   config_prefix = "media_watermark",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   links = {
 *     "add-form" = "/admin/config/media/media_watermark/add",
 *     "edit-form" = "/admin/config/media/media_watermark/{media_watermark}",
 *     "delete-form" = "/admin/config/media/media_watermark/{media_watermark}/delete"
 *   }
 * )
 */
class MediaWatermark extends ConfigEntityBase implements MediaWatermarkInterface {

  /**
   * The media_watermark ID.
   *
   * @var string|int
   */
  public $id;

  /**
   * The media_watermark file ID.
   *
   * @var string|int
   */
  protected $fid;

  /**
   * The media_watermark label.
   *
   * @var string
   */
  protected $label;

  /**
   * The media_watermark horizontal position.
   *
   * @var string|int
   */
  protected $horizontalPosition;

  /**
   * The media_watermark vertical position.
   *
   * @var string|int
   */
  protected $verticalPosition;

  /**
   * The media_watermark horizontal margin.
   *
   * @var string|int
   */
  protected $horizontalMargin;

  /**
   * The media_watermark vertical margin.
   *
   * @var string|int
   */
  protected $verticalMargin;

  /**
   * {@inheritdoc}
   */
  public function getFid() {
    return $this->fid;
  }

  /**
   * {@inheritdoc}
   */
  public function setFid($fid) {
    $this->fid = $fid;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getHorizontalPosition() {
    return $this->horizontalPosition;
  }

  /**
   * {@inheritdoc}
   */
  public function setHorizontalPosition($horizontalPosition) {
    $this->horizontalPosition = $horizontalPosition;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getVerticalPosition() {
    return $this->verticalPosition;
  }

  /**
   * {@inheritdoc}
   */
  public function setVerticalPosition($verticalPosition) {
    $this->verticalPosition = $verticalPosition;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getHorizontalMargin() {
    return $this->horizontalMargin;
  }

  /**
   * {@inheritdoc}
   */
  public function setHorizontalMargin($horizontalMargin) {
    $this->horizontalMargin = $horizontalMargin;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getVerticalMargin() {
    return $this->verticalMargin;
  }

  /**
   * {@inheritdoc}
   */
  public function setVerticalMargin($verticalMargin) {
    $this->verticalMargin = $verticalMargin;
    return $this;
  }

  /**
   * Function to return watermark names array.
   *
   * @param array $results
   *   watermarks array
   *
   * @return array
   *   names array
   */
  public static function prepareNames($results) {
    $names = array();

    foreach ($results as $value) {
      $fids = $value->getFid();
      if (!empty($fids)) {
        $fid = reset($fids);
        $names[$fid] = $value->id();
      }
    }

    return $names;
  }

}
?>