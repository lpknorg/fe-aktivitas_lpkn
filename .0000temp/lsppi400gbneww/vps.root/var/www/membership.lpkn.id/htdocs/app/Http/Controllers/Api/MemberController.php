<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Admin\{Member, MemberKantor};
use Illuminate\Support\Str;
use Carbon\Carbon;

class MemberController extends Controller{
	public function loginAplikasi(Request $request){    
        $validator = Validator::make($request->all(), array(
            'email' => "required",
            'password' => "required"
        ));

        if ($validator->fails()) {
            return response()->json([
                'status'    => "fail",
                'messages' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        
        if(!$user){
            return response()->json([
                'status'    => "fail",
                'messages' => "User tidak terdaftar.",
            ], 422);
        }

        if (auth()->attempt(array('email' => $request->email, 'password' => $request->password))){
            if (\Auth::user()->roles->isEmpty()) {
                \Auth::user()->syncRoles(['member']);
            }
            $user->update([
                'from_apps' => 1
            ]);
            $_user = User::where('email', $request->email)->with(['member.alamatProvinsi', 'member.alamatKota', 'member.alamatKecamatan', 'member.alamatKelurahan', 'member.memberKantor'])->first();
            return response()->json([
                'data' => $_user,
                'status'    => "ok",
                'messages' => "Sukses login"
            ], 200);
        }else{
            return response()->json([
                'status'    => "fail",
                'messages' => "Email dan password tidak cocok",
            ], 422);
        }   
    }

	public function checkAlumni(Request $request){
		$check = User::whereEmail($request->email)->with('member.memberKantor')->first();
		if ($check) {
			return response()->json([
				'status'       => "ok",
				'messages'     => "Data alumni ditemukan",
				'data'         =>  $check
			], 200);
		}
		return response()->json([
			'status'    => "fail",
			'messages'     => "Data alumni tidak ditemukan",
		],422);
	}

	public function checkAlumni2(Request $request){
		// $check = User::whereEmail($request->email)->with('member.memberKantor')->first();
		$check = \DB::selectOne('SELECT u.id, u.email, u.name, m.no_hp, mk.nama_instansi, mk.unit_organisasi FROM `users` u
		LEFT JOIN members m ON m.user_id = u.id
		LEFT JOIN member_kantors mk ON mk.member_id = m.id
		where u.email=?', [$request->email]);
		if ($check) {
			return response()->json([
				'status'       => "ok",
				'messages'     => "Data alumni ditemukan",
				'data'         =>  $check
			], 200);
		}
		return response()->json([
			'status'    => "fail",
			'messages'     => "Data alumni tidak ditemukan",
		],422);
	}

	public function daftar(Request $request){
		$validator = Validator::make($request->all(),[
			'nama_lengkap'    => 'required|string|max:255',
			'no_hp' => 'required|string|max:13',
			'email'    => 'required|email|string|max:255',
			'password' => 'required|string|max:255'
		]);

		if($validator->fails()) {
			return response()->json([
				'status'    => "fail",
				'messages'  => $validator->errors()->first(),
			],422);
		}
		$userm = Member::where('no_hp', $request['no_hp'])->first();
		if($userm){
			return response()->json([
				'status'    => "fail",
				'messages' => "No handphone sudah digunakan",
			], 422);
		}

		$user = User::where('email', $request['email'])->first();
		if($user){
			return response()->json([
				'status'    => "fail",
				'messages' => "Email sudah digunakan, silakan login dengan password: lpkn1234",
			], 422);
		}


		$request['name'] = $request->nama_lengkap;
		$request['password'] = \Hash::make($request->password);
		$request['email_verified_at'] = now();
		\DB::beginTransaction();
		try {
			$user = User::create($request->only('name', 'email', 'password', 'email_verified_at'));
			$user->syncRoles('member');
			$request['user_id'] = $user->id;

			$member = Member::create($request->only('no_hp', 'user_id'));
			MemberKantor::create([
				'member_id' => $member->id
			]);
			// $this->sendLinkVerifRegister($request);
			\DB::commit();
		} catch (Exception $e) {
			\DB::rollback();
			return response()->json([
				'status'    => "fail",
				'messages' => "Ada kesalahan dalam proses daftar",
			], 422);
		}
		return response()->json([
			'status'       => "ok",
			'messages'     => "Berhasil mendaftar",
			'data'         =>  $user
		], 200);
	}

	public function daftarLpkn(Request $request){
		$validator = Validator::make($request->all(),[
			'nama_lengkap'    => 'required|string|max:255',
			'tanggal_lahir'    => 'required|string|max:255',
			'kota'    => 'required|string|max:255',
			'domisili_lengkap'    => 'required|string|max:255',
			'pendidikan_terakhir'    => 'required|string|max:255',
			// 'instansi'    => 'required|string|max:255',
			// 'jabatan'    => 'required|string|max:255',
			// 'unit_kerja'    => 'required|string|max:255',
			'email'    => 'required|string|max:255',
			'password' => 'required|string|max:255',
			'no_hp' => 'required|string|max:13',
			// 'profil_singkat' => 'required|string|max:1000',
			// 'tema_kegiatan' => 'required|string|max:255',
			'upload_foto' => 'required',
			// 'bintang' => 'required|string',
			// 'testimoni' => 'required|string|max:500',
		]);

		if($validator->fails()) {
			return response()->json([
				'status'    => "fail",
				'messages'  => $validator->errors()->first(),
			],422);
		}
		$userm = Member::where('no_hp', $request['no_hp'])->first();
		// if($userm){
		// 	return response()->json([
		// 		'status'    => "fail",
		// 		'messages' => "No handphone sudah digunakan",
		// 	], 422);
		// }

		$userExists = User::where('email', $request['email'])->first();
		// if($user){
		// 	return response()->json([
		// 		'status'    => "fail",
		// 		'messages' => "Email sudah digunakan",
		// 	], 422);
		// }

		$userNik = User::where('nik', $request['nik'])->first();
		if($userNik && $request->nik){
			$request['nik'] = $request->nik.rand(1,999);
			// return response()->json([
			// 	'status'    => "fail",
			// 	'messages' => "nik sudah digunakan",
			// ], 422);
		}

		$usernip = User::where('nip', $request['nip'])->first();
		if($usernip && $request->nip){
			$request['nip'] = $request->nip.rand(1,999);
			// return response()->json([
			// 	'status'    => "fail",
			// 	'messages' => "nip sudah digunakan",
			// ], 422);
		}


		$request['name'] = $request->nama_lengkap;
		$request['password'] = \Hash::make($request->password);
		$request['email_verified_at'] = now();
		$request['deskripsi_diri'] = $request->profil_singkat;
		\DB::beginTransaction();
		try {
			if (!$userExists) {
				$user = User::create($request->only('name', 'email', 'password', 'nik', 'nip', 'email_verified_at', 'deskripsi_diri'));
				$user->syncRoles('member');
				$reqMember['user_id'] = $user->id;
			}else{
				$userExists->update([
					'name' => $request->name,
					'email' => $request->email,
					'password' => $request->password,
					'nik' => $request->nik,
					'nip' => $request->nip,
					'deskripsi_diri' => $request->deskripsi_diri,
					'email_verified_at' => $request->email_verified_at
				]);
				$reqMember['user_id'] = $userExists->id;
				$userExists->syncRoles(['member']);
			}			
			$reqMember['no_hp'] = $request->no_hp;			
			$reqMember['no_member'] = $request->no_member;
			$reqMember['pendidikan_terakhir'] = $request->pendidikan_terakhir;
			$reqMember['nama_lengkap_gelar'] = $request->nama_lengkap;
			$reqMember['tgl_lahir'] = $request->tanggal_lahir;
			$reqMember['alamat_lengkap'] = $request->domisili_lengkap;
			$reqMember['nama_kota'] = $request->kota;
			$reqMember['foto_profile'] = null;
			$reqMember['no_member'] = $request->no_member;
			if (base64_decode($request->upload_foto)) {
				$reqMember['foto_profile'] = \Helper::storeBase64File('foto_profile', $request->upload_foto, \Helper::generateRandString());
			}
			// if ($request->hasFile('upload_foto')) {
			// 	$reqMember['foto_profile'] = \Helper::storeFile('foto_profile', $request->upload_foto);
			// }
			$reqMember['profil_singkat'] = $request->profil_singkat;
			if (!$userExists) {
				$member = Member::create($reqMember);
				MemberKantor::create([
					'member_id' => $member->id,
					'nama_jabatan' => $request->jabatan,
					'nama_instansi' => $request->instansi,
					'unit_kerja' => $request->unit_kerja,
					'pemerintah_instansi' => '-'
				]);
			}else{
				$member =  Member::where('user_id', $userExists->id)->first();
				$member->update($reqMember);

				$memKantor = MemberKantor::where('member_id', $member->id)->first();
				$memKantor->update([
					'member_id' => $member->id,
					'nama_jabatan' => $request->jabatan,
					'nama_instansi' => $request->instansi,
					'unit_kerja' => $request->unit_kerja,
					'pemerintah_instansi' => '-'
				]);
			}
			// $this->sendLinkVerifRegister($request);
			if (!$userExists) {
				$_user = User::whereId($user->id)->with('member.memberKantor')->first();
			}else{
				$_user = User::whereId($userExists->id)->with('member.memberKantor')->first();
			}			
			\DB::commit();
		} catch (Exception $e) {
			\DB::rollback();
			return response()->json([
				'status'    => "fail",
				'messages' => "Ada kesalahan dalam proses daftar",
			], 422);
		}
		return response()->json([
			'status'       => "ok",
			'messages'     => "Berhasil mendaftar dari LPKN",
			'data'         =>  $_user
		], 200);
	}

	public function sendLinkVerifRegister(Request $request){
		if (is_null(env('MAIL_USERNAME'))) {
			return response()->json([
				'status'    => "fail",
				'messages' => "ENV untuk email belum dikonfigurasi.",
			], 422);
		}

		$user = User::where('email', $request->email)->first();

		$user->token_verif_regist = Str::uuid();
		$user->save();

		\Mail::send('auth.verif-email', ['data' => $user], function($message) use($user){

			$message->to($user->email);
			$message->subject('Verifikasi Membership LPKN');
		});

		return response()->json([
			'status'    => "ok",
			'messages' => "Silakan check kembali email anda untuk verifikasi email ".$request->email
		], 200);           
	}

	public function customSendEmailVerifRegister($user){
		
	}
	public function updateVerifyEmail($token){
		$user = User::where('token_verif_regist', $token)->first();
		if ($user) {
			$now = Carbon::now();
			$user = User::where('token_verif_regist', $token)->first();
			$user->email_verified_at = now();
			$user->save();
			return redirect('login')->with('success_verify_email', 'Berhasil melakukan verifikasi email');
		}
		return redirect('login')->with('exception_verify_password', 'Url Link verify email tidak valid');
	}
	
	public function updatePasswordAlumni(Request $request){
		//var_dump($request->email);die;
		$user = User::where('email', $request->email)->first();
		$user->password = bcrypt($request->password);
		$user->save();
		return response()->json([
			'status'    => "ok",
			'messages' => "Berhasil melakukan ubah password"
		], 200);
	}

}
