<?php

namespace Wikigods\LaravelInstaller\Controllers;

use Illuminate\Routing\Controller;
use Wikigods\LaravelInstaller\Events\LaravelInstallerFinished;
use Wikigods\LaravelInstaller\Helpers\EnvironmentManager;
use Wikigods\LaravelInstaller\Helpers\FinalInstallManager;
use Wikigods\LaravelInstaller\Helpers\InstalledFileManager;

class FinalController extends Controller
{
    /**
     * Update installed file and display finished view.
     *
     * @param  \Wikigods\LaravelInstaller\Helpers\InstalledFileManager  $fileManager
     * @param  \Wikigods\LaravelInstaller\Helpers\FinalInstallManager  $finalInstall
     * @param  \Wikigods\LaravelInstaller\Helpers\EnvironmentManager  $environment
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function finish(InstalledFileManager $fileManager, FinalInstallManager $finalInstall, EnvironmentManager $environment)
    {
        $finalMessages = $finalInstall->runFinal();
        $finalStatusMessage = $fileManager->update();
        $finalEnvFile = $environment->getEnvContent();

        event(new LaravelInstallerFinished);

        return view('vendor.installer.finished', compact('finalMessages', 'finalStatusMessage', 'finalEnvFile'));
    }
}
