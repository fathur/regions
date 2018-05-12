<?php

namespace App\Console\Commands;

use App\Model\Region;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Readers\LaravelExcelReader;

class FillRegions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'region:fill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill the regions table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $indonesia = Region::create([
            'name' => 'Indonesia',
            'level' => 0
        ]);

        \Excel::batch('resources/docs/regions', function (LaravelExcelReader $rows, $file) use ($indonesia){

            $rows->each(function ($row) use ($indonesia) {

                $province = $row->province;
                $type = $row->citykab;
                $kabupaten = $row->citykab_name;
                $kecamatan = $row->kecsubdistrict;
                $kelurahan = $row->kelurahanvillage;
                $postal_code = $row->postal_code;


                $pv = $this->loadProvince($indonesia, $province);

                $kb = $this->loadKabupaten($pv, "{$type} {$kabupaten}");

                $kc = $this->loadKecamatan($kb, $kecamatan, $postal_code);

                $ds = $this->loadDesa($kc, $kelurahan);
            });
        });
    }

    /**
     * @param $province
     * @return mixed
     */
    public function loadProvince($country, $province)
    {
        return \Cache::remember('region:' . str_slug($country->name . $province), Carbon::now()->addDay(), function () use ($country, $province) {
            $pv = Region::where('name', '=', $province)
                ->where('parent_id', $country->id)
                ->first();

            if (!$pv) {
                $pv = $country->children()->create([
                    'name' => $province,
                    'level' => 1
                ]);

                $this->info('Created ' . $province);
            }

            return $pv;
        });

    }

    public function loadKabupaten($province, $kabupaten)
    {
        return \Cache::remember('region:' . str_slug($province->name . $kabupaten), Carbon::now()->addDay(), function () use ($province, $kabupaten) {
            $kb = Region::where('name', '=', $kabupaten)
                ->where('parent_id', $province->id)
                ->first();

            if (!$kb) {
                $kb = $province->children()->create([
                    'name' => $kabupaten,
                    'level' => 2
                ]);

                $this->info('Created ' . $kabupaten);
            }

            return $kb;
        });


    }

    public function loadKecamatan($kabupaten, $kecamatan, $postalCode)
    {
        return \Cache::remember('region:' . str_slug($kabupaten->name . $kecamatan), Carbon::now()->addDay(), function () use ($kabupaten, $kecamatan, $postalCode) {
            $kc = Region::where('name', '=', $kecamatan)
                ->where('parent_id', $kabupaten->id)
                ->first();

            if (!$kc) {
                $kc = $kabupaten->children()->create([
                    'name' => $kecamatan,
                    'postal_code' => $postalCode,
                    'level' => 3
                ]);

                $this->info('Created kecamatan ' . $kecamatan);
            }

            return $kc;
        });
    }

    public function loadDesa($kecamatan, $kelurahan)
    {
        return \Cache::remember('region:' . str_slug($kecamatan->name . $kecamatan->postal_code . $kelurahan), Carbon::now()->addDay(), function () use ($kecamatan, $kelurahan) {
            $kb = Region::where('name', '=', $kelurahan)
                ->where('parent_id', $kecamatan->id)
                ->first();

            if (!$kb) {
                $kb = $kecamatan->children()->create([
                    'name' => $kelurahan,
                    'level' => 4
                ]);

                $this->info('Created desa/kelurahan ' . $kelurahan);
            }

            return $kb;
        });
    }

}
