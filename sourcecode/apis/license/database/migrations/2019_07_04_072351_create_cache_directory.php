<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Migrations\Migration;

class CreateCacheDirectory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // The upgrade to Lumen 5.5 demands a 'storage/framework/cache' directory
        // or the 'cache:clear' command will fail on deployment.

        // Attempt to create the 'storage/framework/cache' directory and correctly set permissions / owner / (group).
        // If anything goes wrong, attempt to clean up and throw an exception so the migration fails.

        $viewsPath = storage_path('framework/views');
        $cachePath = storage_path('framework/cache');

        try {
            if ($this->allFunctionsExist()) {
                if (!is_dir($cachePath)) {
                    if (mkdir($cachePath, 0775, true)) {
                        Log::debug(__METHOD__ . ": Created '$cachePath' directory.");

                        clearstatcache(true);

                        $viewsOwner = posix_getpwuid(fileowner($viewsPath))['name'];
                        $viewsGroup = posix_getgrgid(filegroup($viewsPath))['name'];

                        $cacheOwner = posix_getpwuid(fileowner($cachePath))['name'];
                        $cacheGroup = posix_getgrgid(filegroup($cachePath))['name'];

                        $cachePermissions = substr(sprintf('%o', fileperms($cachePath)), -4);

                        if ($cachePermissions !== '0775') {
                            chmod($cachePath, 0775);
                            clearstatcache(true, $cachePath);
                            $newCachePermissions = substr(sprintf('%o', fileperms($cachePath)), -4);
                            Log::debug(__METHOD__ . ": Changed permissions '$cachePath' from '$cachePermissions' to '$newCachePermissions'.");
                        }

                        if ($viewsOwner !== $cacheOwner) {
                            chown($cachePath, $viewsOwner);
                            Log::debug(__METHOD__ . ": Changed owner of '$cachePath' from '$cacheOwner' to '$viewsOwner'.");
                        }
/*
                        if ($viewsGroup !== $cacheGroup) {
                            // Make sure the user running this migration is root or a member of the group the views directory has or it _will_ fail.
                            chgrp($cachePath, $viewsGroup);
                            Log::debug(__METHOD__ . ": Changed group of '$cachePath' from '$cacheGroup' to '$viewsGroup'.");
                        }
*/
                    } else {
                        throw new ErrorException(__METHOD__ . ": Unable to create '$cachePath' directory.");
                    }
                }

            } else {
                throw new ErrorException(__METHOD__ . ": Unable to create '$cachePath' directory. Functions not available.");
            }

        } catch (\Throwable $t) {
            // Clean up directory if it exists
            Log::debug(__METHOD__ . ": Attempting clean up.");

            if (is_dir($cachePath)) {
                Log::debug(__METHOD__ . ": Trying to delete '$cachePath'.");
                if (rmdir($cachePath)) {
                    Log::debug(__METHOD__ . ": Deleted '$cachePath'.");
                } else {
                    Log::debug(__METHOD__ . ": Failed to delete '$cachePath'.");
                }
            }

            print($t->getTraceAsString());

            throw $t;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No takebacks!
    }

    public function allFunctionsExist()
    {
        return function_exists('is_dir')
            && function_exists('mkdir')
            && function_exists('fileowner')
            && function_exists('filegroup')
            && function_exists('posix_getpwuid')
            && function_exists('posix_getgrgid')
            && function_exists('chown')
            && function_exists('chgrp')
            && function_exists('chmod')
            && function_exists('clearstatcache')
            && function_exists('fileperms');
    }
}
