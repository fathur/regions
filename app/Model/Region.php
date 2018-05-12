<?php
namespace App\Model;


use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class Region extends Model
{
    use NodeTrait;

    protected $fillable = ['name', 'postal_code', 'level'];
}