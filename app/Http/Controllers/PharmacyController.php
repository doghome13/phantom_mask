<?php

namespace App\Http\Controllers;

use App\Models\Pharmacies;
use App\Models\Products;
use App\Validators\PharmacyValidator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PharmacyController extends Controller
{
    /**
     * List all pharmacies that are open at a certain time, and on a day of the week if requested
     *
     * @return json
     */
    public function getPharmaciesByPeriod(Request $request)
    {
        PharmacyValidator::checkByPeriod($request->all());

        $date = $request->get('date') ?? 0;
        $time = $request->get('time');

        // 轉換日期為星期
        if ($date) {
            $date = Carbon::parse($date)->toDayDateTimeString();
            $date = substr($date, 0, 3);
        }

        $query = Pharmacies::select([
            'id',
            'name',
        ])
            ->whereIn('id', function ($sub) use ($time, $date) {
                $sub = $sub->select('pharmacy_id')
                    ->from('opening_hours');

                if ($date) {
                    $sub->where('week_day', $date);
                }

                return $sub->where('start_at', '<=', $time)
                    ->where('end_at', '>=', $time);
            })
            ->with(['openingHours' => function ($sub) {
                return $sub->select(['pharmacy_id', 'week_day', 'start_at', 'end_at']);
            }])
            ->get()
            ->toArray();

        return [
            'data' => $query,
        ];
    }

    /**
     * List all masks that are sold by a given pharmacy, sorted by mask name or mask price
     *
     * @return json
     */
    public function getMasksByPharmacy(Request $request)
    {
        PharmacyValidator::checkByPharmacy($request->all());

        $pharmacyId = $request->get('pharmacyId');
        $sorts      = $request->get('sorts') ?? ['name,asc'];

        $query = Products::select([
            'masks.name',
            'products.unit',
            'products.price',
        ])
            ->where('products.pharmacy_id', $pharmacyId)
            ->join('masks', 'masks.id', '=', 'products.mask_id');

        foreach ($sorts as $sort) {
            list($column, $val) = explode(',', $sort);
            $query->orderBy("masks.{$column}", $val);
        }

        $query = $query->get()
            ->toArray();

        return [
            'data' => $query,
        ];
    }

    /**
     * get all pharmacies id and name
     *
     * @return json
     */
    public function getPharmacies()
    {
        $query = Pharmacies::select(['id', 'name'])
            ->get()
            ->toArray();

        return [
            'data' => $query,
        ];
    }

    /**
     * List all pharmacies that have more or less than x mask products within a price range
     *
     * @return json
     */
    public function getPharmaciesByPrice(Request $request)
    {
        PharmacyValidator::checkByPrice($request->all());

        $min = $request->get('min');
        $max = $request->get('max');

        $query = Pharmacies::select(['id', 'name'])
            ->whereIn('id', function ($sub) use ($min, $max) {
                return $sub->select('pharmacy_id')
                    ->from('products')
                    ->whereBetween('price', [$min, $max]);
            })
            ->with(['products' => function ($sub) use ($min, $max) {
                // counting x mask products
                return $sub->select(['id', 'pharmacy_id'])
                    ->whereBetween('price', [$min, $max]);
            }])
            ->get()
            ->toArray();

        return [
            'data' => $query,
        ];
    }

    /**
     * Search for pharmacies or masks by name, ranked by relevance to search term
     *
     * @return json
     */
    public function searchByName(Request $request)
    {
        PharmacyValidator::checkSearch($request->all());

        $keyword = $request->get('keyword');
        $keyword = trim($keyword);
        $keyword = str_replace(' ', '* ', $keyword) . '*';

        $sql = "SELECT `t1`.*
            FROM ( SELECT `name`, NULL AS `color`, MATCH(`name`) AGAINST('{$keyword}' IN BOOLEAN MODE) AS `score` FROM `pharmacies` UNION SELECT `name`, `color`, MATCH(`name`) AGAINST('{$keyword}' IN BOOLEAN MODE) AS `score` FROM `masks` ) AS `t1`
            WHERE `t1`.`score` > 0.001
            ORDER BY `t1`.`score` DESC";
        $query = DB::select($sql);

        return [
            'data' => $query,
        ];
    }

    /**
     * get products id and name
     *
     * @return json
     */
    public function getProducts(Request $request)
    {
        $pharmacyId = $request->get('pharmacyId') ?? 0;

        $query = Products::select([
            'products.id',
            'products.price',
            'masks.name',
            'masks.color',
        ])
            ->join('masks', 'masks.id', '=', 'products.mask_id');

        if ($pharmacyId) {
            $query->where('pharmacy_id', $pharmacyId);
        }

        $query = $query->get()->toArray();

        return [
            'data' => $query,
        ];
    }
}
