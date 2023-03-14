<?php

namespace Viveksingh;

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

    public static function table($request, $table_name = null)
    {
        //Set global variables for database
        Self::$start = $request->start;
        Self::$length = $request->length;
        Self::$search_text = !empty($request->search['value']) ? $request->search['value'] : null;
        Self::$table = !empty($table_name) ? $table_name : $request->table_name;

        foreach ($request->columns as $data){
            if($data['searchable'] == 'true' && !empty($data['data'])){
                Self::$search_keys[] = $data['data'];
            }
            if($data['orderable'] == 'true' && !empty($data['data'])){
                Self::$orderable_keys[] = $data['data'];
            }
            if(!empty($data['data'])){
                Self::$column_names[] = $data['data'];
            }
        }
        
        foreach ($request->order as $order){
            if(count(Self::$column_names) > $order['column']){
                Self::$order_columns[Self::$column_names[$order['column']]] = $order['dir']; //keys[column_name[id]] = [desc/asc]
            }
        }

        $users = DB::table(Self::$table);
        
        if(!empty(Self::$order_columns)){
            foreach (Self::$order_columns as $key => $value) {
                $users->orderBy($key, $value);
            }
        }

        if(!empty(Self::$search_text)) {
            foreach (Self::$search_keys as $key) {
                $users->orWhere($key, 'like', "%".Self::$search_text."%");
            }
        }
        $total = $users->count();
        $fetchData = $users->when((Self::$length > 0), function ($query) {
            return $query->offset(Self::$start)->limit(Self::$length);
        })->get();

        return response()->json([
            'data' => $fetchData,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
        ]);
    }
}
