<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Member\EventKamuController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Admin\{Provinsi, Instansi};
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Exports\ExportAlumni;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('admin.user.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required',  'max:255'],
            'email' => ['required',  'max:255'],
            'password' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'   => "fail",
                'messages' => $validator->errors()->first(),
            ], 422);
        }
        User::create([
            'name'       => $request->name,
            'email'       => $request->email,
            'password'       => Hash::make($request->password),
        ]);

        return response()->json([
            'status'   => 'ok',
            'messages' => "Berhasil menambah User"
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        $provinsi = Provinsi::select('id', 'nama')->orderBy('nama')->get();
        $instansi = Instansi::orderBy('nama')->get();
        return view('admin.user.response_data', compact('user', 'provinsi', 'instansi'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return User::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'       => ['required', 'max:255'],
            'email'       => ['required', 'max:255']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'   => "fail",
                'messages' => $validator->errors()->first(),
            ], 422);
        }
        $us = User::findOrFail($id);
        $us->update([
            'name'      => $request->name,
            'email'      => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $us->password,
            'is_confirm' => $request->verifikasi_akun == 1 ? 1 : 0
        ]);
        // return $request->all();

        return response()->json([
            'status'   => 'ok',
            'messages' => "Berhasil update user"
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $us = User::findOrFail($id);
        if ($us->member->memberKantor()->exists()) {
            $us->member->memberKantor()->delete();
        }
        if ($us->member()->exists()) {
            $us->member->delete();
        }
        $us->delete();
        return response()->json([
            'status'   => 'ok',
            'messages' => "Berhasil menghapus user"
        ], 200);
    }

    public function getDatatable(Request $request)
    {        
        // if ($request->ajax()) {
            $emailArrLulus = [];
            $emailArrTdkLulus = [];
            if ($request->kelulusan_event) {
                $endpoint_ = env('API_EVENT').'member/event/peserta_lulus';
                $datapost = ['lulus' => 1, 'judul' => $request->kelulusan_event];
                $eventData = \Helper::getRespApiWithParam($endpoint_, 'POST', $datapost);
                $emailArrLulus = array_unique(array_column($eventData, 'email'));
            }
            // print_r($emailArrLulus);die;
            if ($request->ketidakkelulusan_event) {
                $endpoint_ = env('API_EVENT').'member/event/peserta_lulus';
                $datapost = ['lulus' => 0, 'judul' => $request->ketidaklulusan_event];
                $eventData = \Helper::getRespApiWithParam($endpoint_, 'POST', $datapost);
                $emailArrTdkLulus = array_unique(array_column($eventData, 'email'));
            }
            $data = User::with('member:id,user_id,tgl_lahir')
            ->select('id', 'name', 'email', 'nip')
            ->when($request->tanggal_awal, function($q)use($request){
                $q->whereHas('member', function($qq)use($request){
                    $qq->whereDate('tgl_lahir', '>=', $request->tanggal_awal);
                });
            })
            ->when($request->tanggal_akhir, function($q)use($request){
                $q->whereHas('member', function($qq)use($request){
                    $qq->whereDate('tgl_lahir', '<=', $request->tanggal_akhir);
                });
            })
            ->when($request->status_kepegawaian, function($q)use($request){
                $q->whereHas('member.memberKantor', function($qq)use($request){
                    $qq->where('status_kepegawaian', $request->status_kepegawaian);
                });
            })
            ->when($request->kelulusan_event, function($q)use($emailArrLulus){
                $q->whereIn('email', $emailArrLulus);
            })
            ->when($request->ketidaklulusan_event, function($q)use($emailArrTdkLulus){
                $q->whereIn('email', $emailArrTdkLulus);
            })   
            // ->limit(100)         
            ->orderBy('updated_at', 'DESC');
            return \DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('status_user', function($row){
                $s = '';
                if ($row->is_confirm) {
                    $s .= '<span class="badge badge-primary">Sudah Verifikasi</span>';
                }else{
                    $s .= '<span class="badge badge-warning">Menunggu Verifikasi</span>';
                }
                return $s;
            })
            ->addColumn('email_', function($row){
                return "<a style='color: #4f4fbd;' target='_blank' href=".route('dashboard2.detail_alumni', $row->email).">{$row->name}</a>";
            })
            ->addColumn('action', function ($row) {
                $actionBtn = '<a href="' . route('admin.user.import_biodata', $row->id) . '" class="btn-sm btn btn-info  mr-1 mb-2 mb-lg-2" data-toggle="tooltip" data-placement="top" title="Download Biodata"><i class="fa fa-download"></i></a>';
                $actionBtn .= '<a href="' . route('admin.user.show', $row->id) . '" id="btnShow" class="btn-sm btn btn-info  mr-1 mb-2 mb-lg-2" data-toggle="tooltip" data-placement="top" title="Lihat Data"><i class="fa fa-eye"></i></a>';
                $actionBtn .= '<a data-toggle="tooltip" data-placement="top" title="Edit Data" id="btnEdit" href="' . route('admin.user.show', $row->id) . '" class="btn-sm btn btn-warning mx-1 ml-4 ml-md-0 mb-2"><i class="fa fa-edit"></i></a>';
                $actionBtn .= '<button type="button" class="btn-sm btn btn-danger mb-2 mb-lg-2" id="btnHapus" data-id=' . $row->id . ' action="' . route('admin.user.destroy', $row->id) . '"><i class="fa fa-trash"></i></button>';
                return $actionBtn;
            })
            ->rawColumns(['action', 'status_user', 'email_'])
            ->make(true);
        // }
    }

    public function profile()
    {
        $id = \Auth::user()->id;
        $users = User::findOrFail($id);
        return view('admin.user.profile', ['users' => $users]);
    }
    public function importBiodata($id){
        $data = User::findOrFail($id);
        $pdf = \PDF::loadView('admin.user.pdf_biodata', compact('data'));
        return $pdf->download('Biodata '.$data->name.'.pdf');
        // return view('admin.user.pdf_biodata', compact('data'));
    }

    public function exportExcelAlumni(Request $request){
        ini_set('memory_limit', '512M');
        $emailArr = [];
        // if ($request->kelulusan_event) {
        //     $endpoint_ = env('API_EVENT')."member/event/peserta_lulus";
        //     if ($request->kelulusan_event) {
        //         $datapost = ['lulus' => 1, 'judul' => $request->kelulusan_event];
        //     }
        //     // defaultnya nyari data yang ngga lulus ujian
        //     $datapost = ['lulus' => 0, 'judul' => $request->ketidaklulusan_event];
        //     $eventData = \Helper::getRespApiWithParam($endpoint_, 'POST', $datapost);
        //     $emailArr = array_unique(array_column($eventData, 'email'));
        // }
        $data = User::whereHas('member')
        ->with([
            'member:id,user_id,no_hp,pendidikan_terakhir,tempat_lahir,tgl_lahir,alamat_lengkap,prov_id,kota_id,kecamatan_id,kelurahan_id',
            'member.memberKantor' => function ($query) use ($request) {
                $query->select('id', 'member_id', 'status_kepegawaian', 'posisi_pelaku_pengadaan', 'jenis_jabatan', 'nama_jabatan', 'golongan_terakhir', 'nama_instansi', 'pemerintah_instansi', 'alamat_kantor_lengkap');
                if ($request->status_kepegawaian) {
                    $query->where('status_kepegawaian', $request->status_kepegawaian);
                }
            },
            'member.alamatProvinsi:id,nama',
            'member.alamatKota:id,kota',
            'member.alamatKecamatan:id,kecamatan',
            'member.alamatKelurahan:id,kelurahan',
            'member.sertifikatLain:id,member_id,no,nama,tahun',
        ])
        ->select('id', 'name', 'email', 'nip', 'nik')
        ->when($request->tanggal_awal && $request->tanggal_akhir, function($q) use ($request) {
            $tanggalAwal = explode('-', $request->tanggal_awal); // Misal: "07-15" -> ["07", "15"]
            $tanggalAkhir = explode('-', $request->tanggal_akhir); // Misal: "08-20" -> ["08", "20"]
    
            $q->whereHas('member', function($qq) use ($tanggalAwal, $tanggalAkhir) {
                $qq->whereRaw("(MONTH(tgl_lahir) = ? AND DAY(tgl_lahir) >= ?)", [$tanggalAwal[1], $tanggalAwal[2]])
                   ->orWhereRaw("(MONTH(tgl_lahir) = ? AND DAY(tgl_lahir) <= ?)", [$tanggalAkhir[1], $tanggalAkhir[2]]);
            });
        })
        ->when($request->kelulusan_event, function ($q) use ($emailArr) {
            $q->whereIn('email', $emailArr);
        })
        ->orderBy('updated_at', 'desc')
        ->limit(100)
        ->get();

        $a = date('d-M-Y');
        // return view('admin.user.export_alumni', compact('data'));
        return Excel::download(new ExportAlumni($data),"alumni-{$a}.xlsx");
    }
}
