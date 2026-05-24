<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Region;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;
use App\Models\Employee;
use App\Models\Status;
use App\Models\Campus;
use App\Models\Office;
use App\Models\FamilyBg;
use App\Models\EducBg;
use App\Models\Qualification;
use App\Models\Eligibility;
use App\Models\WorkExperience;
use App\Models\VoluntaryWork;
use App\Models\LearningDev;
use App\Models\OtherInfo;
use App\Models\InfoQuestion;
use App\Models\PdsReference;
use App\Models\GovId;
use App\Models\Device;
use PDF;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class PdsController extends Controller
{
    private function resolveEmployeeRouteId($id): ?int
    {
        if ($id === null || $id === '') {
            return null;
        }

        if (is_numeric($id)) {
            return (int) $id;
        }

        $decrypted = shortDecrypt((string) $id);

        return is_numeric($decrypted) ? (int) $decrypted : null;
    }

    private function redirectEncryptedRouteIfNeeded(string $routeName, $id)
    {
        if ($id !== null && is_numeric($id)) {
            return redirect()->route($routeName, shortEncrypt((string) $id));
        }

        return null;
    }

    private function employeeIdForPds($id, string $routeName): array
    {
        if (auth()->guard('employee')->check()) {
            if ($id === null) {
                return [auth()->guard('employee')->id(), null];
            }

            $empid = $this->resolveEmployeeRouteId($id);

            if ((int) $empid !== (int) auth()->guard('employee')->id()) {
                return [null, redirect()->route($routeName)];
            }

            return [null, redirect()->route($routeName)];
        }

        if ($redirect = $this->redirectEncryptedRouteIfNeeded($routeName, $id)) {
            return [null, $redirect];
        }

        $guard = $this->getGuard();
        $empid = $id !== null
            ? $this->resolveEmployeeRouteId($id)
            : auth()->guard($guard)->user()->id;

        if (!$empid) {
            abort(404);
        }

        return [$empid, null];
    }

    private function padCsvAttribute($model, string $attribute, int $size, string $default = ''): void
    {
        if (!$model) {
            return;
        }

        $values = explode(',', (string) ($model->{$attribute} ?? ''));
        $values = array_pad($values, $size, $default);

        $model->{$attribute} = implode(',', $values);
    }

    private function normalizePdsPdfData(?OtherInfo $otherinfo, ?InfoQuestion $infoquestion): void
    {
        $this->padCsvAttribute($otherinfo, 'skills_hob', 3);
        $this->padCsvAttribute($otherinfo, 'recognition', 3);
        $this->padCsvAttribute($otherinfo, 'mem_org', 3);

        $this->padCsvAttribute($infoquestion, 'question', 13, '0');
        $this->padCsvAttribute($infoquestion, 'detail', 13);
        $this->padCsvAttribute($infoquestion, 'qdetails', 13);
    }

    public function getGuard()
    {
        if(\Auth::guard('web')->check()) {
            return 'web';
        } elseif(\Auth::guard('employee')->check()) {
            return 'employee';
        }
    }

    public function columnStat($empid){
        $familyBg = FamilyBg::firstOrCreate(['empid' => $empid]);
        $educBg = EducBg::firstOrCreate(['empid' => $empid]);
        $eligibility = Eligibility::where('empid', $empid)->get();
        $workexperience = WorkExperience::where('empid', $empid)->get();
        $voluntaryworks = VoluntaryWork::where('empid', $empid)->get();
        $learningdev = LearningDev::where('empid', $empid)->get();
        $otherinfo = OtherInfo::firstOrCreate(['empid' => $empid]);
        $infoquestion = InfoQuestion::firstOrCreate(['empid' => $empid]);
        $references = PdsReference::firstOrCreate(['empid' => $empid]);
        $govids= GovId::firstOrCreate(['empid' => $empid]);
        
        $columnstatus = [
            'colfamstat' => $familyBg->famhasAnyValue(),
            'coleducstat' => $educBg->educhasAnyValue(),
            'eligibility' => $eligibility,
            'workexperience' => $workexperience,
            'voluntaryworks' => $voluntaryworks,
            'learningdev' => $learningdev,
            'colotherinfo' => $otherinfo->otherinfoAnyValue(),
            'colinfoquestion' => $infoquestion->infoquestionValue(),
            'colreferences' => $references->referencesValue(),
            'colgovids' => $govids->govidsValue(),
        ];
        
        return $columnstatus;
    }

    public function signature($id = null){
        $guard = $this->getGuard();
        $empid = $id ? $this->resolveEmployeeRouteId($id) : auth()->guard($guard)->user()->id;

        if (!$empid) {
            abort(404);
        }

        if (auth()->guard('employee')->check() && (int) $empid !== (int) auth()->guard('employee')->id()) {
            abort(403);
        }

        $employee = Employee::findOrFail($empid);


        $imageData = asset('Uploads/esign-default.jpg'); // fallback
        if ($employee->esign) {
            try {
                $decrypted = Crypt::decrypt($employee->esign);
                $imageData = 'data:image/png;base64,' . base64_encode($decrypted);
            } catch (\Exception $e) {
                // fallback stays default
            }
        }

        return view("emp.signature", compact('employee', 'guard', 'empid', 'imageData'));
    }

    public function uploadSignature(Request $request, $id = null)
    {
        $request->validate([
            'signature' => 'required|image|mimes:png|max:2048',
        ]);

        $guard = $this->getGuard();
        $empid = $id ? $this->resolveEmployeeRouteId($id) : auth()->guard($guard)->user()->id;

        if (!$empid) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record was not found.'
            ], 404);
        }

        if (auth()->guard('employee')->check() && (int) $empid !== (int) auth()->guard('employee')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to update this signature.'
            ], 403);
        }

        $employee = Employee::findOrFail($empid);

        if ($request->hasFile('signature')) {
            try {
                $file = $request->file('signature');
                $binaryContent = file_get_contents($file->getRealPath());

                $encryptedContent = Crypt::encrypt($binaryContent);
                $employee->esign = $encryptedContent;
                $employee->save();

                $imageUrl = 'data:image/png;base64,' . base64_encode($binaryContent);

                return response()->json([
                    'success' => true,
                    'image_url' => $imageUrl,
                ]);
            } catch (\Exception $e) {
                Log::error('Unable to upload employee e-signature.', [
                    'employee_id' => $employee->id,
                    'exception' => $e,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unable to upload signature. Please try again.'
                ], 500);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'No file selected or upload failed'
        ], 400);
    }

    public function empPDS(){
        $guard = $this->getGuard();
        $empid = auth()->guard($guard)->user()->id; 

        if ($guard === 'employee') {
            return redirect()->route('PDS');
        }

        return redirect()->route('PDS', shortEncrypt((string) $empid));
    }

    public function generatepds($id = null){
        [$empid, $redirect] = $this->employeeIdForPds($id, 'generatepds');
        if ($redirect) {
            return $redirect;
        }

        $employee = Employee::findOrFail($empid);

        $familyBg = FamilyBg::firstOrCreate(['empid' => $employee->emp_ID]);
        $educBg = EducBg::firstOrCreate(['empid' => $employee->emp_ID]);
        $eligibility = Eligibility::where('empid', $employee->emp_ID)->where('status', '!=', 0)->get();
        $workexperience = WorkExperience::where('empid', $employee->emp_ID)
        ->where('status', '!=', 0)->orderByDesc('inc_date1')->orderByDesc('inc_date2')->get();

        $voluntaryworks = VoluntaryWork::where('empid', $employee->emp_ID)->where('status', '!=', 0)
        ->where('status', '!=', 0)->orderByDesc('inc_date1')->orderByDesc('inc_date2')->get();
        
        $learningdev = LearningDev::where('empid', $employee->emp_ID)->where('status', '!=', 0)
        ->where('status', '!=', 0)->orderByDesc('inc_date1')->orderByDesc('inc_date2')->get();
            
        $otherinfo = OtherInfo::firstOrCreate(['empid' => $employee->emp_ID]);
        $infoquestion = InfoQuestion::firstOrCreate(['empid' => $employee->emp_ID]);
        $references = PdsReference::firstOrCreate(['empid' => $employee->emp_ID]);
        $govids= GovId::firstOrCreate(['empid' => $employee->emp_ID]);
        $this->normalizePdsPdfData($otherinfo, $infoquestion);
 
        $barangay = Barangay::find($employee->add_brgy);
        $city = City::where('city_id', $employee->add_city)->first();
        $province = Province::where('province_id', $employee->add_prov)->first();

        $barangay1 = Barangay::find($employee->padd_brgy);
        $city1 = City::where('city_id', $employee->padd_city)->first();
        $province1 = Province::where('province_id', $employee->padd_prov)->first();

        $datas = [
            'employee' => $employee,
            'familyBg' => $familyBg,
            'educBg' => $educBg,
            'eligibility' => $eligibility,
            'workexperience' => $workexperience,
            'voluntaryworks' => $voluntaryworks,
            'learningdev' => $learningdev,
            'otherinfo' => $otherinfo,
            'infoquestion' => $infoquestion,
            'references' => $references,
            'govids' => $govids, 
            'barangay' => $barangay,
            'city' => $city,
            'province' => $province,
            'barangay1' => $barangay1,
            'city1' => $city1,
            'province1' => $province1,
        ];

        $customPaper = array(0, 0, 612, 990);
        $pdf = \PDF::loadView('emp.generate-pds', compact('datas'))->setPaper($customPaper, 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'enable_php' => false,
            'enable_javascript' => false,
            'defaultFont' => 'Helvetica',
            'dpi' => 96,
            'margin-top' => 0,
            'margin-right' => 0,
            'margin-bottom' => 0,
            'margin-left' => 0,
        ]);

        return $pdf->stream('pds-' . $employee->emp_ID . '.pdf');
    }

    public function genpdsAtthachment($id = null){
        [$empid, $redirect] = $this->employeeIdForPds($id, 'genpdsAtthachment');
        if ($redirect) {
            return $redirect;
        }

        $employee = Employee::findOrFail($empid);
        $workexperience = WorkExperience::where('empid', $employee->emp_ID)->where('status', '!=', 0)->get();

        $customPaper = array(0, 0, 612, 970);
        $pdf = \PDF::loadView('emp.gen-pds-attachment', compact('workexperience'))->setPaper($customPaper, 'portrait');

        $pdf->setOption('margin-top', 0);
        $pdf->setOption('margin-right', 0);
        $pdf->setOption('margin-bottom', 0);
        $pdf->setOption('margin-left', 0);
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('enable_php', false);
        $pdf->setOption('enable_javascript', false);
        $pdf->setOption('defaultFont', 'Helvetica');
        $pdf->setOption('dpi', 96);

        return $pdf->stream('pds-work-experience-' . $employee->emp_ID . '.pdf');
    }
}
