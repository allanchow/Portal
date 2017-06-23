<?php

namespace App\Model\Cdn;

use App\BaseModel;

class CdnDailyReport extends BaseModel
{
    protected $table = 'cdn_daily_report';
    protected $fillable = ['report_date', 'resource_id', 'http_byte', 'https_byte', 'total_byte', 'total_req'];
    protected $primaryKey = ['report_date', 'resource_id'];
    public $incrementing = false;
}
