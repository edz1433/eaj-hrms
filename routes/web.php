<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\NoCacheMiddleware;
use App\Http\Controllers\LoginAuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\TirednessController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MyAccountController;
use App\Http\Controllers\DtrController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\PdsController;
use App\Http\Controllers\FamilybgController;
use App\Http\Controllers\EducBgController;
use App\Http\Controllers\EligibilityController;
use App\Http\Controllers\WorkExperienceController;
use App\Http\Controllers\VoluntaryWorkController;
use App\Http\Controllers\LearningDevController;
use App\Http\Controllers\OtherInfoController;
use App\Http\Controllers\InfoQuestionController;
use App\Http\Controllers\PdsReferencesController;
use App\Http\Controllers\GovIdController;
use App\Http\Controllers\LeaveCreditController;
use App\Http\Controllers\LeaveApplicationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PendingController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\JobHiringController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ModifyController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TimeEntryPageController;

Route::get('/', function () {
    if (Auth::guard('web')->check()) {
        return redirect()->route('dashboard');
    }elseif(Auth::guard('employee')->check()){
        return redirect()->route('empPDS');
    }
    return view('login');
});

//login
Route::get('/login',[LoginAuthController::class,'getLogin'])->name('getLogin')->middleware([NoCacheMiddleware::class]);
Route::post('/login',[LoginAuthController::class,'postLogin'])->name('postLogin');
Route::get('/time-entry', [TimeEntryPageController::class, 'index'])->name('time-entry.index');
// Route::get('/update-pass', [EmployeeController::class, 'updateEmployeePasswords']);

Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
Route::get('/verify', [GoogleAuthController::class, 'verifyForm'])->name('verify');
Route::post('/verify', [GoogleAuthController::class, 'verify'])->name('verify.code');
// Route::get('/convert-esign', [PdsController::class, 'convertEsign'])->name('convertEsign');

Route::group(['middleware' => ['login_auth', NoCacheMiddleware::class]], function() {
    //Performance
    Route::post('/data-privacy-notice', [MasterController::class, 'dataPrivacyNotice'])->name('dataPrivacyNotice');
    //Performance
    Route::get('/system-performance', [PerformanceController::class, 'systemPerformance'])->name('systemPerformance');
    // Dashboard
    Route::get('/dashboard', [MasterController::class, 'dashboard'])->name('dashboard');
    Route::get('/data-privacy', [MasterController::class, 'dataPrivacy'])->name('dataPrivacy');
    Route::get('/payroll', fn () => redirect('https://hris.cpsu.edu.ph/pms'))->name('payroll');

    // DTR
    Route::prefix('dtr')->group(function() {
        Route::get('/', [DtrController::class, 'dtrRead'])->name('dtr-read');
        Route::post('/', [DtrController::class, 'dtrSearch'])->name('dtrSearch');
        Route::get('/dtr-logs', [DtrController::class, 'dtrLogs'])->name('dtrLogs');
        Route::post('/dtr-logs', [DtrController::class, 'dtrLogs'])->name('dtrLogspost');
        Route::get('/dtr-log-pdf/{employeeId}/{dateFrom}/{dateTo}/{overtime?}', [DtrController::class, 'logDtrView'])->name('logDtrView');
        Route::get('/pdf', [DtrController::class, 'dtrPdf'])->name('dtr-pdf');
    });
    // User
    Route::prefix('user')->group(function() {
        Route::get('/', [UserController::class, 'ulist'])->name('ulist');
        Route::post('/create', [UserController::class, 'uCreate'])->name('uCreate');
        Route::get('/edit/{id}', [UserController::class, 'uEdit'])->name('uEdit');
        Route::post('/update', [UserController::class, 'uUpdate'])->name('uUpdate');
        Route::post('/delete', [UserController::class, 'uDelete'])->name('uDelete');

        Route::get('/myaccount', [UserController::class, 'myAccount'])->name('myAccount');
    });

    // My Account
    Route::prefix('/myaccount')->group(function(){
        // Route::get('/', [MyAccountController::class, 'myAccount']) ->name('myAccount');
        // Route::post('/update-account', [MyAccountController::class, 'updateAccount']) ->name('updateAccount');
    }); 

    Route::prefix('career')->group(function () {
        Route::get('/', [JobHiringController::class, 'jlist'])->name('jlist');
        Route::post('/create', [JobHiringController::class, 'jCreate'])->name('jCreate');
        Route::get('/edit/{id}', [JobHiringController::class, 'jEdit'])->name('jEdit');
        Route::post('/update', [JobHiringController::class, 'jUpdate'])->name('jUpdate');
        Route::post('/delete', [JobHiringController::class, 'jDelete'])->name('jDelete');

        // optional extra route if applicants can apply directly
        Route::get('/applications', [MasterController::class, 'appList'])->name('appList');
        Route::post('/applications/create', [ApplicationController::class, 'appCreate'])->name('appCreate');
        Route::post('/application/setCtrlNo', [ApplicationController::class, 'setCtrlNo'])->name('setCtrlNo');
        Route::post('/application/update-status', [ApplicationController::class, 'updateStatus'])->name('updateStatus');
    });

    // Employee
    Route::prefix('employees')->group(function() {
        Route::get('/', [EmployeeController::class, 'employees'])->name('employees');
        Route::get('/add', [EmployeeController::class, 'empAdd'])->name('empAdd');
        Route::get('/generate', [EmployeeController::class, 'genEmp'])->name('genEmp');

        Route::post('/create', [EmployeeController::class, 'empCreate'])->name('empCreate');
        Route::post('/update-profile/{id}', [EmployeeController::class, 'updateProfilePicture'])->name('updateProfilePicture');
        Route::post('/update', [EmployeeController::class, 'empUpdate'])->name('empUpdate');
        Route::post('/employee-update', [EmployeeController::class, 'employeeUpdate'])->name('employeeUpdate');
        Route::get('/edit/{id}', [EmployeeController::class, 'empEdit'])->name('empEdit');
        Route::post('/toggle-acct-stat', [EmployeeController::class, 'toggleAcctStat'])->name('toggleAcctStat');
        Route::post('/official-time/{empid}', [EmployeeController::class, 'OfficialTimeRead'])->name('OfficialTimeRead');
        Route::post('/official-time-create', [EmployeeController::class, 'OfficialTimeCreate'])->name('OfficialTimeCreate');
        Route::get('/emp-qr', [EmployeeController::class, 'empQr'])->name('empQr');

        Route::get('/delete/{id}', [EmployeeController::class, 'empDelete'])->name('empDelete');
    });
    
    Route::prefix('tardiness')->group(function(){
        Route::get('/data', [TirednessController::class, 'readTiredness'])->name('readTiredness');
        Route::post('/data', [TirednessController::class, 'readTiredness'])->name('tirednessSearch');
        Route::get('/pdf/{employeeId}/{month}', [TirednessController::class, 'pdfTirednes'])->name('pdfTirednes');
    });

    Route::prefix('pending')->group(function(){
        Route::get('/{type}/{cat?}', [PendingController::class, 'readPending'])->name('readPending');
    });
    
    //pds
    Route::prefix('pds')->group(function() {
        Route::get('/', [PdsController::class, 'empPDS'])->name('empPDS');  
        Route::get('/generate/{id?}', [PdsController::class, 'generatepds'])->name('generatepds');
        Route::get('/attachment/{id?}', [PdsController::class, 'genpdsAtthachment'])->name('genpdsAtthachment');
        
        //personal Info
        Route::get('personal-info/{id?}', [EmployeeController::class, 'PDS'])->name('PDS');   

        //family background
        Route::get('/family-bg/{id?}', [FamilybgController::class, 'familybg'])->name('familybg');
        Route::post('/update-child', [FamilybgController::class, 'updateChild'])->name('update-child');
        Route::post('/familybg-update', [FamilybgController::class, 'familyBgUpdate'])->name('familyBgUpdate');
        Route::post('/familybg-update-array', [FamilybgController::class, 'familyBgUpdateArray'])->name('familyBgUpdateArray');
        
        //Educational Background
        Route::get('/educ-bg/{id?}', [EducBgController::class, 'educbg'])->name('educbg');
        Route::post('/update-educ-child', [EducBgController::class, 'updateEducChild'])->name('updateEducChild');
        Route::post('/educbg-update', [EducBgController::class, 'educBgUpdate'])->name('educBgUpdate');
        Route::post('/educbg-update-array', [EducBgController::class, 'educBgUpdateArray'])->name('educBgUpdateArray');

        Route::post('/graduate-studies-update', [EducBgController::class, 'graduateStudiesUpdate'])->name('graduateStudiesUpdate');
        Route::post('/educbg-update-graduate-array', [EducBgController::class, 'educBgUpdateGraduateArray'])->name('educBgUpdateGraduateArray');

        //Eligibility
        Route::get('/eligibility/{id?}', [EligibilityController::class, 'eligibility'])->name('eligibility');
        Route::post('/eligibility-create', [EligibilityController::class, 'eligibilityCreate'])->name('eligibilityCreate');
        Route::get('/eligibility-edit/{id?}/{eid}', [EligibilityController::class, 'eligibilityEdit'])->name('eligibilityEdit');
        Route::post('/eligibility-update/{id}', [EligibilityController::class, 'eligibilityUpdate'])->name('eligibilityUpdate');
        Route::post('/eligibility-delete/{id}', [EligibilityController::class, 'eliDelete'])->name('eliDelete');
        Route::post('/eligibility-approve/{id}', [EligibilityController::class, 'eliApprove'])->name('eliApprove');
        Route::post('/eligibility-cancel', [EligibilityController::class, 'eliCancel'])->name('eliCancel');

        //Work-experience
        Route::get('/work-experience/{id?}', [WorkExperienceController::class, 'workexperience'])->name('work-experience');
        Route::post('/work-experience-create', [WorkExperienceController::class, 'workexperienceCreate'])->name('workexperienceCreate');
        Route::get('/work-experience-edit/{id?}/{eid}', [WorkExperienceController::class, 'workexperienceEdit'])->name('workexperienceEdit');
        Route::post('/work-experience-update/{id}', [WorkExperienceController::class, 'workexperienceUpdate'])->name('workexperienceUpdate');
        Route::post('/work-experience-delete/{id}', [WorkExperienceController::class, 'workDelete'])->name('workDelete');
        Route::post('/work-experience-approve/{id}', [WorkExperienceController::class, 'expApprove'])->name('expApprove');
        Route::post('/work-experience-cancel', [WorkExperienceController::class, 'workexperienceCancel'])->name('workexperienceCancel');

        //Voluntary-works
        Route::get('/voluntary-work/{id?}', [VoluntaryWorkController::class, 'voluntaryworks'])->name('voluntary-work');
        Route::post('/voluntary-work-create', [VoluntaryWorkController::class, 'voluntaryworksCreate'])->name('voluntaryworksCreate');
        Route::get('/voluntary-work-edit/{id?}/{eid}', [VoluntaryWorkController::class, 'voluntaryworksEdit'])->name('voluntaryworksEdit');
        Route::post('/voluntary-work-update/{id}', [VoluntaryWorkController::class, 'voluntaryworksUpdate'])->name('voluntaryworksUpdate');
        Route::post('/voluntary-work-delete/{id}', [VoluntaryWorkController::class, 'voluntaryworkDelete'])->name('voluntaryworkDelete');
        Route::post('/voluntary-work-approve/{id}', [VoluntaryWorkController::class, 'voluntaryworksApprove'])->name('voluntaryworksApprove');
        Route::post('/voluntary-work-cancel', [VoluntaryWorkController::class, 'voluntaryworksCancel'])->name('voluntaryworksCancel');

        //Learning-development
        Route::get('/learning-dev/{id?}', [LearningDevController::class, 'learningdev'])->name('learning-dev');
        Route::post('/learning-dev-create', [LearningDevController::class, 'learningdevCreate'])->name('learningdevCreate');
        Route::get('/learning-dev-edit/{id?}/{eid}', [LearningDevController::class, 'learningdevEdit'])->name('learningdevEdit');
        Route::post('/learning-dev-update/{id}', [LearningDevController::class, 'learningdevUpdate'])->name('learningdevUpdate');
        Route::post('/learning-dev-delete/{id}', [LearningDevController::class, 'learningdevDelete'])->name('learningdevDelete');
        Route::post('/learning-dev-approve/{id}', [LearningDevController::class, 'learningdevApprove'])->name('learningdevApprove');
        Route::post('/learning-dev-cancel', [LearningDevController::class, 'learningdevCancel'])->name('learningdevCancel');

        //Other Information
        Route::get('/other-info/{id?}', [OtherInfoController::class, 'otherInfo'])->name('otherInfo');
        Route::post('/update-child-oi', [OtherInfoController::class, 'updateChild'])->name('update-child-oi');
        Route::post('/otherinfo-update', [OtherInfoController::class, 'otherInfoUpdate'])->name('otherInfoUpdate');
        Route::post('/otherInfo-update-array', [OtherInfoController::class, 'otherInfoUpdateArray'])->name('otherInfoUpdateArray');

        //Other Information Question
        Route::get('/info-question/{id?}', [InfoQuestionController::class, 'infoQuestion'])->name('infoQuestion');
        Route::post('/update-info-question', [InfoQuestionController::class, 'update'])->name('update.info.question');
        
        //References
        Route::get('/references/{id?}', [PdsReferencesController::class, 'references'])->name('references');
        Route::post('/update-references', [PdsReferencesController::class, 'update'])->name('update.references');

        //Government ID
        Route::get('/government-id/{id?}', [GovIdController::class, 'govids'])->name('govids'); 
        Route::post('/update-govids', [GovIdController::class, 'update'])->name('update.govids');
        
        //Signature
        Route::get('/signature/{id?}', [PdsController::class, 'signature'])->name('signature');
        Route::post('/upload-signature/{id?}', [PdsController::class, 'uploadSignature'])->name('uploadSignature');
    });
    
    // Modify
    Route::prefix('modify')->group(function() {
        Route::post('/show', [ModifyController::class, 'modifyShow'])->name('modifyShow');
        Route::post('/update', [ModifyController::class, 'modifyUpdate'])->name('modifyUpdate');
    });

    // Office
    Route::prefix('office')->group(function() {
        Route::get('/', [OfficeController::class, 'officeList'])->name('officeList');
        Route::post('/create', [OfficeController::class, 'officeCreate'])->name('officeCreate');
        Route::get('/edit/{id}', [OfficeController::class, 'officeEdit'])->name('officeEdit');
        Route::post('/update', [OfficeController::class, 'officeUpdate'])->name('officeUpdate');
        Route::get('/delete/{id}', [OfficeController::class, 'officeDelete'])->name('officeDelete');
    });

    //Address
    Route::prefix('/address')->group(function() {
        Route::get('/provinces/{regionId}', [AddressController::class, 'getProvinces'])->name('getProvinces');
        Route::get('/cities/{provinceId}', [AddressController::class, 'getCities'])->name('getCities');
        Route::get('/barangays/{cityId}', [AddressController::class, 'getBarangays'])->name('getBarangays');
    }); 

    // Calendar
    Route::prefix('events')->group(function() {
        Route::get('/list', [CalendarController::class, 'eventRead'])->name('eventRead');
        Route::get('/show', [CalendarController::class, 'eventShow'])->name('eventShow');
        // Route::post('/create', [CalendarController::class, 'eventCreate'])->name('eventCreate');
        Route::get('/edit/{id}', [CalendarController::class, 'eventEdit'])->name('eventEdit');
        Route::post('/update', [CalendarController::class, 'eventUpdate'])->name('eventUpdate');
        Route::get('/delete/{id}', [CalendarController::class, 'eventDelete'])->name('eventDelete');
    });
    
    //Leave-Credits
    Route::prefix('leaves')->group(function() {
        Route::get('/{id?}', [LeaveCreditController::class, 'leavesRead'])->name('leavesRead');
        Route::post('/leaves-create', [LeaveCreditController::class, 'leavesCreate'])->name('leavesCreate');
        Route::post('/leaves-deduct', [LeaveCreditController::class, 'leavescreditDeduct'])->name('leavescreditDeduct');
        Route::post('/leaves-deduct-update', [LeaveCreditController::class, 'leavescreditDeductUpdate'])->name('leavescreditDeductUpdate');
        Route::post('/leaves-edit/{id}', [LeaveCreditController::class, 'leavesEdit'])->name('leavesEdit');
        Route::post('/leaves-update', [LeaveCreditController::class, 'leavesUpdate'])->name('leavesUpdate');
        Route::post('/delete/{id}/{empid}', [LeaveCreditController::class, 'leavesDelete'])->name('leavesDelete');  
    });

    //Notification
    Route::prefix('notification')->group(function() {
        // Route::get('/load/{page}', [NotificationController::class, 'loadMore'])->name('notificationload');
        Route::get('/load', [NotificationController::class, 'loadMore'])->name('notificationload');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
        Route::get('/update-notif/{menid}/{lappid}/{menu}', [NotificationController::class, 'updateNotif'])->name('updateNotif');
    });

    Route::prefix('time-entry')->name('time-entry.')->group(function() {
        Route::get('/register', [TimeEntryPageController::class, 'register'])->middleware('face_registration')->name('register');
        Route::get('/logs', [TimeEntryPageController::class, 'logs'])->middleware('face_registration')->name('logs');
    });

    // leave
    Route::prefix('leave')->group(function() {
        Route::get('/', [LeaveCreditController::class, 'leavesReadEmp'])->name('leavesReadEmp');
        Route::post('/create', [LeaveApplicationController::class, 'LeaveAppCreate'])->name('LeaveAppCreate');
        
        Route::get('/status/{id?}', [LeaveApplicationController::class, 'leaveStatus'])->name('leaveStatus');
        Route::post('/leave-wpay', [LeaveApplicationController::class, 'leaveWpay'])->name('leaveWpay');
        Route::post('/approve', [LeaveApplicationController::class, 'leaveApprove'])->name('leaveApprove');
        Route::post('/approve-pres', [LeaveApplicationController::class, 'leaveApprovePres'])->name('leaveApprovePres');
        Route::post('/dis-approve', [LeaveApplicationController::class, 'leaveDisapprove'])->name('leaveDisapprove');
        Route::get('/preview-leave/{id}', [LeaveApplicationController::class, 'previewLeave'])->name('previewLeave');   
        Route::post('/leave-live/{id?}', [LeaveApplicationController::class, 'leaveLive'])->name('leaveLive');
        Route::get('/history/{id?}', [LeaveApplicationController::class, 'historyRead'])->name('historyRead');
        Route::post('/return/{id?}', [LeaveApplicationController::class, 'leaveReturn'])->name('leaveReturn');

        Route::post('/undo/{id?}', [LeaveApplicationController::class, 'leaveUndo'])->name('leaveUndo');
        
        Route::post('/cacelLeave/{id}', [LeaveApplicationController::class, 'cancelLeave'])->name('cancelLeave');
        
        Route::post('/get-pdf-path', [LeaveApplicationController::class, 'getPdfPath'])->name('getPdfPath');
        
        Route::post('/leaves-report', [LeaveApplicationController::class, 'leaveReport'])->name('leaveReport');
    });
    
    // events
    Route::prefix('event')->group(function() {
        Route::get('/', [EventController::class, 'eventIndex'])->name('eventIndex');
        Route::post('/create', [EventController::class, 'eventCreate'])->name('eventCreate');
        Route::get('/event-show', [EventController::class, 'eventShow'])->name('event.show');
        Route::get('/reports', [EventController::class, 'showReport'])->name('showReport');
        Route::post('/reports', [EventController::class, 'searchReport'])->name('searchReport');
        Route::get('/reports-generate/{eventid}/{campusid}/{statusid}', [EventController::class, 'reportGenrate'])->name('reportGenrate');
    });

    // ── Settings (replaced MasterController stub with full SettingsController) ──
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/save-org',         [SettingsController::class, 'saveOrg'])->name('settings.saveOrg');
    Route::post('/settings/save-general',     [SettingsController::class, 'saveGeneral'])->name('settings.saveGeneral');
    Route::post('/settings/save-theme',       [SettingsController::class, 'saveTheme'])->name('settings.saveTheme');
    Route::post('/settings/save-menu',        [SettingsController::class, 'saveMenuVisibility'])->name('settings.saveMenu');
    Route::post('/settings/save-permissions', [SettingsController::class, 'saveUserPermissions'])->name('settings.savePermissions');
    Route::post('/settings/link-employee',    [SettingsController::class, 'linkEmployee'])->name('settings.linkEmployee');

    Route::get('/leave/disapprove', [LeaveApplicationController::class, 'leaveDisapprove']);
    Route::post('/logout', [MasterController::class, 'logout'])->name('logout');
});



