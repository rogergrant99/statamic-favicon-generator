<?php

namespace rogergrant99\FaviconGenerator\Http\Controllers\Cp;

use Illuminate\Http\Request;
use rogergrant99\FaviconGenerator\Blueprints\Favicons;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Facades\Asset;
use Illuminate\Support\Facades\Log;
use ZipArchive;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

final class FaviconController extends CpController
{
    protected $path;

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function index()
    {
        $blueprint = Favicons::blueprint();
        $data = Favicons::values();

        $fields = $blueprint->fields()->addValues($data)->preProcess();

        return view('favicons::cp.settings.index', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
        ]);
    }

    public function update(Request $request)
    {
        $blueprint = Favicons::blueprint();

        $fields = $blueprint->fields()->addValues($request->all());

        $fields->validate();

        File::put(config('statamic.favicons.path'), YAML::dump($fields->process()->values()->all()));

        // Generate favicons
        return $this->generate($request);
    }

public function generate(Request $request) {
    $apiKey = $request->input('api_key');
    $masterImage = $request->input('icon'); // Now it's just a URL string
    
    $apiUrl = 'https://realfavicongenerator.net/api/favicon';
    $filesLocationPath = '/' . Favicons::getAssetsContainer()->handle() . '/';

    $payload = config('statamic.favicons.payload');
    $payload['favicon_generation']['api_key'] = $apiKey;
    $payload['favicon_generation']['master_picture']['url'] = $masterImage;
    $payload['favicon_generation']['files_location']['path'] = $filesLocationPath;
    $payload['favicon_generation']['versioning']['param_value'] = Str::random(6);
    
    $response = Http::timeout(120)->post($apiUrl, $payload);


		if ($response->successful() && $response->json('favicon_generation_result.result.status') == 'success') {
            Log::debug('Favicon Generator: API call successful.', ['response' => $response->json()]);

			// Handle generated zip file
			$zipUrl = $response->json('favicon_generation_result.favicon.package_url');
            Log::debug('Favicon Generator: Zip URL', ['zipUrl' => $zipUrl]);

			$zipFile = sys_get_temp_dir() . '/favicons.zip';
            Log::debug('Favicon Generator: Temporary zip file path', ['zipFile' => $zipFile]);

            try {
                file_put_contents($zipFile, file_get_contents($zipUrl));
                Log::debug('Favicon Generator: Zip file downloaded successfully.');
            } catch (\Exception $e) {
                Log::error('Favicon Generator: Failed to download zip file.', ['error' => $e->getMessage()]);
                return response()->json([
                    'status' => 'error',
                    'msg' => 'Failed to download generated favicon package: ' . $e->getMessage()
                ], 200);
            }

			$zip = new ZipArchive;
            if ($zip->open($zipFile) === TRUE) {
                $faviconsDirectory = public_path($filesLocationPath);
                // Ensure the directory exists
                if (! File::isDirectory($faviconsDirectory)) {
                    File::makeDirectory($faviconsDirectory, 0755, true);
                }
                Log::debug('Favicon Generator: Favicons extraction directory', ['directory' => $faviconsDirectory]);

                try {
				    $zip->extractTo($faviconsDirectory);
				    $zip->close();
                    Log::debug('Favicon Generator: Favicons extracted successfully.');
                } catch (\Exception $e) {
                    Log::error('Favicon Generator: Failed to extract zip file.', ['error' => $e->getMessage()]);
                    return response()->json([
                        'status' => 'error',
                        'msg' => 'Failed to extract generated favicon package: ' . $e->getMessage()
                    ], 200);
                }

				unlink($zipFile);
                Log::debug('Favicon Generator: Temporary zip file deleted.');

			// Write new blueprint values
			$values = $request->all();

			$values['html_tags'] = $response->json('favicon_generation_result.favicon.html_code');
			$values['generated_at'] = now()->format('Y-m-d H:i:s');

			$blueprint = Favicons::blueprint();

			$fields = $blueprint->fields()->addValues($values);

			$fields->validate();

			File::put(config('statamic.favicons.path'), YAML::dump($fields->process()->values()->all()));
            Log::debug('Favicon Generator: Blueprint values saved to', ['path' => config('statamic.favicons.path'), 'values' => YAML::dump($fields->process()->values()->all())]);


			return response()->json([
				'status' => 'success',
				'msg' => 'Saved and generated'
			], 200);
            } else {
                Log::error('Favicon Generator: Failed to open zip file.', ['zipFile' => $zipFile]);
                return response()->json([
                    'status' => 'error',
                    'msg' => 'Failed to open generated favicon package.'
                ], 200);
            }
		} else {
			$apiResponse = $response->json();
            // Log the full response body for debugging, even if it's not valid JSON
            Log::error('RealFaviconGenerator API Error (raw body):', ['body' => $response->body()]);

            $errorMessage = 'An unknown error occurred with the RealFaviconGenerator API.';

            if (isset($apiResponse['favicon_generation_result']['result']['error_message'])) {
                $errorMessage = $apiResponse['favicon_generation_result']['result']['error_message'];
            } elseif (isset($apiResponse['favicon_generation_result']['result']['status'])) {
                $errorMessage = 'RealFaviconGenerator API Status: ' . $apiResponse['favicon_generation_result']['result']['status'];
            } elseif ($response->status() !== 200) {
                // This branch already includes the body, but let's make it consistent.
                $errorMessage = 'RealFaviconGenerator API returned HTTP status code: ' . $response->status() . '. Response body: ' . $response->body();
            }

            return response()->json([
                'status' => 'error',
                'msg' => $errorMessage
            ], 200);
		}
	}
}