<?php

namespace Xgenious\Installer\Commands;


use Illuminate\Console\Command;

class RemoveMiddlewareCommand extends Command
{
    protected $signature = 'xgenious:remove-middleware {middleware}';
    protected $description = 'Remove a middleware from the web middleware group in Kernel.php';

    public function handle()
    {
        $middleware = $this->argument('middleware');
        $this->removeMiddlewareFromKernel($middleware);
    }

    private function removeMiddlewareFromKernel($middlewareToRemove)
    {
        $kernelPath = app_path('Http/Kernel.php');
        $content = file_get_contents($kernelPath);

        // Find the 'web' middleware group
        $pattern = '/protected\s+\$middlewareGroups\s*=\s*\[(.*?)\'web\'\s*=>\s*\[(.*?)\](.*?)\]/s';
        if (preg_match($pattern, $content, $matches)) {
            $webMiddleware = $matches[2];

            // Remove the specified middleware
            $updatedWebMiddleware = preg_replace('/\s*' . preg_quote($middlewareToRemove, '/') . '(::class)?,?/m', '', $webMiddleware);

            // Replace the old 'web' middleware group with the updated one
            $updatedContent = preg_replace($pattern, 'protected $middlewareGroups = [${1}\'web\' => [' . $updatedWebMiddleware . ']${3}]', $content);

            // Write the updated content back to the file
            file_put_contents($kernelPath, $updatedContent);

            $this->info("Middleware {$middlewareToRemove} removed successfully.");
        } else {
            $this->error("Could not find 'web' middleware group in Kernel.php");
        }
    }
}