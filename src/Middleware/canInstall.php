<?php

namespace Wikigods\LaravelInstaller\Middleware;

use Closure;

class canInstall
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->alreadyInstalled()) {
            $route = $request->route();
            $routeName = is_object($route) && method_exists($route, 'getName')
                ? $route->getName()
                : '';

            // Allow the one-time completion response for the session that installed the app.
            if ($routeName === 'LaravelInstaller::final'
                && $request->session()->get('installer.completed', false)) {
                return $next($request);
            }

            $installedRedirect = config('installer.installedAlreadyAction');

            switch ($installedRedirect) {
                case 'route':
                    $routeName = config('installer.installed.redirectOptions.route.name');
                    $data = config('installer.installed.redirectOptions.route.message');

                    return redirect()->route($routeName)->with(['data' => $data]);
                    break;

                case 'abort':
                    abort(config('installer.installed.redirectOptions.abort.type'));
                    break;

                case 'dump':
                    $dump = config('installer.installed.redirectOptions.dump.data');
                    dd($dump);
                    break;

                case '404':
                case 'default':
                default:
                    abort(404);
                    break;
            }
        }

        return $next($request);
    }

    /**
     * If application is already installed.
     *
     * @return bool
     */
    public function alreadyInstalled()
    {
        return file_exists(storage_path('installed'));
    }
}
