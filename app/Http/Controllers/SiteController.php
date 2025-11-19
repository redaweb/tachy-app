<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SiteController extends Controller
{
    /**
     * Définir le site dans la session
     */
    public function setSite(Request $request)
    {
        $request->validate([
            'site' => 'required|string|in:ALG,ORN,CST,SBA,ORG,STF,MGM'
        ]);

        Session::put('site', $request->site);

        return redirect()->back()->with('success', 'Site changé avec succès');
    }
}

