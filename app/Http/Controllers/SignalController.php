<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\Request;

class SignalController extends Controller
{
    public function join(Request $request, Meeting $meeting)
    {
        return response()->json(['ok' => true]);
    }

    public function send(Request $request, Meeting $meeting)
    {
        return response()->json(['ok' => true]);
    }

    public function poll(Request $request, Meeting $meeting)
    {
        return response()->json(['signals' => []]);
    }

    public function leave(Request $request, Meeting $meeting)
    {
        return response()->json(['ok' => true]);
    }
}
