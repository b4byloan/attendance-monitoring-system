<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Str;
use App\Models\Setting;

class QrCodeController extends Controller
{
    public function show(Student $student)
    {
        $dataUri = $this->buildQrCode($student->qr_code)->getDataUri();

        return view('admin.students.qr-code', compact('student', 'dataUri'));
    }

    public function regenerate(Student $student)
    {
        $student->update(['qr_code' => Str::random(32)]);

        return redirect()
            ->route('admin.students.qr-code.show', $student)
            ->with('success', 'QR code regenerated. The old code no longer works.');
    }

    public function download(Student $student)
    {
        $result = $this->buildQrCode($student->qr_code);

        return response($result->getString(), 200)
            ->header('Content-Type', $result->getMimeType())
            ->header('Content-Disposition', 'attachment; filename="qr-' . $student->lrn . '.png"');
    }

    private function buildQrCode(string $data)
    {
        $builder = new Builder(
            writer: new PngWriter(),
            data: $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: Setting::current()->qr_default_size,
            margin: 10,
        );

        return $builder->build();
    }
}