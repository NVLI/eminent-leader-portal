<?php
/**
 * @file
 * Contains \Drupal\media_watermark\Controller\MediaWatermarkListBuilder.
 */

namespace Drupal\media_watermark\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Component\Utility\Html;
use Drupal\file\Entity\File;

/**
 * Provides a listing of Example.
 */
class MediaWatermarkListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Media Watermark');
    $header['id'] = $this->t('Machine name');
    $header['fid'] = $this->t('Image');
    $header['horizontalPosition'] = $this->t('Horizontal Position');
    $header['verticalPosition'] = $this->t('Vertical Position');
    $header['horizontalMargin'] = $this->t('Horizontal Margin');
    $header['verticalMargin'] = $this->t('Vertical Margin');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = Html::escape($this->getLabel($entity));
    $row['id'] = Html::escape($entity->id());
    $row['fid'] = $this->prepareImage($entity->getFid());
    $row['horizontalPosition'] = Html::escape($entity->getHorizontalPosition());
    $row['verticalPosition'] = Html::escape($entity->getVerticalPosition());
    $row['horizontalMargin'] = Html::escape($entity->getHorizontalMargin());
    $row['verticalMargin'] = Html::escape($entity->getVerticalMargin());

    // You probably want a few more properties here...

    return $row + parent::buildRow($entity);
  }

  /**
   * Helper to prepare image.
   *
   * @param $fids
   *
   * @return mixed
   */
  private function prepareImage($fids) {
    if (!empty($fids) && is_array($fids)) {
      $fid = reset($fids);
      $file = File::load($fid);
      $image = array(
        '#theme' => 'image_style',
        '#width' => 200,
        '#height' => NULL,
        '#style_name' => 'media_watermark',
        '#uri' => $file->getFileUri(),
      );

      return render($image);
    }
  }

}
?>