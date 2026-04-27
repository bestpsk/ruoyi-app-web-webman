<?php

namespace app\controller;

use support\Request;
use app\common\AjaxResult;

class CommonController
{
    public function upload(Request $request)
    {
        $file = $request->file('file');
        if (!$file || !$file->isValid()) {
            return AjaxResult::error('上传文件异常');
        }
        $ext = $file->getUploadExtension() ?: 'bin';
        $filename = date('Ymd') . '/' . md5(uniqid()) . '.' . $ext;
        $uploadDir = public_path() . '/profile/upload/';
        $fullPath = $uploadDir . $filename;
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $file->move($fullPath);
        $url = '/profile/upload/' . $filename;
        return AjaxResult::success('', [
            'fileName' => $filename,
            'url' => $url,
            'newFileName' => basename($filename),
            'originalFilename' => $file->getUploadName(),
        ]);
    }

    public function downloads(Request $request)
    {
        $fileName = $request->input('fileName', '');
        $filePath = public_path() . '/profile/upload/' . $fileName;
        if (!file_exists($filePath)) {
            return AjaxResult::error('文件不存在');
        }
        return response()->download($filePath);
    }
}
