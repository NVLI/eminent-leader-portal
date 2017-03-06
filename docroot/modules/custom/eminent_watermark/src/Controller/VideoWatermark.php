<?php

namespace Drupal\eminent_admin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FFMpeg\FFMpeg;
use Drupal\Core\File\FileSystemInterface;

/**
 * VideoWatermark Class. Contains the methods for Video watermark Creation.
 */
class VideoWatermark extends ControllerBase {

  protected $entity_query;
  protected $entity_manager;
  protected $file_system;

  /**
   * {@inheritdoc}
   */
  public function __construct(QueryFactory $entity_query, EntityManagerInterface $entity_manager, FileSystemInterface $file_system) {
    $this->entity_query = $entity_query;
    $this->entity_manager = $entity_manager;
    $this->file_system =  $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('entity.manager'),
      $container->get('file_system')
    );
  }

  /**
   * Add Watermark to Video files.
   */
  public function generate_watermark() {
    $entity_type = 'media';
    $bundle = 'video';
    $query = $this->entity_query->get($entity_type)
      ->condition('bundle', $bundle);
    $nodeIds = $query->execute();
    if (!empty($nodeIds)) {
	  $node_storage = $this->entity_manager->getStorage('media');
	  $nodes = $node_storage->loadMultiple($nodeIds);
      if (!empty($nodes)) {
        foreach ($nodes as $node) {
		  $file_referance = $node->field_media_video->getValue();
		  if (!empty($file_referance)) {
		    $fid = $file_referance[0]['target_id'];
            $file_storage = $this->entity_manager->getStorage('file');
		    $file = $file_storage->load($fid);
		    if (!empty($file->getFileUri())) {
			  $files[] = $file->getFileUri();
			}
		  }
		}
		$file_names = fopen("public://archive/watermark-video-name.txt", "a+") or die("Unable to open file!");
		// Output one character until end-of-file
		$video_names = array();
		while(!feof($file_names)) {
		  $videos[] = trim(fgets($file_names));
		}
		fclose($file_names);
		// Create watermark
		$supporting_format = array('mpg', 'mpge', 'mp4');
		foreach ($files as $file) {
		  if (!in_array($file->filename, $videos)) {
			$ext = strtolower(pathinfo($file->filename, PATHINFO_EXTENSION));
			if (in_array($ext, $supporting_format)) {
			  $status = $this->create_watermark($file->uri, $file->filename);
			  $fh = fopen("public://archive/watermark-video-name.txt", "a+") or die("Unable to open file!");
			  fwrite($fh, $file->filename . "\n");
			  fclose($fh);
			}
		  }
		}
		$element = array(
		  '#markup' => 'Watermark Created Successfully ',
		);
        return $element;
	  }
    }
	$element = array(
		'#markup' => 'No Videos founds. ',
	);
    return $element;
  }

  /**
   * Create watermaek to Video.
   */
  public function create_watermark($uri, $file_name) {
    // Copy orignal file to another folder before create watermark.
	$public_path_backup = 'public://archive/video-backup/';
	file_prepare_directory($public_path_backup, FILE_CREATE_DIRECTORY);
	$original_video = file_get_contents($uri);
	$file_name = basename($uri);
	if ($original_video) {
	  $save_file = file_save_data($original_video, $public_path_backup . $file_name, FILE_EXISTS_RENAME);
	}
	$ffmpeg = \FFMpeg\FFMpeg::create([
		'ffmpeg.binaries'  => exec('which ffmpeg'),
		'ffprobe.binaries' => exec('which ffprobe'),
		'timeout'          => 3600, // the timeout for the underlying process
		'ffmpeg.threads'   => 1,   // the number of threads that FFMpeg should use
	], $logger);
	$video = $ffmpeg->open($uri);
	$video
		->filters()
		->watermark('sites/default/files/video_watermark.png', array(
			'position' => 'relative',
			'bottom' => 50,
			'right' => 50,
		));
	$video
       ->save(new \FFMpeg\Format\Video\WebM(),  $public_path_backup . $file_name);
	return 1;
  }
}
