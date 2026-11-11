<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Student;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Str;

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
        // Larger size and margin than the on-screen preview specifically -
        // this copy is meant for printing, the preview isn't.
        $result = $this->buildQrCode($student->qr_code, size: 600, margin: 20);

        return response($result->getString(), 200)
            ->header('Content-Type', $result->getMimeType())
            ->header('Content-Disposition', 'attachment; filename="student-'.$student->lrn.'-qr.png"');
    }

    private function buildQrCode(string $data, ?int $size = null, ?int $margin = null)
    {
        $builder = new Builder(
            writer: new PngWriter,
            data: $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $size ?? Setting::current()->qr_default_size,
            margin: $margin ?? 10,
        );

        return $builder->build();
    }
}
