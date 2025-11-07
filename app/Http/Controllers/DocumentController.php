<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    /**
     * Download a document file.
     * 
     * @param Document $document
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Document $document)
    {
        try {
            // Security check: Ensure the document path exists
            if (!Storage::disk('public')->exists($document->path)) {
                Log::warning('Document file not found on disk', [
                    'document_id' => $document->id,
                    'path' => $document->path,
                    'user_id' => Auth::id()
                ]);
                abort(404, 'Document file not found');
            }

            // Get the full path to the file
            $filePath = Storage::disk('public')->path($document->path);

            // Log the download
            Log::info('Document downloaded', [
                'document_id' => $document->id,
                'original_name' => $document->original_name,
                'user_id' => Auth::id()
            ]);

            // Return the file as a download response
            return response()->download($filePath, $document->original_name);

        } catch (\Exception $e) {
            Log::error('Error downloading document', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Error downloading document');
        }
    }

    /**
     * Delete a document.
     * 
     * @param Document $document
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Document $document)
    {
        try {
            Log::info('Attempting to delete document', [
                'document_id' => $document->id,
                'path' => $document->path,
                'user_id' => Auth::id()
            ]);

            // Delete the file from storage if it exists
            if (Storage::disk('public')->exists($document->path)) {
                Storage::disk('public')->delete($document->path);
                
                // Also try to delete thumbnail if it exists (for images)
                $ext = strtolower(pathinfo($document->path, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $thumbPath = dirname($document->path) . '/thumbs/' . basename($document->path);
                    if (Storage::disk('public')->exists($thumbPath)) {
                        Storage::disk('public')->delete($thumbPath);
                    }
                }
            }

            // Delete the database record
            $document->delete();

            Log::info('Document deleted successfully', [
                'document_id' => $document->id,
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('success', 'Document deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Error deleting document', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to delete document: ' . $e->getMessage());
        }
    }

    /**
     * Stream a document inline (for preview in browser).
     * 
     * @param Document $document
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function stream(Document $document)
    {
        try {
            // Security check: Ensure the document path exists
            if (!Storage::disk('public')->exists($document->path)) {
                abort(404, 'Document file not found');
            }

            // Get the full path to the file
            $filePath = Storage::disk('public')->path($document->path);

            // Log the stream
            Log::info('Document streamed', [
                'document_id' => $document->id,
                'original_name' => $document->original_name,
                'user_id' => Auth::id()
            ]);

            // Return the file as inline (display in browser)
            return response()->file($filePath, [
                'Content-Type' => $document->mime ?? 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . $document->original_name . '"'
            ]);

        } catch (\Exception $e) {
            Log::error('Error streaming document', [
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);
            abort(500, 'Error streaming document');
        }
    }
}
