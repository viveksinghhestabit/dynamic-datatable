<?php

namespace Viveksingh\DynamicDatatable;

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
        Self::$table = $table_name == null ? $table_name : $request->table_name;

        foreach ($request->columns as $data) {
            if ($data['searchable'] == 'true' && !empty($data['data'])) {
                Self::$search_keys[] = $data['data'];
            }
            if ($data['orderable'] == 'true' && !empty($data['data'])) {
                Self::$orderable_keys[] = $data['data'];
            }
            if (!empty($data['data'])) {
                Self::$column_names[] = $data['data'];
            }
        }

        foreach ($request->order as $order) {
            Self::$order_columns[Self::$column_names[$order['column']]] = $order['dir']; //keys[column_name[id]] = [desc/asc]
        }

        $users = DB::table(Self::$table)->select(Self::$column_names);
        foreach (Self::$order_columns as $key => $value) {
            $users->orderBy($key, $value);
        }

        if (!empty(Self::$search_text)) {
            foreach (Self::$search_keys as $key) {
                $users->orWhere($key, 'like', "%" . Self::$search_text . "%");
            }
        }
        $total = $users->count();
        $fetchData = $users->limit(Self::$length)->offset(Self::$start)->get();
        //Return data to datatable
        return response()->json([
            'data' => $fetchData,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
        ]);
    }
}
