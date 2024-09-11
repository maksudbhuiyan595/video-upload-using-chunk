<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function upload(Request $request)
    {
        $fileName = $request->fileName; // Get the filename
        $chunkNumber = $request->chunkNumber; // Get the chunk number
        $totalChunks = $request->totalChunks; // Total number of chunks

        // Create temporary directory for chunks if not exists
        $tempDir = storage_path('app/temp_chunks');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        // Store the current chunk
        $chunkPath = $tempDir . '/' . $fileName . '.part' . $chunkNumber;
        file_put_contents($chunkPath, file_get_contents($request->file('file')->getRealPath()));

        // If it's the first chunk, create a video record in the database
        if ($chunkNumber == 1) {
            Video::create([
                'file_name' => $fileName,
                'file_size' => $request->file('file')->getSize(),
                'status' => 'uploading',
            ]);
        }

        // If it's the last chunk, merge the chunks and update the database
        if ($chunkNumber == $totalChunks) {
            $completePath = storage_path('app/public/videos/' . $fileName);

            // Create the directory if it does not exist
            if (!is_dir(dirname($completePath))) {
                mkdir(dirname($completePath), 0777, true);
            }

            if (!file_exists($completePath)) {
                $outputFile = fopen($completePath, 'wb');
                for ($i = 1; $i <= $totalChunks; $i++) {
                    $chunkPath = $tempDir . '/' . $fileName . '.part' . $i;
                    if (file_exists($chunkPath)) {
                        fwrite($outputFile, file_get_contents($chunkPath));
                        unlink($chunkPath); // Remove chunk after merging
                    }
                }
                fclose($outputFile);

                // Update video record in the database
                $video = Video::where('file_name', $fileName)->first();
                if ($video) {
                    $video->file_path = 'videos/' . $fileName;
                    $video->status = 'completed';
                    $video->save();
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
    public function show($id)
    {
        $video = Video::findOrFail($id);

        return view('show-video', compact('video'));
    }
}
