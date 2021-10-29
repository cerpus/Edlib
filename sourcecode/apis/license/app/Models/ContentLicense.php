<?php
/**
 * Created by PhpStorm.
 * User: janespen
 * Date: 26.07.16
 * Time: 14:45
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentLicense extends Model
{
    protected $table = "content_license";

    protected $visible = ['name'];

    protected $appends = ['name'];

    public function getNameAttribute(){
        return $this->attributes['license_id'];
    }
}