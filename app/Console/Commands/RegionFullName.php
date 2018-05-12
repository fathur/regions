<?php
/**
 * Created by PhpStorm.
 * User: fathur
 * Date: 12/05/18
 * Time: 23.53
 */

namespace App\Console\Commands;


use App\Model\Region;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RegionFullName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'region:full';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Full name of region';

    /**
     *
     */
    public function handle()
    {
        Region::chunk(100, function ($regions) {
            foreach ($regions as $region) {
                switch ($region->level) {
                    case 1:
                        $country = $region->parent;
                        $fullName = "{$region->name}, {$country->name}";
                        break;
                    case 2:
                        $country = $region->parent->parent;
                        $province = $region->parent;
                        $fullName = "{$region->name}, {$province->name}, {$country->name}";

                        break;
                    case 3:
                        $country = $region->parent->parent->parent;
                        $province = $region->parent->parent;
                        $district = $region->parent;
                        $fullName = "{$region->name}, {$district->name}, {$province->name}, {$country->name}";

                        break;
                    case 4:
                        $country = $region->parent->parent->parent->parent;
                        $province = $region->parent->parent->parent;
                        $district = $region->parent->parent;
                        $subDistrict = $region->parent;
                        $fullName = "{$region->name}, {$subDistrict->name}, {$district->name}, {$province->name}, {$country->name}";

                        break;
                }

                if (isset($fullName)) {
                    \Cache::put("region:full@{$region->id}", $fullName, Carbon::now()->addMonth());
                    $this->info('Cached ' . $fullName);
                }

            }
        });
    }

}