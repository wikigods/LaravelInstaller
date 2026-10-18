<?php

namespace Wikigods\LaravelInstaller\Controllers;

use Illuminate\Routing\Controller;
use Wikigods\LaravelInstaller\Helpers\DatabaseManager;
use Wikigods\LaravelInstaller\Helpers\FinalInstallManager;
use Wikigods\LaravelInstaller\Helpers\InstalledFileManager;

class DatabaseController extends Controller
{
    /**
     * @var DatabaseManager
     */
    private $databaseManager;

    /**
     * @param  DatabaseManager  $databaseManager
     */
    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * Show the database confirmation page.
     *
     * @return \Illuminate\View\View
     */
    public function database()
    {
        return view('vendor.installer.database-confirm');
    }

    /**
     * Run installation migrations after an authorized POST.
     *
     * @param  FinalInstallManager  $finalInstall
     * @param  InstalledFileManager  $fileManager
     * @return \Illuminate\Http\RedirectResponse
     */
    public function run(FinalInstallManager $finalInstall, InstalledFileManager $fileManager)
    {
        $response = $this->databaseManager->migrateAndSeed();

        if (($response['status'] ?? null) !== 'success') {
            return redirect()->route('LaravelInstaller::database')->with(['message' => $response]);
        }

        $finalMessages = $finalInstall->runFinal();
        $finalStatusMessage = $fileManager->update();

        session()->put('installer.final_data', [
            'databaseOutput' => $response['dbOutputLog'] ?? '',
            'finalMessages' => is_array($finalMessages) ? ($finalMessages['message'] ?? '') : $finalMessages,
            'finalStatusMessage' => $finalStatusMessage,
        ]);
        session()->put('installer.completed', true);

        return redirect()->route('LaravelInstaller::final');
    }
}
