<?php

namespace Xgenious\Installer\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Xgenious\Installer\Helpers\InstallationHelper;

class InstallerMiddleware
{
    public function handle($request, Closure $next)
    {

        if (InstallationHelper::isInstallerNeeded() && !$request->is(['install','install/verify-purchase','install/check-database','install/check-database-exists'])) {
            return redirect('/install');
        }

        return $next($request);
    }
}
