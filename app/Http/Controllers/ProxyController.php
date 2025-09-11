<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProxyController extends Controller
{
    public function getLlmFileContent(Request $request)
    {
        $shop = $request->shop;
        if (!$shop) {
            return response('Shop not found', 404);
        }

        $folderName = $shop;
        $filePath = $folderName . '/llms.txt';
        $content = Storage::disk('public')->get($filePath);
        return response("<pre>$content</pre>", 200, headers: [
            'Content-Type' => 'text/html; charset=UTF-8'
        ]);
    }
}
