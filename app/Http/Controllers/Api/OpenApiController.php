<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class OpenApiController extends Controller
{
    public function show(): Response
    {
        $contents = file_get_contents(resource_path('openapi/openapi.yaml'));

        return response((string) $contents, 200, [
            'Content-Type' => 'application/yaml',
        ]);
    }
}
