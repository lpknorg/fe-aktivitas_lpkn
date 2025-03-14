<?php

namespace App\Http\Helpers;
use Illuminate\Support\Facades\Http;
use \ZipArchive;
use App\Models\UserEventHistory;

class Helper {
	public static function storeFile($folder, $file, $oldFile=null){
		if ($file) {
			if (!is_null($oldFile)) {
				self::deleteFile($folder, $oldFile);
			}
			$time = time();			
			$fileInfo = pathinfo($file->getClientOriginalName());
			$filename = $fileInfo['filename'];
			$extension = $fileInfo['extension'];
			$sanitizedFileName = preg_replace('/[^A-Za-z0-9]/', '', $filename);
			$filename = "{$time}_{$sanitizedFileName}.{$extension}";
			// $filename = "{$time}_{$file->getClientOriginalName()}";
			$file->move(public_path("uploaded_files/{$folder}"), $filename);
			return $filename;
		}
	}

	public static function passHashedEncrypt($pass){
		$data = $pass;
		$key = "lpkn1234"; // Kunci rahasia
		$method = "aes-256-cbc"; // Metode enkripsi
		$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));

		// Enkripsi data
		$encrypted_data = openssl_encrypt($data, $method, $key, 0, $iv);

		// Menggabungkan IV dengan data terenkripsi agar dapat digunakan saat dekripsi
		$encrypted_data_with_iv = base64_encode($iv . $encrypted_data);

		return $encrypted_data_with_iv;
	}

	public static function passHashedDecrypt($pass){
		$data = $pass;
		$key = "lpkn1234"; // Kunci rahasia
		$method = "aes-256-cbc"; // Metode enkripsi
		
		// Memisahkan IV dan data terenkripsi
		$encrypted_data_with_iv = base64_decode($data);
		$iv_length = openssl_cipher_iv_length($method);
		$iv = substr($encrypted_data_with_iv, 0, $iv_length);
		$encrypted_data = substr($encrypted_data_with_iv, $iv_length);

		// Dekripsi data
		$decrypted_data = openssl_decrypt($encrypted_data, $method, $key, 0, $iv);

		return $decrypted_data;
	}

	public static function getGolongan(){
		return [
			'I/b' => 'Juru Muda Tk.I','I/c' => 'Juru','I/d' => 'Juru Tk. I','II/a' => 'Pengatur Muda','II/b' => 'Pengatur Muda Tk. I','II/c' => 'Pengatur','II/d' => 'Pengatur Tk. I','III/a' => 'Penata Muda','III/b' => 'Penata Muda Tk. I','III/c' => 'Penata','III/d' => 'Penata Tk. I','IV/a' => 'Pembina','IV/b' => 'Pembina Tk. I','IV/c' => 'Pembina Utama Muda','IV/d' => 'Pembina Utama Madya','IV/e' => 'Pembina Utama','Golongan I' => 'PPPK I','Golongan II' => 'PPPK II','Golongan III' => 'PPPK III','Golongan IV' => 'PPPK IV','Golongan V' => 'PPPK V','Golongan VI' => 'PPPK VI','Golongan VII' => 'PPPK VII','Golongan VIII' => 'PPPK VIII','Golongan IX' => 'PPPK IX','Golongan X' => 'PPPK X','Golongan XI' => 'PPPK XI','Golongan XII' => 'PPPK XI',
		];
	}

	function imageToBase64($image) {
		$image = public_path("uploaded_files/{$image}");
		$imageData = file_get_contents($image);

	    // Langkah 3: Encode konten gambar ke base64
		$base64Data = base64_encode($imageData);

	    // Langkah 4: Tentukan tipe mime gambar
		$mimeType = mime_content_type($image);

	    // Langkah 5: Gabungkan tipe mime dengan data base64
		$base64Image = 'data:' . $mimeType . ';base64,' . $base64Data;
		return $base64Image;
	}

	public static function storeBase64File($folder, $file, $namaFile='', $oldFile=null){
		if (!\File::exists(public_path("uploaded_files/{$folder}"))) {
			\File::makeDirectory(public_path("uploaded_files/{$folder}"), 0755, true, true);
		}
		if (substr($file, 0, 5) == "data:") {
			$ext       = explode('/', mime_content_type($file))[1];
			//$image     = str_replace("data:image/{$ext};base64,", '', $file);
			$image = explode(',', $file);
			$image = $image[1];
			if($namaFile){
				$imageName = $namaFile.'.'.$ext;
			}else{
				$imageName = strtotime("now").'.'.$ext;
			}
			$path      = public_path()."/uploaded_files/{$folder}/".$imageName;
			\File::put($path, base64_decode($image));
			return $imageName;
		}else{
			return self::storeFile($folder, $file, $namaFile, $oldFile);
		}
	}

	public function generateRandString($length=10){
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';

		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}

		return $randomString;
	}

	public static function deleteFile($folder, $oldFile){
		if (file_exists(public_path("uploaded_files/{$folder}/").$oldFile)) {
			unlink("uploaded_files/{$folder}/{$oldFile}");
		}
	}

	public static function downloadZip($filePaths, $tipe){
		// $filePaths = [
		// 	public_path('uploaded_files/foto_ktp/1691057213_flazz1.png') => 'custom_name1.png',
		// 	public_path('uploaded_files/foto_ktp/1691480733_AdventureTime.jpg') => 'custom_name2.png',
		// ];
		$rand = rand(1,99999);
		$zipFileName = "{$tipe}_{$rand}.zip";

		$zip = new ZipArchive;

		if ($zip->open(public_path($zipFileName), ZipArchive::CREATE) === TRUE) {
			foreach ($filePaths as $filePath => $zipEntryName) {
				if (file_exists($filePath)) {
					$zip->addFile($filePath, ucwords($zipEntryName));
				}else{
					// var_dump(123);
				}
			}
			$zip->close();

			return response()->download(public_path($zipFileName));
		} else {
			return 'Gagal membuat file zip';
		}
	}

	public static function showImageFromGdrive($img){
		if (strpos($img, '?id=') !== false) {
			$getId = explode("id=", $img);
			$getId = $getId[1];
		}elseif(strpos($img, 'file/d/') !== false) {
			$getId = explode("/", $img);
			$getId = $getId[5];
		}		
		return "https://drive.google.com/thumbnail?id={$getId}&sz=w1000";
	}

	public static function downloadImageFromGoogleDrive($url_drive, $folder)
	{
		$url_drive = \Helper::showImageFromGdrive($url_drive);
		$response = Http::get($url_drive);		
		$contentType = $response->header('Content-Type');				
		$mimeToExt = [
			'image/jpeg' => 'jpg',
			'image/png' => 'png',
			'application/pdf' => 'pdf'
		];
		$extension = $mimeToExt[$contentType] ?? 'unknown';
		if ($extension == 'unknown') {
			return null;
		}
		$time = time();$rnd = rand(1,9999);
		$filename = "{$time}-gd{$rnd}.{$extension}";
		if ($response->ok()) {
			$pathh = public_path("uploaded_files/{$folder}/");
			if (!\File::exists($pathh)) {
				\File::makeDirectory($pathh, 0755, true, true);
			}
			$path = $pathh.$filename;
			file_put_contents($path, $response->body());

			return $filename;
		}
	}

	public static function showImage($img, $fold=null){
		// akses foto dari google drive
		if (substr($img, 0, 13) == 'https://drive') {
			return self::showImageFromGdrive($img);
		}
		if (is_null($img)) {
			return asset("default.png");
		}
		if ($fold) {
			return asset("uploaded_files/{$fold}/{$img}");
		}
		if (file_exists(public_path($img))) {
			return asset($img);
		}
		return asset("default.png");
	}

	public static function changeFormatDate($date, $format='d-m-Y'){
		//jika bukan tanggal beneran
		if (!strtotime($date) !== false) {
			return $date;	
		}
		if (is_null($date)) {
			return '-';
		}
		$d = date_create($date);
		return date_format($d, $format);
	}

	public static function getUname($user){
		$u = strtolower($user->email);
		$u = explode("@", $u);
		return $u[0].'-'.$user->id;
	}

	public static function get_client_ip($ip = null, $deep_detect = TRUE){
		return gethostname();
	}

	public static function showNominal($rp, $show_rp=true){
		if ($show_rp) {
			return "Rp " . number_format($rp, 0, ",", ".");
		}
		return number_format($rp, 0, ",", ".");
	}

	public static function cutString($text, $limit=60, $use_dot=true){
		if (strlen($text) > $limit) {
			return $use_dot ? substr($text, 0, $limit).'...' : substr($text, 0, $limit);
		}else{
			return $text;
		}
	}

	public static function convertFromBulanIndo($bulan, $a){
		if($bulan == 'januari'){
			$fixTglLahir = $a[2].'-01-'.$a[0];
		}elseif($bulan == 'februari'){
			$fixTglLahir = $a[2].'-02-'.$a[0];
		}elseif($bulan == 'maret'){
			$fixTglLahir = $a[2].'-03-'.$a[0];
		}elseif($bulan == 'april'){
			$fixTglLahir = $a[2].'-04-'.$a[0];
		}elseif($bulan == 'mei'){
			$fixTglLahir = $a[2].'-05-'.$a[0];
		}elseif($bulan == 'juni'){
			$fixTglLahir = $a[2].'-06-'.$a[0];
		}elseif($bulan == 'juli'){
			$fixTglLahir = $a[2].'-07-'.$a[0];
		}elseif($bulan == 'agustus'){
			$fixTglLahir = $a[2].'-08-'.$a[0];
		}elseif($bulan == 'september'){
			$fixTglLahir = $a[2].'-09-'.$a[0];
		}elseif($bulan == 'oktober'){
			$fixTglLahir = $a[2].'-10-'.$a[0];
		}elseif($bulan == 'november'){
			$fixTglLahir = $a[2].'-11-'.$a[0];
		}elseif($bulan == 'desember'){
			$fixTglLahir = $a[2].'-12-'.$a[0];
		}else{
			$fixTglLahir = null;
		}
		return $fixTglLahir;
	}

	public static function bulanIndo($bulan){
		switch ($bulan){
			case 1 : $bulan="Januari";
			Break;
			case 2 : $bulan="Februari";
			Break;
			case 3 : $bulan="Maret";
			Break;
			case 4 : $bulan="April";
			Break;
			case 5 : $bulan="Mei";
			Break;
			case 6 : $bulan="Juni";
			Break;
			case 7 : $bulan="Juli";
			Break;
			case 8 : $bulan="Agustus";
			Break;
			case 9 : $bulan="September";
			Break;
			case 10 : $bulan="Oktober";
			Break;
			case 11 : $bulan="November";
			Break;
			case 12 : $bulan="Desember";
			Break;
		}
		return $bulan;
	}

	public static function getRespApiWithParam($url, $type='get', $datapost=[]){
		$client = new \GuzzleHttp\Client(['verify' => false]);
		$userAgents = [
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
			'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36',
			'Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Mobile/15E148 Safari/604.1',
			'Mozilla/5.0 (iPad; CPU OS 16_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.4 Mobile/15E148 Safari/604.1',
			'Mozilla/5.0 (Android 12; Mobile; rv:109.0) Gecko/109.0 Firefox/109.0',
			'Mozilla/5.0 (Linux; Android 11; SM-G996B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Mobile Safari/537.36',
			'Mozilla/5.0 (Linux; U; Android 10; en-us; Nexus One Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Version/15.0 Safari/537.36'
		];
		$randomUserAgent = $userAgents[array_rand($userAgents)];

		$request = $client->$type($url, [
			'form_params' => $datapost,
			'headers' => [
				'User-Agent' => $randomUserAgent,
				'Authorization'  => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjEiLCJ1c2VybmFtZSI6ImFkbWluaXN0cmF0b3IiLCJ1c2VyX2dyb3VwIjoiYWRtaW4iLCJpYXQiOjE2NTg4MzQzMzN9.dhoLWPcm4cpXOUouX4GEMFrQBmIz5-RRaMACMUW0wxs',
				'Cookie' => 'ci_session=e40e0d7d948983435b6949a4df8efbfaf2238c4b'
			],
			'timeout'  => 30,
			'connect_timeout' => 5
		]);

		$response = $request->getBody()->getContents();
		// return $response;
		$data = json_decode($response, true);
		return $data;
	}

	public static function storeHistoryUserEvent($arr_uv_id, $status=0, $event_id_tujuan=null){
		foreach($arr_uv_id as $u => $uv_id){
			UserEventHistory::create([
				'user_event_id'   => $uv_id,
				'createdBy' 	  => \Auth::user()->id,
				'status_id'		  => $status,
				'event_id_tujuan' => $event_id_tujuan
			]);
		}		
	}
}
