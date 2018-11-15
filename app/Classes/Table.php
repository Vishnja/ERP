<?php
namespace App\Classes;


class Table
{
    public static function table($headColumns, $bodyRows){
        return
            "<table>" .
                "<thead>" .
                    static::head($headColumns) .
                "</thead>" .
                "<tbody>" .
                    static::body($bodyRows) .
                "</tbody>" .
            "</table>";
    }

    public static function head($columns) {
        $ret = '<tr>';

        foreach ($columns as $column){
            if ($column[0] == 'checkbox') {
                $ret .= '<th><input type="checkbox" class="cb-select-all"></th>';
            } else {
                $ret .= "<th>$column[1]</th>";
            }
        }

        $ret .= '</tr>';

        return $ret;
    }

    public static function body($data) {
        $ret = '';
        
        foreach ($data as $row) {
            $ret .= '<tr>';

            // $i is a hack,
            // it allows to use not only one-dimensional array
            // but sometimes assoc. array
            // with keys that are names of the column
            foreach ($row as $i => $col) {
                $ret .= "<td>$col</td>";
            }

            $ret .= '</tr>';
        }

        return $ret;
    }
}