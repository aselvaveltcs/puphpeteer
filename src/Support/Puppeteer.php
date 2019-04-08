<?php

namespace Nesk\Puphpeteer\Support;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Puppeteer
{
    /**
     * @var string
     */
    protected $nodeExecutablePath;

    /**
     * @var string
     */
    protected $npmManifestPath;

    /**
     * @var string
     */
    protected $puppeteerDirPath;

    public function __construct(string $nodeExecutablePath, string $npmManifestPath)
    {
        $this->nodeExecutablePath = $nodeExecutablePath;
        $this->npmManifestPath = $npmManifestPath;
        $this->puppeteerDirPath = dirname($npmManifestPath).'/';
    }

    public function currentPuppeteerVersion(): ?string {
        $script = "process.stdout.write(require('puppeteer/package.json').version)";
        $process = new Process([$this->nodeExecutablePath, '-e', $script]);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            if (strpos($process->getErrorOutput(), "Error: Cannot find module 'puppeteer/package.json'") !== false) {
                throw new \RuntimeException("Puppeteer doesn't seem to be installed.");
            }

            throw $exception;
        }

        return $process->getOutput();
    }

    public function acceptedPuppeteerVersion(): string {
        $npmManifest = json_decode(file_get_contents($this->npmManifestPath));

        return $npmManifest->dependencies->puppeteer;
    }
}
