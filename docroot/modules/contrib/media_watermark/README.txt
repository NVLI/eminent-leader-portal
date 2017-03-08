CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Installation
 * Usage



INTRODUCTION
------------

Current Maintainer: Bogdan Tur <bogdan.tur1988@gmail.com>

Media Watermark module give possibility to add watermarks to already uploaded files.
I should notice that watermark could be added only to
.png,.gif,.jpg,.jpeg image files, and watermarking file should be .png or .gif
with transparency.


INSTALLATION
------------

1. Copy media_watermark directory to modules directory.

2. Enable the media_watermark as any other drupal module.

3. Or via drush: drush en -y media_watermark.


USAGE
-----

1. To access media_watermark interface follow yoursite.com/admin/config page,
find Media modules settings and Media Watermark link
(yoursite.com/admin/config/media/media_watermark);
Also available config link from yoursite.com/admin/modules page.
Media Watermark Settings page gives ability to create, update and delete watermark entities.
Please see corresponding links in Operations column and Add Media Watermark link.

2. To add watermark follow this path yoursite.com/admin/config/media/media_watermark/add.

3. To apply watermark to any uploaded file, please visit Media Watermark Batch page
(yoursite.com/admin/config/media/media_watermark/batch). Select needed watermark file and files
to apply watermark and press Add watermark button.
