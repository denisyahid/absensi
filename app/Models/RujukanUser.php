<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RujukanUser extends Pivot
{
    protected $table = 'rujukan_user';

    public function pns()
    {
        return $this->belongsTo(Pns::class, 'pns_id');
    }
}
