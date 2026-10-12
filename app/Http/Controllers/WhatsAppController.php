<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;

class WhatsAppController extends Controller
{
    public function status(WhatsAppService $wa)
    {
        return response()->json($wa->status());
    }

    public function logout(WhatsAppService $wa)
    {
        return response()->json(['ok' => $wa->logout()]);
    }
}
