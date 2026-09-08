<?php

namespace App\Http\Controllers;

use App\Services\WhitelistFileService;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WhitelistFileController extends Controller
{
    public function __construct(private readonly WhitelistFileService $files) {}

    public function index(string $directory): View|Response
    {
        if ($directory !== $this->files->webDirectoryName()) {
            abort(404);
        }

        return view('files.index', [
            'directory' => $directory,
            'files' => $this->files->files(),
        ]);
    }

    public function show(string $directory, string $filename): BinaryFileResponse
    {
        $path = $this->files->pathForWebFile($directory, $filename);

        return response()->file($path, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}