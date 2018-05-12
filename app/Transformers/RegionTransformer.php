<?php
/**
 * Created by PhpStorm.
 * User: fathur
 * Date: 12/05/18
 * Time: 19.45
 */

namespace App\Transformers;


use League\Fractal\TransformerAbstract;

class RegionTransformer extends TransformerAbstract
{
    public function transform($region)
    {
        return [
            'id' => $region->id,
            'name' => $region->name,
            'postal_code' => $region->postal_code ?? ""
        ];
    }
}