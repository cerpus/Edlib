<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourseExport extends Model
{
    public $incrementing = false;

    protected $guarded = [];

    protected $primaryKey = 'ndla_id';

    public static function byNdlaId($id)
    {
        $courseExport = self::where('ndla_id', $id)->first();
        if (!$courseExport) {
            $courseExport = self::create(['ndla_id' => $id]);
        }

        return $courseExport;
    }
}
