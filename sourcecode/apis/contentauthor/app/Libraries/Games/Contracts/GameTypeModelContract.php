<?php
/**
 * Created by PhpStorm.
 * User: oddarne
 * Date: 31.07.18
 * Time: 17:11
 */

namespace App\Libraries\Games\Contracts;


interface GameTypeModelContract
{
    public function getAssets();
    public function getMachineFolder();
    public function getPublicFolder();
}
