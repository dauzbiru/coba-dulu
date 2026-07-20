<?php

namespace App\Services;

trait FontRegistration
{
    protected function registerArimoFont(): bool
    {
        $fontDir = storage_path('fonts');
        if (!is_dir($fontDir)) mkdir($fontDir, 0755, true);

        $regular = $fontDir . '/Arimo-Regular.ttf';
        $bold = $fontDir . '/Arimo-Bold.ttf';

        if (!file_exists($regular) || !file_exists($bold)) {
            try {
                $fonts = [
                    $regular => 'https://github.com/google/fonts/raw/main/ofl/arimo/static/Arimo-Regular.ttf',
                    $bold => 'https://github.com/google/fonts/raw/main/ofl/arimo/static/Arimo-Bold.ttf',
                ];
                foreach ($fonts as $path => $url) {
                    if (!file_exists($path)) {
                        $data = @file_get_contents($url);
                        if ($data) file_put_contents($path, $data);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to download Arimo font', ['error' => $e->getMessage()]);
            }
        }

        $fontLoaded = false;
        if (file_exists($regular) && filesize($regular) > 1000) {
            try {
                $fontMetrics = app('dompdf')->getFontMetrics();
                $fontMetrics->registerFont(['family' => 'Arimo', 'style' => 'normal', 'weight' => 'normal'], $regular);
                if (file_exists($bold) && filesize($bold) > 1000) {
                    $fontMetrics->registerFont(['family' => 'Arimo', 'style' => 'normal', 'weight' => 'bold'], $bold);
                }
                $fontLoaded = true;
            } catch (\Exception $e) {
                \Log::warning('Failed to register Arimo font', ['error' => $e->getMessage()]);
            }
        }

        return $fontLoaded;
    }
}
