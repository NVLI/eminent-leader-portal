<?php

namespace Drupal\blazy\Dejavu;

use Drupal\Core\Url;

/**
 * A Trait common for Video embed field integration.
 */
trait BlazyVideoTrait {

  /**
   * Builds relevant video embed field settings based on the given media url.
   */
  public function buildVideo(array &$settings = [], $external_url, $provider_manager = NULL) {
    /** @var \Drupal\video_embed_field\ProviderManagerInterface $provider */
    if ($provider_manager) {
      $provider    = $provider_manager->loadProviderFromInput($external_url);
      $definitions = $provider_manager->loadDefinitionFromInput($external_url);
    }
    else {
      // @todo drop for Beta > 4 so this method can be used without DI such as
      // by Slick Browser with a combination of different entities.
      $provider    = $this->providerManager->loadProviderFromInput($external_url);
      $definitions = $this->providerManager->loadDefinitionFromInput($external_url);
    }

    // Ensures thumbnail is available.
    $provider->downloadThumbnail();

    // @todo extract URL from the SRC of final rendered TWIG instead.
    $render  = $provider->renderEmbedCode(640, 360, '0');
    $old_url = isset($render['#attributes']) && isset($render['#attributes']['src']) ? $render['#attributes']['src'] : '';
    $url     = isset($render['#url']) ? $render['#url'] : $old_url;
    $query   = isset($render['#query']) ? $render['#query'] : [];

    // Prevents complication by now.
    unset($query['autoplay'], $query['auto_play']);

    $settings['video_id']  = $provider::getIdFromInput($external_url);
    $settings['embed_url'] = Url::fromUri($url, ['query' => $query])->toString();
    $settings['scheme']    = $definitions['id'];
    $settings['uri']       = $provider->getLocalThumbnailUri();
    $settings['type']      = 'video';
  }

}
