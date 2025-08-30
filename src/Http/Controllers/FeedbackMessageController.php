<?php

namespace Mydnic\Volet\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Mydnic\Volet\Features\FeatureManager;

class FeedbackMessageController
{
    public function store(Request $request)
    {
        $model = config('volet.feedback-messages.model');
        $categories = collect(
            app(FeatureManager::class)
                ->getFeature('feedback-messages')
                ->getCategories()
        )
            ->pluck('slug')
            ->toArray();

        $validated = $request->validate([
            'message' => 'required|string|max:255',
            'category' => 'required|string|in:'.implode(',', $categories),
            'user_info' => 'nullable|array',
            'screenshots' => 'nullable|array|max:4',
            'screenshots.*.data' => 'required|string', // Base64 데이터
            'screenshots.*.filename' => 'required|string|max:255',
            'screenshots.*.type' => 'required|string|in:image/png,image/jpeg,image/jpg,image/gif,image/webp',
        ]);

        $screenshots = $this->processScreenshots($validated['screenshots'] ?? null);

        $feedback = $model::create([
            'message' => $validated['message'],
            'category' => $validated['category'],
            'user_info' => $this->getUserInfo($request),
            'screenshots' => $screenshots,
        ]);

        return response()->json($feedback);
    }

    protected function processScreenshots($screenshots)
    {
        if (!$screenshots) {
            return [];
        }

        $storedScreenshots = [];

        foreach ($screenshots as $screenshot) {
            // Base64 데이터에서 실제 이미지 데이터 추출
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $screenshot['data']));

            // 파일 크기 제한 (예: 5MB)
            if (strlen($imageData) > 5 * 1024 * 1024) {
                continue; // 5MB 초과시 스킵
            }

            // 이미지 유효성 검사
            // $tempFile = tmpfile();
            // fwrite($tempFile, $imageData);
            // $tempPath = stream_get_meta_data($tempFile)['uri'];

            // if (!getimagesize($tempPath)) {
            //     fclose($tempFile);
            //     continue; // 유효한 이미지가 아니면 스킵
            // }

            // fclose($tempFile);

            // 파일명 생성 (중복 방지)
            $filename = 'screenshots/' . uniqid() . '_' . $screenshot['filename'];

            // Storage에 파일 저장
            Storage::disk('public')->put($filename, $imageData);

            $storedScreenshots[] = [
                'filename' => $screenshot['filename'],
                'path' => $filename,
                'url' => Storage::disk('public')->url($filename),
                'type' => $screenshot['type'],
                'size' => strlen($imageData),
            ];
        }

        return $storedScreenshots;
    }

    protected function getUserInfo(Request $request)
    {
        return array_merge([
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->header('referer'),
            'language' => $request->getPreferredLanguage(),
            'user_id' => auth()->check() ? auth()->id() : null,
        ], $request->user_info ?? []);
    }
}
