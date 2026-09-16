<?php

namespace App\Database\Firebird\Query\Grammars;

use HarryGulliford\Firebird\Query\Grammars\FirebirdGrammar as BaseFirebirdGrammar;
use Illuminate\Database\Query\Builder;

class FirebirdGrammar extends BaseFirebirdGrammar
{
    /**
     * Compile an insert and get ID statement into SQL.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $values
     * @param  string  $sequence
     * @return string
     */
    public function compileInsertGetId(Builder $query, $values, $sequence)
    {
        return $this->compileInsert($query, $values) . ' RETURNING ' . $this->wrap($sequence ?: 'id');
    }

    /**
     * Compile a date based where clause.
     *
     * @param  string  $type
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function dateBasedWhere($type, Builder $query, $where)
    {
        $value = $this->parameter($where['value']);
        $column = $this->wrap($where['column']);

        return match (strtolower($type)) {
            'date' => 'CAST('.$column.' AS DATE) '.$where['operator'].' '.$value,
            'time' => 'CAST('.$column.' AS TIME) '.$where['operator'].' '.$value,
            default => 'EXTRACT('.strtoupper($type).' FROM '.$column.') '.$where['operator'].' '.$value,
        };
    }

    /**
     * Get the format for database stored dates.
     *
     * Firebird expects timestamps in a specific format.
     *
     * @return string
     */
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }
}

