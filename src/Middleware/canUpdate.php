<?php

namespace Wikigods\LaravelInstaller\Middleware;

use Closure;

class canUpdate
{
    use \Wikigods\LaravelInstaller\Helpers\MigrationsHelper;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $updateEnabled = filter_var(config('installer.updaterEnabled'), FILTER_VALIDATE_BOOLEAN);
        switch ($updateEnabled) {
            case true:
                $canInstall = new canInstall;

                // if the application has not been installed,
                // redirect to the installer
                if (! $canInstall->alreadyInstalled()) {
                    return redirect()->route('LaravelInstaller::welcome');
                }

                if ($this->alreadyUpdated()) {
                    abort(404);
                }
                break;

            case false:
            default:
                abort(404);
                break;
        }

        return $next($request);
    }

    /**
     * If application is already updated.
     *
     * @return bool
     */
    public function alreadyUpdated()
    {
        return count($this->getPendingMigrations()) === 0;
    }
}
