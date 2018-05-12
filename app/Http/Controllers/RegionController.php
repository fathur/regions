<?php
/**
 * Created by PhpStorm.
 * User: fathur
 * Date: 12/05/18
 * Time: 17.35
 */

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Model\Region;
use App\Transformers\RegionTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class RegionController extends Controller
{
    protected $fractal;

    public function __construct()
    {
        $this->fractal = new Manager();

    }

    public function lists(Request $request)
    {
        if ($request->has('parent')) {

            $parentId = $request->get('parent');


            if ($request->has('q')) {
                $q = strtolower($request->get('q'));

                $regions = \Cache::remember("parent:{$parentId}-q:{$q}", Carbon::now()->addDay(), function() use ($parentId, $q) {

                    $parent = Region::find($parentId);

                    return $parent->children()
                        ->whereRaw("LOWER(name) LIKE '%{$q}%'")
                        ->orderBy('name', 'asc')
                        ->get();
                });



            } else {
                $parent = Region::find($parentId);

                $regions = $parent->children()
                    ->orderBy('name', 'asc')
                    ->get();;

            }
        } else {

            if ($request->has('q')) {
                $q = $request->get('q');

                $regions =  Region::whereIsRoot()
                    ->whereRaw("LOWER(name) LIKE '%{$q}%'")

                    ->get();

            } else {
                $regions = Region::whereIsRoot()->get();

            }
        }


        $resource = new Collection($regions, new RegionTransformer());
        $data = $this->fractal->createData($resource)->toArray();

        return response()->json($data);
    }

    public function view($id, Request $request)
    {
        $region = Region::find($id);
        $resource = new Item($region, new RegionTransformer());
        $data = $this->fractal->createData($resource)->toArray();

        return response()->json($data);
    }
}