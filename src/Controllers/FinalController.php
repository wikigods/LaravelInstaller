<?php

namespace Wikigods\LaravelInstaller\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Wikigods\LaravelInstaller\Events\LaravelInstallerFinished;

class FinalController extends Controller
{
    /**
     * Display the one-time installation result without exposing environment values.
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function finish(Request $request)
    {
        if (! $request->session()->pull('installer.completed', false)) {
            abort(404);
        }

        $finalData = $request->session()->pull('installer.final_data', []);

        event(new LaravelInstallerFinished);

        return view('vendor.installer.finished', $finalData);
    }
}
