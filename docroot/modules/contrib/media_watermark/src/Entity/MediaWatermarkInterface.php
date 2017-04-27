<?php
/**
 * @file
 * Contains \Drupal\example\ExampleInterface.
 */

namespace Drupal\media_watermark\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a Example entity.
 */
interface MediaWatermarkInterface extends ConfigEntityInterface {

  /**
   * Gets Watermark file id.
   *
   * @return string|int
   *   The file id as expected.
   */
  public function getFid();

  /**
   * Sets the Watermark file id.
   *
   * @param string $fid
   *   Watermark file id.
   *
   * @return $this
   */
  public function setFid($fid);


  /**
   * Gets Watermark file horizontal position.
   *
   * @return string
   *   The file horizontal position as expected.
   */
  public function getHorizontalPosition();

  /**
   * Sets the Watermark file horizontal position.
   *
   * @param string $horizontalPosition
   *   Watermark file horizontal position.
   *
   * @return $this
   */
  public function setHorizontalPosition($horizontalPosition);

  /**
   * Gets Watermark file vertical position.
   *
   * @return string|int
   *   The file vertical position as expected.
   */
  public function getVerticalPosition();

  /**
   * Sets the Watermark file vertical position.
   *
   * @param string $verticalPosition
   *   Watermark file vertical position.
   *
   * @return $this
   */
  public function setVerticalPosition($verticalPosition);

  /**
   * Gets Watermark file horizontal margin.
   *
   * @return string|int
   *   The file horizontal margin as expected.
   */
  public function getHorizontalMargin();

  /**
   * Sets the Watermark file horizontal margin.
   *
   * @param string $verticalPosition
   *   Watermark file horizontal margin.
   *
   * @return $this
   */
  public function setHorizontalMargin($horizontalMargin);

  /**
   * Gets Watermark file vertical margin.
   *
   * @return string|int
   *   The file vertical margin as expected.
   */
  public function getVerticalMargin();

  /**
   * Sets the Watermark file vertical margin.
   *
   * @param string $verticalMargin
   *   Watermark file vertical margin.
   *
   * @return $this
   */
  public function setVerticalMargin($verticalMargin);
}
?>