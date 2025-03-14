<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\{User, UserEvent, UserEventLkpp, HistoryKelasDiklatOnline};
use App\Exports\{
    ExportDataFormByEvent,
    ExportPresensiPelatihan,
    ExportDataTo,
    ExportDenah
};
use Illuminate\Support\Facades\Validator;
use Carbon\CarbonPeriod;
use Carbon\Carbon;


class ViewMemberController extends Controller
{
    public function downloadZip($tipe, $id_event){
        $users = UserEvent::where([
            ['event_id', $id_event],
            ['is_deleted', 0]
        ])
        ->get();
        $filePaths = [];
        foreach($users as $u){                        
            if (substr($u->userDetail->member->$tipe, 0, 13) == 'https://drive') {
                $_file = $u->userDetail->member->$tipe;                
                $nama = \Helper::downloadImageFromGoogleDrive($_file, $tipe);
                if ($nama) {
                    $u->userDetail->member->update([
                        $tipe => $nama
                    ]);
                }else{
                    return 'ada link google yang masih private';
                }                
            }    
            $_file = $u->userDetail->member->$tipe;   
            if (!empty($_file)) {
                $_ext = pathinfo($_file);
                $cekk2 = explode(".", $_file);
                $_ext = ".".$_ext['extension'];                
                if (count($cekk2) > 1) {
                    $filePaths[public_path("uploaded_files/{$tipe}/$_file")] = $u->userDetail->name.$_ext;
                } 
            }

        }
        return \Helper::downloadZip($filePaths, $tipe);
    }

    public function viewByEvent($id_event){
        if (!session()->has('kelas_diklatonline')) {
            $endpoint = env('API_DIKLATONLINE').'Membership/listKelas';
            $list_api = \Helper::getRespApiWithParam($endpoint, 'get');        
            session(['kelas_diklatonline' => $list_api]);
        }
        if (!session()->has('api_detail_event'.$id_event)) {
            $endpoint = env('API_EVENT').'member/event/detailevent_by_id?id_event='.$id_event;
            $list_api = \Helper::getRespApiWithParam($endpoint, 'get');        
            session(['api_detail_event'.$id_event => $list_api]);
        }        
        $list_event = session('api_detail_event'.$id_event);

        $tgl_pelaksanaan = Carbon::parse($list_event['event']['tgl_end'])
        ->locale('id')
        ->translatedFormat("l, d F Y");
        $lokasi_event = $list_event['event']['lokasi_event'];

        $list_kelasdo = session('kelas_diklatonline');
        $users = UserEvent::where([
            ['event_id', $id_event],
            ['is_deleted', 0]
        ])->orderBy('id', 'desc')->get();
        $users_deleted = UserEvent::where([
            ['event_id', $id_event],
            ['is_deleted', 1]
        ])->orderBy('updated_at', 'desc')->get();
        $uv_lkpp = UserEventLkpp::where('event_id', $id_event)->get();
        $countuv_lkpp = count($uv_lkpp);
        return view('detail_member_event', compact('users', 'users_deleted', 'list_kelasdo', 'list_event',  'id_event', 'uv_lkpp', 'countuv_lkpp', 'tgl_pelaksanaan', 'lokasi_event'));
    }

    public function updateDataMemberKredens($id, Request $request){
        $selUser = User::where('id', $id)->first();
        if ($selUser) {
            if (isset($request->nik)) {
                $countCar = strlen($request->nik);
                if ($countCar != 16) {
                    return response()->json([
                        'status'   => 'fail',
                        'messages' => "NIK harus 16 digit"
                    ], 422);
                }
                $cekNik = User::where([
                    ['id', '!=', $id],
                    ['nik', $request->nik]
                ])->first();
                if ($cekNik) {
                    return response()->json([
                        'status'   => 'fail',
                        'messages' => "NIK tersebut sudah digunakan dengan a/n {$cekNik->name}"
                    ], 422);
                }
                $selUser->update([
                    'nik' => $request->nik
                ]);
            }else if (isset($request->email)) {
                $cekEmail = User::where([
                    ['id', '!=', $id],
                    ['email', $request->email]
                ])->first();
                if ($cekEmail) {
                    return response()->json([
                        'status'   => 'fail',
                        'messages' => "Email tersebut sudah digunakan dengan a/n {$cekEmail->name}"
                    ], 422);
                }
                $selUser->update([
                    'email' => $request->email
                ]);
            }else if (isset($request->foto_ktp)) {
                $validator = Validator::make($request->only(['foto_ktp']), [
                    'foto_ktp' => 'required|mimes:jpeg,png,jpg',
                ]);
                if ($validator->fails()) {
                    return response()->json([
                        'status'   => "fail",
                        'messages' => $validator->errors()->first(),
                    ], 422);
                }
                $foto_ktp = \Helper::storeFile('foto_ktp', $request->foto_ktp, $selUser->member->foto_ktp);

                $selUser->member->update([
                    'foto_ktp' => $foto_ktp
                ]);
            }else if (isset($request->foto_profile)) {
                $validator = Validator::make($request->only(['foto_profile']), [
                    'foto_profile' => 'required|mimes:jpeg,png,jpg',
                ]);
                if ($validator->fails()) {
                    return response()->json([
                        'status'   => "fail",
                        'messages' => $validator->errors()->first(),
                    ], 422);
                }
                $foto_profile = \Helper::storeFile('foto_profile', $request->foto_profile, $selUser->member->foto_profile);

                $selUser->member->update([
                    'foto_profile' => $foto_profile,
                    'pas_foto3x4'=>$foto_profile,
                ]);
            }else if (isset($request->file_sk_pengangkatan_asn)) {
                $validator = Validator::make($request->only(['file_sk_pengangkatan_asn']), [
                    'file_sk_pengangkatan_asn' => 'required|mimes:jpeg,png,jpg',
                ]);
                if ($validator->fails()) {
                    return response()->json([
                        'status'   => "fail",
                        'messages' => $validator->errors()->first(),
                    ], 422);
                }
                $file_sk_pengangkatan_asn = \Helper::storeFile('file_sk_pengangkatan_asn', $request->file_sk_pengangkatan_asn, $selUser->member->file_sk_pengangkatan_asn);

                $selUser->member->update([
                    'file_sk_pengangkatan_asn' => $file_sk_pengangkatan_asn
                ]);
            }
            return response()->json([
                'status'   => 'ok',
                'messages' => "Berhasil update data atas nama {$selUser->name}"
            ], 200);
        }else{
            return response()->json([
                'status'   => 'fail',
                'messages' => "Data member tidak ditemukan, silakan coba lagi"
            ], 422);
        }
    }

    public function updateDataMember(Request $request){
        if ($request->isi_field == "Click to edit") {
            $request['isi_field'] = null;
        }
        $u = User::whereNik($request->nik)->first();
        // return $u->userEvent;
        if($u){
            // ini kalo isinya engga kosong
            $_field = $request->nama_field;
            if (!is_null($request->isi_field)) {
                // ini kalo users dan data sebelumnya tidak sama dengan data yang sekarang diupdate
                if ($request->tipe == 'user_event' && $request->isi_field != $u->userEvent->$_field) {
                    $uv = UserEvent::where('user_id', $u->id)
                    ->where('event_id', $request->id_event)
                    ->first();
                    if ($uv) {
                        $uv->update([
                            $_field => $request->isi_field
                        ]);     
                    }                                
                }
                if ($request->tipe == 'users' && $request->isi_field != $u->$_field) {                    
                    if ($request->nama_field == "password_lkpp") {
                        $u->update([
                            $_field => \Helper::passHashedEncrypt($request->isi_field)
                        ]);
                    }else{
                        $u->update([
                            $_field => $request->isi_field
                        ]);
                    }                    
                }
                if ($request->tipe == 'member' && $request->isi_field != $u->member->$_field) {
                    $u->member->update([
                        $_field => $request->isi_field
                    ]);                 
                }
                if ($request->tipe == 'member_kantor' && $request->isi_field != $u->member->memberKantor->$_field) {
                    $u->member->memberKantor->update([
                        $_field => $request->isi_field
                    ]);                 
                }
            }                        
        }        
    }

    public function deletePeserta(Request $request){
        $query = UserEvent::whereIn('id', $request->idArr);
        $queryCount = $query->count();
        if ($request->is_deleted == 1) {
            $endpointsertif = env('API_SSERTIFIKAT').'member/hapusMemberSertifikat';
            \Helper::getRespApiWithParam($endpointsertif, 'POST', [
                'email' => $request->emailArr,
                'id_event' => $request->id_event
            ]);
        }else{
            $arrPeserta = [];
            foreach($query->get() as $u){
                array_push($arrPeserta, [
                    'id_kelas_event'    => $request->id_event,
                    'nama_lengkap'      => $u->userDetail->name,
                    'no_hp'             => $u->userDetail->member->no_hp,
                    'email'             => $u->userDetail->email,
                    'instansi'          => $u->userDetail->member->memberKantor->nama_instansi,
                    'unit_organisasi'   => $u->userDetail->member->memberKantor->unit_organisasi,
                    'alamat'            => $u->userDetail->member->alamat_lengkap,
                    'nik'               => $u->userDetail->nik,
                    'tempat_lahir'      => $u->userDetail->member->tempat_lahir,
                    'tgl_lahir'         => $u->userDetail->member->tgl_lahir,
                    'status_pembayaran' => 1,                        
                    'bukti'             => 'default_restore_data.jpg'
                ]);
            }
            $endpoint = env('API_EVENT').'member/Regis_event/import_regis_event';
            $datapost = ['data_peserta'=>$arrPeserta];
            $xxx = \Helper::getRespApiWithParam($endpoint, 'post', $datapost);
        }     

        $query->update([
            'is_deleted' => $request->is_deleted,
            'updated_at' => now()
        ]);
        \Helper::storeHistoryUserEvent($request->idArr, $request->is_deleted);
        return response()->json([
            'status'   => 'ok',
            'messages' => "{$queryCount} peserta berhasil diupdate."
        ], 200);
    }

    public function storeToDiklatOnline(Request $request){
        $selUser = User::whereIn('email', $request->emailArr)->get();
        foreach($selUser as $u){
            $arrayData[] = [
                'nama'              => $u->name ?? '-',
                'instansi'          => $u->member->memberKantor->nama_instansi ?? '-',
                'email'             => $u->email,
                'no_hp'             => $u->member->no_hp ?? '-',
                'id_kelas'          => $request->id_kelas,
                'id_event'          => $request->id_event
            ];
        }
        $endpoint = env('API_DIKLATONLINE').'Membership/process_data';
        $datapost = ['data_peserta'=>$arrayData];
        $xxx = \Helper::getRespApiWithParam($endpoint, 'post', $datapost);
        if($xxx['message'] == 'success'){
            $uv = UserEvent::whereIn('id', $request->idArr)
            ->update(['idkelas_diklatonline' => $request->id_kelas]);
            foreach($request->idArr as $key => $value){
                HistoryKelasDiklatOnline::updateOrCreate(
                    ['user_event_id' => $value,'idkelas_diklatonline' => $request->id_kelas],
                    [
                        'createdBy' => \Auth::user()->id,
                        'updated_at' => now()
                    ]
                );
            }            
            return response()->json([
                'status'   => 'ok',
                'messages' => "Berhasil submit data ke Diklatonline"
            ], 200);
        }
    }

    public function updateCss(Request $request){
        $uv = UserEvent::whereIn('id', $request->idArr)
        ->when($request->tipe == "background-color", function($q2)use($request){
            $q2->update(['bg_color' => $request->color]);
        })
        ->when($request->tipe == "font-color", function($q2)use($request){
            $q2->update(['font_color' => $request->color]);
        });
    }

    public function downloadExcelByEvent($id_event){
        $userse = UserEvent::join('users', 'user_events.user_id', '=', 'users.id')
        ->where('user_events.event_id', $id_event)
        ->where('user_events.is_deleted', 0)
        ->orderBy('users.name', 'asc')
        ->select('user_events.*') // pastikan hanya memilih kolom yang diperlukan
        ->with('userDetail')
        ->get();
        return Excel::download(new ExportDataFormByEvent($userse),"data-peserta-{$id_event}.xlsx");
    }

    public function downloadExcelTo($id_event){
        $userse = UserEvent::join('users', 'user_events.user_id', '=', 'users.id')
        ->where('user_events.event_id', $id_event)
        ->where('user_events.is_deleted', 0)
        ->orderBy('users.name', 'asc')
        ->select('user_events.*') // pastikan hanya memilih kolom yang diperlukan
        ->with('userDetail')
        ->get();
        return Excel::download(new ExportDataTo($userse),"data-peserta_to-{$id_event}.xlsx");
    }

    public function downloadPresensiPelatihan($id_event, Request $request){        
        $period = CarbonPeriod::create($request->tgl_mulai, $request->tgl_selesai);
        $rangeTgl = [];
        foreach ($period as $date) {
            $harii = $date->translatedFormat('l');
            $bulann = \Helper::bulanIndo((int)$date->month);
            $rangeTgl[] = [
                'tanggal_penuh' => "{$harii}, {$date->day} {$bulann} {$date->year}",
                // 'hari' => $harii,    
                // 'tanggal' => $date->day,              
                // 'bulan' => $date->month,  
                // 'tahun' => $date->year,          
            ];
        }
        // dd($rangeTgl);

        $userse = UserEventLkpp::where('event_id', $id_event)->with('userDetail.member')
        ->limit(3)
        ->get();
        // dd($userse);
        return Excel::download(new ExportPresensiPelatihan($userse, $rangeTgl),"data-presensi_pelatihan-{$id_event}.xlsx");
    }

    public function importPdfLkpp($id, Request $request){
        $validator = Validator::make($request->all(), [
            'dokumen_presensi_lkpp' => 'required|mimes:pdf'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'   => "fail",
                'messages' => $validator->errors()->first(),
            ], 422);
        }
        $pdfFilePath = \Helper::storeFile('pdf_presensi_lkpp', $request->dokumen_presensi_lkpp);
        $pythonScript = storage_path('app/scripts/read_pdf/to_doc.py');
        // $pythonScript = storage_path('app/scripts/read_pdf/to_doc_by_3.py');
        $filePdf = "uploaded_files/pdf_presensi_lkpp/$pdfFilePath";
        $output = shell_exec("python3 $pythonScript $filePdf 2>&1");
        $outputArray = json_decode($output, true);
        if ($outputArray['status'] != 'success') {
            return response()->json([
                'status'    => "fail",
                'messages' => "Ada kesalahan dalam proses import, silakan cek file pdf",
            ], 422);
        }

        \DB::beginTransaction();
        try {
            UserEventLkpp::where('event_id', $id)->delete();
            foreach($outputArray['data'] as $data){
                $no_urut = $data['noo_urut']; 
                $no_ujian = $data['no_ujian']; 
                $nama = $data['nama']; 
                $nik = $data['nik']; 
                $nip = $data['nip']; 
                $email = $data['email']; 
                $instansi = $data['instansi'];                 
                UserEventLkpp::create([
                    'event_id' => $id,
                    'no_ujian' => $no_ujian,
                    'nama' => $nama,
                    'nik' => $nik,
                    'nip' => $nip,
                    'email' => $email,
                    'asal_instansi' => $instansi
                ]);
            }
            \DB::commit();
            return response()->json([
                'status'   => 'ok',
                'messages' => "Berhasil generate data peserta dalam format docx",
                'file_datapeserta' => url($outputArray['file_datapeserta'])
            ], 200);
        }catch (Exception $e) {
            \DB::rollback();
            return response()->json([
                'status'    => "fail",
                'messages' => "Ada kesalahan dalam proses import",
            ], 422);
        }                
    }

    public function convertDenahUjian($event_id, Request $request){
        return Excel::download(new ExportDenah($request), "data-denah-{$event_id}.xlsx");
    }

}
