<?php

namespace Viveksingh;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DynamicDatatable
{

    protected static $search_keys = array();
    protected static $orderable_keys = array();
    protected static $column_names = array();
    protected static $search_text = null;
    protected static $table;
    protected static $order_columns; //array [column => desc/asc]
    protected static $start = 0;
    protected static $length = 10;

    /**
     * @Dev Function initialize the global variables for datatable from request
     * @param Request $request
     * @return void
     */
    public static function initialize(Request $request)
    {
        Self::$start = $request->start;
        Self::$length = $request->length;
        Self::$search_text = !empty($request->search['value']) ? $request->search['value'] : null;

        foreach ($request->columns as $data) {
            if ($data['searchable'] === 'true') {
                Self::$search_keys[] = !empty($data['name']) ? $data['name'] : $data['data'];
            }
            if ($data['orderable'] === 'true') {
                Self::$orderable_keys[] = !empty($data['name']) ? $data['name'] : $data['data'];
            }
            if (!empty($data['name']) || !empty($data['data'])) {
                Self::$column_names[] = !empty($data['name']) ? $data['name'] : $data['data'];
            }
        }

        foreach ($request->order as $order) {
            if (count(Self::$column_names) > $order['column']) {
                Self::$order_columns[Self::$column_names[$order['column']]] = $order['dir']; //keys[column_name[id]] = [desc/asc]
            }
        }
    }

    /**
     * @Dev Function to get data from database using DB facade
     * @param Request $request
     * @param table_name $table_name (override's table name from request)
     * @return \Illuminate\Http\Response received from draw() function
     */
    public static function table(Request $request, $table_name = null)
    {
        Self::initialize($request);
        Self::$table = !empty($table_name) ? $table_name : $request->table_name;

        $query = DB::table(Self::$table)->select(Self::$column_names);

        if($request->has('joins')){
            foreach ($request->joins as $join){
                if(array_key_exists('type', $join) && $join['type'] == 'left')
                    $query->leftJoin($join['table'], $join['on'], '=', $join['to']);
                else if(array_key_exists('type', $join) && $join['type'] == 'right')
                    $query->rightJoin($join['table'], $join['on'], '=', $join['to']);
                else
                    $query->join($join['table'], $join['on'], '=', $join['to']);
            }
        }
        
        if(!empty(Self::$order_columns)){
            foreach (Self::$order_columns as $key => $value) {
                $query->orderBy($key, $value);
            }
        }

        if(!empty(Self::$search_text)) {
            foreach (Self::$search_keys as $key) {
                if(!empty($key))
                    $query->orWhere($key, 'like', "%".Self::$search_text."%");
            }
        }

        $total = $query->count();
        $fetchData = $query->when((Self::$length > 0), function ($query) {
            return $query->offset(Self::$start)->limit(Self::$length);
        })->get();

        return Self::draw($fetchData, $total);
    }

    /**
     * @Dev Function to draw datatables using Laravel Collection instance
     * @param Request $request
     * @param Collection collections instance of data
     * @return \Illuminate\Http\Response received from draw() function
     */
    public static function collection(Request $request, Collection $collection)
    {
        Self::initialize($request);
        $totalRecordsCount = 0;

        if (!empty(Self::$order_columns)) {
            foreach (Self::$order_columns as $key => $value) {
                if($value == 'asc')
                    $collection = $collection->sortBy($key);
                else
                    $collection = $collection->sortByDesc($key);
            }
        }

        $searched = false;
        if (!empty(Self::$search_text)) {
            $collection->filter(function ($value) use (&$filteredCollection) {
                array_walk_recursive($value , function ($element, $key) use (&$filteredCollection, $value) {
                    if (in_array($key, Self::$search_keys) && stripos($element, Self::$search_text) !== false) {
                        // push if key exists in search keys and value contains search text
                        $filteredCollection[] = (object)$value;
                    }
                });
            });
            $searched = true;
        }

        if($searched == false){
            $totalRecordsCount = $collection->count();
            $fetchData = $collection->when((Self::$length > 0), function ($query) {
                return $query->skip(Self::$start)->slice(0, Self::$length);
            })->values()->all();
        } else {
            $final = collect($filteredCollection)->unique();
            $totalRecordsCount = $final->count();
            $fetchData = $final->when((Self::$length > 0), function ($query) {
                return $query->skip(Self::$start)->slice(0, Self::$length);
            })->values()->all();
        }

        return Self::draw($fetchData, $totalRecordsCount);
    }

     /**
     * @Dev Function to return json response for datatable
     * @param data data to be returned
     * @param totalRecordsCount total records count
     * @return \Illuminate\Http\Response
     */
    public static function draw($data, $totalRecordsCount)
    {
        return response()->json([
            'data' => $data,
            'recordsTotal' => $totalRecordsCount,
            'recordsFiltered' => $totalRecordsCount,
        ]);
    }
}
