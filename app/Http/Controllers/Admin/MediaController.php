<?php

namespace App\Http\Controllers\Admin;


use App\Models\Media as Media;
use App\Models\StorageType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Imagick;
use Illuminate\Support\Facades\Validator;
use App\Services\DeletionService;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use SimpleXMLElement;
use Illuminate\Support\Facades\Config;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class MediaController extends Controller
{

    public function index(Request $request)
    {
        // dd($request);
        // dd('here');
        $store_id = !empty(request('store_id')) ? request('store_id') : getStoreId();
        $user_id = Auth::user()->id;

        $search = trim($request->input('search'));
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');
        $limit = $request->input('limit', 20);
        $offset = $request->input('offset', 0);

        $type = $request->input('type');

        // dd($type);
        // Create a base query to filter by search term (if provided)
        $query = Media::query();
        $query->where('store_id', $store_id);
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        if (isset($type) and $type != '') {
            $type = explode(",", $request->input('type'));
            $where_in = $type;
        }
        if (isset($where_in) && !empty($where_in)) {
            $query->whereIn("type", $where_in);
        }
        // dd($query->tosql());
        // $query->where("type", 'video');
        // Get the total count before applying pagination
        $total = $query->count();
        // dd($total);
        // Apply sorting and pagination to the query
        // $media = $query->orderBy($sort, $order)
        //     ->paginate($limit);
        // dd($total <= $limit);
        if ($total <= $limit) {
            $items = $query->orderBy($sort, $order)->get();
            $media = new LengthAwarePaginator(
                $items,
                $total,
                $limit,
                ($offset / $limit) + 1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } else {
            $media = $query->orderBy($sort, $order)->paginate($limit);
        }
        dd($media->total() > $media->perPage());

        if (request()->ajax()) {
            return view('admin.pages.forms.media', compact('media', 'total'));
        }
        return view('admin.pages.forms.media', compact('media', 'total'));
    }
    // public function upload(Request $request)
    // {

    //     try {
    //         $mediaIds = [];
    //         $store_id = !empty(request('store_id')) ? request('store_id') : getStoreId();
    //         $media_storage_settings = fetchDetails('storage_types', ['is_default' => 1], '*');

    //         $mediaStorageType = isset($media_storage_settings) && !empty($media_storage_settings) ? $media_storage_settings[0]->id : 1;
    //         $disk = isset($media_storage_settings) && !empty($media_storage_settings) ? $media_storage_settings[0]->name : 'public';
    //         $sub_directory = '/media';
    //         if ($request->hasFile('documents')) {
    //             $media = StorageType::find($mediaStorageType);
    //             $mediaFiles = $request->file('documents');
    //             // dd($mediaFiles);
    //             // upload compressed images
    //             foreach ($mediaFiles as $mediaFile) {
    //                 $media_url = '';
    //                 $file_extension = $mediaFile->getClientOriginalExtension();
    //                 $file_mime = $mediaFile->getClientMimeType();
    //                 $type = 'document';

    //                 // Generate a unique ID
    //                 $uniqueId = time() . '_' . mt_rand(1000, 9999);

    //                 // Determine the type based on the file MIME type
    //                 if (str_contains($file_mime, 'image')) {
    //                     $type = 'image';

    //                     // Compress the image using Intervention Image
    //                     $image = Image::make($mediaFile->getRealPath())
    //                         ->resize(1920, null, function ($constraint) {
    //                             $constraint->aspectRatio();
    //                             $constraint->upsize();
    //                         })
    //                         ->encode('jpg', 75); // Compress to 75% quality

    //                     // Save the compressed image to a temporary file
    //                     $compressedImagePath = tempnam(sys_get_temp_dir(), 'compressed_');
    //                     file_put_contents($compressedImagePath, $image);

    //                     // Use the original file name and add the unique ID
    //                     $originalFileName = pathinfo($mediaFile->getClientOriginalName(), PATHINFO_FILENAME);
    //                     $originalFileExtension = $mediaFile->getClientOriginalExtension();
    //                     $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $originalFileName));
    //                     $finalFileName = "{$sanitizedFileName}-{$uniqueId}.{$originalFileExtension}";

    //                     // Add the compressed image to the Spatie Media Library

    //                     $mediaItem = $media->addMedia($compressedImagePath)
    //                         ->sanitizingFileName(function () use ($finalFileName) {
    //                             return $finalFileName;
    //                         })
    //                         ->usingFileName($finalFileName)
    //                         ->withAttributes([
    //                             'store_id' => $store_id,
    //                             'extension' => $file_extension,
    //                             'sub_directory' => $sub_directory,
    //                             'type' => $type,
    //                             'name' => $sanitizedFileName,
    //                         ])
    //                         ->toMediaCollection('media', $disk);
    //                 } else {

    //                     // Handle non-image files normally

    //                     $originalFileName = pathinfo($mediaFile->getClientOriginalName(), PATHINFO_FILENAME);
    //                     $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $originalFileName));
    //                     $finalFileName = "{$sanitizedFileName}-{$uniqueId}.{$file_extension}";

    //                     $mediaItem = $media->addMedia($mediaFile)
    //                         ->sanitizingFileName(function () use ($finalFileName) {
    //                             return $finalFileName;
    //                         })
    //                         ->usingFileName($finalFileName)
    //                         ->withAttributes([
    //                             'store_id' => $store_id,
    //                             'extension' => $file_extension,
    //                             'sub_directory' => $sub_directory,
    //                             'type' => $type,
    //                         ])
    //                         ->toMediaCollection('media', $disk);
    //                 }

    //                 $mediaIds[] = $mediaItem->id;
    //             }

    //             // upload original images

    //             // foreach ($mediaFiles as $mediaFile) {

    //             //     $media_url = '';
    //             //     $file_extension = $mediaFile->getClientOriginalExtension();
    //             //     $file_mime = $mediaFile->getClientMimeType();

    //             //     $type = 'document';

    //             //     // Determine the type based on the file MIME type
    //             //     if (str_contains($file_mime, 'image')) {
    //             //         $type = 'image';
    //             //     } elseif (str_contains($file_mime, 'video')) {
    //             //         $type = 'video';
    //             //     } elseif (str_contains($file_mime, 'audio')) {
    //             //         $type = 'audio';
    //             //     }

    //             //     $mediaItem = $media->addMedia($mediaFile)
    //             //         ->sanitizingFileName(function ($fileName) use ($media) {
    //             //             // Replace special characters and spaces with hyphens
    //             //             $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));

    //             //             // Generate a unique identifier based on timestamp and random component
    //             //             $uniqueId = time() . '_' . mt_rand(1000, 9999);

    //             //             $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
    //             //             $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

    //             //             return "{$baseName}-{$uniqueId}.{$extension}";
    //             //         })
    //             //         ->withAttributes(['store_id' => $store_id, 'extension' => $file_extension, 'sub_directory' => $sub_directory, 'type' => $type])
    //             //         ->toMediaCollection('media', $disk);

    //             //     $mediaIds[] = $mediaItem->id;
    //             // }

    //             //code for storing s3 object url for media

    //             if ($disk == 's3') {
    //                 $media_list = $media->getMedia('media');
    //                 for ($i = 0; $i < count($mediaIds); $i++) {
    //                     $media_url = $media_list[($media_list->count()) - (count($mediaIds) - $i)]->getUrl();
    //                     updateDetails(['object_url' => $media_url], ['id' => $mediaIds[$i]], 'media');
    //                 }
    //             }

    //             return response()->json([
    //                 'error' => false,
    //                 'message' =>
    //                 labels('admin_labels.files_uploaded_successfully', 'Files Uploaded Successfully..!'),
    //             ]);
    //         } else {
    //             return response()->json([
    //                 'error' => true,
    //                 'error_message' =>
    //                 labels('admin_labels.files_not_found', 'Files not found !'),
    //             ]);
    //         }
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'error' => true,
    //             'message' => $e->getMessage(),
    //             'error_message' =>
    //             labels('admin_labels.select_at_least_one_media', 'Please Select At Least One Media.'),
    //         ]);
    //     }
    // }
    // original without compression

    public function upload(Request $request)
    {

        try {
            $mediaIds = [];
            $store_id = !empty(request('store_id')) ? request('store_id') : getStoreId();
            $media_storage_settings = fetchDetails('storage_types', ['is_default' => 1], '*');

            $mediaStorageType = isset($media_storage_settings) && !empty($media_storage_settings) ? $media_storage_settings[0]->id : 1;
            $disk = isset($media_storage_settings) && !empty($media_storage_settings) ? $media_storage_settings[0]->name : 'public';
            $sub_directory = '/media';
            if ($request->hasFile('documents')) {
                $media = StorageType::find($mediaStorageType);
                $mediaFiles = $request->file('documents');


                foreach ($mediaFiles as $mediaFile) {

                    $media_url = '';
                    $file_extension = $mediaFile->getClientOriginalExtension();
                    $file_mime = $mediaFile->getClientMimeType();

                    $type = 'document';

                    // Determine the type based on the file MIME type
                    if (str_contains($file_mime, 'image')) {
                        $type = 'image';
                    } elseif (str_contains($file_mime, 'video')) {
                        $type = 'video';
                    } elseif (str_contains($file_mime, 'audio')) {
                        $type = 'audio';
                    }

                    $mediaItem = $media->addMedia($mediaFile)
                        ->sanitizingFileName(function ($fileName) use ($media) {
                            // Replace special characters and spaces with hyphens
                            $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));

                            // Generate a unique identifier based on timestamp and random component
                            $uniqueId = time() . '_' . mt_rand(1000, 9999);

                            $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                            $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                            return "{$baseName}-{$uniqueId}.{$extension}";
                        })
                        ->withAttributes(['store_id' => $store_id, 'extension' => $file_extension, 'sub_directory' => $sub_directory, 'type' => $type])
                        ->toMediaCollection('media', $disk);

                    $mediaIds[] = $mediaItem->id;
                }

                //code for storing s3 object url for media

                if ($disk == 's3') {
                    $media_list = $media->getMedia('media');
                    for ($i = 0; $i < count($mediaIds); $i++) {
                        $media_url = $media_list[($media_list->count()) - (count($mediaIds) - $i)]->getUrl();
                        updateDetails(['object_url' => $media_url], ['id' => $mediaIds[$i]], 'media');
                    }
                }

                return response()->json([
                    'error' => false,
                    'message' =>
                    labels('admin_labels.files_uploaded_successfully', 'Files Uploaded Successfully..!'),
                ]);
            } else {
                return response()->json([
                    'error' => true,
                    'error_message' =>
                    labels('admin_labels.files_not_found', 'Files not found !'),
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'error_message' =>
                labels('admin_labels.select_at_least_one_media', 'Please Select At Least One Media.'),
            ]);
        }
    }

    public function list(Request $request)
    {

        $store_id = getStoreId();

        $search = trim($request->input('search'));
        $sort = $request->input('sort', 'id');
        $order = (request('order')) ? request('order') : "DESC";
        $limit = $request->input('limit', 10);
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $type = $request->input('type');

        // Create a base query to filter by search term (if provided)

        $query = Media::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });
        }
        if (isset($type) and $type != '') {

            $type = explode(",", $request->input('type'));
            $where_in = $type;
        }

        if (isset($where_in) && !empty($where_in)) {
            $query->whereIn("type", $where_in);
        }
        $query->where('store_id', $store_id);

        // Get the total count before applying pagination
        $total = $query->count();

        // Apply sorting and pagination to the query
        $media = $query->orderBy($sort, 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();

        // Transform the data for response
        $mediaData = $media->map(function ($m) {

            $isPublicDisk = $m->disk == 'public' ? 1 : 0;

            // Generate file URL based on disk visibility
            $fileUrl = $isPublicDisk
                ? asset('storage' . '/' . $m->sub_directory . '/' . $m->file_name)
                : $m->object_url;

            $delete_url = route('admin.media.destroy', $m->id);

            $action = '<div class="dropdown bootstrap-table-dropdown">
            <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="bx bx-dots-horizontal-rounded"></i>
            </a>
            <div class="dropdown-menu table_dropdown" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item copy-to-clipboard"><i class="fa-solid fa-copy"></i> Copy To Clipboard</a>
                <a class="dropdown-item copy-relative-path" ><i class="fa-solid fa-image"></i> Copy Image Path</a>
                <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i> Delete</a>
            </div>
        </div>';
            return [

                'id' => $m->id,
                'name' => $m->file_name,
                'image' => '<div class="image-container table-image"><a href="' . $fileUrl . '" data-lightbox="image-' . $m->id . '"><img src="' . $fileUrl . '" alt="Avatar" class="rounded"/>' .
                    "<span class='path d-none'>" . config('app.url') . 'storage' . '/' . $m->file_name . "</span>" .
                    "<span class='relative-path d-none'>" . '/' . $m->file_name . "</span></a></div>",
                'size' => $m->size,
                'extension' => $m->extension,
                'sub_directory' => $m->sub_directory,
                'disk' => $m->disk,
                'object_url' => $m->object_url,
                'operate' => $action,
            ];
        });

        return response()->json([
            'rows' => $mediaData,
            'total' => $total,
        ]);
    }
    public function destroy($id)
    {
        $media = Media::find($id);
        $disk = $media->disk;

        if ($media->delete()) {

            $path = 'media/' . $media->file_name; // Example path to the file you want to delete

            // Call the removeFile method to delete the file
            removeMediaFile($path, $disk);

            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.media_deleted_successfully', 'Media deleted successfully!')
            ]);
        } else {
            return response()->json(['error' => labels('admin_labels.something_went_wrong', 'Something went wrong')]);
        }
    }
    public function storage_type()
    {
        return view('admin.pages.forms.storage_types');
    }

    public function store_storage_type(Request $request)
    {
        $rules = [
            'name' => 'required|unique:storage_types',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        $storage_type = new StorageType();
        $storage_type->name = $request->name;
        $storage_type->save();

        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.storage_type_added_successfully', 'Storage Type added successfully')
            ]);
        }
    }

    public function storage_type_list(Request $request)
    {
        $store_id = getStoreId();
        $search = trim(request('search'));
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        // $offset = trim(request()->input('search')) ? 0 : request()->input('offset', 0);
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = (request('limit')) ? request('limit') : "10";

        $storage_type_data = StorageType::when($search, function ($query) use ($search) {
            return $query->where('name', 'like', '%' . $search . '%');
        });

        $total = $storage_type_data->count();

        // Use Paginator to handle the server-side pagination
        $storage_types = $storage_type_data->orderBy($sort, $order)->offset($offset)
            ->limit($limit)
            ->get();

        // Prepare the data for the "Actions" field
        $data = $storage_types->map(function ($s) {
            $delete_url = route('admin.storage_type.destroy', $s->id);
            $action = '<div class="form-check form-switch set_default_storage_type" data-id="' . $s->id . '" data-url="/admin/storage_type/set_default/' . $s->id . '"><input type="checkbox" name="customer_privacy" class="form-check-input mx-2"' . ($s->is_default == 1 ? ' checked' : '') . '></div>';
            return [
                'id' => $s->id,
                'name' => $s->name,
                'operate' => $action,
            ];
        });

        return response()->json([
            "rows" => $data, // Return the formatted data for the "Actions" field
            "total" => $total,
        ]);
    }

    public function storage_type_destroy($id)
    {
        $storage_type = StorageType::find($id);

        if (deleteDetails(['id' => $id], 'storage_types')) {
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.storage_type_deleted_successfully', 'Storage Type deleted successfully!')
            ]);
        }
        return response()->json(['error' => labels('admin_labels.something_went_wrong', 'Something went wrong')]);
    }


    public function storage_type_edit($id)
    {
        $data = StorageType::find($id);

        if (!$data) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.data_not_found', 'Data Not Found')], 404);
        }

        return response()->json($data);
    }

    public function storage_type_update(Request $request, $id)
    {
        $data = StorageType::find($id);

        if (!$data) {
            return response()->json(['error' => true, 'message' => labels('admin_labels.data_not_found', 'Data Not Found')], 404);
        }

        $fields = ['name'];

        foreach ($fields as $field) {
            $data->{strtolower($field)} = $request->input($field);
        }

        $data->save();

        return response()->json([
            'message' => labels('admin_labels.storage_type_updated_successfully', 'Storage Type updated successfully')
        ]);
    }
    public function set_default_storage_type(Request $request)
    {

        $id = $request->input('id');
        // First, update all currencies to 'is_default' = 0 (false)
        StorageType::query()->update(['is_default' => 0]);

        // Then, set the selected currency to 'is_default' = 1 (true)
        $storage_type = StorageType::find($id);
        if ($storage_type) {
            $storage_type->is_default = 1;
            $storage_type->save();
        }

        if ($request->ajax()) {
            return response()->json([
                'message' => labels('admin_labels.default_storage_set_successfully', 'Default storage set successfully')
            ]);
        }
    }

    public function dynamic_image_old(Request $request)
    {
        $appUrl = Config::get('app.url');
        $awsBucket = env('AWS_BUCKET', '');
        $url = $request->input('url', '');
        $width = $request->input('width', 100);
        $quality = $request->input('quality', 90);

        // Validate width and quality
        $width = intval($width) <= 0 ? 100 : intval($width);
        $quality = (intval($quality) <= 0 || intval($quality) > 100) ? 90 : intval($quality);

        try {
            // Check if the URL contains the allowed domain
            if (strpos($url, $appUrl) === false && strpos($url, $awsBucket) === false) {
                throw new Exception('Domain is restricted');
            }

            // Download the image from the provided URL
            $imageData = @file_get_contents($url);
            if ($imageData === false) {
                throw new Exception('Failed to download image');
            }

            // Determine MIME type based on file content
            $mimeType = $this->getMimeTypeFromContent($imageData);
            if (!$mimeType) {
                throw new Exception('Failed to determine MIME type');
            }

            // Handle SVG differently
            if ($mimeType === 'image/svg+xml') {
                $resizedSvgData = $this->resizeSvg($imageData, $width);
                return response()->make($resizedSvgData, 200)->header('Content-Type', 'image/svg+xml');
            }

            // Resize animated GIFs using Imagick (to preserve animation)
            if ($mimeType === 'image/gif') {
                $gif = new Imagick();
                $gif->readImageBlob($imageData);
                foreach ($gif as $frame) {
                    // Resize each frame while preserving animation
                    $frame->resizeImage($width, 0, Imagick::FILTER_LANCZOS, 1);
                }
                $gif = $gif->deconstructImages();
                $encodedImage = $gif->getImagesBlob();
            } else {
                // Resize other image types using Intervention\Image
                $image = Image::make($imageData);
                $image->resize($width, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $encodedImage = $image->encode(null, $quality);
            }

            // Set the response content type based on the detected MIME type
            return response()->make($encodedImage, 200)->header('Content-Type', $mimeType);
        } catch (Exception $e) {
            // Handle any exceptions and return an error response
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function dynamic_image(Request $request)
    {
        $appUrl = Config::get('app.url');
        $awsBucket = env('AWS_BUCKET', '');
        $url = $request->input('url', '');
        $width = intval($request->input('width', 100));
        $quality = intval($request->input('quality', 90));

        $width = $width <= 0 ? 100 : $width;
        $quality = ($quality <= 0 || $quality > 100) ? 90 : $quality;

        try {
            // Validate domain
            if (strpos($url, $appUrl) === false && strpos($url, $awsBucket) === false) {
                throw new Exception('Domain is restricted');
            }

            // Convert full URL to local file path if it's a local image
            if (Str::startsWith($url, $appUrl)) {
                $relativePath = str_replace($appUrl, '', $url);
                $localPath = public_path($relativePath);
                if (!file_exists($localPath)) {
                    throw new Exception('Local file not found');
                }
                $imageData = file_get_contents($localPath);
            } else {
                // Fallback to remote image (e.g., from S3)
                $imageData = @file_get_contents($url);
                if ($imageData === false) {
                    throw new Exception('Failed to download image');
                }
            }

            // Detect MIME
            $mimeType = $this->getMimeTypeFromContent($imageData);
            if (!$mimeType) {
                throw new Exception('Failed to determine MIME type');
            }

            // Handle SVG
            if ($mimeType === 'image/svg+xml') {
                $resizedSvgData = $this->resizeSvg($imageData, $width);
                return response($resizedSvgData, 200)->header('Content-Type', 'image/svg+xml');
            }

            // Handle GIFs
            if ($mimeType === 'image/gif') {
                $gif = new \Imagick();
                $gif->readImageBlob($imageData);
                foreach ($gif as $frame) {
                    $frame->resizeImage($width, 0, \Imagick::FILTER_LANCZOS, 1);
                }
                $gif = $gif->deconstructImages();
                $encodedImage = $gif->getImagesBlob();
            } else {
                // Use Intervention for others
                $image = Image::make($imageData);
                $image->resize($width, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $encodedImage = $image->encode(null, $quality);
            }

            return response($encodedImage, 200)->header('Content-Type', $mimeType);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getMimeTypeFromContent($data)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $data);
        finfo_close($finfo);
        return $mimeType;
    }

    private function resizeSvg($svgData, $targetWidth = null)
    {
        $svg = new SimpleXMLElement($svgData);
        $originalWidth = (float) $svg['width'];
        $originalHeight = (float) $svg['height'];
        $aspectRatio = $originalWidth / $originalHeight;
        $targetHeight = $targetWidth / $aspectRatio;
        $svg['width'] = $targetWidth;
        $svg['height'] = $targetHeight;
        return $svg->asXML();
    }
}
