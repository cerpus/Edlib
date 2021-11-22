<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ContentLock extends Model
{
    use HasFactory;

    const EXPIRES = 90; // seconds
    public $incrementing = false;
    protected $primaryKey = 'content_id';

    public static function notExpiredById($id)
    {
        if (!empty(config('feature.content-locking'))) {
            $lock = self::where('content_id', $id)->active()->get();
            return $lock->first();
        }

        return false;
    }

    public function unlock($id)
    {
        if (!empty(config('feature.content-locking'))) {
            self::where('content_id', $id)->delete();
        }
    }

    /**
     * @param $id
     * @return bool|ContentLock
     */
    public function hasLock($id)
    {
        if (!empty(config('feature.content-locking'))) {
            $lock = self::notExpiredById($id);
            if ($lock) {
                if (Session::get('authId') == $lock->auth_id) { // User owns lock
                    return false; // Let user who owns the lock edit content
                }
            }
            return $lock;
        }

        return false;
    }

    public function lock($id)
    {
        if (!empty(config('feature.content-locking'))) {
            self::unlock($id);
            $contentLock = new ContentLock();
            $contentLock->content_id = $id;
            $contentLock->auth_id = Session::get('authId');
            $contentLock->email = Session::get('email');
            $contentLock->name = Session::get('name');

            $contentLock->save();
        }
    }

    public function getEditor()
    {
        if($this->name){
            return $this->name;
        }
        if($this->email){
            return $this->email;
        }

        return "???";
    }

    public function scopeActive($query)
    {
       return $query->where('updated_at', '>', Carbon::now()->subSeconds(self::EXPIRES));
    }
}
