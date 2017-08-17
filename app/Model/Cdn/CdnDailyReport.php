<?php

namespace App\Model\Cdn;

use App\BaseModel;

class CdnDailyReport extends BaseModel
{
    protected $table = 'cdn_daily_report';
    protected $fillable = ['report_date', 'resource_id', 'http_byte', 'https_byte', 'total_byte', 'total_req'];
    protected $primaryKey = ['report_date', 'resource_id'];
    public $incrementing = false;

    /**
    * Set the keys for a save update query.
    * This is a fix for tables with composite keys
    * TODO: Investigate this later on
    *
    * @param  \Illuminate\Database\Eloquent\Builder  $query
    * @return \Illuminate\Database\Eloquent\Builder
    */
    protected function setKeysForSaveQuery(\Illuminate\Database\Eloquent\Builder $query) {
        if (is_array($this->primaryKey)) {
            foreach ($this->primaryKey as $pk) {
                $query->where($pk, '=', $this->original[$pk]);
            }
            return $query;
        }else{
            return parent::setKeysForSaveQuery($query);
        }
    }
}
