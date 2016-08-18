<?php

/**
 * @file
 * Contains \Drupal\blazy\Dejavu\BlazyVideoTrait.
 */

namespace Drupal\blazy\Dejavu;

use Drupal\Core\Url;

/**
 * A Trait common for Video embed field integration.
 */
trait BlazyVideoTrait {

  /**
   * Builds relevant video embed field settings based on the given media url.
   */
  public function buildVideo(array &$settings = [], $media_url) {
    /** @var \Drupal\video_embed_field\ProviderManagerInterface $provider */
    $provider    = $this->providerManager->loadProviderFromInput($media_url);
    $definitions = $this->providerManager->loadDefinitionFromInput($media_url);

    // @todo extract URL from the SRC of final rendered TWIG instead.
    $render  = $provider->renderEmbedCode(640, 360, '0');
    $old_url = isset($render['#attributes']) && isset($render['#attributes']['src']) ? $render['#attributes']['src'] : '';
    $url     = isset($render['#url']) ? $render['#url'] : $old_url;
    $query   = isset($render['#query']) ? $render['#query'] : [];

    // Prevents complication by now.
    unset($query['autoplay'], $query['auto_play']);

    $settings['video_id']  = $provider::getIdFromInput($media_url);
    $settings['embed_url'] = Url::fromUri($url, ['query' => $query])->toString();
    $settings['scheme']    = $definitions['id'];
    $settings['uri']       = $provider->getLocalThumbnailUri();
    $settings['image_url'] = file_create_url($settings['uri']);
    $settings['type']      = 'video';

    // No file API with unmanaged VEF image without image_style.
    if (empty($settings['image_style']) && !empty($settings['image_url'])) {
      list($settings['width'], $settings['height']) = getimagesize($settings['image_url']);
    }
  }

}
