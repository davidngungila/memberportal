<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\LoanCompletionCertificate;
use App\Models\ShareCertificate;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $memberNumber = $user->membercode;

        $loanCertificates = LoanCompletionCertificate::whereHas('loan', function($query) use ($memberNumber) {
            $query->where('member_number', $memberNumber);
        })->with('loan')->orderBy('completion_date', 'desc')->get();

        $shareCertificates = ShareCertificate::whereHas('sharePurchase', function($query) use ($memberNumber) {
            $query->where('member_number', $memberNumber);
        })->with(['sharePurchase.shareProduct'])->orderBy('issue_date', 'desc')->get();

        return view('member.certificates.index', compact('loanCertificates', 'shareCertificates'));
    }

    public function getMembershipCertificate()
    {
        $user = auth()->user();
        $member = $user->member;
        $application = $user->membershipApplications()
            ->whereIn('application_status', ['draft', 'in_progress', 'submitted', 'under_review', 'correction_required'])
            ->latest()
            ->first();
        $personalDetails = $application?->personalDetail;

        $displayName = $member?->full_name ?? $personalDetails?->full_name ?? $user->name;
        $memberNumber = $member?->membercode ?? $user->membercode ?? 'N/A';
        
        // Generate unique verification code
        $verificationCode = 'CERT-' . strtoupper((string) $memberNumber) . '-' . Str::random(8);
        
        // Generate verification URL
        $verificationUrl = url('/verify-certificate/' . $verificationCode);
        
        return response()->json([
            'name' => $displayName,
            'member_number' => $memberNumber,
            'registration_date' => $member?->registration_date?->format('Y-m-d') ?? $user->created_at?->format('Y-m-d') ?? 'N/A',
            'branch' => $member?->branch ?? $user->branch ?? 'N/A',
            'status' => $member?->status ?? $user->status ?? 'active',
            'organization' => 'FEED TAN CMG SACCO',
            'issue_date' => now()->format('m/d/Y'),
            'verification_code' => $verificationCode,
            'verification_url' => $verificationUrl,
        ]);
    }

    public function showLoanCertificate($id)
    {
        $certificate = LoanCompletionCertificate::with(['loan', 'loan.member'])
            ->whereHas('loan', function($query) {
                $query->where('member_number', auth()->user()->membercode);
            })
            ->findOrFail($id);

        return view('member.certificates.loan-show', compact('certificate'));
    }

    public function printLoanCertificate($id)
    {
        $certificate = LoanCompletionCertificate::with(['loan', 'loan.member'])
            ->whereHas('loan', function($query) {
                $query->where('member_number', auth()->user()->membercode);
            })
            ->findOrFail($id);

        return view('member.certificates.loan-print', compact('certificate'));
    }

    public function showShareCertificate($id)
    {
        $certificate = ShareCertificate::with(['sharePurchase', 'sharePurchase.shareProduct', 'sharePurchase.member'])
            ->whereHas('sharePurchase', function($query) {
                $query->where('member_number', auth()->user()->membercode);
            })
            ->findOrFail($id);

        return view('member.certificates.share-show', compact('certificate'));
    }

    public function printShareCertificate($id)
    {
        $certificate = ShareCertificate::with(['sharePurchase', 'sharePurchase.shareProduct', 'sharePurchase.member'])
            ->whereHas('sharePurchase', function($query) {
                $query->where('member_number', auth()->user()->membercode);
            })
            ->findOrFail($id);

        return view('member.certificates.share-print', compact('certificate'));
    }
}
