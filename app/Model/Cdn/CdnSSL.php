<?php

namespace App\Model\Cdn;

use App\BaseModel;

class CdnSSL extends BaseModel
{
    protected $table = 'cdn_ssl';
    protected $fillable = ['resource_id', 'type', 'cert', 'key', 'status'];
    protected $primaryKey = 'resource_id';

    public function validate_cert($cert = null)
    {
        if (is_null($cert)) {
        	$cert = $this->cert;
        }
        return openssl_x509_parse($cert) !== false;
    }

    public function validate_key($key = null)
    {
        if (is_null($key)) {
        	$key = $this->key;
        }
        return openssl_pkey_get_private($key) !== false;
    }
}
