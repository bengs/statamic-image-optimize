<?php

namespace JustBetter\ImageOptimize\Actions;

use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\Facades\Image;
use JustBetter\ImageOptimize\Contracts\ResizesImage;
use JustBetter\ImageOptimize\Events\ImageResizedEvent;
use League\Glide\Manipulators\Size;
use Statamic\Assets\Asset;

class ResizeImage implements ResizesImage
{
    public function resize(Asset $asset, ?int $width = null, ?int $height = null): void
    {
        if (! $asset->exists() ||
            ! $asset->isImage() ||
            in_array($asset->containerHandle(), config('image-optimize.excluded_containers'))
        ) {
            return;
        }

        $width ??= (int) config('image-optimize.default_resize_width');
        $height ??= (int) config('image-optimize.default_resize_height');

        // Prevents exceptions occurring when resizing non-compatible filetypes like SVG.
        try {
            $orientedImage = Image::make($asset->resolvedPath())->orientate();

            $image = (new Size)->runMaxResize($orientedImage, $width, $height);

            $encodedImage = $image->encode(null, config('image-optimize.default_quality'))->getEncoded();

            $checkIfSmaller = config('image-optimize.only_if_smaller');

            if($checkIfSmaller) {
                $originalFileSize = $asset->size();
                $newFileSize = strlen($encodedImage);
            }
            
            if (!$checkIfSmaller || ($newFileSize && $newFileSize < $originalFileSize)) {
                $asset->disk()->filesystem()->put($asset->path(), $encodedImage);
                $asset->merge(['image-optimized' => '1', 'image-optimize-ignored-filesize' => null]);
                $asset->save();
                $asset->meta();
            } elseif(isset($newFileSize)) {
                $asset->merge([
                    'image-optimize-ignored-filesize' => $newFileSize
                ]);
                $asset->save();
                $asset->meta();
            }
        } catch (NotReadableException) {
            return;
        }

        ImageResizedEvent::dispatch();
    }

    public static function bind(): void
    {
        app()->singleton(ResizesImage::class, static::class);
    }
}
