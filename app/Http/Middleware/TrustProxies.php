<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Middleware\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Set to * untuk menerima semua proxy (ngrok termasuk)
     *
     * @var array|string|null
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * Gunakan default Laravel
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_AWS_ELB;
}
