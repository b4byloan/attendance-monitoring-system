<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class QrCodeController extends Controller
{
    public function __construct(private readonly QrCodeService $qrCode) {}

    public function showForStudent(Request $request): View
    {
        $student = $request->user()->student;
        abort_unless($student, 404);

        return view('student.qr-code', [
            'student' => $student,
            'dataUri' => $this->qrCode->dataUri($student->qr_code),
        ]);
    }

    public function regenerate(Student $student)
    {
        $student->update(['qr_code' => Str::random(32)]);

        return back()->with('success', 'QR code regenerated. The old code no longer works.');
    }

    public function download(Student $student): Response
    {
        return $this->downloadResponse($student);
    }

    public function downloadForStudent(Request $request): Response
    {
        $student = $request->user()->student;
        abort_unless($student, 404);

        return $this->downloadResponse($student);
    }

    private function downloadResponse(Student $student): Response
    {
        // Larger than the on-screen preview specifically - this copy is meant
        // for printing, the preview isn't.
        $result = $this->qrCode->build($student->qr_code, size: 600);

        return response($result->getString(), 200)
            ->header('Content-Type', $result->getMimeType())
            ->header('Content-Disposition', 'attachment; filename="student-'.$student->lrn.'-qr.png"');
    }
}
