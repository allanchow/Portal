<?php

namespace App\Model\Cdn;

use App\BaseModel;

class CdnDailyReport extends BaseModel
{
    protected $table = 'cdn_daily_report';
    protected $fillable = ['resource_id', 'report_date', 'http_byte', 'https_byte', 'total_byte', 'total_req'];
}
