<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class propertyimage extends Model
{
    protected $table = 'propertyimage';

    const CREATED_AT = "createdat";
    const UPDATED_AT = "updatedat";
}
