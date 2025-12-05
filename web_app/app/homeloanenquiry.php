<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class homeloanenquiry extends Model
{
    protected $table = 'homeloanenquiry';

    const CREATED_AT = "createdat";
    const UPDATED_AT = "updatedat";
}
