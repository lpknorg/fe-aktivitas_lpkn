@extends('layouts.template')
@section('breadcumb')
<div class="col-md-12 page-title">
	<div class="title_left">
		<h3>Alumni</h3>
	</div>
</div>
@endsection
@section('content')
<div class="row">
	<div class="col-md-12 col-sm-12">
		<!-- <button class="btn btn-primary btn-sm" id="btnAdd">Tambah</button> -->
		<!-- <div class="row float-right">
			<div class="col-md-12">
				<div class="form-group">
					<form action="{{url('import_member')}}" method="POST" enctype="multipart/form-data">
						@csrf
						<a href="{{route('downloadFile', ['file' =>  'template_member.xlsx', 'excel?']).rand(1, 9999)}}" class="btn btn-primary btn-sm float-right">Import Template</a>
						<label for="char">Dokumen Import</label>
						<input type="file" class="form-control" name="dok_import_member" placeholder="Dokumen Import Member" required>
						<button type="submit" class="btn btn-success btn-sm mt-2">Import Member</button>
					</form>
				</div>
			</div>
		</div> -->
		
		<div class="x_panel">
			<form action="{{route('admin.user.exportExcelAlumni')}}">
				@csrf
				<div class="row mb-2">
					<div class="col-md-4">
						<label for="">Tanggal Awal Ultah</label>
						<input type="date" name="tanggal_awal" class="form-control" placeholder="">
					</div>
					<div class="col-md-4">
						<label for="">Tanggal Akhir Ultah</label>
						<input type="date" name="tanggal_akhir" class="form-control" placeholder="">
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label>Status Kepegawaian</label>
							<?php $arr = ['PNS', 'SWASTA', 'TNI/POLRI', 'BUMN/BUMD', 'HONORER / KONTRAK', 'ASN', 'Lainnya']; ?>
							<select class="form-control" name="status_kepegawaian">
								<option value="">Pilih Status Kepegawaian</option>
								@foreach($arr as $k => $v)
								<option value="{{$v}}">{{$v}}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label>Kelulusan Event</label>
							<?php $arr = ['PBJ', 'CPOF', 'CPST', 'CPSP'] ?>;
							<select class="form-control" name="kelulusan_event">
								<option value="">Pilih Kelulusan Event</option>
								@foreach($arr as $j)
								@if($j == "PBJ")
								<option value="PENGADAAN BARANG/JASA|PEMERINTAH">{{$j}}</option>
								@else							
								<option value="{{strtolower($j)}}">{{$j}}</option>
								@endif
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label>Ketidaklulusan Event</label>
							<?php $arr = ['PBJ', 'CPOF', 'CPST', 'CPSP'] ?>;
							<select class="form-control" name="ketidaklulusan_event">
								<option value="">Pilih Ketidaklulusan Event</option>
								@foreach($arr as $j)
								@if($j == "PBJ")
								<option value="PENGADAAN BARANG/JASA|PEMERINTAH">{{$j}}</option>
								@else							
								<option value="{{strtolower($j)}}">{{$j}}</option>
								@endif
								@endforeach
							</select>
						</div>
					</div>
				</div>
				<div class="row float-right">
					<div class="col-md">
						<a href="{{route('dashboard2.exportAlumniRegis')}}" class="btn btn-outline-primary btn-sm" id="btnDownloadRegist">Download Excel Data Registrasi</a>
						<button type="submit" class="btn btn-outline-primary btn-sm">Download Excel</button>
					</div>
				</div>
			</form>
			<!-- <table class="table table-hover table-bordered table-responsive-sm" id="table-Datatable" style="width: 100%;">
				<thead>
					<tr>
						<th width="10px">No</th>
						<th>Nama Lengkap</th>
						<th>Email</th>
						<th>NIP</th>
						<th width="90px">Action</th>
					</tr>
				</thead>
				<tbody>

				</tbody>
			</table> -->
		</div>
	</div>
</div>
@include('admin.user.modal')
@endsection
@section('scripts')
<script>
	$(document).ready(function(){
		function _vall(names){
			return $(`[name=${names}]`).val()
		}
		function getStringDownloadRegist(){
			let abc = '{{url('dashboard2')}}'
			abc += `/exportAlumniRegis?kelulusan_event=${_vall('kelulusan_event')}`
			abc += `&ketidaklulusan_event=${_vall('ketidaklulusan_event')}`
			return abc
		}
		$('[name=tanggal_awal]').change(function(){
			table.draw()
		})
		$('[name=tanggal_akhir]').change(function(){
			table.draw()
		})
		$('[name=status_kepegawaian]').change(function(){
			table.draw()
		})
		$('[name=kelulusan_event]').change(function(){
			let _href = getStringDownloadRegist()
			$('#btnDownloadRegist').attr('href', _href)
			table.draw()
		})
		$('[name=ketidaklulusan_event]').change(function(){
			let _href = getStringDownloadRegist()
			$('#btnDownloadRegist').attr('href', _href)
			table.draw()
		})
		@if(\Session::has('success_import_member'))
		showAlert('{{\Session::get('success_import_member')}}')
		@endif
		$('body').on('click', '[id="btnAdd"]', function(e){
			showModal2('add')
		})
		$('body').on('click', '[id^="btnShow"]', function(e){
			e.preventDefault()
			$.ajax({
				type: 'get',
				url: $(this).attr("href"),
				success: function(data) {
					showModal2('show', data)
				},
				error: function(data) {
					showAlert("Ada kesalahan dalam melihat data User", "error")
				}
			});
		})
		$('body').on('click', '[id^="btnEdit"]', function(e){
			e.preventDefault()
			$.ajax({
				type: 'get',
				url: $(this).attr("href"),
				success: function(data) {
					showModal2('edit', data)
				},
				error: function(data) {
					showAlert("Ada kesalahan dalam melihat data User", "error")
				}
			});
		})
		$('body').on('click', '[id="btnHapus"]', function(e){
			Swal.fire({
				title: 'Apa anda yakin ?',
				text: "Data akan hilang jika dihapus!",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Ya, Hapus!'
			}).then((result) => {
				if (result.isConfirmed) {
					$.ajaxSetup({
						headers: {
							'X-CSRF-TOKEN': $('[name=_token]').val()
						}
					});

					$.ajax({
						type: 'DELETE',
						url: $(this).attr("action"),
						data: {
							id: $(this).attr('data-id')
						},
						dataType: 'json',
						success: function(data) {
							if (data.status == "ok") {
								Swal.fire('Berhasil',data.messages,'success')
								table.ajax.reload()
							}
						},
						error: function(data) {
							var data = data.responseJSON;
							if (data.status == "fail") {
								alertShow(data.messages, "error")
							}
						}
					});
				}
			})
		})
		$('#modalAdd form').submit(function(e) {
			e.preventDefault();
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('[name=_token]').val()
				}
			});

			var form_data = new FormData($(this)[0]);
			// form_data.append('photo', $('[name=photo]').prop('files')[0]);

			$.ajax({
				type: 'post',
				url: $(this).attr("action"),
				data: form_data,
				dataType: 'json',
				processData: false,
				contentType: false,
				beforeSend: function() {
					sendAjax('#btnSimpan', false)
				},
				success: function(data) {
					if (data.status == "ok") {
						showAlert(data.messages)
						setTimeout(function() {
							$('#modalAdd').modal('hide')
							$('#modalAdd input:not([name=_token]), #modalAdd textarea').val('')
						}, 1000);
						table.ajax.reload()
					}
				},
				error: function(data) {
					var data = data.responseJSON;
					if (data.status == "fail") {
						showAlert(data.messages, "error")
					}
				},
				complete: function() {
					sendAjax('#btnSimpan', true, 'Simpan')
				}
			});
		});
		$('#modalShow form').submit(function(e) {
			e.preventDefault();
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('[name=_token]').val()
				}
			});

			var form_data = new FormData($(this)[0]);
			form_data.append('_method', 'PATCH')
			// form_data.append('photo', $('[name=photo]').prop('files')[0]);

			$.ajax({
				type: 'post',
				url: $(this).attr("action"),
				data: form_data,
				dataType: 'json',
				processData: false,
				contentType: false,
				beforeSend: function() {
					sendAjax('#btnUpdate', false)
				},
				success: function(data) {
					console.log(data)
					if (data.status == "ok") {
						showAlert(data.messages)
						setTimeout(function() {
							$('#modalShow').modal('hide')
							$('#modalShow input:not([name=_token]), #modalShow textarea').val('')
						}, 1000);
						table.ajax.reload()
					}
				},
				error: function(data) {
					var data = data.responseJSON;
					if (data.status == "fail") {
						showAlert(data.messages, "error")
					}
				},
				complete: function() {
					sendAjax('#btnUpdate', true, 'Update')
				}
			});
		});

		function showModal2(act, data=null){
			console.log(data)
			if(act == 'show' || act == 'edit'){
				$('#modalShow').modal('show')
				$('#modalShow #modalResponseShow').html(data)
			}
			if (act == 'add') {
				$('#modalAdd').modal('show')
			}else if (act == 'show') {
				$('#modalShow .modal-title').text('Lihat Data Members')
				$('#modalShow #btnUpdate').hide()
				$('#modalShow input, #modalShow textarea, #modalShow select').attr('disabled', true)
			}else{
				$('#modalShow .modal-title').text('Ubah Data Member')
				$('#modalShow #btnUpdate').show()
				$('#modalShow input, #modalShow textarea, #modalShow select').attr('disabled', false)
			}
		}

		// var table = $('#table-Datatable').DataTable({
			// processing: true,
			// serverSide: true,
			// ajax: {
				// "url" : "{{ route('admin.user.dataTables') }}",
				// data: function(d){
					// d.tanggal_awal = $('[name=tanggal_awal]').val()
					// d.tanggal_akhir = $('[name=tanggal_akhir]').val()
					// d.status_kepegawaian = $('[name=status_kepegawaian]').find(":selected").val()
					// d.kelulusan_event = $('[name=kelulusan_event]').find(":selected").val()
					// d.ketidaklulusan_event = $('[name=ketidaklulusan_event]').find(":selected").val()
				// }
			// },
			// columns: [
				// {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
				// {data: 'email_', 'name': 'name'},
				// {data: 'email', name: 'users.email'},
				// {data: 'nip', name: 'nip'},
				// {
					// data: 'action',
					// name: 'action',
					// orderable: true,
					// searchable: false
				// },
				// ]
		// });
	})
</script>
@endsection
