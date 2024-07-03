<?php

namespace Xgenious\Installer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Xgenious\Installer\Helpers\InstallationHelper;

class InstallerController extends Controller
{
    protected $installationHelper;

    public function __construct(InstallationHelper $installationHelper)
    {
        $this->installationHelper = $installationHelper;
    }

    public function index()
    {
        return view('installer::installer.index');
    }

    public function verifyPurchase(Request $request)
    {
        // Implement purchase verification logic
        return response()->json(['success' => true, 'message' => 'Purchase verified']);
    }

    public function checkDatabase(Request $request)
    {
        // Implement database connection check
        return response()->json(['success' => true, 'message' => 'Database connection successful']);
    }

    public function install(Request $request)
    {
        // Implement full installation process
        $this->installationHelper->createEnvFile($request->all());
        $this->installationHelper->updateDatabaseConfig($request->all());
        $this->installationHelper->migrateAndSeedDatabase();
        
        return response()->json(['success' => true, 'message' => 'Installation completed successfully']);
    }
}