<?php

namespace App\Support;

use setasign\Fpdi\Fpdi;
use Throwable;

class StudyMaterialDownloadWatermark
{
    public static function watermarkText(): string
    {
        return (string) config('app.study_material_watermark', 'SOILNWATER');
    }

    /**
     * @return array{path: string, temporary: bool}
     */
    public static function prepare(string $absolutePath): array
    {
        if (! is_file($absolutePath)) {
            throw new \InvalidArgumentException('Study material file not found.');
        }

        $mime = mime_content_type($absolutePath) ?: '';

        if (self::isImagePath($absolutePath, $mime)) {
            $temp = self::watermarkImage($absolutePath);

            if ($temp !== null) {
                return ['path' => $temp, 'temporary' => true];
            }
        }

        if (self::isPdfPath($absolutePath, $mime)) {
            $temp = self::watermarkPdf($absolutePath);

            if ($temp !== null) {
                return ['path' => $temp, 'temporary' => true];
            }
        }

        return ['path' => $absolutePath, 'temporary' => false];
    }

    private static function isImagePath(string $path, string $mime): bool
    {
        if (str_starts_with($mime, 'image/')) {
            return true;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    }

    private static function isPdfPath(string $path, string $mime): bool
    {
        if ($mime === 'application/pdf') {
            return true;
        }

        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf';
    }

    private static function watermarkImage(string $sourcePath): ?string
    {
        $imageInfo = @getimagesize($sourcePath);
        if (! is_array($imageInfo) || empty($imageInfo[2])) {
            return null;
        }

        $createImageByType = match ($imageInfo[2]) {
            IMAGETYPE_JPEG => fn (string $path) => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => fn (string $path) => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function (string $path) {
                if (! function_exists('imagecreatefromwebp')) {
                    return false;
                }

                return @imagecreatefromwebp($path);
            },
            IMAGETYPE_GIF => fn (string $path) => @imagecreatefromgif($path),
            default => null,
        };

        if ($createImageByType === null) {
            return null;
        }

        $image = $createImageByType($sourcePath);
        if (! is_resource($image) && ! is_object($image)) {
            return null;
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);

        self::applyTiledTextWatermark($image);

        $tempBase = tempnam(sys_get_temp_dir(), 'snw_wm_img_');
        if ($tempBase === false) {
            imagedestroy($image);

            return null;
        }

        $extension = match ($imageInfo[2]) {
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_WEBP => 'webp',
            IMAGETYPE_GIF => 'gif',
            default => 'png',
        };

        $tempPath = $tempBase.'.'.$extension;
        @unlink($tempBase);

        $saved = match ($imageInfo[2]) {
            IMAGETYPE_JPEG => imagejpeg($image, $tempPath, 90),
            IMAGETYPE_PNG => imagepng($image, $tempPath),
            IMAGETYPE_WEBP => function_exists('imagewebp') ? imagewebp($image, $tempPath, 90) : false,
            IMAGETYPE_GIF => imagegif($image, $tempPath),
            default => false,
        };

        imagedestroy($image);

        return $saved ? $tempPath : null;
    }

    /**
     * @param  resource|\GdImage  $image
     */
    private static function applyTiledTextWatermark($image): void
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= 0 || $height <= 0) {
            return;
        }

        $watermarkText = self::watermarkText();
        $fontPath = public_path('assets/fonts/DejaVuSans-Bold.ttf');
        $canUseTtf = is_file($fontPath) && function_exists('imagettftext');

        if ($canUseTtf) {
            $fontSize = max(18, (int) round(min($width, $height) * 0.045));
            $angle = -28;
            $bbox = imagettfbbox($fontSize, $angle, $fontPath, $watermarkText);
            $textWidth = (int) (max($bbox[2], $bbox[4]) - min($bbox[0], $bbox[6]));
            $textHeight = (int) (max($bbox[1], $bbox[3]) - min($bbox[5], $bbox[7]));
            $stepX = max($textWidth + (int) round($fontSize * 1.6), (int) round($width * 0.24));
            $stepY = max($textHeight + (int) round($fontSize * 1.3), (int) round($height * 0.18));
            $watermarkColor = imagecolorallocatealpha($image, 128, 128, 128, 88);

            for ($y = -$stepY; $y < $height + $stepY; $y += $stepY) {
                $offsetX = (((int) floor($y / $stepY)) % 2 === 0) ? 0 : (int) round($stepX * 0.45);
                for ($x = -$stepX; $x < $width + $stepX; $x += $stepX) {
                    imagettftext($image, $fontSize, $angle, $x + $offsetX, $y + $textHeight, $watermarkColor, $fontPath, $watermarkText);
                }
            }

            return;
        }

        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($watermarkText);
        $textHeight = imagefontheight($font);
        $stepX = max($textWidth + 55, (int) round($width * 0.22));
        $stepY = max($textHeight + 40, (int) round($height * 0.16));
        $watermarkColor = imagecolorallocatealpha($image, 128, 128, 128, 86);

        for ($y = -$stepY; $y < $height + $stepY; $y += $stepY) {
            $offsetX = (((int) floor($y / $stepY)) % 2 === 0) ? 0 : (int) round($stepX * 0.5);
            for ($x = -$textWidth; $x < $width + $textWidth; $x += $stepX) {
                imagestring($image, $font, $x + $offsetX, $y, $watermarkText, $watermarkColor);
            }
        }
    }

    private static function watermarkPdf(string $sourcePath): ?string
    {
        if (! class_exists(Fpdi::class)) {
            return null;
        }

        try {
            $pdf = new Fpdi;
            $pageCount = $pdf->setSourceFile($sourcePath);
            $text = self::watermarkText();

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);
                $width = (float) ($size['width'] ?? 210);
                $height = (float) ($size['height'] ?? 297);
                $orientation = $width > $height ? 'L' : 'P';

                $pdf->AddPage($orientation, [$width, $height]);
                $pdf->useTemplate($templateId);

                $pdf->SetFont('Helvetica', 'B', 28);
                $pdf->SetTextColor(210, 210, 210);

                $stepY = 65;
                $stepX = 95;

                for ($y = 25.0; $y < $height; $y += $stepY) {
                    $offset = (((int) floor($y / $stepY)) % 2) * 35;
                    for ($x = 10.0 + $offset; $x < $width; $x += $stepX) {
                        $pdf->SetXY($x, $y);
                        $pdf->Cell(90, 8, $text, 0, 0);
                    }
                }
            }

            $tempBase = tempnam(sys_get_temp_dir(), 'snw_wm_pdf_');
            if ($tempBase === false) {
                return null;
            }

            $tempPath = $tempBase.'.pdf';
            @unlink($tempBase);
            $pdf->Output('F', $tempPath);

            return is_file($tempPath) ? $tempPath : null;
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }
}
