<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\Admin\MemberTypeController as AdminMemberTypeController;
use App\Http\Controllers\Admin\LoanController as AdminLoanController;
use App\Http\Controllers\Admin\LoanProductController as AdminLoanProductController;
use App\Http\Controllers\Admin\SavingController as AdminSavingController;
use App\Http\Controllers\Admin\DepositController as AdminDepositController;
use App\Http\Controllers\Admin\SwfController as AdminSwfController;
use App\Http\Controllers\Admin\InvestmentController as AdminInvestmentController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Admin\GoogleSheetsController as AdminGoogleSheetsController;
use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\PermissionController as AdminPermissionController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ShareController as AdminShareController;
use App\Http\Controllers\Admin\ShareProductController as AdminShareProductController;
use App\Http\Controllers\Admin\SharePurchaseController as AdminSharePurchaseController;
use App\Http\Controllers\Admin\ShareCertificateController as AdminShareCertificateController;
use App\Http\Controllers\Admin\ShareTransferController as AdminShareTransferController;
use App\Http\Controllers\Admin\ShareDividendController as AdminShareDividendController;
use App\Http\Controllers\Admin\ShareTransactionController as AdminShareTransactionController;
use App\Http\Controllers\Admin\ShareReportController as AdminShareReportController;
use App\Http\Controllers\Admin\ShareSettingController as AdminShareSettingController;
use App\Http\Controllers\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Admin\JournalEntryController as AdminJournalEntryController;
use App\Http\Controllers\Admin\LedgerAccountController as AdminLedgerAccountController;
use App\Http\Controllers\Admin\TrialBalanceController as AdminTrialBalanceController;
use App\Http\Controllers\Admin\BalanceSheetController as AdminBalanceSheetController;
use App\Http\Controllers\Admin\IncomeStatementController as AdminIncomeStatementController;
use App\Http\Controllers\Admin\CashFlowController as AdminCashFlowController;
use App\Http\Controllers\Admin\BankAccountController as AdminBankAccountController;
use App\Http\Controllers\Admin\FixedAssetController as AdminFixedAssetController;
use App\Http\Controllers\Admin\ReceiptController as AdminReceiptController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ExpenseController as AdminExpenseController;
use App\Http\Controllers\Admin\RevenueController as AdminRevenueController;
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\ProfileController as MemberProfileController;
use App\Http\Controllers\Member\LoanController as MemberLoanController;
use App\Http\Controllers\Member\ShareController as MemberShareController;
use App\Http\Controllers\Member\SavingController as MemberSavingController;
use App\Http\Controllers\Member\ErrorController as MemberErrorController;
use App\Http\Controllers\Member\DepositController as MemberDepositController;
use App\Http\Controllers\Member\SwfController as MemberSwfController;
use App\Http\Controllers\Member\InvestmentController as MemberInvestmentController;
use App\Http\Controllers\Member\StatementController as MemberStatementController;
use App\Http\Controllers\Member\NotificationController as MemberNotificationController;
use App\Http\Controllers\Member\SavingPlanController as MemberSavingPlanController;
use App\Http\Controllers\Member\CertificateController as MemberCertificateController;
use App\Http\Controllers\Registration\AccountController as RegistrationAccountController;
use App\Http\Controllers\Registration\DashboardController as RegistrationDashboardController;
use App\Http\Controllers\Registration\MembershipTypeController as RegistrationMembershipTypeController;
use App\Http\Controllers\Registration\PaymentController as RegistrationPaymentController;
use App\Http\Controllers\Registration\PersonalDetailsController as RegistrationPersonalDetailsController;
use App\Http\Controllers\Registration\ProfileController as RegistrationProfileController;
use App\Http\Controllers\Registration\BankDetailsController as RegistrationBankDetailsController;
use App\Http\Controllers\Registration\NextOfKinController as RegistrationNextOfKinController;
use App\Http\Controllers\Registration\ReferralController as RegistrationReferralController;
use App\Http\Controllers\Registration\SavingPlanController as RegistrationSavingPlanController;
use App\Http\Controllers\Registration\ReviewController as RegistrationReviewController;
use App\Http\Controllers\Registration\SubmitController as RegistrationSubmitController;
use App\Http\Controllers\Admin\MembershipApplicationController as AdminMembershipApplicationController;

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->isApprovedMember()) {
            return redirect()->route('member.dashboard');
        }
        if ($user->hasActiveApplication()) {
            return redirect()->route('register.dashboard');
        }
        return redirect()->route('register.create');
    }
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [VerificationController::class, 'show'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/resend', [VerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.resend');
});

Route::prefix('admin')->middleware(['auth', 'role:admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/create', [AdminNotificationController::class, 'create'])->name('notifications.create');
    Route::post('/notifications', [AdminNotificationController::class, 'store'])->name('notifications.store');
    Route::post('/notifications/{id}/mark-read', [AdminNotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::delete('/notifications/{id}', [AdminNotificationController::class, 'destroy'])->name('notifications.destroy');

    Route::get('/members', [AdminMemberController::class, 'index'])->name('members.index');
    Route::get('/members/template', [AdminMemberController::class, 'downloadTemplate'])->name('members.template');
    Route::get('/members/import/{jobId}/progress', [AdminMemberController::class, 'importProgress'])->name('members.import-progress');
    Route::get('/members/{memberNumber}', [AdminMemberController::class, 'show'])->name('members.show');
    Route::get('/members/{encryptedMemberNumber}/loans', [AdminMemberController::class, 'loans'])->name('members.loans');
    Route::get('/members/{encryptedMemberNumber}/savings', [AdminMemberController::class, 'savings'])->name('members.savings');
    Route::post('/members/import', [AdminMemberController::class, 'import'])->name('members.import');

    Route::get('/member-types', [AdminMemberTypeController::class, 'index'])->name('member-types.index');
    Route::get('/member-types/create', [AdminMemberTypeController::class, 'create'])->name('member-types.create');
    Route::post('/member-types', [AdminMemberTypeController::class, 'store'])->name('member-types.store');
    Route::get('/member-types/{encryptedId}', [AdminMemberTypeController::class, 'show'])->name('member-types.show');
    Route::get('/member-types/{encryptedId}/edit', [AdminMemberTypeController::class, 'edit'])->name('member-types.edit');
    Route::put('/member-types/{encryptedId}', [AdminMemberTypeController::class, 'update'])->name('member-types.update');
    Route::delete('/member-types/{encryptedId}', [AdminMemberTypeController::class, 'destroy'])->name('member-types.destroy');

    Route::get('/loans/applications', [AdminLoanController::class, 'applications'])->name('loans.applications');
    Route::get('/loans', [AdminLoanController::class, 'index'])->name('loans.index');
    Route::get('/loans/repayments', [AdminLoanController::class, 'repayments'])->name('loans.repayments');
    Route::get('/loans/create', [AdminLoanController::class, 'create'])->name('loans.create');
    Route::post('/loans/store-basic-info', [AdminLoanController::class, 'storeBasicInfo'])->name('loans.store-basic-info');
    Route::post('/loans/store-loan-details', [AdminLoanController::class, 'storeLoanDetails'])->name('loans.store-loan-details');
    Route::post('/loans/store-collateral', [AdminLoanController::class, 'storeCollateral'])->name('loans.store-collateral');
    Route::post('/loans', [AdminLoanController::class, 'store'])->name('loans.store');
    Route::get('/loans/{encryptedLoanNumber}', [AdminLoanController::class, 'show'])->name('loans.show');
    Route::get('/loans/{encryptedLoanNumber}/export-pdf', [AdminLoanController::class, 'exportPdf'])->name('loans.export-pdf');
    Route::get('/loans/{encryptedLoanNumber}/export-csv', [AdminLoanController::class, 'exportCsv'])->name('loans.export-csv');
    Route::post('/loans/{encryptedLoanNumber}/recordPayment', [AdminLoanController::class, 'recordPayment'])->name('loans.recordPayment');
    Route::get('/loans/{id}/edit', [AdminLoanController::class, 'edit'])->name('loans.edit');
    Route::put('/loans/{id}', [AdminLoanController::class, 'update'])->name('loans.update');
    Route::delete('/loans/{id}', [AdminLoanController::class, 'destroy'])->name('loans.destroy');
    Route::post('/loans/{encryptedId}/approve', [AdminLoanController::class, 'approve'])->name('loans.approve');
    Route::post('/loans/{encryptedId}/disburse', [AdminLoanController::class, 'disburse'])->name('loans.disburse');
    Route::get('/loans/{encryptedLoanNumber}/appreciation-certificate', [AdminLoanController::class, 'appreciationCertificate'])->name('loans.appreciation-certificate');
    Route::post('/loans/import-loan-payments', [AdminLoanController::class, 'importLoanPayments'])->name('loans.import-loan-payments');
    Route::post('/loans/import-loans-information', [AdminLoanController::class, 'importLoansInformation'])->name('loans.import-loans-information');

    Route::get('/loan-products', [AdminLoanProductController::class, 'index'])->name('loan-products.index');
    Route::get('/loan-products/create', [AdminLoanProductController::class, 'create'])->name('loan-products.create');
    Route::post('/loan-products', [AdminLoanProductController::class, 'store'])->name('loan-products.store');
    Route::get('/loan-products/{encryptedId}', [AdminLoanProductController::class, 'show'])->name('loan-products.show');
    Route::get('/loan-products/{encryptedId}/edit', [AdminLoanProductController::class, 'edit'])->name('loan-products.edit');
    Route::put('/loan-products/{encryptedId}', [AdminLoanProductController::class, 'update'])->name('loan-products.update');
    Route::delete('/loan-products/{encryptedId}', [AdminLoanProductController::class, 'destroy'])->name('loan-products.destroy');

    Route::get('/savings', [AdminSavingController::class, 'index'])->name('savings.index');
    Route::get('/savings/create', [AdminSavingController::class, 'create'])->name('savings.create');
    Route::post('/savings', [AdminSavingController::class, 'store'])->name('savings.store');
    Route::get('/savings/{encryptedMemberNumber}', [AdminSavingController::class, 'show'])->name('savings.show');

    Route::get('/products', [App\Http\Controllers\Admin\ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [App\Http\Controllers\Admin\ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [App\Http\Controllers\Admin\ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{encryptedId}', [App\Http\Controllers\Admin\ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{encryptedId}/edit', [App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{encryptedId}', [App\Http\Controllers\Admin\ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{encryptedId}', [App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/saving-plans', [App\Http\Controllers\Admin\SavingPlanController::class, 'index'])->name('saving-plans.index');
    Route::get('/saving-plans/create', [App\Http\Controllers\Admin\SavingPlanController::class, 'create'])->name('saving-plans.create');
    Route::post('/saving-plans', [App\Http\Controllers\Admin\SavingPlanController::class, 'store'])->name('saving-plans.store');
    Route::get('/saving-plans/{encryptedId}', [App\Http\Controllers\Admin\SavingPlanController::class, 'show'])->name('saving-plans.show');
    Route::get('/saving-plans/{encryptedId}/edit', [App\Http\Controllers\Admin\SavingPlanController::class, 'edit'])->name('saving-plans.edit');
    Route::put('/saving-plans/{encryptedId}', [App\Http\Controllers\Admin\SavingPlanController::class, 'update'])->name('saving-plans.update');
    Route::delete('/saving-plans/{encryptedId}', [App\Http\Controllers\Admin\SavingPlanController::class, 'destroy'])->name('saving-plans.destroy');

    Route::get('/statements', [App\Http\Controllers\Admin\StatementController::class, 'index'])->name('statements.index');
    Route::get('/statements/{id}', [App\Http\Controllers\Admin\StatementController::class, 'show'])->name('statements.show');
    Route::get('/statements/{id}/download', [App\Http\Controllers\Admin\StatementController::class, 'download'])->name('statements.download');

    Route::get('/transactions', [App\Http\Controllers\Admin\TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [App\Http\Controllers\Admin\TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [App\Http\Controllers\Admin\TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{id}/edit', [App\Http\Controllers\Admin\TransactionController::class, 'edit'])->name('transactions.edit');
    Route::put('/transactions/{id}', [App\Http\Controllers\Admin\TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{id}', [App\Http\Controllers\Admin\TransactionController::class, 'destroy'])->name('transactions.destroy');
    Route::get('/transactions/export', [App\Http\Controllers\Admin\TransactionController::class, 'export'])->name('transactions.export');
    Route::post('/transactions/import', [App\Http\Controllers\Admin\TransactionController::class, 'import'])->name('transactions.import');

    Route::get('/deposits', [AdminDepositController::class, 'index'])->name('deposits.index');
    Route::get('/deposits/create', [AdminDepositController::class, 'create'])->name('deposits.create');
    Route::post('/deposits', [AdminDepositController::class, 'store'])->name('deposits.store');
    Route::get('/deposits/{encryptedCertificateNumber}', [AdminDepositController::class, 'show'])->name('deposits.show');

    Route::prefix('swf')->name('swf.')->group(function () {
        Route::get('/', [AdminSwfController::class, 'index'])->name('index');
        Route::get('/members/create', [\App\Http\Controllers\Admin\SwfMemberController::class, 'create'])->name('members.create');
        Route::post('/members', [\App\Http\Controllers\Admin\SwfMemberController::class, 'store'])->name('members.store');
        Route::get('/members/{encryptedId}', [\App\Http\Controllers\Admin\SwfMemberController::class, 'show'])->name('members.show');
        
        Route::get('/contributions/create/{encryptedSwfMemberId}', [\App\Http\Controllers\Admin\SwfContributionController::class, 'create'])->name('contributions.create');
        Route::post('/contributions', [\App\Http\Controllers\Admin\SwfContributionController::class, 'store'])->name('contributions.store');
        
        Route::get('/benefits', [\App\Http\Controllers\Admin\SwfBenefitController::class, 'index'])->name('benefits.index');
        Route::get('/benefits/create', [\App\Http\Controllers\Admin\SwfBenefitController::class, 'create'])->name('benefits.create');
        Route::post('/benefits', [\App\Http\Controllers\Admin\SwfBenefitController::class, 'store'])->name('benefits.store');
        Route::post('/benefits/grant', [\App\Http\Controllers\Admin\SwfBenefitController::class, 'grant'])->name('benefits.grant');
        
        Route::get('/{encryptedId}', [AdminSwfController::class, 'show'])->name('show');
    });

    Route::get('/investments', [AdminInvestmentController::class, 'index'])->name('investments.index');
    Route::get('/investments/create', [AdminInvestmentController::class, 'create'])->name('investments.create');
    Route::post('/investments', [AdminInvestmentController::class, 'store'])->name('investments.store');
    Route::get('/investments/{encryptedMemberNumber}', [AdminInvestmentController::class, 'show'])->name('investments.show');

    Route::get('/investment-products', [App\Http\Controllers\Admin\InvestmentProductController::class, 'index'])->name('investment-products.index');
    Route::get('/investment-products/create', [App\Http\Controllers\Admin\InvestmentProductController::class, 'create'])->name('investment-products.create');
    Route::post('/investment-products', [App\Http\Controllers\Admin\InvestmentProductController::class, 'store'])->name('investment-products.store');
    Route::get('/investment-products/{id}/edit', [App\Http\Controllers\Admin\InvestmentProductController::class, 'edit'])->name('investment-products.edit');
    Route::put('/investment-products/{id}', [App\Http\Controllers\Admin\InvestmentProductController::class, 'update'])->name('investment-products.update');
    Route::delete('/investment-products/{id}', [App\Http\Controllers\Admin\InvestmentProductController::class, 'destroy'])->name('investment-products.destroy');

    Route::get('/shares', [AdminShareController::class, 'index'])->name('shares.index');
    Route::get('/shares/{encryptedMemberNumber}', [AdminShareController::class, 'show'])->name('shares.show');

    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/generate', [AdminReportController::class, 'generate'])->name('reports.generate');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::get('/users/{encryptedId}', [AdminUserController::class, 'show'])->name('users.show');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::post('/users/basic-info', [AdminUserController::class, 'storeBasicInfo'])->name('users.store-basic-info');
    Route::post('/users/{userId}/contact-info', [AdminUserController::class, 'storeContactInfo'])->name('users.store-contact-info');
    Route::post('/users/{userId}/membership-details', [AdminUserController::class, 'storeMembershipDetails'])->name('users.store-membership-details');
    Route::post('/users/{userId}/account-info', [AdminUserController::class, 'storeAccountInfo'])->name('users.store-account-info');
    Route::post('/users/{userId}/next-of-kin', [AdminUserController::class, 'storeNextOfKin'])->name('users.store-next-of-kin');
    Route::post('/users/{userId}/banking-info', [AdminUserController::class, 'storeBankingInfo'])->name('users.store-banking-info');
    Route::post('/users/{userId}/documents-info', [AdminUserController::class, 'storeDocumentsInfo'])->name('users.store-documents-info');
    Route::post('/users/{userId}/additional-info', [AdminUserController::class, 'storeAdditionalInfo'])->name('users.store-additional-info');
    Route::get('/users/{encryptedId}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{encryptedId}', [AdminUserController::class, 'update'])->name('users.update');
    Route::put('/users/{encryptedId}/basic-info', [AdminUserController::class, 'updateBasicInfo'])->name('users.update-basic-info');
    Route::put('/users/{encryptedId}/contact-info', [AdminUserController::class, 'updateContactInfo'])->name('users.update-contact-info');
    Route::put('/users/{encryptedId}/membership-details', [AdminUserController::class, 'updateMembershipDetails'])->name('users.update-membership-details');
    Route::put('/users/{encryptedId}/account-info', [AdminUserController::class, 'updateAccountInfo'])->name('users.update-account-info');
    Route::put('/users/{encryptedId}/next-of-kin', [AdminUserController::class, 'updateNextOfKin'])->name('users.update-next-of-kin');
    Route::put('/users/{encryptedId}/banking-info', [AdminUserController::class, 'updateBankingInfo'])->name('users.update-banking-info');
    Route::put('/users/{encryptedId}/documents-info', [AdminUserController::class, 'updateDocumentsInfo'])->name('users.update-documents-info');
    Route::put('/users/{encryptedId}/additional-info', [AdminUserController::class, 'updateAdditionalInfo'])->name('users.update-additional-info');
    Route::delete('/users/{encryptedId}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{encryptedId}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/users/bulk-reset-password', [AdminUserController::class, 'bulkResetPassword'])->name('users.bulk-reset-password');

    Route::get('/staff', [AdminStaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/create', [AdminStaffController::class, 'create'])->name('staff.create');
    Route::post('/staff', [AdminStaffController::class, 'store'])->name('staff.store');
    Route::get('/staff/{encryptedId}', [AdminStaffController::class, 'show'])->name('staff.show');
    Route::get('/staff/{encryptedId}/edit', [AdminStaffController::class, 'edit'])->name('staff.edit');
    Route::put('/staff/{encryptedId}', [AdminStaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{encryptedId}', [AdminStaffController::class, 'destroy'])->name('staff.destroy');

    Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/test-sms', [AdminSettingController::class, 'testSms'])->name('settings.test-sms');
    Route::get('/settings/test-sms-page', [AdminSettingController::class, 'testSmsPage'])->name('settings.test-sms-page');
    Route::post('/settings/test-whatsapp', [AdminSettingController::class, 'testWhatsApp'])->name('settings.test-whatsapp');
    Route::get('/settings/test-whatsapp-page', [AdminSettingController::class, 'testWhatsAppPage'])->name('settings.test-whatsapp-page');
    Route::post('/settings/check-whatsapp-connection', [AdminSettingController::class, 'checkWhatsAppConnection'])->name('settings.check-whatsapp-connection');

    Route::get('/google-sheets', [AdminGoogleSheetsController::class, 'index'])->name('google-sheets.index');
    Route::post('/google-sheets/sync', [AdminGoogleSheetsController::class, 'sync'])->name('google-sheets.sync');
    Route::get('/google-sheets/status', [AdminGoogleSheetsController::class, 'status'])->name('google-sheets.status');
    Route::get('/google-sheets/logs', [AdminGoogleSheetsController::class, 'logs'])->name('google-sheets.logs');
    Route::get('/google-sheets/customers', [AdminGoogleSheetsController::class, 'customers'])->name('google-sheets.customers');
    Route::get('/google-sheets/customers/{customerId}', [AdminGoogleSheetsController::class, 'customer'])->name('google-sheets.customer');
    Route::get('/google-sheets/summary', [AdminGoogleSheetsController::class, 'summary'])->name('google-sheets.summary');
    Route::post('/google-sheets/manual-sync', [AdminGoogleSheetsController::class, 'manualSync'])->name('google-sheets.manual-sync');

    Route::get('/activity-logs', [AdminActivityLogController::class, 'index'])->name('activity-logs.index');

    // Shares routes
    Route::get('/share-products', [AdminShareProductController::class, 'index'])->name('share-products.index');
    Route::get('/share-products/create', [AdminShareProductController::class, 'create'])->name('share-products.create');
    Route::post('/share-products', [AdminShareProductController::class, 'store'])->name('share-products.store');
    Route::get('/share-products/{encryptedId}', [AdminShareProductController::class, 'show'])->name('share-products.show');
    Route::get('/share-products/{encryptedId}/edit', [AdminShareProductController::class, 'edit'])->name('share-products.edit');
    Route::put('/share-products/{encryptedId}', [AdminShareProductController::class, 'update'])->name('share-products.update');
    Route::delete('/share-products/{encryptedId}', [AdminShareProductController::class, 'destroy'])->name('share-products.destroy');

    Route::get('/share-purchases', [AdminSharePurchaseController::class, 'index'])->name('share-purchases.index');
    Route::get('/share-purchases/create', [AdminSharePurchaseController::class, 'create'])->name('share-purchases.create');
    Route::post('/share-purchases', [AdminSharePurchaseController::class, 'store'])->name('share-purchases.store');
    Route::get('/share-purchases/{encryptedId}', [AdminSharePurchaseController::class, 'show'])->name('share-purchases.show');
    Route::get('/share-purchases/{encryptedId}/edit', [AdminSharePurchaseController::class, 'edit'])->name('share-purchases.edit');
    Route::put('/share-purchases/{encryptedId}', [AdminSharePurchaseController::class, 'update'])->name('share-purchases.update');
    Route::delete('/share-purchases/{encryptedId}', [AdminSharePurchaseController::class, 'destroy'])->name('share-purchases.destroy');

    Route::get('/share-certificates', [AdminShareCertificateController::class, 'index'])->name('share-certificates.index');
    Route::get('/share-certificates/create', [AdminShareCertificateController::class, 'create'])->name('share-certificates.create');
    Route::post('/share-certificates', [AdminShareCertificateController::class, 'store'])->name('share-certificates.store');
    Route::get('/share-certificates/{encryptedId}', [AdminShareCertificateController::class, 'show'])->name('share-certificates.show');
    Route::get('/share-certificates/{encryptedId}/edit', [AdminShareCertificateController::class, 'edit'])->name('share-certificates.edit');
    Route::put('/share-certificates/{encryptedId}', [AdminShareCertificateController::class, 'update'])->name('share-certificates.update');
    Route::delete('/share-certificates/{encryptedId}', [AdminShareCertificateController::class, 'destroy'])->name('share-certificates.destroy');

    Route::get('/share-transfers', [AdminShareTransferController::class, 'index'])->name('share-transfers.index');
    Route::get('/share-transfers/create', [AdminShareTransferController::class, 'create'])->name('share-transfers.create');
    Route::post('/share-transfers', [AdminShareTransferController::class, 'store'])->name('share-transfers.store');
    Route::get('/share-transfers/{encryptedId}', [AdminShareTransferController::class, 'show'])->name('share-transfers.show');
    Route::get('/share-transfers/{encryptedId}/edit', [AdminShareTransferController::class, 'edit'])->name('share-transfers.edit');
    Route::put('/share-transfers/{encryptedId}', [AdminShareTransferController::class, 'update'])->name('share-transfers.update');
    Route::delete('/share-transfers/{encryptedId}', [AdminShareTransferController::class, 'destroy'])->name('share-transfers.destroy');

    Route::get('/share-dividends', [AdminShareDividendController::class, 'index'])->name('share-dividends.index');
    Route::get('/share-dividends/create', [AdminShareDividendController::class, 'create'])->name('share-dividends.create');
    Route::post('/share-dividends', [AdminShareDividendController::class, 'store'])->name('share-dividends.store');
    Route::get('/share-dividends/{encryptedId}', [AdminShareDividendController::class, 'show'])->name('share-dividends.show');
    Route::get('/share-dividends/{encryptedId}/edit', [AdminShareDividendController::class, 'edit'])->name('share-dividends.edit');
    Route::put('/share-dividends/{encryptedId}', [AdminShareDividendController::class, 'update'])->name('share-dividends.update');
    Route::delete('/share-dividends/{encryptedId}', [AdminShareDividendController::class, 'destroy'])->name('share-dividends.destroy');

    Route::get('/share-transactions', [AdminShareTransactionController::class, 'index'])->name('share-transactions.index');
    Route::get('/share-transactions/create', [AdminShareTransactionController::class, 'create'])->name('share-transactions.create');
    Route::post('/share-transactions', [AdminShareTransactionController::class, 'store'])->name('share-transactions.store');
    Route::get('/share-transactions/{encryptedId}', [AdminShareTransactionController::class, 'show'])->name('share-transactions.show');
    Route::get('/share-transactions/{encryptedId}/edit', [AdminShareTransactionController::class, 'edit'])->name('share-transactions.edit');
    Route::put('/share-transactions/{encryptedId}', [AdminShareTransactionController::class, 'update'])->name('share-transactions.update');
    Route::delete('/share-transactions/{encryptedId}', [AdminShareTransactionController::class, 'destroy'])->name('share-transactions.destroy');

    Route::get('/share-reports', [AdminShareReportController::class, 'index'])->name('share-reports.index');

    Route::get('/share-settings', [AdminShareSettingController::class, 'index'])->name('share-settings.index');
    Route::put('/share-settings', [AdminShareSettingController::class, 'update'])->name('share-settings.update');

    // Chart of Accounts routes
    Route::get('/accounts', [AdminAccountController::class, 'index'])->name('accounts.index');
    Route::get('/accounts/create', [AdminAccountController::class, 'create'])->name('accounts.create');
    Route::post('/accounts', [AdminAccountController::class, 'store'])->name('accounts.store');
    Route::get('/accounts/{id}', [AdminAccountController::class, 'show'])->name('accounts.show');
    Route::get('/accounts/{id}/edit', [AdminAccountController::class, 'edit'])->name('accounts.edit');
    Route::put('/accounts/{id}', [AdminAccountController::class, 'update'])->name('accounts.update');
    Route::delete('/accounts/{id}', [AdminAccountController::class, 'destroy'])->name('accounts.destroy');

    // Journal Entries routes
    Route::get('/journal-entries', [AdminJournalEntryController::class, 'index'])->name('journal-entries.index');
    Route::get('/journal-entries/create', [AdminJournalEntryController::class, 'create'])->name('journal-entries.create');
    Route::post('/journal-entries', [AdminJournalEntryController::class, 'store'])->name('journal-entries.store');
    Route::get('/journal-entries/{encryptedId}', [AdminJournalEntryController::class, 'show'])->name('journal-entries.show');
    Route::get('/journal-entries/{encryptedId}/edit', [AdminJournalEntryController::class, 'edit'])->name('journal-entries.edit');
    Route::put('/journal-entries/{encryptedId}', [AdminJournalEntryController::class, 'update'])->name('journal-entries.update');
    Route::delete('/journal-entries/{encryptedId}', [AdminJournalEntryController::class, 'destroy'])->name('journal-entries.destroy');
    Route::post('/journal-entries/{encryptedId}/post', [AdminJournalEntryController::class, 'post'])->name('journal-entries.post');
    Route::post('/journal-entries/{encryptedId}/void', [AdminJournalEntryController::class, 'void'])->name('journal-entries.void');

    // General Ledger routes
    Route::get('/ledger', [AdminLedgerAccountController::class, 'index'])->name('ledger.index');
    Route::get('/ledger/{encryptedId}', [AdminLedgerAccountController::class, 'show'])->name('ledger.show');
    Route::post('/ledger/filter', [AdminLedgerAccountController::class, 'accountLedger'])->name('ledger.filter');

    // Trial Balance routes
    Route::get('/trial-balance', [AdminTrialBalanceController::class, 'index'])->name('trial-balance.index');

    // Balance Sheet routes
    Route::get('/balance-sheet', [AdminBalanceSheetController::class, 'index'])->name('balance-sheet.index');

    // Income Statement routes
    Route::get('/income-statement', [AdminIncomeStatementController::class, 'index'])->name('income-statement.index');

    // Cash Flow routes
    Route::get('/cash-flow', [AdminCashFlowController::class, 'index'])->name('cash-flow.index');

    // Bank Accounts routes
    Route::get('/bank-accounts', [AdminBankAccountController::class, 'index'])->name('bank-accounts.index');
    Route::get('/bank-accounts/create', [AdminBankAccountController::class, 'create'])->name('bank-accounts.create');
    Route::post('/bank-accounts', [AdminBankAccountController::class, 'store'])->name('bank-accounts.store');
    Route::get('/bank-accounts/{id}', [AdminBankAccountController::class, 'show'])->name('bank-accounts.show');
    Route::get('/bank-accounts/{id}/edit', [AdminBankAccountController::class, 'edit'])->name('bank-accounts.edit');
    Route::put('/bank-accounts/{id}', [AdminBankAccountController::class, 'update'])->name('bank-accounts.update');
    Route::delete('/bank-accounts/{id}', [AdminBankAccountController::class, 'destroy'])->name('bank-accounts.destroy');

    // Fixed Assets routes
    Route::get('/fixed-assets', [AdminFixedAssetController::class, 'index'])->name('fixed-assets.index');
    Route::get('/fixed-assets/create', [AdminFixedAssetController::class, 'create'])->name('fixed-assets.create');
    Route::post('/fixed-assets', [AdminFixedAssetController::class, 'store'])->name('fixed-assets.store');
    Route::get('/fixed-assets/{id}', [AdminFixedAssetController::class, 'show'])->name('fixed-assets.show');
    Route::get('/fixed-assets/{id}/edit', [AdminFixedAssetController::class, 'edit'])->name('fixed-assets.edit');
    Route::put('/fixed-assets/{id}', [AdminFixedAssetController::class, 'update'])->name('fixed-assets.update');
    Route::delete('/fixed-assets/{id}', [AdminFixedAssetController::class, 'destroy'])->name('fixed-assets.destroy');
    Route::post('/fixed-assets/{id}/calculate-depreciation', [AdminFixedAssetController::class, 'calculateDepreciation'])->name('fixed-assets.calculate-depreciation');

    // Receipts routes
    Route::get('/receipts', [AdminReceiptController::class, 'index'])->name('receipts.index');
    Route::get('/receipts/create', [AdminReceiptController::class, 'create'])->name('receipts.create');
    Route::post('/receipts', [AdminReceiptController::class, 'store'])->name('receipts.store');
    Route::get('/receipts/{id}', [AdminReceiptController::class, 'show'])->name('receipts.show');
    Route::delete('/receipts/{id}', [AdminReceiptController::class, 'destroy'])->name('receipts.destroy');

    // Payments routes
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [AdminPaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [AdminPaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{id}', [AdminPaymentController::class, 'show'])->name('payments.show');
    Route::delete('/payments/{id}', [AdminPaymentController::class, 'destroy'])->name('payments.destroy');

    // Expenses routes
    Route::get('/expenses', [AdminExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/create', [AdminExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/expenses', [AdminExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/{id}', [AdminExpenseController::class, 'show'])->name('expenses.show');
    Route::delete('/expenses/{id}', [AdminExpenseController::class, 'destroy'])->name('expenses.destroy');

    // Revenues routes
    Route::get('/revenues', [AdminRevenueController::class, 'index'])->name('revenues.index');
    Route::get('/revenues/create', [AdminRevenueController::class, 'create'])->name('revenues.create');
    Route::post('/revenues', [AdminRevenueController::class, 'store'])->name('revenues.store');
    Route::get('/revenues/{id}', [AdminRevenueController::class, 'show'])->name('revenues.show');
    Route::delete('/revenues/{id}', [AdminRevenueController::class, 'destroy'])->name('revenues.destroy');

    // Accounting routes
    Route::get('/accounting/dashboard', [AdminAccountingController::class, 'dashboard'])->name('accounting.dashboard');
    Route::get('/accounting/chart-of-accounts', [AdminAccountingController::class, 'chartOfAccounts'])->name('accounting.chart-of-accounts');
    Route::get('/accounting/journal-entries', [AdminAccountingController::class, 'journalEntries'])->name('accounting.journal-entries');
    Route::get('/accounting/general-ledger', [AdminAccountingController::class, 'generalLedger'])->name('accounting.general-ledger');
    Route::get('/accounting/trial-balance', [AdminAccountingController::class, 'trialBalance'])->name('accounting.trial-balance');
    Route::get('/accounting/balance-sheet', [AdminAccountingController::class, 'balanceSheet'])->name('accounting.balance-sheet');
    Route::get('/accounting/income-statement', [AdminAccountingController::class, 'incomeStatement'])->name('accounting.income-statement');
    Route::get('/accounting/cash-flow', [AdminAccountingController::class, 'cashFlow'])->name('accounting.cash-flow');
    Route::get('/accounting/fixed-assets', [AdminAccountingController::class, 'fixedAssets'])->name('accounting.fixed-assets');
    Route::get('/accounting/depreciation', [AdminAccountingController::class, 'depreciation'])->name('accounting.depreciation');
    Route::get('/accounting/bank-accounts', [AdminAccountingController::class, 'bankAccounts'])->name('accounting.bank-accounts');
    Route::get('/accounting/bank-reconciliation', [AdminAccountingController::class, 'bankReconciliation'])->name('accounting.bank-reconciliation');
    Route::get('/accounting/receipts', [AdminAccountingController::class, 'receipts'])->name('accounting.receipts');
    Route::get('/accounting/payments', [AdminAccountingController::class, 'payments'])->name('accounting.payments');
    Route::get('/accounting/expenses', [AdminAccountingController::class, 'expenses'])->name('accounting.expenses');
    Route::get('/accounting/revenue', [AdminAccountingController::class, 'revenue'])->name('accounting.revenue');
    Route::get('/accounting/accounts-receivable', [AdminAccountingController::class, 'accountsReceivable'])->name('accounting.accounts-receivable');
    Route::get('/accounting/accounts-payable', [AdminAccountingController::class, 'accountsPayable'])->name('accounting.accounts-payable');
    Route::get('/accounting/budgets', [AdminAccountingController::class, 'budgets'])->name('accounting.budgets');
    Route::get('/accounting/financial-periods', [AdminAccountingController::class, 'financialPeriods'])->name('accounting.financial-periods');
    Route::get('/accounting/closing-entries', [AdminAccountingController::class, 'closingEntries'])->name('accounting.closing-entries');
    Route::get('/accounting/tax-management', [AdminAccountingController::class, 'taxManagement'])->name('accounting.tax-management');
    Route::get('/accounting/audit-trail', [AdminAccountingController::class, 'auditTrail'])->name('accounting.audit-trail');
    Route::get('/accounting/financial-reports', [AdminAccountingController::class, 'financialReports'])->name('accounting.financial-reports');
    Route::get('/accounting/settings', [AdminAccountingController::class, 'settings'])->name('accounting.settings');

    Route::get('/roles', [AdminRoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [AdminRoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [AdminRoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{id}/edit', [AdminRoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{id}', [AdminRoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{id}', [AdminRoleController::class, 'destroy'])->name('roles.destroy');

    Route::get('/permissions', [AdminPermissionController::class, 'index'])->name('permissions.index');
    Route::get('/permissions/create', [AdminPermissionController::class, 'create'])->name('permissions.create');
    Route::post('/permissions', [AdminPermissionController::class, 'store'])->name('permissions.store');
    Route::get('/permissions/{id}/edit', [AdminPermissionController::class, 'edit'])->name('permissions.edit');
    Route::put('/permissions/{id}', [AdminPermissionController::class, 'update'])->name('permissions.update');
    Route::delete('/permissions/{id}', [AdminPermissionController::class, 'destroy'])->name('permissions.destroy');

    Route::get('/profile', [AdminProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');

    Route::prefix('communication')->name('communication.')->group(function () {
        Route::get('/sms', [\App\Http\Controllers\Admin\CommunicationController::class, 'sms'])->name('sms');
        Route::get('/email', [\App\Http\Controllers\Admin\CommunicationController::class, 'email'])->name('email');
        Route::get('/whatsapp', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'index'])->name('whatsapp');
        Route::match(['post', 'put'], '/whatsapp/personal-token', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'storePersonalAccessToken'])->name('whatsapp.personal-token');
        Route::post('/whatsapp/session', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'createSession'])->name('whatsapp.session');
        Route::match(['post', 'put'], '/whatsapp/session-api-key', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'storeSessionApiKey'])->name('whatsapp.session-api-key');
        Route::post('/whatsapp/send-single', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'sendSingleMessage'])->name('whatsapp.send-single');
        Route::post('/whatsapp/send-bulk', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'sendBulkMessage'])->name('whatsapp.send-bulk');

        // WasenderAPI single media message endpoints
        Route::post('/whatsapp/send-image', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'sendImageMessage'])->name('whatsapp.send-image');
        Route::post('/whatsapp/send-video', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'sendVideoMessage'])->name('whatsapp.send-video');
        Route::post('/whatsapp/send-document', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'sendDocumentMessage'])->name('whatsapp.send-document');
        Route::post('/whatsapp/send-audio', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'sendAudioMessage'])->name('whatsapp.send-audio');
        Route::post('/whatsapp/send-sticker', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'sendStickerMessage'])->name('whatsapp.send-sticker');
        Route::post('/whatsapp/send-contact', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'sendContactMessage'])->name('whatsapp.send-contact');
        Route::post('/whatsapp/send-location', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'sendLocationMessage'])->name('whatsapp.send-location');

        // WasenderAPI bulk media message endpoints
        Route::post('/whatsapp/send-bulk-image', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'sendBulkImage'])->name('whatsapp.send-bulk-image');
        Route::post('/whatsapp/send-bulk-document', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'sendBulkDocument'])->name('whatsapp.send-bulk-document');

        // WasenderAPI media utility endpoints
        Route::post('/whatsapp/refresh-sessions', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'refreshSessions'])->name('whatsapp.refresh-sessions');
        Route::post('/whatsapp/upload-media', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'uploadMedia'])->name('whatsapp.upload-media');
        Route::post('/whatsapp/decrypt-media', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'decryptMedia'])->name('whatsapp.decrypt-media');
        Route::post('/whatsapp/decrypt-media-raw', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'decryptMediaRaw'])->name('whatsapp.decrypt-media-raw');

        // SMS endpoints
        Route::post('/sms/send', [\App\Http\Controllers\Admin\CommunicationController::class, 'sendSms'])->name('sms.send');
        Route::post('/sms/bulk', [\App\Http\Controllers\Admin\CommunicationController::class, 'sendBulkSms'])->name('sms.bulk');
        Route::get('/sms/history', [\App\Http\Controllers\Admin\CommunicationController::class, 'smsHistory'])->name('sms.history');
        Route::post('/whatsapp/send-single-sms', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'sendSingleSms'])->name('whatsapp.send-single-sms');
        Route::post('/whatsapp/send-bulk-sms', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'sendBulkSms'])->name('whatsapp.send-bulk-sms');
        Route::post('/whatsapp/toggle-status', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'toggleStatus'])->name('whatsapp.toggle-status');
        Route::post('/whatsapp/disconnect-session', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'disconnectSession'])->name('whatsapp.disconnect-session');
        Route::post('/whatsapp/restart-session', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'restartSession'])->name('whatsapp.restart-session');
        Route::get('/whatsapp/groups', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'fetchGroups'])->name('whatsapp.groups');
        Route::post('/whatsapp/groups/participants/add', [\App\Http\Controllers\Admin\WhatsAppCommunicationController::class, 'addGroupParticipants'])->name('whatsapp.groups.participants.add');
        Route::post('/whatsapp/send', [\App\Http\Controllers\Admin\CommunicationController::class, 'sendWhatsApp'])->name('whatsapp.send');
        Route::get('/whatsapp/test', [\App\Http\Controllers\Admin\CommunicationController::class, 'testWhatsAppPage'])->name('whatsapp.test');
        Route::post('/whatsapp/test', [\App\Http\Controllers\Admin\CommunicationController::class, 'testWhatsApp'])->name('whatsapp.test.send');
    });
});

Route::prefix('member')->middleware(['auth', 'role:member', 'member.isolation'])->name('member.')->group(function () {
    Route::get('/dashboard', [MemberDashboardController::class, 'index'])->name('dashboard');
    
    // Error routes
    Route::get('/error', [MemberErrorController::class, 'show'])->name('error');
    Route::get('/error/http/{code}', [MemberErrorController::class, 'http'])->name('error.http');
    Route::get('/error/authentication/{key}', [MemberErrorController::class, 'authentication'])->name('error.authentication');
    Route::get('/error/authorization/{key}', [MemberErrorController::class, 'authorization'])->name('error.authorization');
    Route::get('/error/validation/{key}', [MemberErrorController::class, 'validation'])->name('error.validation');
    Route::get('/error/file-upload/{key}', [MemberErrorController::class, 'fileUpload'])->name('error.file-upload');
    Route::get('/error/database/{key}', [MemberErrorController::class, 'database'])->name('error.database');
    Route::get('/error/payment/{key}', [MemberErrorController::class, 'payment'])->name('error.payment');
    Route::get('/error/network/{key}', [MemberErrorController::class, 'network'])->name('error.network');
    Route::get('/error/system/{key}', [MemberErrorController::class, 'system'])->name('error.system');
    
    Route::get('/profile', [MemberProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/show', [MemberProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [MemberProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [MemberProfileController::class, 'update'])->name('profile.update');

    Route::prefix('certificates')->name('certificates.')->group(function () {
        Route::get('/', [CertificateController::class, 'index'])->name('index');
        Route::get('/membership-preview', [CertificateController::class, 'getMembershipCertificate'])->name('membership-preview');
        Route::get('/loan/{id}', [CertificateController::class, 'showLoanCertificate'])->name('show-loan');
        Route::get('/loan/{id}/print', [CertificateController::class, 'printLoanCertificate'])->name('print-loan');
        Route::get('/share/{id}', [CertificateController::class, 'showShareCertificate'])->name('show-share');
        Route::get('/share/{id}/print', [CertificateController::class, 'printShareCertificate'])->name('print-share');
    });

    Route::get('/loans', [MemberLoanController::class, 'index'])->name('loans.index');
    Route::get('/loans/create', [MemberLoanController::class, 'create'])->name('loans.create');
    Route::post('/loans/store-basic-info', [MemberLoanController::class, 'storeBasicInfo'])->name('loans.store-basic-info');
    Route::post('/loans/store-loan-details', [MemberLoanController::class, 'storeLoanDetails'])->name('loans.store-loan-details');
    Route::post('/loans/store-collateral', [MemberLoanController::class, 'storeCollateral'])->name('loans.store-collateral');
    Route::post('/loans', [MemberLoanController::class, 'store'])->name('loans.store');
    Route::get('/loans/{encryptedLoanNumber}', [MemberLoanController::class, 'show'])->name('loans.show');

    Route::get('/savings', [MemberSavingController::class, 'index'])->name('savings.index');

    Route::get('/shares', [MemberShareController::class, 'index'])->name('shares.index');

    Route::get('/saving-plan', [MemberSavingPlanController::class, 'index'])->name('saving-plan.index');

    Route::get('/deposits', [MemberDepositController::class, 'index'])->name('deposits.index');
    Route::get('/deposits/{encryptedCertificateNumber}', [MemberDepositController::class, 'show'])->name('deposits.show');

    Route::get('/swf', [MemberSwfController::class, 'index'])->name('swf.index');

    Route::get('/investments', [MemberInvestmentController::class, 'index'])->name('investments.index');
    Route::get('/investments/{encryptedId}', [MemberInvestmentController::class, 'show'])->name('investments.show');

    Route::get('/statements', [MemberStatementController::class, 'index'])->name('statements.index');
    Route::get('/statements/download/{type}', [MemberStatementController::class, 'download'])->name('statements.download');

    Route::get('/notifications', [MemberNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [MemberNotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/{id}/read', [MemberNotificationController::class, 'markRead'])->name('notifications.read');
});

// Certificate verification route (public)
Route::get('/verify-certificate/{code}', function($code) {
    return view('certificates.verify', compact('code'));
})->name('verify.certificate');

// Registration Routes
Route::prefix('register')->name('register.')->group(function () {
    // Account creation (unauthenticated)
    Route::get('/', [RegistrationAccountController::class, 'showCreateForm'])->name('create');
    Route::post('/', [RegistrationAccountController::class, 'createAccount'])->name('store');

    // Verification & password (authenticated, pre-registration)
    Route::middleware('auth')->group(function () {
        Route::get('/verify', [RegistrationAccountController::class, 'showVerificationForm'])->name('verify');
        Route::post('/verify/phone', [RegistrationAccountController::class, 'verifyPhone'])->name('verify.phone');
        Route::post('/resend/phone', [RegistrationAccountController::class, 'resendPhoneCode'])->name('resend.phone');
        Route::get('/password', [RegistrationAccountController::class, 'showPasswordForm'])->name('password');
        Route::post('/password', [RegistrationAccountController::class, 'createPassword'])->name('password.store');
    });
});

// Registration Application Routes (authenticated + stage gating)
Route::prefix('application')->name('register.')->middleware(['auth', 'registration.stage'])->group(function () {
    Route::get('/', [RegistrationDashboardController::class, 'index'])->name('dashboard');
    Route::get('/status', [RegistrationDashboardController::class, 'status'])->name('status');

    Route::get('/membership-type', [RegistrationMembershipTypeController::class, 'showForm'])->name('membership-type');
    Route::post('/membership-type', [RegistrationMembershipTypeController::class, 'select'])->name('membership-type.store');

    Route::get('/payment', [RegistrationPaymentController::class, 'showForm'])->name('payment');
    Route::post('/payment', [RegistrationPaymentController::class, 'process'])->name('payment.process');
    Route::get('/payment/{paymentId}/pending', [RegistrationPaymentController::class, 'pending'])->name('payment.pending');
    Route::post('/payment/{paymentId}/confirm', [RegistrationPaymentController::class, 'confirm'])->name('payment.confirm');
    Route::post('/payment/callback', [RegistrationPaymentController::class, 'callback'])->name('payment.callback');

    Route::get('/personal-details', [RegistrationPersonalDetailsController::class, 'showForm'])->name('personal-details');
    Route::post('/personal-details', [RegistrationPersonalDetailsController::class, 'save'])->name('personal-details.store');

    Route::get('/profile-photo', [RegistrationProfileController::class, 'showForm'])->name('profile-photo');
    Route::post('/profile-photo', [RegistrationProfileController::class, 'upload'])->name('profile-photo.upload');

    Route::get('/bank-details', [RegistrationBankDetailsController::class, 'showForm'])->name('bank-details');
    Route::post('/bank-details', [RegistrationBankDetailsController::class, 'save'])->name('bank-details.store');

    Route::get('/next-of-kin', [RegistrationNextOfKinController::class, 'showForm'])->name('next-of-kin');
    Route::post('/next-of-kin', [RegistrationNextOfKinController::class, 'save'])->name('next-of-kin.store');

    Route::get('/referral', [RegistrationReferralController::class, 'showForm'])->name('referral');
    Route::post('/referral', [RegistrationReferralController::class, 'save'])->name('referral.store');
    Route::post('/validate-membercode', [RegistrationReferralController::class, 'validateMembercode'])->name('validate-membercode');

    Route::get('/saving-plan', [RegistrationSavingPlanController::class, 'showForm'])->name('saving-plan');
    Route::post('/saving-plan', [RegistrationSavingPlanController::class, 'save'])->name('saving-plan.store');

    Route::get('/review', [RegistrationReviewController::class, 'index'])->name('review');

    Route::get('/submit', [RegistrationSubmitController::class, 'showForm'])->name('submit');
    Route::post('/submit', [RegistrationSubmitController::class, 'submit'])->name('submit.store');
});

// Admin Membership Application Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('membership-applications', AdminMembershipApplicationController::class)->only(['index', 'show']);
    Route::post('membership-applications/{application}/approve', [AdminMembershipApplicationController::class, 'approve'])->name('membership-applications.approve');
    Route::post('membership-applications/{application}/reject', [AdminMembershipApplicationController::class, 'reject'])->name('membership-applications.reject');
    Route::post('membership-applications/{application}/correction', [AdminMembershipApplicationController::class, 'requestCorrection'])->name('membership-applications.request-correction');
});
