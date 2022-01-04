<?php
namespace App\Http\Controllers;

use App\Libraries\H5P\H5Plugin;
use Illuminate\Support\Facades\DB;
use H5PStorage;
use Illuminate\Support\Facades\Session;

/**
 * H5P Plugin.
 *
 * @package   H5P
 * @author    Joubel <contact@joubel.com>
 * @license   MIT
 * @link      http://joubel.com
 * @copyright 2014 Joubel
 */

/**
 * Plugin admin class.
 *
 * TODO: Add development mode
 * TODO: Move results stuff to seperate class
 *
 * @package H5P_Plugin_Admin
 * @author Joubel <contact@joubel.com>
 */
class H5P_Plugin_Admin {

  /**
   * Instance of this class.
   *
   * @since 1.0.0
   * @var \H5P_Plugin_Admin
   */
  protected static $instance = NULL;

  /**
   * @since 1.1.0
   */
  private $plugin_slug = NULL;

  /**
   * Keep track of the current content.
   *
   * @since 1.0.0
   */
  private $content = NULL;

  /**
   * Keep track of the current library.
   *
   * @since 1.1.0
   */
  private $library = NULL;

  /**
   * Handle upload of new H5P content file.
   *
   * @since 1.1.0
   * @param array $content
   * @return boolean
   */
  public static function handle_upload($content = NULL, $only_upgrade = NULL) {
    $validator = resolve(\H5PValidator::class);
    $core = resolve(\H5PCore::class);
    $interface = $core->h5pF;

    // Make it possible to disable file extension check
    $core->disableFileCheck = (filter_input(INPUT_POST, 'h5p_disable_file_check', FILTER_VALIDATE_BOOLEAN) ? TRUE : FALSE);

    // Move so core can validate the file extension.
    rename($_FILES['h5p_file']['tmp_name'], $interface->getUploadedH5pPath());
    $skipContent = ($content === NULL);

    if ($validator->isValidPackage($skipContent, $only_upgrade) && ($skipContent || $content['title'] !== NULL)) {
      // KJØRER
      if (isset($content['id'])) {
        $interface->deleteLibraryUsage($content['id']);
        // KJØRER IKKE
      }

        /** @var H5PStorage $storage */
      $storage = resolve(H5PStorage::class);
      $storage->savePackage($content, NULL, $skipContent, $only_upgrade);
      return $storage->contentId;
    } else {
      self::flashErrorMessages($validator->h5pF->getErrorMessages());
    }
    // The uploaded file was not a valid H5P package
    @unlink($interface->getUploadedH5pPath());
    return FALSE;
  }

  public static function flashErrorMessages($errorMessages)
  {
    $markup = '<ul>' . collect($errorMessages)
            ->map(function ($message) {
              return sprintf("<li>%s</li>", $message);
            })->implode("") . '</ul>';
    Session::flash("invalidMessage", $markup);
  }

}
