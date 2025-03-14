<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Lengkap</th>
            <th>No Hp</th>
            <th>Email</th>
            <th>NIK</th>
            <th>NIP</th>
            <th>Pendidikan Terakhir</th>
            <th>Tempat Lahir</th>
            <th>Tanggal Lahir</th>
            <th>Alamat Lengkap</th>
            <th>Detail</th>
            <th>Status Kepegawaian</th>
            <th>Posisi Pelaku Pengadaan</th>
            <th>Jenis Jabatan</th>
            <th>Nama Jabatan</th>
            <th>Golongan Terakhir</th>
            <th>Tempat Kerja/Instansi</th>
            <th>Pemerintah (Kota/Kabupaten)</th>
            <th>Alamat Lengkap Kantor</th>
            <th>Sertifikat Lain Yang Dimiliki</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $d)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $d->name }}</td>
            <td>{{ optional($d->member)->no_hp ?? '-' }}</td>
            <td>{{ $d->email }}</td>
            <td>{{ $d->nik ? "'".$d->nik : '-' }}</td>
            <td>{{ $d->nip ? "'".$d->nip : '-' }}</td>
            <td>{{ optional($d->member)->pendidikan_terakhir ?? '-' }}</td>
            <td>{{ optional($d->member)->tempat_lahir ?? '-' }}</td>
            <td>{{ optional($d->member)->tgl_lahir ?? '-' }}</td>
            <td>{{ optional($d->member)->alamat_lengkap ?? '-' }}</td>
            <td>
                @php
                    $alamatDetail = [];
                    if ($prov = optional($d->member->alamatProvinsi)->nama) $alamatDetail[] = "Provinsi: $prov";
                    if ($kota = optional($d->member->alamatKota)->kota) $alamatDetail[] = "Kota: $kota";
                    if ($kecamatan = optional($d->member->alamatKecamatan)->kecamatan) $alamatDetail[] = "Kecamatan: $kecamatan";
                    if ($kelurahan = optional($d->member->alamatKelurahan)->kelurahan) $alamatDetail[] = "Kelurahan: $kelurahan";
                @endphp
                {{ implode(', ', $alamatDetail) ?: '-' }}
            </td>
            <td>{{ optional($d->member->memberKantor)->status_kepegawaian ?? '-' }}</td>
            <td>{{ optional($d->member->memberKantor)->posisi_pelaku_pengadaan ?? '-' }}</td>
            <td>{{ optional($d->member->memberKantor)->jenis_jabatan ?? '-' }}</td>
            <td>{{ optional($d->member->memberKantor)->nama_jabatan ?? '-' }}</td>
            <td>{{ optional($d->member->memberKantor)->golongan_terakhir ?? '-' }}</td>
            <td>{{ optional($d->member->memberKantor)->nama_instansi ?? '-' }}</td>
            <td>{{ optional($d->member->memberKantor)->pemerintah_instansi ?? '-' }}</td>
            <td>{{ optional($d->member->memberKantor)->alamat_kantor_lengkap ?? '-' }}</td>
            <td>
                @php
                    $sertifikatList = $d->member->sertifikatLain->map(function($sl, $i) {
                        return ($i+1).". No: {$sl->no}, Nama: {$sl->nama}, Tahun: {$sl->tahun}";
                    })->toArray();
                @endphp
                {!! implode('<br>', $sertifikatList) ?: '-' !!}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>