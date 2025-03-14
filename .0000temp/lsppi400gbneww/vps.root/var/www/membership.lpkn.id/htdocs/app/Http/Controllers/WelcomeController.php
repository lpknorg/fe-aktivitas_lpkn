<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Member\ProfileController;
use App\Models\Artikel\Artikel;

class WelcomeController extends Controller
{
    public function welcome(Request $request)
    {
        if (!session()->has('api_event_welcome')) {
            $url = env('API_EVENT').'member/event/welcome';
            $p = new ProfileController();
            $dataa2 = $p->getRespApi($url);
            session(['api_event_welcome' => $dataa2]);
        }        
        $event = session('api_event_welcome');
        $artikel = Artikel::limit(10)->whereIn('status_id', [1, 6])->latest()->get();
        return view('Frontend/index', compact('event', 'artikel'));
    }
}
