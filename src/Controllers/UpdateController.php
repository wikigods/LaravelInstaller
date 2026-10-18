<?php

namespace Wikigods\LaravelInstaller\Controllers;

use Illuminate\Routing\Controller;
use Wikigods\LaravelInstaller\Helpers\DatabaseManager;
use Wikigods\LaravelInstaller\Helpers\InstalledFileManager;

class UpdateController extends Controller
{
    use \Wikigods\LaravelInstaller\Helpers\MigrationsHelper;

    /**
     * Display the updater welcome page.
     *
     * @return \Illuminate\View\View
     */
    public function welcome()
    {
        return view('vendor.installer.update.welcome');
    }

    /**
     * Display the updater overview page.
     *
     * @return \Illuminate\View\View
     */
    public function overview()
    {
        return view('vendor.installer.update.overview', [
            'numberOfUpdatesPending' => count($this->getPendingMigrations()),
        ]);
    }

    /**
     * Show the updater database confirmation page.
     *
     * @return \Illuminate\View\View
     */
    public function database()
    {
        return view('vendor.installer.update.database-confirm');
    }

    /**
     * Run pending migrations after an authorized POST.
     *
     * @param  InstalledFileManager  $fileManager
     * @return \Illuminate\Http\RedirectResponse
     */
    public function run(InstalledFileManager $fileManager)
    {
        $databaseManager = new DatabaseManager;
        $response = $databaseManager->migrateAndSeed();

        if (($response['status'] ?? null) !== 'success') {
            return redirect()->route('LaravelUpdater::database')->with(['message' => $response]);
        }

        $fileManager->update();
        session()->put('updater.final_data', [
            'updateMessage' => trans('installer_messages.updater.final.finished'),
        ]);
        session()->put('updater.completed', true);

        return redirect()->route('LaravelUpdater::final');
    }

    /**
     * Display the one-time updater result.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function finish(\Illuminate\Http\Request $request)
    {
        if (! $request->session()->pull('updater.completed', false)) {
            abort(404);
        }

        $finalData = $request->session()->pull('updater.final_data', []);

        return view('vendor.installer.update.finished', $finalData);
    }
}
