<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $stats = [
            'templates' => \App\Models\Template::count(),
            'messages_sent' => \App\Models\Message::where('status', 'sent')->count(),
            'messages_pending' => \App\Models\Message::where('status', 'pending')->count(),
            'messages_failed' => \App\Models\Message::where('status', 'failed')->count(),
        ];
        return view('dashboard.index', compact('stats'));
    }
}
